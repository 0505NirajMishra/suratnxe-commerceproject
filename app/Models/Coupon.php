<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'max_discount', 'min_order', 'description', 'is_active', 'expires_at'];
    protected $casts = ['is_active' => 'boolean', 'expires_at' => 'datetime', 'value' => 'float', 'max_discount' => 'float', 'min_order' => 'float'];

    public function setCodeAttribute($v): void { $this->attributes['code'] = strtoupper(trim($v)); }

    public function isUsable(): bool
    {
        return $this->is_active && (!$this->expires_at || $this->expires_at->isFuture());
    }
}
