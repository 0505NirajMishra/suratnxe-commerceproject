<article class="pcard" data-category="{{ $p->category->slug ?? '' }}">
    <a class="pimg" href="{{ route('product', $p) }}" aria-label="{{ $p->name }}">
        @if($p->images->first())
            <img loading="lazy" src="{{ asset('storage/' . $p->images->first()->path) }}" alt="{{ $p->name }}">
        @else
            <div class="fabric-art" style="--fabric-one:{{ $p->art['c'][0] ?? '#e5d7bf' }};--fabric-two:{{ $p->art['c'][1] ?? '#9e8666' }};--fabric-three:{{ $p->art['c'][2] ?? '#f0e6d5' }}">SURAT NX</div>
        @endif
        @if(in_array('new', $p->tags ?? []))
            <span class="pbadge">NEW</span>
        @elseif($p->mrp && $p->mrp > $p->price)
            <span class="pbadge red">{{ round((1 - $p->price / $p->mrp) * 100) }}% OFF</span>
        @endif
    </a>
    <form class="pwish-form" method="post" action="{{ route('wishlist.toggle', $p) }}">
        @csrf
        <button class="pwish" type="submit" aria-label="Add {{ $p->name }} to wishlist"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg></button>
    </form>
    <div class="pbody">
        <h3><a href="{{ route('product', $p) }}">{{ $p->name }}</a></h3>
        <p class="pmeta">{{ $p->category->name ?? '' }} · {{ $p->sku }}</p>
        <div class="pprice"><b>₹{{ number_format($p->price, 2) }}</b><span> / metre</span>@if($p->mrp > $p->price)<s>₹{{ number_format($p->mrp, 2) }}</s>@endif</div>
        {{-- @if($p->coupon?->isUsable())
            <p class="product-coupon-offer">Code {{ $p->coupon->code }} · applies automatically</p>
        @endif --}}
        @if($p->stock < 1)<p class="stock-warning">Currently out of stock</p>@endif
        <form method="post" action="{{ route('cart.add') }}">
            @csrf
            <input type="hidden" name="product_id" value="{{ $p->id }}">
            <button class="btn btn-dark btn-block" type="submit" {{ $p->stock < 1 ? 'disabled' : '' }}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/></svg>
                {{ $p->stock > 0 ? 'Add to Cart' : 'Out of stock' }}
            </button>
        </form>
    </div>
</article>