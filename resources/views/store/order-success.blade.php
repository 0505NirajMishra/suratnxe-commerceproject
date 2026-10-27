@extends('layouts.app')
@section('title')
    @if ($order->payment_method === 'PayU' && $order->payment_status === 'Pending')
        Payment processing - Surat NX
    @elseif ($order->payment_method === 'PayU' && $order->payment_status === 'Failed')
        Payment failed - Surat NX
    @else
        Order confirmed - Surat NX
    @endif
@endsection
@section('content')
@php
    $deliveryAddress = collect($order->address ?? [])
        ->only(['line1', 'line2', 'landmark', 'city', 'state', 'pin'])
        ->filter()
        ->implode(', ');
    $estimatedDelivery = $order->eta();
    $trackUrl = route('track', ['order_no' => $order->order_no, 'email' => $order->email]);
    $paymentPending = $order->payment_method === 'PayU' && $order->payment_status === 'Pending';
    $paymentFailed = $order->payment_method === 'PayU' && $order->payment_status === 'Failed';
@endphp
<main class="sec order-success-page">
    <div class="wrap">
        <section class="order-success-hero">
            <span class="order-success-check" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
            </span>
            @if ($paymentPending)
                <p class="order-success-eyebrow">PAYMENT PROCESSING</p>
                <h1>Payment confirmation pending</h1>
                <p>PayU is confirming payment for order <strong>{{ $order->order_no }}</strong>. Please do not pay again; this page will reflect the verified payment status when you refresh.</p>
            @elseif ($paymentFailed)
                <p class="order-success-eyebrow">PAYMENT NOT COMPLETED</p>
                <h1>We could not confirm your payment</h1>
                <p>Order <strong>{{ $order->order_no }}</strong> is held temporarily while we reconcile its final status. If your account was debited, do not pay again; contact support with this order number.</p>
            @else
                <p class="order-success-eyebrow">ORDER CONFIRMED</p>
                <h1>Thank you, {{ Str::before($order->name, ' ') }}!</h1>
                <p>Your order <strong>{{ $order->order_no }}</strong> has been placed. A confirmation has been sent to {{ $order->email }}.</p>
            @endif
        </section>

        <div class="order-success-grid">
            <section class="box order-success-card">
                <h2>Order details</h2>
                <dl class="order-success-facts">
                    <div><dt>Order ID</dt><dd>{{ $order->order_no }}</dd></div>
                    <div><dt>Date</dt><dd>{{ $order->created_at->format('d M Y, h:i A') }}</dd></div>
                    <div><dt>Payment</dt><dd>{{ $order->payment_method }} <span class="pill">{{ $order->payment_status }}</span></dd></div>
                    <div><dt>Order status</dt><dd><span class="pill">{{ $order->status }}</span></dd></div>
                    <div><dt>Estimated delivery</dt><dd>{{ $estimatedDelivery->format('d M Y') }}</dd></div>
                </dl>
                <div class="order-success-address">
                    <h3>Delivering to</h3>
                    <strong>{{ $order->name }}</strong>
                    <p>{{ $deliveryAddress ?: 'Delivery address unavailable' }}</p>
                    <p>Phone: {{ $order->phone }}</p>
                </div>
            </section>

            <section class="box order-success-card">
                <h2>Items</h2>
                <div class="order-success-items">
                    @foreach($order->items as $item)
                        <div class="order-success-item">
                            @if($item->image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image) }}" alt="{{ $item->name }}">
                            @else
                                <span class="order-success-item-placeholder" aria-hidden="true">SN</span>
                            @endif
                            <div class="order-success-item-info">
                                <strong>{{ $item->name }}</strong>
                                <span>{{ $item->qty }} m × ₹{{ number_format((float) $item->price, 2) }}</span>
                            </div>
                            <strong class="order-success-item-price">₹{{ number_format((float) $item->qty * $item->price, 2) }}</strong>
                        </div>
                    @endforeach
                </div>
                <div class="order-success-totals">
                    <div><span>Subtotal</span><span>₹{{ number_format((float) $order->subtotal, 2) }}</span></div>
                    @if((float) $order->discount > 0)<div><span>Discount</span><span>− ₹{{ number_format((float) $order->discount, 2) }}</span></div>@endif
                    <div><span>Shipping</span><span>{{ (float) $order->shipping > 0 ? '₹'.number_format((float) $order->shipping, 2) : 'FREE' }}</span></div>
                    <div><span>GST</span><span>₹{{ number_format((float) $order->tax, 2) }}</span></div>
                    <div class="order-success-grand-total"><strong>Total</strong><strong>₹{{ number_format((float) $order->total, 2) }}</strong></div>
                </div>
            </section>
        </div>

        <div class="order-success-actions">
            <a class="btn btn-gold" href="{{ $trackUrl }}">Track order</a>
            @if(auth()->check() && !auth()->user()->isAdmin())
                <a class="btn btn-light" href="{{ route('account') }}#orders">My orders</a>
            @endif
            <button class="btn btn-light" type="button" onclick="window.print()">Print order</button>
            <a class="btn btn-dark" href="{{ route('shop') }}">Continue shopping</a>
        </div>
    </div>
</main>
@endsection
