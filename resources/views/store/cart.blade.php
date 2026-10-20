@extends('layouts.app')
@section('title', 'Shopping Cart - Surat NX')
@section('content')
<main class="sec cart-page">
    <div class="wrap">
        <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span>Cart</span></nav>
        <div class="page-head"><h1>Shopping Cart</h1><p>{{ $totals['items']->count() }} fabric{{ $totals['items']->count() === 1 ? '' : 's' }} · {{ $totals['meters'] }} metres</p></div>
        @if($totals['items']->isEmpty())
            <div class="cart-empty box">
                <div class="cart-empty-icon" aria-hidden="true"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h2l2.2 11.2a2 2 0 0 0 2 1.6h8.5a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></div>
                <h2>Your cart is empty</h2>
                <p>Add a few fabrics and they will show up here.</p>
                <a class="btn btn-dark" href="{{ route('shop') }}">Start shopping</a>
            </div>
        @else
            @php
                $freeShippingLimit = (float) \App\Models\Setting::get('free_ship');
                $amountForFreeShipping = max(0, $freeShippingLimit - ($totals['sub'] - $totals['discount']));
                $shippingProgress = $freeShippingLimit > 0 ? min(100, (($totals['sub'] - $totals['discount']) / $freeShippingLimit) * 100) : 100;
            @endphp
            <div class="cart">
                <section class="box cart-items-box" aria-label="Cart items">
                    <div class="freebar" id="cart-free-shipping" data-limit="{{ $freeShippingLimit }}">
                        @if($amountForFreeShipping > 0)
                            <span>Add <b id="cart-free-shipping-amount">₹{{ number_format($amountForFreeShipping, 2) }}</b> more to unlock free shipping.</span>
                        @else
                            <span>You have unlocked <b>free shipping</b>.</span>
                        @endif
                        <i><b style="width:{{ $shippingProgress }}%"></b></i>
                    </div>
                    <p id="cart-update-status" class="cart-update-status" role="status" aria-live="polite" hidden></p>
                    <form id="cart-update-form" method="post" action="{{ route('cart.update') }}">
                        @csrf @method('PATCH')
                        @foreach($totals['items'] as $item)
                            @php($product = $item['product'])
                            <article class="citem cart-item" data-product-id="{{ $product->id }}" data-unit-price="{{ (float) $product->price }}">
                                <a class="cart-item-image" href="{{ route('product', $product) }}">
                                    @if($product->images->first())
                                        <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="fabric-art" style="--fabric-one:{{ $product->art['c'][0] ?? '#e5d7bf' }};--fabric-two:{{ $product->art['c'][1] ?? '#9e8666' }};--fabric-three:{{ $product->art['c'][2] ?? '#f0e6d5' }}">SURAT NX</div>
                                    @endif
                                </a>
                                <div class="cart-item-info">
                                    <h4><a href="{{ route('product', $product) }}">{{ $product->name }}</a></h4>
                                    <div class="m">{{ $product->sku }} · ₹{{ number_format((float) $product->price, 2) }} / metre</div>
                                    @if($product->stock < 20)<div class="m cart-stock-note">Only {{ $product->stock }} m left in stock</div>@endif
                                    <div class="acts">
                                        <button class="link" type="submit" form="remove-cart-item-{{ $product->id }}">Remove</button>
                                    </div>
                                </div>
                                <div class="cart-item-total">
                                    <div class="qty sm">
                                        <button type="button" data-qty-step="-1" aria-label="Remove one metre from {{ $product->name }}">−</button>
                                        <input aria-label="Metres of {{ $product->name }}" type="number" name="qty[{{ $product->id }}]" min="1" max="{{ max(1, $product->stock) }}" value="{{ $item['qty'] }}">
                                        <button type="button" data-qty-step="1" aria-label="Add one metre of {{ $product->name }}">+</button>
                                    </div>
                                    <b data-line-total>₹{{ number_format((float) $product->price * $item['qty'], 2) }}</b>
                                </div>
                            </article>
                        @endforeach
                        <div class="cart-update-row">
                            <a class="link" href="{{ route('shop') }}">← Continue shopping</a>
                            <button class="btn btn-light btn-sm" type="submit">Update cart</button>
                        </div>
                    </form>
                    @foreach($totals['items'] as $item)
                        <form id="remove-cart-item-{{ $item['product']->id }}" method="post" action="{{ route('cart.remove', $item['product']) }}">@csrf @method('DELETE')</form>
                    @endforeach
                </section>
                <aside class="box sum cart-summary">
                    <h3>Order Summary</h3>
                    @if($totals['couponAvailable'] && count($totals['availableCouponCodes']) === 1)
                        <form class="coupon" method="post" action="{{ route('cart.coupon') }}">
                            @csrf
                            <input name="code" aria-label="Coupon code" placeholder="Coupon code" value="{{ old('code', $totals['availableCouponCodes'][0]) }}">
                            <button class="btn btn-dark btn-sm" type="submit">Apply</button>
                        </form>
                    @elseif($totals['couponAvailable'])
                        <p class="coupon-applied">Eligible product coupons are applied automatically.</p>
                    @endif
                    @if($errors->has('code'))<p class="coupon-error">{{ $errors->first('code') }}</p>
                    @elseif($totals['couponErr'])<p class="coupon-error">{{ $totals['couponErr'] }}</p>@endif
                    <p id="cart-coupon-applied" class="coupon-applied" @if(!$totals['coupon']) hidden @endif><span id="cart-coupon-code">{{ $totals['coupon'] }} applied</span><span>− ₹<span id="cart-discount">{{ number_format($totals['discount'], 2) }}</span></span></p>
                    <div class="sum-r"><span>Subtotal (<span id="cart-meters">{{ $totals['meters'] }}</span> m)</span><span>₹<span id="cart-subtotal">{{ number_format($totals['sub'], 2) }}</span></span></div>
                    <div id="cart-discount-row" class="sum-r" @if($totals['discount'] <= 0) hidden @endif><span>Discount</span><span class="g">− ₹<span id="cart-discount-total">{{ number_format($totals['discount'], 2) }}</span></span></div>
                    <div class="sum-r"><span>Shipping</span><span id="cart-shipping">{{ $totals['ship'] > 0 ? '₹' . number_format($totals['ship'], 2) : 'FREE' }}</span></div>
                    <div class="sum-r"><span>GST ({{ number_format($totals['taxRate'], 2) }}%)</span><span>₹<span id="cart-tax">{{ number_format($totals['tax'], 2) }}</span></span></div>
                    <div class="sum-r t"><span>Total</span><span>₹<span id="cart-total">{{ number_format($totals['total'], 2) }}</span></span></div>
                    <p class="muted cart-inclusive">Inclusive of applicable taxes.</p>
                    <a class="btn btn-gold btn-block" href="{{ route('checkout') }}">Proceed to Checkout <span aria-hidden="true">→</span></a>
                    <p class="cart-secure"><span aria-hidden="true">✓</span> Secure checkout · UPI, cards and Cash on Delivery</p>
                </aside>
            </div>
        @endif
    </div>
