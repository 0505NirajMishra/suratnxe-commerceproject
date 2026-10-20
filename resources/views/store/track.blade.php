@extends('layouts.app')
@section('title', 'Track Your Order - Surat NX')
@section('content')
<main class="sec track-page">
    <div class="wrap">
        <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span>Track Order</span></nav>
        <div class="page-head"><h1>Track Your Order</h1><p>Follow your fabric order from Surat to your doorstep.</p></div>

        <section class="box track-search">
            <h2>Find your order</h2>
            <p>Enter the order number and the email or phone number used when placing the order.</p>
            <form method="get" action="{{ route('track') }}" class="track-search-form">
                <div class="field"><label for="track-order-no">Order number</label><input id="track-order-no" name="order_no" required value="{{ request('order_no') }}" placeholder="e.g. SNX-77292"></div>
                <div class="field"><label for="track-contact">Email or phone</label><input id="track-contact" name="contact" required value="{{ $contact }}" placeholder="you@email.com or mobile number"></div>
                <button class="btn btn-dark" type="submit">Track order <span aria-hidden="true">→</span></button>
            </form>
            @if(auth()->check() && $recentOrders->isNotEmpty())
                <div class="recent-track-orders"><b>Your recent orders</b><div>
                    @foreach($recentOrders as $recentOrder)
                        <a class="recent-track-link" href="{{ route('track', ['order_no' => $recentOrder->order_no, 'email' => $recentOrder->email]) }}">
                            {{ $recentOrder->order_no }} <span class="pill">{{ $recentOrder->status }}</span>
                        </a>
                    @endforeach
                </div></div>
            @endif
        </section>

        @if(request()->filled('order_no'))
            @if($order)
                @php
                    $steps = \App\Models\Order::STEPS;
                    $currentStep = $order->stepIndex();
                    $eventsByStatus = $order->timeline->keyBy('status');
                    $deliveryAddress = $order->address ?? [];
                    $isCancelled = $order->status === 'Cancelled';
                @endphp
                <section class="box track-result">
                    <div class="track-result-head">
                        <div><span class="eyebrow">Order number</span><h2>{{ $order->order_no }}</h2><p>Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p></div>
                        <div class="track-status"><span class="eyebrow">Current status</span><span class="pill">{{ $order->status }}</span>
                            @if(!$isCancelled && $order->status !== 'Delivered')<p>Estimated delivery <b>{{ $order->eta()->format('d M Y') }}</b></p>@elseif($order->status === 'Delivered')<p>Delivered successfully</p>@endif
                        </div>
                    </div>
                    @if($isCancelled)
                        <div class="cancel-note"><b>This order was cancelled.</b> Contact our team if you need help with a refund or replacement.</div>
                    @else
                        <div class="tl track-timeline">
                            @foreach($steps as $index => $step)
                                @php
                                    $event = $eventsByStatus->get($step);
                                    $stepClass = $index < $currentStep || ($index === $currentStep && $currentStep === count($steps) - 1) ? 'done' : ($index === $currentStep ? 'cur' : 'todo');
                                @endphp
                                <div class="tl-s {{ $stepClass }}">
                                    <div class="tl-d" aria-hidden="true">{{ $stepClass === 'done' ? '✓' : '' }}</div>
                                    <div class="tx"><b>{{ $step }}</b><span>{{ $event?->created_at?->format('d M') ?? '—' }}</span></div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="trk track-detail-grid">
                        <div class="map track-map">
                            <svg viewBox="0 0 500 280" role="img" aria-label="Delivery route from Surat to {{ $deliveryAddress['city'] ?? 'your destination' }}" preserveAspectRatio="xMidYMid slice">
                                <rect width="500" height="280" fill="#eadfce"/>
                                <path d="M0 66C94 92 153 32 245 68S393 51 500 24M0 226C85 184 142 239 235 211S390 167 500 185" fill="none" stroke="#c5d6da" stroke-width="17"/>
                                <path d="M107 0C125 70 82 154 145 280M365 0C341 84 396 187 351 280" fill="none" stroke="#d9c9b0" stroke-width="8"/>
                                <path d="M75 198C148 198 162 112 245 130S365 79 423 84" fill="none" stroke="#b8946a" stroke-width="4" stroke-dasharray="8 7"/>
                                <circle cx="75" cy="198" r="8" fill="#17140f"/><circle cx="423" cy="84" r="9" fill="#fff" stroke="#17140f" stroke-width="3"/>
                                <text x="75" y="226" text-anchor="middle" font-size="14" font-weight="600" fill="#17140f">Surat</text>
                                <text x="423" y="64" text-anchor="middle" font-size="14" font-weight="600" fill="#17140f">{{ $deliveryAddress['city'] ?? 'Destination' }}</text>
                            </svg>
                            <span class="track-map-caption">Your parcel's journey</span>
                        </div>
                        <div class="track-delivery-info">
                            <h3>Delivery details</h3>
                            <div class="sum-r"><span>Courier</span><b>{{ $order->courier ?: 'To be assigned' }}</b></div>
                            <div class="sum-r"><span>Tracking number</span><b>{{ $order->tracking_no ?: 'Not available yet' }}</b></div>
                            <div class="sum-r"><span>Payment</span><b>{{ $order->payment_method }} · {{ $order->payment_status }}</b></div>
                            <div class="track-address"><b>Delivering to</b>
                                <p>{{ $deliveryAddress['name'] ?? $order->name }}</p>
                                <p>{{ $deliveryAddress['line1'] ?? '' }}@if(!empty($deliveryAddress['line2'])), {{ $deliveryAddress['line2'] }}@endif</p>
                                @if(!empty($deliveryAddress['landmark']))<p>{{ $deliveryAddress['landmark'] }}</p>@endif
                                <p>{{ $deliveryAddress['city'] ?? '' }}, {{ $deliveryAddress['state'] ?? '' }} {{ $deliveryAddress['pin'] ?? '' }}</p>
                                <p>Phone: {{ $deliveryAddress['phone'] ?? $order->phone }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="track-items">
                        <h3>Items in this order</h3>
                        @foreach($order->items as $item)
                            <div class="mini track-item">
                                @if($item->image)<img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">@else<div class="fabric-art track-fabric-art">SN</div>@endif
                                <div class="grow"><b>{{ $item->name }}</b><span>{{ $item->sku }} · {{ $item->qty }} m × ₹{{ number_format((float) $item->price, 2) }}</span></div>
                                <strong>₹{{ number_format((float) $item->price * $item->qty, 2) }}</strong>
                            </div>
                        @endforeach
                        <div class="sum-r track-order-total"><span>Order total</span><b>₹{{ number_format((float) $order->total, 2) }}</b></div>
                    </div>
                </section>
            @else
                <div class="flash error track-not-found">We could not find an order with those details. Check the order number and try the email address or phone number used at checkout.</div>
            @endif
        @endif
    </div>
</main>
@endsection
