<?php

namespace App\Services\v1\products;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function create($request): Product
{
    $category = Category::findOrFail($request->category_id);

    $store = Auth::user()->stores()->where('type', $category->store_type)->firstOrFail();

    $product = $store->products()->create($request->only([
        'name', 'description', 'price', 'category_id'
    ]));

    if ($request->hasFile('images')) {
        $images = collect($request->file('images'))->map(fn($image) => [
            'image_path' => $image->store('products', 'public'),
        ]);


        $product->images()->createMany($images->all());
    }

    return $product->load('category', 'images', 'store.user');
}

    public function getAll() { return Product::with('category', 'images', 'store.user')->latest()->get(); }

    public function getByStoreType(string $type)
    {
        return Product::whereHas('store', fn($q) => $q->where('type', $type))
            ->with('category', 'images', 'store.user')->latest()->get();
    }

    public function getByUser()
    {
        return Product::whereHas('store', fn($q) => $q->where('user_id', Auth::id()))
            ->with('category', 'images', 'store.user')->get();
    }

    public function getByUniversity($universityId, $type = null)
    {
        return Product::whereHas('store', function ($q) use ($universityId, $type) {
            $q->where('university_id', $universityId);
            if ($type) $q->where('type', $type);
        })->with('category', 'images', 'store.user')->get();
    }

    public function getByCountry($countryId, $type = null)
    {
        return Product::whereHas('store.university', function ($q) use ($countryId) {
            $q->where('country_id', $countryId);
        })->when($type, fn($q) => $q->whereHas('store', fn($q) => $q->where('type', $type)))
        ->with('category', 'images', 'store.user')->get();
    }

    public function getByCategory($categoryId, $type = null)
    {
        return Product::where('category_id', $categoryId)
            ->when($type, fn($q) => $q->whereHas('store', fn($q) => $q->where('type', $type)))
            ->with('category', 'images', 'store.user')->get();
    }

   

    public function update($id, $request)
    {
        $product = Auth::user()->stores()->with('products')->get()
            ->pluck('products')->flatten()->firstWhere('id', $id);

        if (!$product) abort(404, 'Product not found or not owned.');

        $product->update($request->only(['name', 'description', 'price', 'category_id']));

        if ($request->hasFile('images')) {
            $product->images()->delete(); // delete old images
            foreach ($request->file('images') as $image) {
                $product->images()->create(['path' => $image->store('products', 'public')]);
            }
        }

        return $product->fresh(['category', 'images', 'store.user']);
    }

    public function delete($id)
    {
        $product = Auth::user()->stores()->with('products')->get()
            ->pluck('products')->flatten()->firstWhere('id', $id);

        if (!$product) abort(404, 'Product not found or not owned.');

        $product->delete();
    }

    public function findById($id)
    {
        return Product::with('category', 'images', 'store.user')->findOrFail($id);
    }
}
