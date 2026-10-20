<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest as CustomerReturnRequest;
use App\Models\Setting;
use App\Services\StoreMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReturnRequestController extends Controller
{
    private const APPROVED_PICKUP_NOTE = 'Your return request is approved. Please pack the product securely and keep it ready. Our pickup agent is expected to collect it within 3-4 days.';

    public function index()
    {
        $orders = auth()->user()->orders()
            ->where('status', 'Delivered')
            ->with(['items', 'timeline', 'returnRequest.orderItem'])
            ->get();

        $returnWindowDays = (int) Setting::get('return_window_days', 7);

        return view('store.returns', compact('orders', 'returnWindowDays'));
    }

    public function store(Request $request, Order $order, StoreMailer $mailer)
    {
        $data = $request->validate([
            'order_item_id' => 'required|integer',
            'reason' => ['required', Rule::in(['Damaged', 'Wrong item', 'Quality issue', 'Other'])],
            'details' => 'required|string|max:2000',
            'photos' => 'required|array|min:1|max:5',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $order = $request->user()->orders()->whereKey($order->id)->where('status', 'Delivered')->firstOrFail();
        if (!$order->items()->whereKey($data['order_item_id'])->exists()) {
            return back()->withErrors(['order_item_id' => 'Choose a product from this delivered order.'])->withInput();
        }
        $returnWindowDays = (int) Setting::get('return_window_days', 7);
        $deliveredRecently = $order->timeline()->where('status', 'Delivered')->where('created_at', '>=', now()->subDays($returnWindowDays))->exists();

        if (!$deliveredRecently) {
            return back()->withErrors(['return' => "Return requests must be submitted within {$returnWindowDays} days of delivery."]);
        }
        if ($order->returnRequest()->exists()) {
            return back()->withErrors(['return' => 'A return request already exists for this order.']);
        }

        $returnRequest = $order->returnRequest()->create([
            'order_item_id' => $data['order_item_id'],
            'user_id' => $request->user()->id,
            'reason' => $data['reason'],
            'details' => $data['details'],
        ]);
        $photoPaths = [];
        foreach ($request->file('photos', []) as $photo) {
            $path = $photo->store('returns/'.$returnRequest->id, 'local');
            if (!$path) {
                Storage::disk('local')->delete($photoPaths);
                $returnRequest->delete();

                return back()->withErrors(['photos' => 'Unable to save the return photos. Please try again.'])->withInput();
            }
            $photoPaths[] = $path;
        }
        $returnRequest->update(['photo_paths' => $photoPaths]);
        $mailer->returnRequested($returnRequest->load(['order.items', 'orderItem', 'user']));

        return redirect()->route('returns.index')->with('success', 'Your return request has been submitted for review.');
    }

    public function adminIndex()
    {
        $returnRequests = CustomerReturnRequest::with(['order.items', 'user'])->latest()->paginate(20);

        return view('admin.returns', compact('returnRequests'));
    }

    public function adminPhoto(CustomerReturnRequest $returnRequest, int $photo)
    {
        return $this->photoResponse($returnRequest, $photo);
    }

    public function customerPhoto(Request $request, CustomerReturnRequest $returnRequest, int $photo)
    {
        abort_unless((int) $returnRequest->user_id === (int) $request->user()->id, 404);

        return $this->photoResponse($returnRequest, $photo);
    }

    private function photoResponse(CustomerReturnRequest $returnRequest, int $photo)
    {
        $path = $returnRequest->photo_paths[$photo] ?? null;
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function adminUpdate(Request $request, CustomerReturnRequest $returnRequest, StoreMailer $mailer)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Requested', 'Approved', 'Rejected', 'Received', 'Resolved'])],
            'admin_note' => 'nullable|string|max:'.($request->input('status') === 'Approved' ? '1800' : '2000'),
        ]);

        $adminNote = trim($data['admin_note'] ?? '');
        if ($data['status'] === 'Approved' && !str_contains($adminNote, self::APPROVED_PICKUP_NOTE)) {
            $adminNote = trim($adminNote."\n".self::APPROVED_PICKUP_NOTE);
        }
        $data['admin_note'] = $adminNote !== '' ? $adminNote : null;

        $returnRequest->update($data);
        $mailer->returnStatusUpdated($returnRequest->fresh(['order.items', 'orderItem', 'user']));

        return back()->with('success', 'Return request updated.');
    }

    public function saveReturnWindow(Request $request)
    {
        $data = $request->validate(['return_window_days' => 'required|integer|min:1|max:60']);
        Setting::put($data);

        return back()->with('success', 'Return window updated.');
    }
}