<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayService
{
    public function isConfigured(): bool
    {
        return filled(config('services.razorpay.key'))
            && filled(config('services.razorpay.secret'))
            && filled(config('services.razorpay.webhook_secret'));
    }

    public function createOrder(int $amountPaise, string $receipt): array
    {
        $this->ensureConfigured();

        $response = Http::withBasicAuth(config('services.razorpay.key'), config('services.razorpay.secret'))
            ->acceptJson()
            ->timeout(15)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amountPaise,
                'currency' => 'INR',
                'receipt' => $receipt,
            ]);

        if (!$response->successful() || !$response->json('id')) {
            throw new RuntimeException('Razorpay could not create a payment order.');
        }

        return $response->json();
    }

    public function verifyCapturedPayment(string $orderId, string $paymentId, string $signature, int $expectedAmountPaise): bool
    {
        $this->ensureConfigured();
        $expectedSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, config('services.razorpay.secret'));

        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        $response = Http::withBasicAuth(config('services.razorpay.key'), config('services.razorpay.secret'))
            ->acceptJson()
            ->timeout(15)
            ->get('https://api.razorpay.com/v1/payments/'.rawurlencode($paymentId));

        if (!$response->successful()) {
            return false;
        }

        $payment = $response->json();
        if (($payment['order_id'] ?? null) !== $orderId || (int) ($payment['amount'] ?? 0) !== $expectedAmountPaise) {
            return false;
        }

        if (($payment['status'] ?? null) === 'authorized') {
            $capture = Http::withBasicAuth(config('services.razorpay.key'), config('services.razorpay.secret'))
                ->acceptJson()
                ->timeout(15)
                ->post('https://api.razorpay.com/v1/payments/'.rawurlencode($paymentId).'/capture', [
                    'amount' => $expectedAmountPaise,
                    'currency' => 'INR',
                ]);

            if (!$capture->successful()) {
                return false;
            }

            $payment = $capture->json();
        }

        return ($payment['order_id'] ?? null) === $orderId
            && ($payment['status'] ?? null) === 'captured'
            && (int) ($payment['amount'] ?? 0) === $expectedAmountPaise;
    }

    private function ensureConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Razorpay credentials are not configured.');
        }
    }
}