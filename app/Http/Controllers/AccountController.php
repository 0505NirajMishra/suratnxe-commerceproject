<?php
namespace App\Http\Controllers;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Services\CartService;
use App\Services\OrderService;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load([
            'orders.items.product.images',
            'orders.timeline',
            'addresses' => fn ($query) => $query->orderByDesc('is_default')->latest(),
            'wishlist',
        ]);
        $reviewedProductIds = ProductReview::query()
            ->where('user_id', $user->id)
            ->pluck('product_id')
            ->map(fn ($productId) => (int) $productId)
            ->all();

        return view('store.account', compact('user', 'reviewedProductIds'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Male,Female,Other',
            'dob' => 'nullable|date',
            'email' => 'required|email|max:190|unique:users,email,' . $user->id,
            'profile_photo' => 'nullable|image',
        ]);
        $photo = $data['profile_photo'] ?? null;
        $previousPhoto = null;
        unset($data['profile_photo']);

        if ($photo) {
            $previousPhoto = $user->profile_photo_path;
            $photoPath = $photo->store('profile-photos', 'public');
            if (!$photoPath) {
                return back()->withErrors(['profile_photo' => 'Unable to save the profile picture. Please try again.'])->withInput();
            }
            $data['profile_photo_path'] = $photoPath;
        }

        $user->update($data);

        if (!empty($previousPhoto)) {
            Storage::disk('public')->delete($previousPhoto);
        }

        return back()->with('success', 'Profile updated.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        if (!Hash::check($data['current_password'], auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        auth()->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password changed.');
    }

    public function storeAddress(Request $request)
    {
        $data = $this->validateAddress($request);
        $user = $request->user();
        $makeDefault = (bool) ($data['is_default'] ?? false) || !$user->addresses()->exists();
        unset($data['is_default']);

        if ($makeDefault) {
            $user->addresses()->update(['is_default' => false]);
        }
        $addressData = $data + ['type' => $request->input('type', 'Home')];
        $existingAddress = $user->addresses()->where($addressData)->first();
        if ($existingAddress) {
            if ($makeDefault) {
                $existingAddress->update(['is_default' => true]);
            }
        } else {
            $user->addresses()->create($addressData + ['is_default' => $makeDefault]);
        }

        return back()->with('success', 'Address saved.');
    }

    public function updateAddress(Request $request, Address $address)
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);
        $data = $this->validateAddress($request);
        $makeDefault = (bool) ($data['is_default'] ?? false);
        $wasDefault = (bool) $address->is_default;
        unset($data['is_default']);

        if ($makeDefault) {
            $request->user()->addresses()->update(['is_default' => false]);
        }
        $address->update($data + ['type' => $request->input('type', 'Home'), 'is_default' => $makeDefault]);
        if (!$makeDefault && $wasDefault) {
            $request->user()->addresses()->where('id', '<>', $address->id)->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address updated.');
    }

    public function deleteAddress(Request $request, Address $address)
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address deleted.');
    }

    public function reorder(Request $request, Order $order, CartService $cart)
    {
        $order = $request->user()->orders()->whereKey($order->id)->with('items')->firstOrFail();
        $quantities = $order->items->groupBy('product_id')->map(fn ($items) => $items->sum('qty'));
        $cartQuantities = $cart->raw();

        DB::transaction(function () use ($quantities, $cartQuantities, $cart) {
            $products = Product::active()->whereIn('id', $quantities->keys())->lockForUpdate()->get()->keyBy('id');

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                if (!$product || $product->stock < ($quantity + (int) ($cartQuantities[$productId] ?? 0))) {
                    throw ValidationException::withMessages([
                        'order' => 'One or more fabrics from this order are unavailable in the requested quantity.',
                    ]);
                }
            }

            foreach ($quantities as $productId => $quantity) {
                if (!$cart->add((int) $productId, (int) $quantity)) {
                    throw ValidationException::withMessages(['order' => 'A fabric from this order is no longer available.']);
                }
            }
        });

        return redirect()->route('cart')->with('success', "Items from order {$order->order_no} added to cart.");
    }

    public function cancelOrder(Request $request, Order $order, OrderService $orders)
    {
        $order = $request->user()->orders()->whereKey($order->id)->firstOrFail();
        if (!in_array($order->status, ['Pending', 'Confirmed', 'Processing'], true) || $order->payment_status === 'Paid') {
            return back()->withErrors(['order' => 'This order can no longer be cancelled online. Please contact us for help.']);
        }

        $orders->setStatus($order, 'Cancelled');

        return back()->with('success', "Order {$order->order_no} cancelled.");
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'phone' => 'required|string|max:20',
            'line1' => 'required|string|max:190',
            'line2' => 'nullable|string|max:190',
            'landmark' => 'nullable|string|max:190',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pin' => 'required|string|max:10',
            'type' => 'required|in:Home,Work,Other',
            'is_default' => 'nullable|boolean',
        ]);
    }
}
