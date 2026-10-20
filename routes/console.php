<?php

use App\Services\OrderService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(fn () => app(OrderService::class)->expireUnpaidRazorpayOrders())
    ->everyFiveMinutes()
    ->name('expire-unpaid-razorpay-orders')
    ->withoutOverlapping();