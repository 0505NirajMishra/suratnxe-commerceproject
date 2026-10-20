<?php
namespace App\Http\Controllers;
use App\Models\{Category,Product,Setting,Order}; use Illuminate\Http\Request;
class StoreController extends Controller {
 public function home()
 {
  $categories = Category::active()
   ->withCount(['products' => fn ($query) => $query->where('status', 'active')])
   ->orderBy('sort_order')
   ->get();
  $featuredTabs = collect(['new' => 'New Arrivals', 'best' => 'Best Sellers', 'trending' => 'Trending'])
   ->mapWithKeys(fn ($label, $tag) => [$tag => [
    'label' => $label,
    'products' => Product::active()->with(['category', 'images', 'coupon'])->whereJsonContains('tags', $tag)->latest()->take(8)->get(),
   ]]);
  $testimonials = \App\Models\ProductReview::query()
   ->whereNotNull('comment')
   ->where('comment', '<>', '')
   ->with(['user', 'product'])
   ->latest()
   ->take(3)
   ->get();
  $productImages = $featuredTabs->flatMap(fn ($tab) => $tab['products'])->unique('id')->flatMap->images;
  $heroImage = $productImages->first();
  $promoImage = $productImages->skip(1)->first() ?? $heroImage;
  $settings = Setting::all_();
  return view('store.home-dynamic', compact('categories', 'featuredTabs', 'testimonials', 'heroImage', 'promoImage', 'settings'));
 }
 public function shop(Request $request)
 {
  $query = Product::active()->with(['category', 'images', 'coupon']);
  $searchInput = $request->input('q');
  $search = is_string($searchInput) ? mb_substr(trim($searchInput), 0, 100) : '';
  $categoryInput = $request->input('cat');
  $categorySlug = is_string($categoryInput) ? mb_substr($categoryInput, 0, 100) : '';
  $tagInput = $request->input('tag');
  $tag = is_string($tagInput) && in_array($tagInput, ['new', 'best', 'trending', 'sale'], true) ? $tagInput : '';
  $sortInput = $request->input('sort', 'featured');
  $sort = is_string($sortInput) && in_array($sortInput, ['featured', 'newest', 'price-asc', 'price-desc', 'rating', 'name'], true)
   ? $sortInput
   : 'featured';
  $stockInput = $request->input('stock');
  $inStock = in_array($stockInput, ['1', 1, true, 'true', 'on', 'yes'], true);
  $minInput = $request->input('min');
  $maxInput = $request->input('max');
  $minimumPrice = is_numeric($minInput) && is_finite((float) $minInput) && (float) $minInput >= 0 ? (string) $minInput : null;
  $maximumPrice = is_numeric($maxInput) && is_finite((float) $maxInput) && (float) $maxInput >= 0 ? (string) $maxInput : null;
  $shopFilters = array_filter([
   'q' => $search !== '' ? $search : null,
   'cat' => $categorySlug !== '' ? $categorySlug : null,
   'tag' => $tag !== '' ? $tag : null,
   'min' => $minimumPrice,
   'max' => $maximumPrice,
   'stock' => $inStock ? '1' : null,
   'sort' => $sort,
  ], fn ($value) => $value !== null && $value !== '');

  if ($search !== '') {
   $query->where(fn ($products) => $products
    ->where('name', 'like', '%' . $search . '%')
    ->orWhere('sku', 'like', '%' . $search . '%')
    ->orWhere('fabric', 'like', '%' . $search . '%')
    ->orWhere('description', 'like', '%' . $search . '%')
    ->orWhereHas('category', fn ($categories) => $categories->where('name', 'like', '%' . $search . '%')));
  }
  if ($categorySlug !== '') {
   $query->whereHas('category', fn ($categories) => $categories->where('slug', $categorySlug));
  }
  if (in_array($tag, ['new', 'best', 'trending'], true)) {
   $query->whereJsonContains('tags', $tag);
  } elseif ($tag === 'sale') {
   $query->whereNotNull('mrp')->whereColumn('mrp', '>', 'price');
  }
  if ($minimumPrice !== null) {
   $query->where('price', '>=', (float) $minimumPrice);
  }
  if ($maximumPrice !== null) {
   $query->where('price', '<=', (float) $maximumPrice);
  }
  if ($inStock) {
   $query->where('stock', '>', 0);
  }

  switch ($sort) {
   case 'price-asc':
    $query->orderBy('price');
    break;
   case 'price-desc':
    $query->orderByDesc('price');
    break;
   case 'newest':
    $query->latest();
    break;
   case 'rating':
    $query->orderByDesc('rating')->orderByDesc('reviews_count');
    break;
   case 'name':
    $query->orderBy('name');
    break;
   default:
    $sort = 'featured';
    $query->orderByDesc('id');
  }

  $products = $query->paginate(12)->appends($shopFilters);
  $categories = Category::active()
   ->withCount(['products' => fn ($products) => $products->where('status', 'active')])
   ->orderBy('sort_order')
   ->get();
  $activeCategory = $categories->firstWhere('slug', $categorySlug);
  $similarProducts = collect();
  if ($activeCategory) {
   $visibleProductIds = $products->getCollection()->modelKeys();
   $similarProducts = Product::active()
    ->with(['category', 'images', 'coupon'])
    ->where('category_id', $activeCategory->id)
    ->whereNotIn('id', $visibleProductIds)
    ->orderByDesc('rating')
    ->orderByDesc('reviews_count')
    ->take(4)
    ->get();

   if ($similarProducts->count() < 4) {
    $recommendationIds = array_merge($visibleProductIds, $similarProducts->modelKeys());
    $additionalProducts = Product::active()
     ->with(['category', 'images', 'coupon'])
     ->whereNotIn('id', $recommendationIds)
     ->orderByDesc('rating')
     ->orderByDesc('reviews_count')
     ->take(4 - $similarProducts->count())
     ->get();
    $similarProducts = $similarProducts->concat($additionalProducts);
   }
  }
  $categoryProductCount = Product::active()->count();
  $collections = [
   '' => 'All',
   'new' => 'New arrivals',
   'best' => 'Best sellers',
   'trending' => 'Trending',
   'sale' => 'On sale',
  ];
  $shopTitle = $search !== ''
   ? 'Results for "' . $search . '"'
   : ($activeCategory?->name
    ? $activeCategory->name . ' Fabrics'
    : ($tag && isset($collections[$tag]) ? $collections[$tag] : 'All Fabrics'));
  $shopDescription = $activeCategory?->description ?: 'Premium fabrics for designers, boutiques and homes.';

  return view('store.shop', compact(
   'products',
   'similarProducts',
   'categories',
   'activeCategory',
   'categoryProductCount',
   'collections',
   'shopTitle',
   'shopDescription',
   'sort',
   'shopFilters'
  ));
 }
 public function product(Product $product){abort_unless($product->status==='active'&&$product->category()->where('is_active',true)->exists(),404);$product->load(['category','images','coupon','reviews.user']);$product->rating = (float) ($product->reviews()->avg('rating') ?? 0);$product->reviews_count = (int) $product->reviews()->count();$related=Product::active()->with(['category','images','coupon'])->where('category_id',$product->category_id)->whereKeyNot($product->id)->take(4)->get();$reviews=$product->reviews()->with('user')->latest()->paginate(6);$alreadyReviewed=auth()->check()&&$product->reviews()->where('user_id',auth()->id())->exists();$canReview=auth()->check()&&!$alreadyReviewed&&\App\Models\Order::where('user_id',auth()->id())->where('status','Delivered')->whereHas('items',fn($q)=>$q->where('product_id',$product->id))->exists();return view('store.product-detail',compact('product','related','reviews','canReview','alreadyReviewed'));}
 public function page(string $page){abort_unless(in_array($page,['about','policy']),404);$settings=Setting::all_();return view('store.'.$page,compact('settings'));}
 public function track(Request $r)
 {
  $order = null;
  $contact = trim((string) $r->input('contact', $r->input('email', '')));
  if ($r->filled('order_no') && $contact !== '') {
   $order = Order::with(['items', 'timeline'])
    ->where('order_no', $r->input('order_no'))
    ->where(fn ($query) => $query->where('email', $contact)->orWhere('phone', $contact))
    ->first();
  }
  $recentOrders = auth()->check()
   ? auth()->user()->orders()->with('items')->take(5)->get()
   : collect();
  return view('store.track', compact('order', 'recentOrders', 'contact'));
 }
}
