<?php

namespace App\Services\v1\products;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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
    public function create($request): Product
    {
        $store = Auth::user()->stores()->first();

        $product = $store->products()->create($request->only([
            'name', 'description', 'price', 'category_id'
        ]));

        if ($request->hasFile('images')) {
            $images = collect($request->file('images'))->map(function ($image) {
                return ['image_path' => $image->store('products', 'public')];
            });

            $product->images()->createMany($images->all());
        }

        return $product->load('category', 'images', 'store.user');
    }

    public function update(string $id, Request $request): Product
    {
        $product = Product::findOrFail($id);

        if ($product->store->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $product->update($request->only([
            'name', 'description', 'price', 'category_id'
        ]));

        if ($request->hasFile('images')) {
            $product->images()->delete();

            $images = collect($request->file('images'))->map(fn($image) => [
                'path' => $image->store('products', 'public'),
            ]);

            $product->images()->createMany($images->all());
        }

        return $product->load('category', 'images', 'store.user');
    }

 
// public function create($request)
// {
//     $user = Auth::user();

//     $store = $user->stores()->first();

//     if (!$store) {
//         throw ValidationException::withMessages([
//             'store' => ['You have not created a store yet.']
//         ]);
//     }

//     $category = Category::find($request->category_id);

//     if (!$category || $category->store_type !== $store->type) {
//         throw ValidationException::withMessages([
//             'category_id' => ['Upload the product to the proper store type.']
//         ]);
//     }

 
//     $product = $store->products()->create($request->only([
//         'name', 'description', 'price', 'category_id'
//     ]));

   
//     if ($request->hasFile('images')) {
//         $images = collect($request->file('images'))->map(function ($image) {
//             return ['path' => $image->store('products', 'public')];
//         });
//         $product->images()->createMany($images->all());
//     }

//     return $product->load('category', 'images', 'store.user');
// }

// public function update(string $id, Request $request): Product
// {
//     $user = Auth::user();
//     $product = Product::findOrFail($id);

//     if ($product->store->user_id !== $user->id) {
//         abort(403, 'Unauthorized.');
//     }

//     $store = $user->stores()->first();

//     if (!$store) {
//         throw ValidationException::withMessages([
//             'store' => ['You have not created a store yet.']
//         ]);
//     }

//     if ($request->filled('category_id')) {
//         $category = Category::find($request->category_id);

//         if (!$category || $category->store_type !== $store->type) {
//             throw ValidationException::withMessages([
//                 'category_id' => ['Upload the product to the proper store type.']
//             ]);
//         }
//     }

  
//     $product->update($request->only(['name', 'description', 'price', 'category_id']));
//     if ($request->hasFile('images')) {
//         $product->images()->delete();

//         $images = collect($request->file('images'))->map(function ($image) {
//             return ['path' => $image->store('products', 'public')];
//         });

//         $product->images()->createMany($images->all());
//     }

//     return $product->load('category', 'images', 'store.user');
// }

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
