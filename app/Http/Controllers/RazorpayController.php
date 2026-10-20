<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RazorpayController extends Controller
{
    public function create(Request $request, CartService $cart, OrderService $orders, RazorpayService $razorpay)
    {
        abort_unless($razorpay->isConfigured(), 503, 'Online payments are not configured.');

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'required|string|max:20',
            'line1' => 'required|string|max:190',
            'line2' => 'nullable|string|max:190',
            'landmark' => 'nullable|string|max:190',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pin' => 'required|string|max:10',
            'express' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $totals = $cart->totals((bool) ($data['express'] ?? false));
        if ($totals['items']->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        $amountPaise = (int) round($totals['total'] * 100);
        if ($amountPaise < 100) {
            return response()->json(['message' => 'Online payments require a minimum order total of INR 1.'], 422);
        }

        try {
            $gatewayOrder = $razorpay->createOrder($amountPaise, 'SNX-'.Str::upper(Str::random(16)));
        } catch (Throwable $exception) {
            Log::error('Razorpay order creation failed.', ['error' => $exception->getMessage()]);
            return response()->json(['message' => 'Online payment is temporarily unavailable. Please choose Cash on Delivery or try again later.'], 503);
        }

        $address = collect($data)->only(['name', 'phone', 'line1', 'line2', 'landmark', 'city', 'state', 'pin'])->all();
        $user = ['id' => auth()->id(), 'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone']];
        $order = $orders->place($user, $address, 'Razorpay', (bool) ($data['express'] ?? false), $data['notes'] ?? null, $gatewayOrder['id'], $amountPaise);
        $request->session()->put('razorpay_orders.'.$gatewayOrder['id'], $order->order_no);

        return response()->json([
            'key' => config('services.razorpay.key'),
            'amount' => $amountPaise,
            'currency' => 'INR',
            'razorpayOrderId' => $gatewayOrder['id'],
            'name' => Setting::get('name'),
            'description' => 'Order '.$order->order_no,
            'prefill' => ['name' => $data['name'], 'email' => $data['email'], 'contact' => $data['phone']],
        ]);
    }

    public function verify(Request $request, RazorpayService $razorpay, OrderService $orders)
    {
        $data = $request->validate([
            'razorpay_order_id' => 'required|string|max:80',
            'razorpay_payment_id' => 'required|string|max:80',
            'razorpay_signature' => 'required|string|max:190',
        ]);

        $orderNo = $request->session()->get('razorpay_orders.'.$data['razorpay_order_id']);
        $order = Order::where('order_no', $orderNo)->where('gateway_order_id', $data['razorpay_order_id'])->firstOrFail();
        abort_if(auth()->check() && $order->user_id !== auth()->id(), 403);

        $verified = $razorpay->verifyCapturedPayment(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
            (int) round((float) $order->total * 100),
        );

        if (!$verified) {
            return response()->json(['message' => 'Payment could not be verified as captured. Please contact support before retrying.'], 422);
        }

        $orders->markPaid($order, $data['razorpay_payment_id']);
        $request->session()->forget('razorpay_orders.'.$data['razorpay_order_id']);
        $request->session()->put('completed_order_no', $order->order_no);

        return response()->json(['redirect' => route('order.success', $order)]);
    }

    public function webhook(Request $request, OrderService $orders)
    {
        $secret = config('services.razorpay.webhook_secret');
        abort_unless(filled($secret), 503, 'Payment webhook is not configured.');

        $signature = (string) $request->header('X-Razorpay-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        abort_unless($signature !== '' && hash_equals($expected, $signature), 403);

        if ($request->input('event') !== 'payment.captured') {
            return response()->noContent();
        }

        $payment = $request->input('payload.payment.entity');
        if (!is_array($payment) || empty($payment['order_id']) || empty($payment['id'])) {
            return response()->json(['message' => 'Invalid payment event.'], 400);
        }

        $order = Order::where('gateway_order_id', $payment['order_id'])->first();
        if (!$order) {
            return response()->json(['message' => 'Order is not available yet.'], 503);
        }
        if (($payment['currency'] ?? null) !== 'INR' || (int) ($payment['amount'] ?? 0) !== (int) round((float) $order->total * 100)) {
            return response()->json(['message' => 'Payment amount does not match the order.'], 422);
        }

        $orders->markPaid($order, $payment['id']);

        return response()->noContent();
    }
}