<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = ['order_id', 'order_item_id', 'user_id', 'reason', 'details', 'photo_paths', 'status', 'admin_note'];
    protected $casts = ['photo_paths' => 'array'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function customerStatusMessage(): string
    {
        $isCod = $this->order?->payment_method === 'COD';

        return match ($this->status) {
            'Requested' => 'Your return request has been received and is currently under review. Pickup has not been scheduled yet. We will update you here after review.',
            'Approved' => 'Your return request is approved. Please pack the product securely and keep it ready. Our pickup agent is expected within 3-4 days. '.($isCod
                ? 'Your refund will be paid in cash by the pickup agent when they collect the product.'
                : 'Your online refund will be initiated within 24 hours after Surat NX receives the returned product.'),
            'Rejected' => 'Your return request has been reviewed and could not be approved. Please see the store response below for details.',
            'Received' => 'The returned product has been received by Surat NX. '.($isCod
                ? 'Your COD refund should have been paid in cash by the pickup agent at collection. Contact us if you have not received it.'
                : 'Your online refund will be initiated within 24 hours.'),
            'Resolved' => 'This return request is marked as resolved. Please check the store response below for the final refund update.',
            default => 'Your return request is being processed. We will post the next update here.',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}