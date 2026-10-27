@extends('layouts.app')
@section('title', 'Checkout - Surat NX')
@section('content')
<main class="sec checkout-page">
    <div class="wrap">
        <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><a href="{{ route('cart') }}">Cart</a><span class="sep">/</span><span>Checkout</span></nav>
        <div class="page-head">
            <h1>Checkout</h1>
            <p>Secure checkout for your Surat NX fabrics.</p>
        </div>

        @php
            $selectedAddressId = old('saved_address', $addresses->firstWhere('is_default', true)?->id);
            $isExpress = old('express') == '1';
            $displayTotals = $isExpress ? $expressTotals : $totals;
        @endphp
        <div class="steps checkout-steps" aria-label="Checkout progress">
            <span class="on"><b>1</b> Cart</span><span class="on"><b>2</b> Address &amp; payment</span><span><b>3</b> Pay</span><span><b>4</b> Done</span>
        </div>
        <form method="post" action="{{ route('checkout.place') }}" id="checkout-form">
            @csrf
            <input id="express-input" type="hidden" name="express" value="{{ $isExpress ? '1' : '0' }}">
            <div class="cart checkout-grid">
                <div class="checkout-main stack">
                    <section class="box checkout-card">
                        <h3>Delivery address</h3>
                    @if ($addresses->isNotEmpty())
                        <select id="saved-address" class="checkout-address-select" aria-label="Select saved address">
                            <option value="">Enter a new address</option>
                            @foreach ($addresses as $address)
                                <option value="{{ $address->id }}" @selected((string) $selectedAddressId === (string) $address->id)
                                    data-name="{{ $address->name }}" data-phone="{{ $address->phone }}" data-pin="{{ $address->pin }}"
                                    data-line1="{{ $address->line1 }}" data-line2="{{ $address->line2 }}" data-landmark="{{ $address->landmark }}"
                                    data-city="{{ $address->city }}" data-state="{{ $address->state }}">
                                    {{ $address->name }} · {{ $address->line1 }}, {{ $address->city }}
                                </option>
                            @endforeach
                        </select>
                        <div class="addr-list checkout-address-list" aria-label="Choose a saved address">
                            @foreach ($addresses as $address)
                                <label class="addr checkout-address {{ (string) $selectedAddressId === (string) $address->id ? 'sel' : '' }}">
                                    <input type="radio" name="saved-address-choice" value="{{ $address->id }}" data-saved-address>
                                    <span class="tag">{{ $address->is_default ? 'DEFAULT · ' : '' }}{{ $address->type }}</span>
                                    <b>{{ $address->name }}</b>
                                    <span>{{ $address->line1 }}@if($address->line2), {{ $address->line2 }}@endif</span>
                                    <span>{{ $address->city }}, {{ $address->state }} – {{ $address->pin }}</span>
                                    <span>Phone: {{ $address->phone }}</span>
                                </label>
                            @endforeach
                            <button class="addr addr-add" type="button" data-new-address>＋ Enter a new address</button>
                        </div>
                    @endif

                    <div class="admin-form-grid checkout-fields">
                        <div class="field"><label for="checkout-name">Full name <span class="req">*</span></label><input id="checkout-name" name="name" required autocomplete="name" value="{{ old('name', auth()->user()->name ?? '') }}"></div>
                        <div class="field"><label for="checkout-phone">Mobile number <span class="req">*</span></label><input id="checkout-phone" type="tel" name="phone" required autocomplete="tel" value="{{ old('phone', auth()->user()->phone ?? '') }}"></div>
                        <div class="field"><label for="checkout-email">Email address <span class="req">*</span></label><input id="checkout-email" type="email" name="email" required autocomplete="email" value="{{ old('email', auth()->user()->email ?? '') }}"></div>
                        <div class="field"><label for="checkout-pin">PIN code <span class="req">*</span></label><input id="checkout-pin" name="pin" required autocomplete="postal-code" value="{{ old('pin') }}"></div>
                        <div class="field full"><label for="checkout-line1">Address line 1 <span class="req">*</span></label><input id="checkout-line1" name="line1" required autocomplete="address-line1" value="{{ old('line1') }}"></div>
                        <div class="field full"><label for="checkout-line2">Address line 2</label><input id="checkout-line2" name="line2" autocomplete="address-line2" value="{{ old('line2') }}"></div>
                        <div class="field"><label for="checkout-landmark">Landmark</label><input id="checkout-landmark" name="landmark" value="{{ old('landmark') }}"></div>
                        <div class="field"><label for="checkout-city">City <span class="req">*</span></label><input id="checkout-city" name="city" required autocomplete="address-level2" value="{{ old('city', 'Surat') }}"></div>
                        <div class="field"><label for="checkout-state">State <span class="req">*</span></label><input id="checkout-state" name="state" required autocomplete="address-level1" value="{{ old('state', 'Gujarat') }}"></div>
                    </div>
                    </section>

                    <section class="box checkout-card">
                        <h3>Delivery method</h3>
                        <div class="checkout-options">
                            <label class="opt checkout-option {{ !$isExpress ? 'sel' : '' }}">
                                <input type="radio" name="delivery-option" value="standard" @checked(!$isExpress)>
                                <span class="checkout-option-icon" aria-hidden="true">↗</span><span class="checkout-option-text"><b>Standard delivery</b><small>Typically {{ \App\Models\Setting::get('standard_delivery_min_days', 3) }}–{{ \App\Models\Setting::get('standard_delivery_max_days', 6) }} working days</small></span>
                                <strong>{{ $totals['ship'] > 0 ? '₹' . number_format($totals['ship'], 2) : 'FREE' }}</strong>
                            </label>
                            <label class="opt checkout-option {{ $isExpress ? 'sel' : '' }}">
                                <input type="radio" name="delivery-option" value="express" @checked($isExpress)>
                                <span class="checkout-option-icon" aria-hidden="true">◷</span><span class="checkout-option-text"><b>Express delivery</b><small>Typically {{ \App\Models\Setting::get('express_delivery_min_days', 1) }}–{{ \App\Models\Setting::get('express_delivery_max_days', 3) }} working days</small></span>
                                <strong>₹{{ number_format((float) $expressTotals['ship'], 2) }}</strong>
                            </label>
                        </div>
                    </section>

                    <section class="box checkout-card">
                        <h3>Payment method</h3>
                        <div class="checkout-options">
                            @if ($onlineEnabled)
                                <label class="opt checkout-option checkout-online-option">
                                    <input type="radio" name="payment_method" value="PayU" required>
                                    <span class="checkout-option-icon" aria-hidden="true">▤</span>
                                    <span class="checkout-option-text">
                                        <b>Pay online securely</b>
                                        <small>Pay securely through PayU</small>
                                        <span class="checkout-payment-types"><i>UPI / QR</i><i>Credit / Debit card</i><i>Net Banking</i><i>Wallets</i></span>
                                    </span>
                                </label>
                            @endif
                            <label class="opt checkout-option sel"><input type="radio" name="payment_method" value="COD" required checked>
                                <span class="checkout-option-icon" aria-hidden="true">₹</span><span class="checkout-option-text"><b>Cash on Delivery</b><small>Pay in cash when your order arrives</small></span>
                            </label>
                        </div>
                        @unless ($onlineEnabled)
                            <p class="muted checkout-note checkout-payment-unavailable">Online payment methods are temporarily unavailable. Cash on Delivery is available.</p>
                        @endunless
                    </section>

                    <section class="box checkout-card">
                        <h3>Order notes <span class="muted checkout-optional">(optional)</span></h3>
                        <div class="field"><label for="checkout-notes">Special instructions</label><textarea id="checkout-notes" name="notes" rows="3" placeholder="Cutting instructions, colour preference or delivery notes">{{ old('notes') }}</textarea></div>
                    </section>
                </div>

                <aside class="box sum checkout-summary">
                    <h3>Order Summary</h3>
                    <div class="checkout-products">
                        @foreach ($totals['items'] as $item)
                            <div class="mini checkout-product">
                                @if($item['product']->images->first())
                                    <img src="{{ asset('storage/' . $item['product']->images->first()->path) }}" alt="{{ $item['product']->name }}">
                                @else
                                    <div class="fabric-art checkout-fabric-art">SN</div>
                                @endif
                                <div class="grow"><b>{{ $item['product']->name }}</b><span>{{ $item['qty'] }} m × ₹{{ number_format((float) $item['product']->price, 2) }}</span></div>
                                <b>₹{{ number_format((float) $item['product']->price * $item['qty'], 2) }}</b>
                            </div>
                        @endforeach
                    </div>
                    <div class="sum-r"><span>Subtotal</span><span>₹{{ number_format($displayTotals['sub'], 2) }}</span></div>
                    @if($displayTotals['discount'] > 0)<div class="sum-r"><span>Discount</span><span class="g">− ₹{{ number_format($displayTotals['discount'], 2) }}</span></div>@endif
                    <div class="sum-r"><span>Shipping</span><span id="checkout-shipping">{{ $displayTotals['ship'] > 0 ? '₹' . number_format($displayTotals['ship'], 2) : 'FREE' }}</span></div>
                    <div class="sum-r"><span>GST ({{ number_format($displayTotals['taxRate'], 2) }}%)</span><span id="checkout-tax">₹{{ number_format($displayTotals['tax'], 2) }}</span></div>
                    <div class="sum-r t"><span>Total</span><span id="checkout-total">₹{{ number_format($displayTotals['total'], 2) }}</span></div>
                    <button class="btn btn-dark btn-block" type="submit" id="checkout-submit">Place Order</button>
                    <p class="cart-secure">♧ 100% secure checkout</p>
                    <a class="link checkout-edit-cart" href="{{ route('cart') }}">← Edit cart</a>
                </aside>
            </div>
        </form>
    </div>
