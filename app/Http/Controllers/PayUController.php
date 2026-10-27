<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\PayUService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PayUController extends Controller
{
    public function callback(Request $request, PayUService $payu, OrderService $orders)
    {
        $result = $this->process($request, $payu, $orders);
        $request->session()->put('completed_order_no', $result['order']->order_no);

        return redirect()->temporarySignedRoute(
            'payments.result',
            now()->addMinutes(30),
            ['order' => $result['order']->order_no]
        );
    }

    public function webhook(Request $request, PayUService $payu, OrderService $orders)
    {
        $this->process($request, $payu, $orders);

        return response()->noContent();
    }

    public function result(Request $request, Order $order)
    {
        abort_unless($request->hasValidSignature(), 403);

        return view('store.order-success', compact('order'));
    }

    private function process(Request $request, PayUService $payu, OrderService $orders): array
    {
        $data = $request->all();
        abort_unless($payu->verifyResponseHash($data), 403, 'Invalid PayU response signature.');

        $txnid = (string) ($data['txnid'] ?? '');
        $order = Order::where('payment_method', 'PayU')
            ->where('gateway_order_id', $txnid)
            ->first();

        if (!$order) {
            Log::warning('PayU notified for an unknown transaction.', ['txnid' => $txnid]);
            abort(503, 'The order for this payment is not available yet.');
        }

        $expectedAmount = (int) round((float) $order->total * 100);
        if (
            ($data['key'] ?? null) !== config('services.payu.key')
            || ($data['productinfo'] ?? null) !== $order->order_no
            || $payu->amountInPaise($data['amount'] ?? null) !== $expectedAmount
        ) {
            Log::warning('PayU callback does not match the stored order.', ['order' => $order->order_no, 'txnid' => $txnid]);
            abort(422, 'Payment details do not match the order.');
        }

        try {
            $transaction = $payu->verifyTransaction($txnid);
        } catch (Throwable $exception) {
            Log::error('PayU transaction verification failed.', ['order' => $order->order_no, 'txnid' => $txnid, 'error' => $exception->getMessage()]);
            abort(503, 'Payment status could not be confirmed. Please wait while PayU retries the notification.');
        }

        if ($payu->amountInPaise($transaction['amt'] ?? null) !== $expectedAmount) {
            Log::warning('PayU verified amount does not match the stored order.', ['order' => $order->order_no, 'txnid' => $txnid]);
            abort(422, 'Verified payment amount does not match the order.');
        }

        $status = strtolower((string) ($transaction['status'] ?? ''));
        if ($status === 'success' && filled($transaction['mihpayid'] ?? null)) {
            $orders->markPaid($order, (string) $transaction['mihpayid']);
            return ['order' => $order];
        }

        if (in_array($status, ['failure', 'failed', 'usercancelled', 'cancelled', 'dropped'], true)) {
            $orders->markPaymentFailed($order);
            return ['order' => $order];
        }

        return ['order' => $order];
    }
}
