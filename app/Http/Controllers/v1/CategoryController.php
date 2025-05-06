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

    // GET /api/categories/regular
    public function regularCategories()
    {
        $categories = Category::whereHas('products.store', function ($query) {
            $query->where('type', 'regular');
        })->distinct()->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    // GET /api/categories/food
    public function foodCategories()
    {
        $categories = Category::whereHas('products.store', function ($query) {
            $query->where('type', 'food');
        })->distinct()->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }
}
