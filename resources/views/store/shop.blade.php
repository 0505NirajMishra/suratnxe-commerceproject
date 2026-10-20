@extends('layouts.app')
@section('title', $shopTitle . ' - Surat NX')
@section('content')
@php
    $categoryFilters = $shopFilters;
    unset($categoryFilters['cat'], $categoryFilters['page']);
    $collectionFilters = $shopFilters;
    unset($collectionFilters['tag'], $collectionFilters['page']);
    $selectedTag = $shopFilters['tag'] ?? '';
    $selectedSort = $shopFilters['sort'] ?? 'featured';
@endphp
<main class="shop-page">
    <div class="wrap">
        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span class="sep">/</span><span>Fabrics</span>
        </nav>
        <div class="page-head shop-page-head">
            <h1>{{ $shopTitle }}</h1>
            <p>{{ $shopDescription }}</p>
        </div>

        <div class="shop">
            <aside class="filters" id="shop-filters" aria-label="Shop filters">
                <div class="shop-filter-heading">
                    <h2>Filters</h2>
                    <button class="shop-filter-close" type="button" data-filter-close aria-label="Close filters">×</button>
                </div>

                <details class="shop-filter-group" open>
                    <summary>Categories</summary>
                    <nav class="fl" aria-label="Categories">
                        <a href="{{ route('shop', $categoryFilters) }}"
                           class="{{ $activeCategory ? '' : 'on' }}"
                           @unless($activeCategory) aria-current="page" @endunless>
                            All Fabrics <small>{{ $categoryProductCount }}</small>
                        </a>
                        @foreach($categories as $category)
                            <a href="{{ route('shop', array_merge($categoryFilters, ['cat' => $category->slug])) }}"
                               class="{{ $activeCategory?->id === $category->id ? 'on' : '' }}"
                               @if($activeCategory?->id === $category->id) aria-current="page" @endif>
                                {{ $category->name }} <small>{{ $category->products_count }}</small>
                            </a>
                        @endforeach
                    </nav>
                </details>

                <details class="shop-filter-group" open>
                    <summary>Collection</summary>
                    <nav class="fl" aria-label="Collections">
                        @foreach($collections as $value => $label)
                            <a href="{{ route('shop', array_merge($collectionFilters, $value === '' ? [] : ['tag' => $value])) }}"
                               class="{{ $selectedTag === $value ? 'on' : '' }}"
                               @if($selectedTag === $value) aria-current="page" @endif>
                                {{ $label }}
                            </a>
                        @endforeach
                    </nav>
                </details>

                <form class="shop-price-filter" method="get" action="{{ route('shop') }}">
                    @foreach($shopFilters as $name => $value)
                        @if(in_array($name, ['q', 'cat', 'tag', 'sort', 'stock'], true))
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <h3>Price per metre (₹)</h3>
                    <div class="price-in">
                        <input type="number" min="0" step="0.01" name="min" value="{{ $shopFilters['min'] ?? '' }}" placeholder="Min" aria-label="Minimum price">
                        <span aria-hidden="true">–</span>
                        <input type="number" min="0" step="0.01" name="max" value="{{ $shopFilters['max'] ?? '' }}" placeholder="Max" aria-label="Maximum price">
                    </div>
                    <button class="btn btn-dark btn-sm btn-block" type="submit">Apply price</button>
                </form>

                <form class="shop-stock-filter" method="get" action="{{ route('shop') }}">
                    @foreach($shopFilters as $name => $value)
                        @if(in_array($name, ['q', 'cat', 'tag', 'sort', 'min', 'max'], true))
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <h3>Availability</h3>
                    <label class="shop-stock-option">
                        <input type="checkbox" name="stock" value="1" @checked(($shopFilters['stock'] ?? '') === '1')>
                        <span>In stock only</span>
                    </label>
                    <button class="btn btn-line btn-sm btn-block" type="submit">Apply availability</button>
                </form>

                <a class="shop-clear-filters" href="{{ route('shop') }}">Clear all filters</a>
            </aside>
            <button type="button" class="shop-filter-scrim" data-filter-close aria-label="Close filters"></button>

            <section class="shop-results" aria-label="Fabric results">
                <div class="shop-bar">
                    <span class="muted">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('fabric', $products->total()) }} found</span>
                    <div class="shop-toolbar">
                        <button class="btn btn-light btn-sm shop-filter-open" type="button" data-filter-open aria-expanded="false" aria-controls="shop-filters">Filters</button>
                        <form id="shop-sort-form" method="get" action="{{ route('shop') }}">
                            @foreach($shopFilters as $name => $value)
                                @if($name !== 'sort')
                                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <select id="shop-sort" name="sort" aria-label="Sort fabrics">
                                <option value="featured" @selected($selectedSort === 'featured')>Sort: Featured</option>
                                <option value="newest" @selected($selectedSort === 'newest')>Newest first</option>
                                <option value="price-asc" @selected($selectedSort === 'price-asc')>Price: Low to High</option>
                                <option value="price-desc" @selected($selectedSort === 'price-desc')>Price: High to Low</option>
                                <option value="rating" @selected($selectedSort === 'rating')>Top rated</option>
                                <option value="name" @selected($selectedSort === 'name')>Name A–Z</option>
                            </select>
                        </form>
                    </div>
                </div>

                @if($products->isNotEmpty())
                    <div class="grid g3">
                        @foreach($products as $product)
                            @include('partials.product-card', ['p' => $product])
                        @endforeach
                    </div>
                    @if($products->lastPage() > 1)
                        <nav class="pager" aria-label="Pagination">
                            @if($products->onFirstPage())
                                <span class="disabled" aria-disabled="true">Previous</span>
                            @else
                                <a href="{{ $products->previousPageUrl() }}" rel="prev">Previous</a>
                            @endif
                            @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                                <a href="{{ $url }}" @if($page === $products->currentPage()) class="on" aria-current="page" @endif>{{ $page }}</a>
                            @endforeach
                            @if($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}" rel="next">Next</a>
                            @else
                                <span class="disabled" aria-disabled="true">Next</span>
                            @endif
                        </nav>
                    @endif
                @else
                    <div class="empty shop-empty">
                        <h2>No fabrics found</h2>
                        <p>Try a different search or clear some filters.</p>
                        <a class="btn btn-dark" href="{{ route('shop') }}">Clear filters</a>
                    </div>
                @endif
            </section>
        </div>

        @if($activeCategory && $similarProducts->isNotEmpty())
            <section class="product-related shop-recommendations" aria-labelledby="shop-recommendations-title">
                <div class="page-head">
                    <h2 id="shop-recommendations-title">You may also like</h2>
                    <p>More fabrics selected for you.</p>
                </div>
                <div class="grid g4">
                    @foreach($similarProducts as $p)
                        @include('partials.product-card', ['p' => $p])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>
@endsection
