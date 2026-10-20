<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['slug', 'name', 'code', 'description', 'image', 'art', 'is_active', 'sort_order'];
    protected $casts = ['art' => 'array', 'is_active' => 'boolean'];

    public function getRouteKeyName(): string { return 'slug'; }

    public function products(): HasMany { return $this->hasMany(Product::class); }

    public function scopeActive(Builder $q): Builder { return $q->where('is_active', true); }
}