</main>

<script>
    (() => {
        const form = document.getElementById('checkout-form');
        const addressSelect = document.getElementById('saved-address');
        const expressInput = document.getElementById('express-input');
        const totals = {
            standard: { ship: @json((float) $totals['ship']), tax: @json((float) $totals['tax']), total: @json((float) $totals['total']) },
            express: { ship: @json((float) $expressTotals['ship']), tax: @json((float) $expressTotals['tax']), total: @json((float) $expressTotals['total']) },
        };
        if (!form) return;
        const fillAddress = () => {
            if (!addressSelect) return;
            const option = addressSelect.selectedOptions[0];
            ['name', 'phone', 'pin', 'line1', 'line2', 'landmark', 'city', 'state'].forEach((field) => {
                const input = form.elements[field];
                if (input) input.value = option?.value ? (option.dataset[field] || '') : '';
            });
        };
        addressSelect?.addEventListener('change', fillAddress);
        document.querySelectorAll('[data-saved-address]').forEach((radio) => {
            const selected = addressSelect?.value === radio.value;
            radio.checked = selected;
            radio.closest('.checkout-address')?.classList.toggle('sel', selected);
            radio.addEventListener('change', () => {
                if (!addressSelect) return;
                addressSelect.value = radio.value;
                addressSelect.dispatchEvent(new Event('change', { bubbles: true }));
                document.querySelectorAll('.checkout-address').forEach((card) => card.classList.toggle('sel', card.contains(radio)));
            });
        });
        document.querySelector('[data-new-address]')?.addEventListener('click', () => {
            if (addressSelect) {
                addressSelect.value = '';
                addressSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
            document.querySelectorAll('[data-saved-address]').forEach((radio) => { radio.checked = false; });
            document.querySelectorAll('.checkout-address').forEach((card) => card.classList.remove('sel'));
            form.querySelector('[name="name"]')?.focus();
        });
        document.querySelectorAll('[name="delivery-option"]').forEach((radio) => radio.addEventListener('change', () => {
            const express = radio.value === 'express';
            document.querySelectorAll('[name="delivery-option"]').forEach((option) => option.closest('.checkout-option')?.classList.toggle('sel', option.checked));
            expressInput.value = express ? '1' : '0';
            const selected = totals[express ? 'express' : 'standard'];
            document.getElementById('checkout-shipping').textContent = selected.ship > 0 ? `₹${selected.ship.toFixed(2)}` : 'FREE';
            document.getElementById('checkout-tax').textContent = `₹${selected.tax.toFixed(2)}`;
            document.getElementById('checkout-total').textContent = `₹${selected.total.toFixed(2)}`;
        }));
        document.querySelectorAll('[name="payment_method"]').forEach((radio) => radio.addEventListener('change', () => {
            document.querySelectorAll('[name="payment_method"]').forEach((option) => option.closest('.checkout-option')?.classList.toggle('sel', option.checked));
            document.getElementById('checkout-submit').textContent = radio.value === 'COD' ? 'Place Order' : 'Proceed to Pay';
        }));
        if (addressSelect?.value) fillAddress();
    })();
</script>
@endsection