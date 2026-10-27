<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\StoreMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** Port of the theme's O (orders) object, but transactional and server-trusted. */
class OrderService
{
    public function __construct(private CartService $cart) {}

    /**
     * Create an order from the current cart. Prices/stock are re-read from the DB
     * inside a transaction, so the browser can never tamper with them.
     */
    public function place(array $user, array $address, string $method, bool $express = false, ?string $notes = null, ?string $gatewayOrderId = null, ?int $expectedAmountPaise = null): Order
    {
        $customer = Auth::guard('web')->user();
        if (!$customer || $customer->isAdmin() || (int) ($user['id'] ?? 0) !== $customer->id || !User::whereKey($customer->id)->where('role', 'customer')->exists()) {
            throw ValidationException::withMessages(['auth' => 'Please sign in with a customer account before placing an order.']);
        }

        $order = DB::transaction(function () use ($user, $address, $method, $express, $notes, $gatewayOrderId, $expectedAmountPaise) {
            $t = $this->cart->totals($express);
            if ($t['items']->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }
            if ($expectedAmountPaise !== null && (int) round($t['total'] * 100) !== $expectedAmountPaise) {
                throw ValidationException::withMessages(['cart' => 'Your cart total changed. Please review the updated total and try again.']);
            }

            // lock rows & verify stock
            $locked = Product::whereIn('id', $t['items']->pluck('product.id'))->lockForUpdate()->get()->keyBy('id');
            foreach ($t['items'] as $i) {
                $p = $locked[$i['product']->id];
                if ($p->stock < $i['qty']) {
                    throw ValidationException::withMessages(['cart' => "Only {$p->stock} m of {$p->name} left in stock."]);
                }
            }

            $order = Order::create([
                'order_no' => $this->nextOrderNo(),
                'user_id' => $user['id'] ?? null,
                'name' => $user['name'], 'email' => $user['email'],
                'phone' => $address['phone'] ?? $user['phone'],
                'address' => $address,
                'payment_method' => $method,
                'gateway_order_id' => $gatewayOrderId,
                'payment_status' => $method === 'COD' ? 'COD' : 'Pending',
                'subtotal' => $t['sub'], 'discount' => $t['discount'], 'coupon_code' => $t['coupon'] ?: null,
                'shipping' => $t['ship'], 'tax' => $t['tax'], 'total' => $t['total'], 'express' => $express,
                'status' => 'Pending', 'notes' => $notes,
            ]);

            foreach ($t['items'] as $i) {
                $p = $locked[$i['product']->id];
                $order->items()->create([
                    'product_id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'price' => $p->price,
                    'qty' => $i['qty'], 'art' => $p->art, 'image' => optional($p->images()->first())->path,
                ]);
                $p->decrement('stock', $i['qty']);
            }

            $order->timeline()->create(['status' => 'Order Placed']);
            $this->cart->clear();

            return $order;
        });

        app(StoreMailer::class)->orderCreated($order->load('items'));

        return $order;
    }

    public function markPaid(Order $order, string $txn): void
    {
        $updated = DB::transaction(function () use ($order, $txn) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->payment_status === 'Paid') {
                if ($locked->txn_id !== $txn) {
                    Log::critical('A second PayU transaction was verified for an already-paid order.', [
                        'order' => $locked->order_no,
                        'existing_transaction' => $locked->txn_id,
                        'new_transaction' => $txn,
                    ]);
                }
                return false;
            }

            if ($locked->status === 'Cancelled') {
                Log::critical('PayU confirmed payment for an order that was already cancelled and restocked.', [
                    'order' => $locked->order_no,
                    'transaction' => $txn,
                ]);
            }

            $locked->update(['payment_status' => 'Paid', 'txn_id' => $txn]);
            return true;
        });

        if (!$updated) {
            return;
        }

        app(StoreMailer::class)->paymentConfirmed($order->fresh());
    }

    public function markPaymentFailed(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->payment_status === 'Pending') {
                $locked->update(['payment_status' => 'Failed']);
            }
        });
    }

    public function expireUnpaidPayUOrders(): int
    {
        $cutoff = now()->subMinutes(30);
        $orderIds = Order::where('payment_method', 'PayU')
            ->whereIn('payment_status', ['Pending', 'Failed'])
            ->where('status', 'Pending')
            ->where('created_at', '<=', $cutoff)
            ->pluck('id');
        $expired = 0;

        foreach ($orderIds as $orderId) {
            $order = DB::transaction(function () use ($orderId, $cutoff) {
                $order = Order::whereKey($orderId)->lockForUpdate()->first();
                if (
                    !$order
                    || $order->payment_method !== 'PayU'
                    || !in_array($order->payment_status, ['Pending', 'Failed'], true)
                    || $order->status !== 'Pending'
                    || $order->created_at->gt($cutoff)
                ) {
                    return null;
                }

                foreach ($order->items()->with('product')->get() as $item) {
                    $item->product?->increment('stock', $item->qty);
                }

                $order->status = 'Cancelled';
                if ($order->payment_status === 'Pending') {
                    $order->payment_status = 'Failed';
                }
                $order->save();
                $order->timeline()->firstOrCreate(['status' => 'Cancelled']);

                return $order;
            });

            if ($order) {
                app(StoreMailer::class)->orderStatusUpdated($order->fresh());
                $expired++;
            }
        }

        return $expired;
    }

    /** Admin status change — same rules as the theme's O.setStatus(). */
    public function setStatus(Order $order, string $status): Order
    {
        $previousStatus = $order->status;
        $updated = DB::transaction(function () use ($order, $status) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($status === 'Cancelled' && $order->status !== 'Cancelled') {
                foreach ($order->items as $i) {
                    $i->product?->increment('stock', $i->qty); // restock
                }
            }
            if ($status === 'Delivered' && $order->payment_status === 'COD') {
                $order->payment_status = 'Paid';
            }
            $order->status = $status;
            $order->save();

            $key = $status === 'Pending' ? 'Order Placed' : $status;
            if (!$order->timeline()->where('status', $key)->exists()) {
                $order->timeline()->create(['status' => $key]);
            }
            return $order;
        });

        if ($updated->status !== $previousStatus) {
            app(StoreMailer::class)->orderStatusUpdated($updated->fresh());
        }

        return $updated;
    }

    private function nextOrderNo(): string
    {
        $last = Order::lockForUpdate()->max('id') ?? 0;
        return 'SNX-' . (77291 + $last);
    }
}
