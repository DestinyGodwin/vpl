<?php

namespace App\Http\Controllers\v1\producsts;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\producsts\ProductService;
use App\Http\Requests\v1\products\ProductRequest;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index() { return ProductResource::collection($this->productService->getAll()); }

    public function regular() { return ProductResource::collection($this->productService->getByStoreType('regular')); }

    public function food() { return ProductResource::collection($this->productService->getByStoreType('food')); }

    public function myProducts() { return ProductResource::collection($this->productService->getByUser()); }

    public function byUniversity($id) { return ProductResource::collection($this->productService->getByUniversity($id)); }

    public function regularByUniversity($id) { return ProductResource::collection($this->productService->getByUniversity($id, 'regular')); }

    public function foodByUniversity($id) { return ProductResource::collection($this->productService->getByUniversity($id, 'food')); }

    public function byCountry($id) { return ProductResource::collection($this->productService->getByCountry($id)); }

    public function regularByCountry($id) { return ProductResource::collection($this->productService->getByCountry($id, 'regular')); }

    public function foodByCountry($id) { return ProductResource::collection($this->productService->getByCountry($id, 'food')); }

    public function byCategory($categoryId) { return ProductResource::collection($this->productService->getByCategory($categoryId)); }

    public function regularByCategory($categoryId) { return ProductResource::collection($this->productService->getByCategory($categoryId, 'regular')); }

    public function foodByCategory($categoryId) { return ProductResource::collection($this->productService->getByCategory($categoryId, 'food')); }

    public function store(ProductRequest $request) { return new ProductResource($this->productService->create($request)); }

    public function show($id) { return new ProductResource($this->productService->findById($id)); }

    public function update(ProductRequest $request, $id) { return new ProductResource($this->productService->update($id, $request)); }

    public function destroy($id)
    {
        $this->productService->delete($id);
        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
