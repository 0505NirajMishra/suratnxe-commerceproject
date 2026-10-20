@extends('layouts.app')
@section('title', \App\Models\Setting::get('name').' - '.\App\Models\Setting::get('tagline'))
@section('content')
<main>
@php
    $heroTitleParts = preg_split('/(fabric)/i', $settings['hero_title'], 2, PREG_SPLIT_DELIM_CAPTURE);
    $heroImageUrl = $heroImage ? asset('storage/' . $heroImage->path) : null;
    $promoImageUrl = $promoImage ? asset('storage/' . $promoImage->path) : null;
@endphp
<section class="hero" id="hero" @if($heroImageUrl) style="background-image:url('{{ $heroImageUrl }}')" @endif>
    <div class="wrap">
        <p class="hero-k">{{ $settings['hero_kicker'] }}</p>
        <h1>@foreach($heroTitleParts as $part)@if(strcasecmp($part, 'fabric') === 0)<em>{{ $part }}</em>@else{{ $part }}@endif @endforeach</h1>
        <p>{{ $settings['hero_subtitle'] }}</p>
        <div class="hero-btns"><a class="btn btn-gold" href="{{ route('shop') }}">Explore Fabrics</a><a class="btn btn-ghost" href="{{ route('about') }}">Our Story</a></div>
    </div>
</section>

<section class="feat">
    <div class="wrap">
        <div class="feat-i"><span class="ic-box" aria-hidden="true"><svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="m8.2 13.9-1.2 8.1 5-3 5 3-1.2-8.1"/></svg></span><div><b>{{ $settings['feature_quality_title'] }}</b><span>{{ $settings['feature_quality_copy'] }}</span></div></div>
        <div class="feat-i"><span class="ic-box" aria-hidden="true"><svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/></svg></span><div><b>{{ $categories->count() }}+ Categories</b><span>{{ $settings['home_categories_link'] }}</span></div></div>
        <div class="feat-i"><span class="ic-box" aria-hidden="true"><svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.1 15.9M14.5 14.5 20 20M8.1 8.1 12 12"/></svg></span><div><b>{{ $settings['feature_custom_title'] }}</b><span>{{ $settings['feature_custom_copy'] }}</span></div></div>
        <div class="feat-i"><span class="ic-box" aria-hidden="true"><svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></svg></span><div><b>{{ $settings['feature_shipping_title'] }}</b><span>{{ $settings['feature_shipping_copy'] }}</span></div></div>
    </div>
</section>

<section class="sec">
    <div class="wrap">
        <div class="sec-h"><h2>{{ $settings['home_categories_title'] }}</h2><a class="link" href="{{ route('shop') }}">{{ $settings['home_categories_link'] }}</a></div>
        <div class="rail-wrap">
            <div class="rail" data-category-rail>
                @foreach ($categories as $category)
                    @php($categoryColors = $category->art['c'] ?? ['#2e211d', '#947557', '#d4b58a'])
                    <a class="catc" href="{{ route('shop', ['cat' => $category->slug]) }}" style="--fabric-one:{{ $categoryColors[0] ?? '#2e211d' }};--fabric-two:{{ $categoryColors[1] ?? '#947557' }};--fabric-three:{{ $categoryColors[2] ?? '#d4b58a' }};@if($category->image)background-image:url('{{ asset('storage/' . $category->image) }}');background-size:cover;background-position:center @endif">
                        <div><h3>{{ $category->name }}</h3><span>{{ $category->products_count }} fabrics</span></div>
                    </a>
                @endforeach
            </div>
            <button class="rail-btn l" type="button" data-rail-scroll="previous" aria-label="Scroll categories left"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></button>
            <button class="rail-btn" type="button" data-rail-scroll="next" aria-label="Scroll categories right"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>
        </div>
    </div>
</section>

