<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'coupon_id', 'name', 'slug', 'sku', 'price', 'mrp', 'stock', 'width', 'fabric', 'usage',
        'care', 'colors', 'description', 'tags', 'art', 'status', 'rating', 'reviews_count',
    ];
    protected $casts = ['tags' => 'array', 'art' => 'array', 'price' => 'float', 'mrp' => 'float', 'rating' => 'float'];

    public function getRouteKeyName(): string { return 'slug'; }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function reviews(): HasMany { return $this->hasMany(ProductReview::class); }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active')->whereHas('category', fn (Builder $category) => $category->where('is_active', true));
    }

    /** Real uploaded image URLs. Empty => Blade falls back to the generated pattern from `art`. */
    public function imageUrls(): array
    {
        return $this->images->map(fn ($i) => Storage::url($i->path))->all();
    }

    public function discountPercent(): int
    {
        return $this->mrp && $this->mrp > $this->price ? (int) round(($this->mrp - $this->price) / $this->mrp * 100) : 0;
    }
}
