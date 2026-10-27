<?php

namespace App\Services;

use App\Mail\OrderPlacedMail;
use App\Mail\StoreNotice;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class StoreMailer
{
    public function enquiry(Enquiry $enquiry): void
    {
        $details = ['Name' => $enquiry->name, 'Email' => $enquiry->email, 'Phone' => $enquiry->phone, 'Subject' => $enquiry->subject];
        $this->send(Setting::get('email'), new StoreNotice('New website enquiry', $enquiry->message, $details));
        $this->send($enquiry->email, new StoreNotice('We received your enquiry', 'Thank you for contacting Surat NX. Our team will get back to you soon.', ['Reference' => '#'.$enquiry->id, 'Subject' => $enquiry->subject]));
    }

    public function subscriber(NewsletterSubscriber $subscriber): void
    {
        $this->send($subscriber->email, new StoreNotice('You are subscribed to Surat NX', 'Thank you for subscribing. We will share collection and store updates with you.'));
    }

    public function orderCreated(Order $order): void
    {
        $address = $order->address ?? [];
        $orderDetails = [
            'number' => $order->order_no,
            'date' => $order->created_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A'),
            'status' => $order->status,
            'payment' => $order->payment_method.' - '.$order->payment_status,
            'customer' => $order->name,
            'email' => $order->email,
            'phone' => $order->phone,
            'address' => implode(', ', array_filter([
                $address['line1'] ?? null,
                $address['line2'] ?? null,
                $address['landmark'] ?? null,
                $address['city'] ?? null,
                $address['state'] ?? null,
                $address['pin'] ?? null,
            ])),
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'coupon' => $order->coupon_code,
            'shipping' => (float) $order->shipping,
            'tax' => (float) $order->tax,
            'total' => (float) $order->total,
        ];
        $items = $order->items->map(fn ($item) => [
            'name' => $item->name,
            'sku' => $item->sku,
            'qty' => (int) $item->qty,
            'price' => (float) $item->price,
            'total' => (float) $item->price * (int) $item->qty,
            'image' => $item->image ? url('storage/'.$item->image) : null,
        ])->all();

        $this->send($order->email, new OrderPlacedMail($orderDetails, $items), Setting::get('email'));
    }

    public function paymentConfirmed(Order $order): void
    {
        $this->send($order->email, new StoreNotice('Payment confirmed for '.$order->order_no, 'We have confirmed your payment. We will update you as your order is processed.', ['Order' => $order->order_no, 'Transaction' => $order->txn_id, 'Total' => 'INR '.number_format((float) $order->total, 2)]));
    }

    public function orderStatusUpdated(Order $order): void
    {
        $details = ['Order' => $order->order_no, 'Status' => $order->status];
        if ($order->courier) {
            $details['Courier'] = $order->courier;
        }
        if ($order->tracking_no) {
            $details['Tracking number'] = $order->tracking_no;
        }

        $this->send($order->email, new StoreNotice('Order '.$order->order_no.' update', 'Your order status has changed.', $details));
    }

    public function returnRequested(\App\Models\ReturnRequest $returnRequest): void
    {
        $products = $returnRequest->orderItem
            ? $returnRequest->orderItem->name.' (SKU '.$returnRequest->orderItem->sku.', '.$returnRequest->orderItem->qty.' m)'
            : $returnRequest->order->items->map(fn ($item) => $item->name.' ('.$item->qty.' m)')->implode(', ');
        $details = [
            'Order' => $returnRequest->order->order_no,
            'Customer' => $returnRequest->order->name,
            'Email' => $returnRequest->order->email,
            'Phone' => $returnRequest->order->phone,
            'Products' => $products,
            'Reason' => $returnRequest->reason,
            'Evidence photos' => count($returnRequest->photo_paths ?? []),
        ];
        $this->send(Setting::get('email'), new StoreNotice('Return request for '.$returnRequest->order->order_no, $returnRequest->details, $details));
        $this->send($returnRequest->user->email, new StoreNotice('Return request received', 'Our team will review your request and update you.', $details));
    }

    public function returnStatusUpdated(\App\Models\ReturnRequest $returnRequest): void
    {
        $products = $returnRequest->orderItem
            ? $returnRequest->orderItem->name.' (SKU '.$returnRequest->orderItem->sku.', '.$returnRequest->orderItem->qty.' m)'
            : $returnRequest->order->items->map(fn ($item) => $item->name.' ('.$item->qty.' m)')->implode(', ');
        $this->send($returnRequest->user->email, new StoreNotice(
            'Return request update for '.$returnRequest->order->order_no,
            $returnRequest->customerStatusMessage(),
            [
                'Order' => $returnRequest->order->order_no,
                'Product' => $products,
                'Return status' => $returnRequest->status,
                'Store response' => $returnRequest->admin_note ?: 'No additional note from the store.',
            ]
        ));
    }

    private function send(?string $recipient, Mailable $mail, ?string $bcc = null): void
    {
        if (!$recipient || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            $message = Mail::to($recipient);
            if ($bcc && filter_var($bcc, FILTER_VALIDATE_EMAIL) && strcasecmp($recipient, $bcc) !== 0) {
                $message->bcc($bcc);
            }
            $message->queue($mail);
        } catch (Throwable $exception) {
            Log::error('Store email delivery failed.', ['recipient' => $recipient, 'subject' => $mail->envelope()->subject, 'error' => $exception->getMessage()]);
        }
    }
}