<?php

use App\Services\OrderService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(fn () => app(OrderService::class)->expireUnpaidPayUOrders())
    ->everyFiveMinutes()
    ->name('expire-unpaid-payu-orders')
    ->withoutOverlapping();