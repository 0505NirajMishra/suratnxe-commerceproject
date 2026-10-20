@php
    $storeCustomer = auth()->check() && !auth()->user()->isAdmin();
    $storeGuest = !$storeCustomer;
    $cartCount = app(\App\Services\CartService::class)->count();
    $wishlistCount = $storeCustomer
        ? auth()->user()->wishlist()
            ->where('status', 'active')
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->count()
        : 0;
@endphp
<div class="topbar">
    <div class="wrap">
        <span class="tb-i">
            <svg class="ic" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ \App\Models\Setting::get('location') }}
        </span>
        <a class="tb-i" href="tel:{{ preg_replace('/\s+/', '', (string) \App\Models\Setting::get('phone')) }}">
            <svg class="ic" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
            {{ \App\Models\Setting::get('phone') }}
        </a>
        <a class="tb-i tb-hide" href="mailto:{{ \App\Models\Setting::get('email') }}">
            <svg class="ic" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
            {{ \App\Models\Setting::get('email') }}
        </a>
        <span class="grow"></span>
        <a href="{{ route('track') }}">Track Order</a>
        <a href="{{ route('contact') }}">Help Center</a>
        @if($storeCustomer)
            <a href="{{ route('account') }}">Hi, {{ Str::before(auth()->user()->name, ' ') }}</a>
            <a href="{{ route('returns.index') }}">Returns</a>
        @elseif($storeGuest)
            <a href="{{ route('login') }}">Login / Register</a>
        @endif
    </div>
</div>
<header class="hdr">
    <div class="wrap hdr-in">
        <button class="hicon burger" type="button" aria-label="Open menu" aria-expanded="false" data-drawer>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>
        <a class="logo" href="{{ route('home') }}">
            <span class="mono">SN</span>
            <span class="logo-t"><b>{{ \App\Models\Setting::get('name') }}</b><small>{{ \App\Models\Setting::get('tagline') }}</small></span>
        </a>
        <nav class="nav">
            <a href="{{ route('home') }}">Home</a>
            <div class="has-dd">
                <a href="{{ route('shop') }}">Fabrics
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </a>
                <div class="dd">
                    <a href="{{ route('shop') }}"><b>All Fabrics</b></a>
                    @foreach(\App\Models\Category::active()->orderBy('sort_order')->get() as $navCategory)
                        <a href="{{ route('shop', ['cat' => $navCategory->slug]) }}">{{ $navCategory->name }}</a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('about') }}">About Us</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>
        <form class="hsearch" action="{{ route('shop') }}">
            <input type="search" name="q" placeholder="Search fabrics...">
            <button aria-label="Search"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
        </form>
        <div class="hicons">
            @if($storeGuest)
                <a class="hicon" href="{{ route('login') }}" aria-label="Wishlist">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                </a>
            @elseif($storeCustomer)
                <a class="hicon" href="{{ route('wishlist') }}" aria-label="Wishlist">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                    @if($wishlistCount > 0)<span class="cnt">{{ $wishlistCount > 99 ? '99+' : $wishlistCount }}</span>@endif
                </a>
            @endif
            <a class="hicon" href="{{ route('cart') }}" aria-label="Cart">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                @if($cartCount > 0)<span class="cnt">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>@endif
            </a>
            @if($storeCustomer)
                <a class="hicon" href="{{ route('account') }}" aria-label="My account">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </a>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="hicon" aria-label="Sign out"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg></button></form>
            @elseif($storeGuest)
                <a class="hicon" href="{{ route('login') }}" aria-label="Login">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </a>
            @endif
        </div>
    </div>
</header>
<div class="scrim" data-drawer-close></div>
<aside class="drawer">
    <div class="drawer-h">
        <a class="logo" href="{{ route('home') }}"><span class="mono">SN</span><span class="logo-t"><b>{{ \App\Models\Setting::get('name') }}</b><small>{{ \App\Models\Setting::get('tagline') }}</small></span></a>
        <button class="hicon" data-drawer-close aria-label="Close menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>
    <nav>
        <a href="{{ route('home') }}">Home</a>
        <div class="drawer-acc">
            <button class="drawer-acc-btn" type="button" aria-expanded="false" aria-controls="drawer-fabrics">
                All Fabrics
                <svg class="acc-ic" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="drawer-acc-body" id="drawer-fabrics" inert>
                <div class="drawer-acc-inner">
                    <a class="sub" href="{{ route('shop') }}">Browse all fabrics</a>
                    @foreach(\App\Models\Category::active()->orderBy('sort_order')->get() as $navCategory)
                        <a class="sub" href="{{ route('shop', ['cat' => $navCategory->slug]) }}">{{ $navCategory->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
        <a href="{{ route('about') }}">About Us</a>
        <a href="{{ route('contact') }}">Contact</a>
        <a href="{{ route('track') }}">Track Order</a>
        @if($storeCustomer)
            <a href="{{ route('account') }}">My Account</a>
            <a href="{{ route('returns.index') }}">Returns</a>
        @elseif($storeGuest)
            <a href="{{ route('login') }}">Login / Register</a>
        @endif
    </nav>
</aside>