</main>
<script>
    (() => {
        const form = document.getElementById('cart-update-form');
        if (!form) return;

        const updateStatus = document.getElementById('cart-update-status');
        const formatMoney = (amount) => Number(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        const refreshLineTotal = (input) => {
            const row = input.closest('.cart-item');
            const quantity = Math.max(Number(input.min), Math.min(Number(input.max), Number(input.value) || 1));
            input.value = quantity;
            row.querySelector('[data-line-total]').textContent = `₹${formatMoney(Number(row.dataset.unitPrice) * quantity)}`;
        };

        form.querySelectorAll('.cart-item input[type="number"]').forEach((input) => {
            input.addEventListener('input', () => refreshLineTotal(input));
            input.addEventListener('change', () => refreshLineTotal(input));
        });

        form.querySelectorAll('.cart-item [data-qty-step]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = button.parentElement.querySelector('input');
                input.value = Math.max(Number(input.min), Math.min(Number(input.max), Number(input.value) + Number(button.dataset.qtyStep)));
                refreshLineTotal(input);
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submit = form.querySelector('button[type="submit"]');
            submit.disabled = true;
            updateStatus.hidden = false;
            updateStatus.textContent = 'Updating cart totals…';
            updateStatus.classList.remove('error');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Unable to update your cart.');
                }

                if (result.items.length === 0) {
                    window.location.reload();
                    return;
                }

                const updatedIds = new Set(result.items.map((item) => String(item.id)));
                form.querySelectorAll('.cart-item').forEach((row) => {
                    if (updatedIds.has(row.dataset.productId)) return;
                    document.getElementById(`remove-cart-item-${row.dataset.productId}`)?.remove();
                    row.remove();
                });

                result.items.forEach((item) => {
                    const row = form.querySelector(`.cart-item[data-product-id="${item.id}"]`);
                    if (!row) return;
                    const input = row.querySelector('input[type="number"]');
                    input.value = item.qty;
                    row.querySelector('[data-line-total]').textContent = `₹${formatMoney(item.lineTotal)}`;
                });

                document.getElementById('cart-meters').textContent = result.meters;
                document.getElementById('cart-subtotal').textContent = formatMoney(result.subtotal);
                document.getElementById('cart-discount').textContent = formatMoney(result.discount);
                document.getElementById('cart-discount-total').textContent = formatMoney(result.discount);
                document.getElementById('cart-discount-row').hidden = result.discount <= 0;
                document.getElementById('cart-shipping').textContent = result.shipping > 0 ? `₹${formatMoney(result.shipping)}` : 'FREE';
                document.getElementById('cart-tax').textContent = formatMoney(result.tax);
                document.getElementById('cart-total').textContent = formatMoney(result.total);

                const couponNotice = document.getElementById('cart-coupon-applied');
                couponNotice.hidden = !result.coupon;
                document.getElementById('cart-coupon-code').textContent = result.coupon ? `${result.coupon} applied` : '';

                const freeShipping = document.getElementById('cart-free-shipping');
                const remaining = Math.max(0, result.freeShippingLimit - (result.subtotal - result.discount));
                freeShipping.querySelector('span').innerHTML = remaining > 0
                    ? `Add <b id="cart-free-shipping-amount">₹${formatMoney(remaining)}</b> more to unlock free shipping.`
                    : 'You have unlocked <b>free shipping</b>.';
                freeShipping.querySelector('i > b').style.width = `${result.freeShippingLimit > 0 ? Math.min(100, ((result.subtotal - result.discount) / result.freeShippingLimit) * 100) : 100}%`;

                document.querySelector('.cart-page .page-head p').textContent = `${result.items.length} fabric${result.items.length === 1 ? '' : 's'} · ${result.meters} metres`;
                updateStatus.textContent = 'Cart quantities and prices updated.';
            } catch (error) {
                updateStatus.classList.add('error');
                updateStatus.textContent = error.message;
            } finally {
                submit.disabled = false;
            }
        });
    })();
</script>
@endsection
