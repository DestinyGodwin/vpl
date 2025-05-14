<?php

namespace App\Services\v1;

use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    public function add(string $productId): Wishlist
    {
        return Wishlist::firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $productId,
        ]);
    }

    public function remove(string $productId): bool
    {
        return Wishlist::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();
    }

    public function getAll()
    {
        return Wishlist::with('product')->where('user_id', Auth::id())->get();
    }

    public function getOne(string $productId): ?Wishlist
    {
        return Wishlist::with('product')
            ->where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();
    }
}
