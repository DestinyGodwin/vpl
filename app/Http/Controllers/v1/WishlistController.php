<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\WishlistService;
use App\Http\Resources\v1\products\ProductResource;
use App\Http\Requests\v1\Wishlist\StoreWishlistRequest;

class WishlistController extends Controller
{
    public function __construct(protected WishlistService $service) {}

    public function index()
    {
        $wishlists = $this->service->getAll();
        return ProductResource::collection($wishlists->pluck('product'));
    }

    public function store(StoreWishlistRequest $request)
    {
        $wishlist = $this->service->add($request->validated()['product_id']);
        return response()->json(['message' => 'Product added to wishlist.'], 201);
    }

    public function destroy(string $productId)
    {
        $this->service->remove($productId);
        return response()->json(['message' => 'Product removed from wishlist.']);
    }

    public function show(string $productId)
    {
        $wishlist = $this->service->getOne($productId);

        if (!$wishlist) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return new ProductResource($wishlist->product);
    }
}

