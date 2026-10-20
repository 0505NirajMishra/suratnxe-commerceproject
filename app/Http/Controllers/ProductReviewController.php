<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $user = $request->user();
        $hasDeliveredOrder = Order::where('user_id', $user->id)
            ->where('status', 'Delivered')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();

        if (!$hasDeliveredOrder) {
            return back()->withErrors(['review' => 'You can review this product after it has been delivered.']);
        }

        DB::transaction(function () use ($data, $product, $user) {
            ProductReview::updateOrCreate(
                ['product_id' => $product->id, 'user_id' => $user->id],
                ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
            );

            $product->update([
                'rating' => $product->reviews()->avg('rating') ?? 0,
                'reviews_count' => $product->reviews()->count(),
            ]);
        });

        return back()->with('success', 'Your review has been saved.');
    }
}