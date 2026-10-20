@extends('layouts.app')

@section('title', $product->name . ' - Surat NX')

@section('content')
@php
    $productImages = $product->images;
    $firstImage = $productImages->first();
    $fabricColors = $product->art['c'] ?? ['#e5d7bf', '#9e8666', '#f0e6d5'];
@endphp
<main class="product-page sec">
    <div class="wrap">
        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span class="sep">/</span>
            <a href="{{ route('shop', ['cat' => $product->category->slug]) }}">{{ $product->category->name }}</a><span class="sep">/</span>
            <span aria-current="page">{{ $product->name }}</span>
        </nav>

        <section class="pd">
            <div class="product-gallery gal" aria-label="{{ $product->name }} images">
                @if($productImages->isNotEmpty())
                    <div class="thumbs" aria-label="Choose product image">
                        @foreach($productImages as $image)
                            <button type="button"
                                class="{{ $loop->first ? 'on' : '' }}"
                                data-product-thumb
                                data-image-src="{{ asset('storage/' . $image->path) }}"
                                data-image-alt="{{ $product->name }} - image {{ $loop->iteration }}"
                                aria-label="Show product image {{ $loop->iteration }}"
                                aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                                <img src="{{ asset('storage/' . $image->path) }}" alt="" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                    <div class="mainimg">
                        <img src="{{ asset('storage/' . $firstImage->path) }}" alt="{{ $product->name }}" data-product-main-image>
                        <button class="zoom" type="button" data-product-zoom aria-label="View larger image of {{ $product->name }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4M11 8v6M8 11h6"></path></svg>
                        </button>
                    </div>
                @else
                    <div class="mainimg product-fabric-placeholder" style="--fabric-one:{{ $fabricColors[0] ?? '#e5d7bf' }};--fabric-two:{{ $fabricColors[1] ?? '#9e8666' }};--fabric-three:{{ $fabricColors[2] ?? '#f0e6d5' }}">
                        <div class="fabric-art">SURAT NX</div>
                    </div>
                @endif
            </div>

            <div class="pd-info">
                <p class="eyebrow">{{ $product->category->name }}</p>
                <h1>{{ $product->name }}</h1>
                <p class="pd-sub">SKU: {{ $product->sku }}
                    <a class="product-review-link" href="#reviews" aria-label="Read {{ $product->reviews_count }} customer reviews">
                        ★ {{ number_format((float) $product->rating, 1) }} ({{ $product->reviews_count }} {{ \Illuminate\Support\Str::plural('review', $product->reviews_count) }})
                    </a>
                </p>

                <div class="pd-price">
                    ₹{{ number_format((float) $product->price, 2) }} <small>/ metre</small>
                    @if($product->mrp && $product->mrp > $product->price)
                        <s>₹{{ number_format((float) $product->mrp, 2) }}</s>
                    @endif
                </div>
                <p class="pd-tax">Inclusive of applicable taxes</p>
                @if($product->coupon?->isUsable())
                    <p class="product-coupon-offer">Coupon {{ $product->coupon->code }} applies automatically to this fabric.</p>
                @endif

                <div class="pd-badges" aria-label="Product information">
                    <span class="{{ $product->stock > 0 ? 'ok' : 'stock-empty' }}">
                        {{ $product->stock > 0 ? '✓ In stock' : 'Currently out of stock' }}
                    </span>
                    <span>Sold by the metre</span>
                    @if($product->discountPercent() > 0)
                        <span>{{ $product->discountPercent() }}% off</span>
                    @endif
                </div>

                @if($product->stock > 0)
                    <form class="product-buy-form" method="post" action="{{ route('cart.add') }}" data-product-buy>
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="pd-buy">
                            <div class="product-quantity-control">
                                <label class="qty-label" for="product-quantity">Quantity <span>(metres)</span></label>
                                <div class="qty">
                                    <button type="button" data-qty-step="-1" aria-label="Remove one metre">−</button>
                                    <input id="product-quantity" type="number" name="qty" min="1" max="{{ min(1000, $product->stock) }}" value="1" required inputmode="numeric" data-product-quantity>
                                    <button type="button" data-qty-step="1" aria-label="Add one metre">+</button>
                                </div>
                            </div>
                            <p class="pd-total">Total: <b data-product-total>₹{{ number_format((float) $product->price, 2) }}</b></p>
                        </div>
                        <div class="product-action-buttons">
                            <button class="btn btn-dark product-add-button" type="submit" name="buy_now" value="0">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 1.9-1.4L22 8H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg>
                                Add to cart
                            </button>
                            <button class="btn btn-line" type="submit" name="buy_now" value="1">
                                Buy now <span aria-hidden="true">→</span>
                            </button>
                        </div>
                    </form>
                @else
                    <p class="stock-warning" role="status">This fabric is currently out of stock.</p>
                @endif

                <div class="product-details-box">
                    <div class="product-description">
                        <h2>About this fabric</h2>
                        <p>{{ $product->description }}</p>
                    </div>
                    <div class="pd-box">
                        <h3>Product details</h3>
                        <table class="spec">
                            <tbody>
                                @foreach([
                                    'Fabric' => $product->fabric,
                                    'Width' => $product->width,
                                    'Colour' => $product->colors,
                                    'Ideal for' => $product->usage,
                                    'Care' => $product->care,
                                ] as $label => $value)
                                    @if(filled($value))
                                        <tr><td>{{ $label }}</td><td>{{ $value }}</td></tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        @include('store.product-reviews')

        @if($related->isNotEmpty())
            <section class="sec product-related">
                <div class="page-head">
                    <h2>You may also like</h2>
                    <p>More fabrics from {{ $product->category->name }}.</p>
                </div>
                <div class="grid g4">
                    @foreach($related as $p)
                        @include('partials.product-card', ['p' => $p])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>

