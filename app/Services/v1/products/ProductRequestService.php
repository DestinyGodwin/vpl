<?php

namespace App\Services\v1\products;

use App\Models\User;
use App\Models\Category;
use App\Models\ProductRequest;
use Illuminate\Support\Facades\Auth;
use App\Notifications\v1\products\ProductRequestedNotification;

class ProductRequestService
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
        $category = Category::findOrFail($request->category_id);
        $storeType = $category->store_type;
        $productRequest = ProductRequest::create([
            'user_id' => Auth::id(),
            'category_id' => $category->id,
            'name' => $request->name,
            'description' => $request->description,
        ]);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('product_requests', 'public');
    
                $productRequest->images()->create([
                    'path' => $path,
                ]);
            }
        }
        $usersToNotify = User::where('id', '!=', Auth::id())
            ->whereHas('stores', fn($q) => $q->where('type', $storeType))
            ->get();
    
        foreach ($usersToNotify as $user) {
            $user->notify(new ProductRequestedNotification($productRequest));
        }
    
        return $productRequest->load('user', 'category', 'images');
    }
}
