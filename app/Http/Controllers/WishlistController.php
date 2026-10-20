<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $products = auth()->user()->wishlist()->with(['category', 'images'])->where('status', 'active')->whereHas('category', fn ($query) => $query->where('is_active', true))->get();

        return view('store.wishlist', compact('products'));
    }

    public function toggle(Request $request, Product $product)
    {
        $user = auth()->user();
        abort_unless(Product::active()->whereKey($product->id)->exists(), 404);
        if ($user->wishlist()->where('product_id', $product->id)->exists()) {
            $user->wishlist()->detach($product->id);
            $message = "{$product->name} removed from wishlist.";
        } else {
            $user->wishlist()->attach($product->id);
            $message = "{$product->name} added to wishlist.";
        }

        return back()->with('success', $message);
    }
}
