<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\CartSession;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;

class CartService
{
    private function sid(): string
    {
        return (string) session()->getId();
    }

    private function cartState(): CartSession
    {
        return CartSession::firstOrCreate(['session_id' => $this->sid()]);
    }

    public function raw(): array
    {
        return CartItem::where('session_id', $this->sid())
            ->pluck('qty', 'product_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    public function count(): int
    {
        return (int) CartItem::where('session_id', $this->sid())
            ->whereHas('product', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('category', fn ($category) => $category->where('is_active', true)))
            ->sum('qty');
    }

    public function items()
    {
        $rows = CartItem::with(['product.category', 'product.images', 'product.coupon'])
            ->where('session_id', $this->sid())
            ->get();

        return $rows->map(function ($row) {
            $product = $row->product;
            if (!$product || $product->status !== 'active' || !$product->category?->is_active) {
                return null;
            }

            return [
                'product' => $product,
                'qty' => max(1, min((int) $row->qty, max(1, (int) $product->stock))),
            ];
        })->filter()->values();
    }

    public function add(int $productId, int $qty = 1): bool
    {
        $product = Product::active()->find($productId);
        if (!$product || $product->stock <= 0) {
            return false;
        }

        $row = CartItem::firstOrNew(['session_id' => $this->sid(), 'product_id' => $productId]);
        $row->user_id = auth()->user()?->isAdmin() ? null : auth()->id();
        $row->qty = min((int) $product->stock, ((int) $row->qty) + max(1, $qty));
        $row->save();

        return true;
    }

    public function set(int $productId, int $qty): void
    {
        $row = CartItem::where('session_id', $this->sid())->where('product_id', $productId)->first();
        if (!$row) {
            return;
        }

        $product = Product::find($productId);
        if (!$product || $product->stock < 1) {
            $row->delete();
            return;
        }

        $row->qty = max(1, min($qty, (int) $product->stock));
        $row->save();
    }

    public function remove(int $productId): void
    {
        CartItem::where('session_id', $this->sid())->where('product_id', $productId)->delete();
    }

    public function clear(): void
    {
        CartItem::where('session_id', $this->sid())->delete();
        CartSession::where('session_id', $this->sid())->delete();
    }

    public function applyCoupon(?string $code): void
    {
        $state = $this->cartState();
        $state->coupon_code = $code ? strtoupper(trim($code)) : null;
        $state->save();
    }

    public function evaluateCoupon(float $subtotal, ?string $code): array
    {
        if (!$code) {
            return ['discount' => 0, 'code' => ''];
        }

        $coupon = Coupon::where('code', strtoupper($code))->first();
        if (!$coupon || !$coupon->isUsable()) {
            return ['discount' => 0, 'code' => '', 'err' => 'Invalid coupon code'];
        }

        return $this->couponDiscount($subtotal, $coupon);
    }

    public function totals(bool $express = false): array
    {
        $items = $this->items();
        $subtotal = $items->sum(fn ($item) => $item['product']->price * $item['qty']);
        $state = CartSession::where('session_id', $this->sid())->first();
        $requestedCode = strtoupper(trim((string) $state?->coupon_code));

        $availableCoupons = $items->map(fn ($item) => $item['product']->coupon)
            ->filter(fn ($coupon) => $coupon?->isUsable())
            ->unique('id')
            ->values();
        $couponError = $requestedCode && $availableCoupons->where('code', $requestedCode)->isEmpty()
            ? 'This coupon is not assigned to an eligible fabric in your cart.'
            : null;

        $discount = 0;
        $appliedCodes = [];
        foreach ($availableCoupons as $coupon) {
            $eligibleSubtotal = $items
                ->filter(fn ($item) => (int) $item['product']->coupon_id === (int) $coupon->id)
                ->sum(fn ($item) => $item['product']->price * $item['qty']);
            $result = $this->couponDiscount((float) $eligibleSubtotal, $coupon);
            if (isset($result['err'])) {
                $couponError ??= $result['err'];
                continue;
            }
            if ($result['discount'] > 0) {
                $discount += $result['discount'];
                $appliedCodes[] = $result['code'];
            }
        }

        $discount = min($discount, $subtotal);
        $afterDiscount = max(0, $subtotal - $discount);
        $shipping = $items->isEmpty()
            ? 0
            : ($express
                ? (float) Setting::get('express_fee')
                : ($afterDiscount >= (float) Setting::get('free_ship')
                    ? 0
                    : (float) Setting::get('ship_fee')));
        $taxRate = (float) Setting::get('gst', 0);
        $tax = round($afterDiscount * $taxRate / 100, 2);

        return [
            'items' => $items,
            'sub' => $subtotal,
            'discount' => $discount,
            'coupon' => implode(', ', $appliedCodes),
            'couponCodes' => $appliedCodes,
            'availableCouponCodes' => $availableCoupons->pluck('code')->all(),
            'couponAvailable' => $availableCoupons->isNotEmpty(),
            'couponErr' => $couponError,
            'ship' => $shipping,
            'taxRate' => $taxRate,
            'tax' => $tax,
            'total' => max(0, $afterDiscount + $shipping + $tax),
            'meters' => $items->sum('qty'),
        ];
    }

    private function couponDiscount(float $subtotal, Coupon $coupon): array
    {
        if ($subtotal < (float) $coupon->min_order) {
            $need = number_format($coupon->min_order - $subtotal, 0);
            return [
                'discount' => 0,
                'code' => '',
                'err' => "Add items worth ₹{$need} more to use {$coupon->code}",
            ];
        }

        $discount = $coupon->type === 'percent'
            ? $subtotal * $coupon->value / 100
            : $coupon->value;
        if ($coupon->type === 'percent' && (float) $coupon->max_discount > 0) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return [
            'discount' => (int) round(min($discount, $subtotal)),
            'code' => $coupon->code,
        ];
    }
}
