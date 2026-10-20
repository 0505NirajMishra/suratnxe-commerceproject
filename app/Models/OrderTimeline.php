<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTimeline extends Model
{
    public const UPDATED_AT = null;
    protected $fillable = ['order_id', 'status'];
}
