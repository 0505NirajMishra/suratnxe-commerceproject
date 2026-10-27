<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cart)
    {
        $totals = $cart->totals();

        return view('store.cart', compact('totals'));
    }

    public function add(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'nullable|integer|min:1|max:1000',
            'buy_now' => 'nullable|boolean',
        ]);
        $product = Product::active()->find($data['product_id']);
        $quantity = (int) ($data['qty'] ?? 1);

        if (!$product || !$cart->add((int) $data['product_id'], $quantity)) {
            return back()->withErrors(['cart' => 'This fabric is unavailable or out of stock.']);
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout')->with('success', "{$quantity} m of {$product->name} added to cart.");
        }

        return back()->with('success', "{$quantity} m of {$product->name} added to cart.");
    }

    public function update(Request $request, CartService $cart)
    {
        $data = $request->validate(['qty' => 'required|array', 'qty.*' => 'required|integer|min:1|max:1000']);
        foreach ($data['qty'] as $id => $quantity) {
            $cart->set((int) $id, (int) $quantity);
        }

        if ($request->expectsJson()) {
            $totals = $cart->totals();

            return response()->json([
                'items' => $totals['items']->map(fn ($item) => [
                    'id' => $item['product']->id,
                    'qty' => $item['qty'],
                    'lineTotal' => (float) $item['product']->price * $item['qty'],
                ])->values(),
                'subtotal' => (float) $totals['sub'],
                'discount' => (float) $totals['discount'],
                'coupon' => $totals['coupon'],
                'couponCodes' => $totals['couponCodes'],
                'couponAvailable' => $totals['couponAvailable'],
                'shipping' => (float) $totals['ship'],
                'taxRate' => (float) $totals['taxRate'],
                'tax' => (float) $totals['tax'],
                'total' => (float) $totals['total'],
                'meters' => (int) $totals['meters'],
                'freeShippingLimit' => (float) \App\Models\Setting::get('free_ship'),
                'couponError' => $totals['couponErr'],
            ]);
        }

        return redirect()->route('cart')->with('success', 'Cart updated.');
    }

    public function remove(Request $request, CartService $cart, Product $product)
    {
        $cart->remove($product->id);

        return redirect()->route('cart')->with('success', 'Item removed.');
    }

    // public function coupon(Request $request, CartService $cart)
    // {
    //     $data = $request->validate(['code' => 'nullable|string|max:50']);
    //     $cart->applyCoupon($data['code'] ?? null);
    //     $totals = $cart->totals();

    //     if ($totals['couponErr']) {
    //         return back()->withErrors(['code' => $totals['couponErr']]);
    //     }

    //     return back()->with('success', 'Coupon applied.');
    // }
    
    public function coupon(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
        ]);

        $cart->applyCoupon($data['code'] ?? null);

        $totals = $cart->totals();

        if ($totals['couponErr']) {
            return back()->withErrors([
                'code' => $totals['couponErr'],
            ])->withInput();
        }

        return back()->with('success', 'Coupon applied.');
    }


}