<section class="sec sec-alt">
    <div class="wrap">
        <div class="sec-h"><div><h2>{{ $settings['home_featured_title'] }}</h2><p>{{ $settings['home_featured_copy'] }}</p></div><a class="link" data-featured-view-all href="{{ route('shop', ['tag' => 'new']) }}">View all fabrics</a></div>
        <div class="tabs" role="tablist" aria-label="Featured fabric collections">
            @foreach($featuredTabs as $tag => $tab)
                <button type="button" class="{{ $loop->first ? 'on' : '' }}" id="featured-tab-{{ $tag }}" role="tab" aria-controls="featured-panel-{{ $tag }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-feature-filter="{{ $tag }}" data-filter-href="{{ route('shop', ['tag' => $tag]) }}">{{ $tab['label'] }}</button>
            @endforeach
        </div>
        @foreach($featuredTabs as $tag => $tab)
            <div class="grid g4" id="featured-panel-{{ $tag }}" data-feature-panel="{{ $tag }}" role="tabpanel" aria-labelledby="featured-tab-{{ $tag }}" @if(!$loop->first) hidden @endif>
                @forelse($tab['products'] as $product)
                    @include('partials.product-card', ['p' => $product])
                @empty
                    <p>No {{ strtolower($tab['label']) }} are available yet.</p>
                @endforelse
            </div>
        @endforeach
    </div>
</section>

<section class="sec">
    <div class="wrap">
        <div class="promo">
            <div class="promo-img" role="img" aria-label="Premium fabric" @if($promoImageUrl) style="background-image:url('{{ $promoImageUrl }}')" @endif></div>
            <div class="promo-t"><h2>{{ $settings['home_custom_title'] }}</h2><p>{{ $settings['home_custom_copy'] }}</p><a class="btn btn-gold" href="{{ route('contact') }}">{{ $settings['home_custom_button'] }}</a></div>
        </div>
    </div>
</section>

<section class="sec sec-alt">
    <div class="wrap">
        <div class="sec-h"><div><h2>{{ $settings['home_why_title'] }}</h2><p>{{ $settings['home_why_copy'] }}</p></div></div>
        <div class="why">
            <div class="why-i"><span class="ic-box" aria-hidden="true"><svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><h3>{{ $settings['why_source_title'] }}</h3><p>{{ $settings['why_source_copy'] }}</p></div>
            <div class="why-i"><span class="ic-box" aria-hidden="true"><svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span><h3>{{ $settings['why_quality_title'] }}</h3><p>{{ $settings['why_quality_copy'] }}</p></div>
            <div class="why-i"><span class="ic-box" aria-hidden="true"><svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span><h3>{{ $settings['why_shipping_title'] }}</h3><p>{{ $settings['why_shipping_copy'] }}</p></div>
            <div class="why-i"><span class="ic-box" aria-hidden="true"><svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg></span><h3>{{ $settings['why_payment_title'] }}</h3><p>{{ $settings['why_payment_copy'] }}</p></div>
        </div>
    </div>
</section>

<section class="sec">
    <div class="wrap">
        <div class="sec-h"><div><h2>What our customers say</h2><p>Boutiques, designers and home tailors who order from us.</p></div></div>
        <div class="testi">
            @forelse($testimonials as $review)
                <figure class="tcard">
                    <span class="stars" aria-label="{{ $review->rating }} out of 5">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                    <q>{{ $review->comment }}</q>
                    <figcaption class="who"><span class="avatar">{{ mb_strtoupper(mb_substr($review->user->name, 0, 1)) }}</span><div><b>{{ $review->user->name }}</b><span>{{ $review->product->name }}</span></div></figcaption>
                </figure>
            @empty
                @foreach([
                    ['Ritika Shah', 'Boutique owner, Ahmedabad', 'The Italian brocade matched the sample exactly, metre after metre. My customers keep asking where the fabric comes from.', 5],
                    ['Farhan Qureshi', 'Fashion designer, Mumbai', 'Fast dispatch and honest descriptions. The satin drapes beautifully and the packing was neat. Ordering again for my festive line.', 5],
                    ['Meera Joshi', 'Home tailor, Vadodara', 'I ordered muslin for kurtis in small quantities and the quality was still excellent. Delivery reached me in four days.', 4],
                ] as [$name, $role, $quote, $rating])
                    <figure class="tcard">
                        <span class="stars" aria-label="{{ $rating }} out of 5">{{ str_repeat('★', $rating) }}{{ str_repeat('☆', 5 - $rating) }}</span>
                        <q>{{ $quote }}</q>
                        <figcaption class="who"><span class="avatar">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span><div><b>{{ $name }}</b><span>{{ $role }}</span></div></figcaption>
                    </figure>
                @endforeach
            @endforelse
        </div>
    </div>
</section>

<section class="band">
    <div class="wrap"><div><h2>{{ $settings['home_bulk_title'] }}</h2><p class="muted">{{ $settings['home_bulk_copy'] }}</p></div><a class="btn btn-dark" href="{{ route('contact') }}">{{ $settings['home_bulk_button'] }}</a></div>
</section>
</main>
@endsection