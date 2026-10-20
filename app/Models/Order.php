<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Setting;

class Order extends Model
{
    /** Same steps as the theme's STEPS / STATUSES constants. */
    public const STEPS = ['Order Placed', 'Confirmed', 'Processing', 'Packed', 'Shipped', 'On the way', 'Delivered'];
    public const STATUSES = ['Pending', 'Confirmed', 'Processing', 'Packed', 'Shipped', 'On the way', 'Delivered', 'Cancelled'];

    protected $guarded = [];
    protected $casts = ['address' => 'array', 'express' => 'boolean'];

    public function getRouteKeyName(): string { return 'order_no'; }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function timeline(): HasMany { return $this->hasMany(OrderTimeline::class)->orderBy('created_at'); }
    public function returnRequest(): HasOne { return $this->hasOne(ReturnRequest::class); }

    public function stepIndex(): int
    {
        $s = $this->status === 'Pending' ? 'Order Placed' : $this->status;
        return (int) array_search($s, self::STEPS, true);
    }

    public function eta(): \Illuminate\Support\Carbon
    {
        $setting = $this->express ? 'express_delivery_max_days' : 'standard_delivery_max_days';
        $remaining = (int) Setting::get($setting, $this->express ? 3 : 6);
        $date = $this->created_at->copy();

        while ($remaining > 0) {
            $date->addDay();
            if ($date->isWeekday()) {
                $remaining--;
            }
        }

        return $date;
    }
}
