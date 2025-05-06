<?php

namespace App\Http\Controllers\v1;

use App\Models\Category;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'categories' => Category::all(),
        ]);
    }

    public function getByStoreType(string $store_type)
    {
        $validTypes = ['regular', 'food'];

        if (!in_array($store_type, $validTypes)) {
            return response()->json([
                'message' => 'Invalid store type. Valid types are: regular, food.',
            ], 422);
        }

        $categories = Category::where('store_type', $store_type)->get();

        return response()->json([
            'store_type' => $store_type,
            'categories' => $categories,
        ]);
    }
}
