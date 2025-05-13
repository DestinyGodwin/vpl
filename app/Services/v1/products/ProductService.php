<?php

namespace App\Services\v1\products;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    public function create($request): Product
    {
        $category = Category::findOrFail($request->category_id);
        $store = Auth::user()->stores()->where('type', $category->store_type)->firstOrFail();

        $product = $store->products()->create($request->only(['name', 'description', 'price', 'category_id']));

        if ($request->hasFile('images')) {
            $images = collect($request->file('images'))->map(fn($image) => [
                'image_path' => $image->store('products', 'public'),
            ]);
            $product->images()->createMany($images->all());
        }

        return $product->load('category', 'images', 'store.user');
    }

    public function getAll($perPage = 50)
    {
        return Product::with('category', 'images', 'store.user')->latest()->paginate($perPage);
    }

    public function update($id, $request)
    {
        $product = Auth::user()->stores()->with('products')->get()
            ->pluck('products')->flatten()->firstWhere('id', $id);

        if (!$product) abort(404, 'Product not found or not owned.');

        $product->update($request->only(['name', 'description', 'price', 'category_id']));

        if ($request->filled('image_ids_to_delete')) {
            $product->images()->whereIn('id', $request->image_ids_to_delete)->delete();
        }

        if ($request->hasFile('images')) {
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

    public function getByUser($type = null, $perPage = 50)
    {
        return Product::whereHas('store', function ($q) use ($type) {
            $q->where('user_id', Auth::id());
            if ($type) $q->where('type', $type);
        })->with('category', 'images', 'store.user')->latest()->paginate($perPage);
    }

    public function getByStore($storeId, $type = null, $perPage = 50)
    {
        return Product::whereHas('store', function ($q) use ($storeId, $type) {
            $q->where('id', $storeId);
            if ($type) $q->where('type', $type);
        })->with('category', 'images', 'store.user')->latest()->paginate($perPage);
    }

    public function getByUniversity($universityId, $type = null, $perPage = 50)
    {
        return Product::whereHas('store', function ($q) use ($universityId, $type) {
            $q->where('university_id', $universityId);
            if ($type) $q->where('type', $type);
        })->with('category', 'images', 'store.user')->latest()->paginate($perPage);
    }

    public function getByCountry(string $country, string $type = null, $perPage = 50)
    {
        return Product::whereHas('store.university', function ($query) use ($country) {
            $query->whereRaw('LOWER(country) = ?', [strtolower($country)]);
        })->when($type, fn($q) => $q->whereHas('store', fn($sq) => $sq->where('type', $type)))
          ->with('category', 'images', 'store.user')
          ->latest()->paginate($perPage);
    }

    public function getByState(string $state, string $type = null, $perPage = 50)
    {
        return Product::whereHas('store.university', function ($query) use ($state) {
            $query->whereRaw('LOWER(state) = ?', [strtolower($state)]);
        })->when($type, fn($q) => $q->whereHas('store', fn($sq) => $sq->where('type', $type)))
          ->with('category', 'images', 'store.user')
          ->latest()->paginate($perPage);
    }

    public function getByCategory($categoryId, $perPage = 50)
    {
        return Product::where('category_id', $categoryId)
            ->with('category', 'images', 'store.user')
            ->latest()->paginate($perPage);
    }

    public function getByStoreType(string $type, $perPage = 50)
    {
        return Product::whereHas('store', fn($q) => $q->where('type', $type))
            ->with('category', 'images', 'store.user')
            ->latest()->paginate($perPage);
    }
    
public function search(array $filters, int $perPage = 50)
{
    return Product::query()
        ->when($filters['category_id'] ?? null, fn($q, $categoryId) => 
            $q->where('category_id', $categoryId)
        )
        ->when($filters['min_price'] ?? null, fn($q, $min) => 
            $q->where('price', '>=', $min)
        )
        ->when($filters['max_price'] ?? null, fn($q, $max) => 
            $q->where('price', '<=', $max)
        )
        ->when($filters['store_id'] ?? null, fn($q, $storeId) =>
            $q->where('store_id', $storeId)
        )
        ->when($filters['store_type'] ?? null, fn($q, $type) =>
            $q->whereHas('store', fn($sq) => $sq->where('type', $type))
        )
        ->when($filters['university_id'] ?? null, fn($q, $universityId) =>
            $q->whereHas('store', fn($sq) => $sq->where('university_id', $universityId))
        )
        ->when($filters['state'] ?? null, fn($q, $state) =>
            $q->whereHas('store.university', fn($sq) =>
                $sq->whereRaw('LOWER(state) = ?', [strtolower($state)])
            )
        )
        ->when($filters['country'] ?? null, fn($q, $country) =>
            $q->whereHas('store.university', fn($sq) =>
                $sq->whereRaw('LOWER(country) = ?', [strtolower($country)])
            )
        )
        ->when($filters['keyword'] ?? null, fn($q, $keyword) =>
            $q->where('name', 'like', '%' . $keyword . '%')
        )
        ->with('category', 'images', 'store.user')
        ->latest()
        ->paginate($perPage);
}





}