@if($productImages->isNotEmpty())
    <div class="modal-wrap product-image-modal-wrap" data-product-modal hidden>
        <section class="modal wide product-image-modal" role="dialog" aria-modal="true" aria-labelledby="product-image-modal-title">
            <header class="product-image-modal-header">
                <h2 id="product-image-modal-title">{{ $product->name }}</h2>
                <div class="product-image-modal-actions">
                    <button class="product-image-zoom-toggle" type="button" data-product-zoom-toggle aria-pressed="false">Zoom in</button>
                    <button class="modal-x" type="button" data-product-modal-close aria-label="Close image viewer">×</button>
                </div>
            </header>
            <div class="product-image-modal-stage">
                <img src="{{ asset('storage/' . $firstImage->path) }}" alt="{{ $product->name }}" data-product-modal-image>
            </div>
        </section>
    </div>
@endif

<script>
(() => {
    const page = document.querySelector('.product-page');
    if (!page) return;

    const quantity = page.querySelector('[data-product-quantity]');
    const total = page.querySelector('[data-product-total]');
    const price = {{ json_encode((float) $product->price) }};
    const money = new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const updateQuantity = () => {
        if (!quantity) return;
        const minimum = Number(quantity.min) || 1;
        const maximum = Number(quantity.max) || 1000;
        const value = Math.max(minimum, Math.min(maximum, Number(quantity.value) || minimum));
        quantity.value = String(value);
        page.querySelector('[data-qty-step="-1"]')?.toggleAttribute('disabled', value <= minimum);
        page.querySelector('[data-qty-step="1"]')?.toggleAttribute('disabled', value >= maximum);
        if (total) total.textContent = `₹${money.format(price * value)}`;
    };

    page.querySelectorAll('[data-qty-step]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!quantity) return;
            quantity.value = String((Number(quantity.value) || 1) + Number(button.dataset.qtyStep));
            updateQuantity();
        });
    });
    quantity?.addEventListener('input', updateQuantity);

    const mainImage = page.querySelector('[data-product-main-image]');
    const modal = document.querySelector('[data-product-modal]');
    const modalImage = modal?.querySelector('[data-product-modal-image]');
    const zoomToggle = modal?.querySelector('[data-product-zoom-toggle]');
    const modalPanel = modal?.querySelector('.product-image-modal');
    const setImage = (source, alt) => {
        if (mainImage) {
            mainImage.src = source;
            mainImage.alt = alt;
        }
        if (modalImage) {
            modalImage.src = source;
            modalImage.alt = alt;
            modalImage.classList.remove('is-zoomed');
            modalPanel?.classList.remove('is-zoomed');
            if (zoomToggle) {
                zoomToggle.textContent = 'Zoom in';
                zoomToggle.setAttribute('aria-pressed', 'false');
            }
        }
    };

    page.querySelectorAll('[data-product-thumb]').forEach((button) => {
        button.addEventListener('click', () => {
            page.querySelectorAll('[data-product-thumb]').forEach((thumb) => {
                thumb.classList.remove('on');
                thumb.setAttribute('aria-pressed', 'false');
            });
            button.classList.add('on');
            button.setAttribute('aria-pressed', 'true');
            setImage(button.dataset.imageSrc, button.dataset.imageAlt);
        });
    });

    const closeModal = () => {
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
        page.querySelector('[data-product-zoom]')?.focus();
    };

    page.querySelector('[data-product-zoom]')?.addEventListener('click', () => {
        if (!modal || !modalImage || !mainImage) return;
        setImage(mainImage.src, mainImage.alt);
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        modal.querySelector('[data-product-modal-close]')?.focus();
    });
    modal?.querySelector('[data-product-modal-close]')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    modal?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeModal();
    });
    zoomToggle?.addEventListener('click', () => {
        const isZoomed = modalImage?.classList.toggle('is-zoomed') ?? false;
        modalPanel?.classList.toggle('is-zoomed', isZoomed);
        zoomToggle.textContent = isZoomed ? 'Zoom out' : 'Zoom in';
        zoomToggle.setAttribute('aria-pressed', String(isZoomed));
    });
    modalImage?.addEventListener('click', () => zoomToggle?.click());

    updateQuantity();
})();
</script>
@endsection
