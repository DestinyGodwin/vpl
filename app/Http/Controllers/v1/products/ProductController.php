<?php

namespace App\Http\Controllers\v1\products;

use App\Http\Controllers\Controller;
use App\Services\v1\products\ProductService;
use App\Http\Requests\v1\products\ProductRequest;
use App\Http\Resources\v1\products\ProductResource;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index() { return ProductResource::collection($this->productService->getAll()); }

    public function regular() { return ProductResource::collection($this->productService->getByStoreType('regular')); }

    public function food() { return ProductResource::collection($this->productService->getByStoreType('food')); }

    public function myProducts() { return ProductResource::collection($this->productService->getByUser()); }

    public function byCountry($id) { return ProductResource::collection($this->productService->getByCountry($id)); }

    public function regularByCountry($id) { return ProductResource::collection($this->productService->getByCountry($id, 'regular')); }

    public function foodByCountry($id) { return ProductResource::collection($this->productService->getByCountry($id, 'food')); }

    public function byCategory($categoryId) { return ProductResource::collection($this->productService->getByCategory($categoryId)); }

    public function regularByCategory($categoryId) { return ProductResource::collection($this->productService->getByCategory($categoryId, 'regular')); }

    public function foodByCategory($categoryId) { return ProductResource::collection($this->productService->getByCategory($categoryId, 'food')); }

    public function store(ProductRequest $request) { return new ProductResource($this->productService->create($request)); }

    public function show($id) { return new ProductResource($this->productService->findById($id)); }

    public function update(ProductRequest $request, $id) { return new ProductResource($this->productService->update($id, $request)); }
    public function getByUniversity(string $universityId, string $type = null)
    {
        $products = $this->productService->getByUniversity($universityId, $type);
    
        // Optionally return 404 if nothing is found
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found for the given parameters.'
            ], 404);
        }
    
        return ProductResource::collection($products);
    }
    public function destroy($id)
    {
        $this->productService->delete($id);
        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
