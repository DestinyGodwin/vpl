<?php

namespace App\Http\Controllers\v1;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'categories' => Category::all(),
        ]);
    }


    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json($category);
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'store_type' => ['required', 'in:regular,food'],
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

   
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
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
