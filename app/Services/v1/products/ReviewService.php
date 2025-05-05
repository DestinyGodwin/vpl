<?php

namespace App\Services\v1\products;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ReviewService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function store($request)
    {
        $product = Product::findOrFail($request->product_id);
        
        return $product->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);
    }

    public function getByProduct($productId)
    {
        return Review::where('product_id', $productId)->with('user')->latest()->get();
    }

    public function delete($id)
    {
        $review = Review::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $review->delete();
    }
}
