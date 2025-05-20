<?php

namespace App\Services\v1\products;

use App\Models\User;
use App\Models\Category;
use App\Models\ProductRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        $currentUser = Auth::user();
        $universityId = $currentUser->university_id;

        $productRequest = ProductRequest::create([
            'user_id' => $currentUser->id,
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
        $usersToNotify = User::where('id', '!=', $currentUser->id)
            ->where('university_id', $universityId)
            ->whereHas('stores', function ($query) use ($storeType) {
                $query->where('type', $storeType);
            })
            ->get();

        foreach ($usersToNotify as $user) {
            $user->notify(new ProductRequestedNotification($productRequest));
        }

        return $productRequest->load('user', 'category', 'images');
    }


    public function destroy($id)
    {
        $productRequest = ProductRequest::where('user_id', Auth::id())->findOrFail($id);
        $productRequest->delete();
        return response()->json(['message' => 'Product request deleted successfully']);
    }
    public function update($request, $id)
    {
        $productRequest = ProductRequest::where('user_id', Auth::id())->findOrFail($id);

        $productRequest->update([
            'name' => $request->name ?? $productRequest->name,
            'description' => $request->description ?? $productRequest->description,
            'category_id' => $request->category_id ?? $productRequest->category_id,
        ]);

        // Remove old images if new ones are uploaded
        if ($request->hasFile('images')) {
            foreach ($productRequest->images as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }

            foreach ($request->file('images') as $image) {
                $path = $image->store('product_requests', 'public');
                $productRequest->images()->create(['path' => $path]);
            }
        }

        return $productRequest->load('user', 'category', 'images');
    }

    public function index()
    {
        return ProductRequest::with('user', 'category', 'images')->latest()->get();
    }

    public function show($id)
    {
        return ProductRequest::with('user', 'category', 'images')->findOrFail($id);
    }
}
