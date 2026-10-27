<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayUService
{
    public function isConfigured(): bool
    {
        return filled(config('services.payu.key'))
            && filled(config('services.payu.salt'))
            && filled($this->paymentUrl())
            && filled(config('services.payu.verify_url'))
            && in_array(config('services.payu.mode'), ['test', 'live'], true);
    }

    public function paymentUrl(): string
    {
        return (string) config('services.payu.payment_url');
    }

    public function paymentData(Order $order): array
    {
        $this->ensureConfigured();

        $amount = number_format((float) $order->total, 2, '.', '');
        $name = trim((string) $order->name);
        $fields = [
            'key' => config('services.payu.key'),
            'txnid' => $order->gateway_order_id,
            'amount' => $amount,
            'productinfo' => $order->order_no,
            'firstname' => $name,
            'email' => $order->email,
            'phone' => $order->phone,
            'address1' => (string) ($order->address['line1'] ?? ''),
            'address2' => (string) ($order->address['line2'] ?? ''),
            'city' => (string) ($order->address['city'] ?? ''),
            'state' => (string) ($order->address['state'] ?? ''),
            'country' => 'India',
            'zipcode' => (string) ($order->address['pin'] ?? ''),
            'surl' => route('payments.payu.callback'),
            'furl' => route('payments.payu.callback'),
            'service_provider' => 'payu',
            'udf1' => $order->order_no,
            'udf2' => '',
            'udf3' => '',
            'udf4' => '',
            'udf5' => '',
        ];

        $hashFields = [
            $fields['key'],
            $fields['txnid'],
            $fields['amount'],
            $fields['productinfo'],
            $fields['firstname'],
            $fields['email'],
            $fields['udf1'],
            $fields['udf2'],
            $fields['udf3'],
            $fields['udf4'],
            $fields['udf5'],
        ];
        $fields['hash'] = hash('sha512', implode('|', $hashFields).'||||||'.config('services.payu.salt'));

        return $fields;
    }

    public function verifyResponseHash(array $response): bool
    {
        $this->ensureConfigured();
        $receivedHash = strtolower((string) ($response['hash'] ?? ''));
        if ($receivedHash === '') {
            return false;
        }

        $hashString = (string) config('services.payu.salt').'|'.(string) ($response['status'] ?? '')
            .'||||||'.(string) ($response['udf5'] ?? '')
            .'|'.(string) ($response['udf4'] ?? '')
            .'|'.(string) ($response['udf3'] ?? '')
            .'|'.(string) ($response['udf2'] ?? '')
            .'|'.(string) ($response['udf1'] ?? '')
            .'|'.(string) ($response['email'] ?? '')
            .'|'.(string) ($response['firstname'] ?? '')
            .'|'.(string) ($response['productinfo'] ?? '')
            .'|'.(string) ($response['amount'] ?? '')
            .'|'.(string) ($response['txnid'] ?? '')
            .'|'.(string) ($response['key'] ?? '');

        $additionalCharges = $response['additionalCharges'] ?? $response['additional_charges'] ?? null;
        if (filled($additionalCharges)) {
            $hashString = (string) $additionalCharges.'|'.$hashString;
        }

        return hash_equals(hash('sha512', $hashString), $receivedHash);
    }

    public function verifyTransaction(string $txnid): array
    {
        $this->ensureConfigured();

        $command = 'verify_payment';
        $hash = hash('sha512', (string) config('services.payu.key').'|'.$command.'|'.$txnid.'|'.(string) config('services.payu.salt'));
        $response = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post((string) config('services.payu.verify_url'), [
                'key' => config('services.payu.key'),
                'command' => $command,
                'var1' => $txnid,
                'hash' => $hash,
            ]);

        if (!$response->successful() || (int) $response->json('status') !== 1) {
            throw new RuntimeException('PayU could not verify this transaction.');
        }

        $transaction = $response->json('transaction_details.'.$txnid);
        if (!is_array($transaction) || ($transaction['txnid'] ?? null) !== $txnid) {
            throw new RuntimeException('PayU returned no matching transaction.');
        }

        return $transaction;
    }

    public function amountInPaise(mixed $amount): ?int
    {
        if (!is_string($amount) && !is_int($amount) && !is_float($amount)) {
            return null;
        }

        $amount = (string) $amount;
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            return null;
        }

        return (int) round((float) $amount * 100);
    }

    private function ensureConfigured(): void
    {
        if (!$this->isConfigured() || !filled($this->paymentUrl()) || !filled(config('services.payu.verify_url'))) {
            throw new RuntimeException('PayU credentials are not configured.');
        }
    }
}
