<?php

namespace App\Http\Controllers\v1\products;

use App\Http\Controllers\Controller;
use App\Services\v1\products\ProductService;
use App\Http\Requests\v1\products\ProductRequest;
use App\Http\Requests\v1\products\UpdateProductRequest;
use App\Http\Resources\v1\products\ProductResource;
use App\Http\Requests\v1\PaginationRequest;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(PaginationRequest $request)
    {
        return ProductResource::collection(
            $this->productService->getAll($request->getPerPage())
        );
    }

    public function store(ProductRequest $request)
    {
        return new ProductResource($this->productService->create($request));
    }

    public function show($id)
    {
        return new ProductResource($this->productService->findById($id));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = $this->productService->update($id, $request);
        return new ProductResource($product);
    }

    public function destroy($id)
    {
        $this->productService->delete($id);
        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function getByUser(PaginationRequest $request, $type = null)
    {
        return ProductResource::collection(
            $this->productService->getByUser($type, $request->getPerPage())
        );
    }

    public function getByStore(PaginationRequest $request, $storeId, $type = null)
    {
        return ProductResource::collection(
            $this->productService->getByStore($storeId, $type, $request->getPerPage())
        );
    }

    public function getByUniversity(PaginationRequest $request, $universityId, $type = null)
    {
        return ProductResource::collection(
            $this->productService->getByUniversity($universityId, $type, $request->getPerPage())
        );
    }

    public function getByCountry(PaginationRequest $request, string $country)
    {
        return ProductResource::collection(
            $this->productService->getByCountry($country, null, $request->getPerPage())
        );
    }

    public function byCountryWithType(PaginationRequest $request, string $country, string $type)
    {
        return ProductResource::collection(
            $this->productService->getByCountry($country, $type, $request->getPerPage())
        );
    }

    public function getByState(PaginationRequest $request, $state, $type = null)
    {
        return ProductResource::collection(
            $this->productService->getByState($state, $type, $request->getPerPage())
        );
    }

    public function byStateWithType(PaginationRequest $request, string $state, string $type)
    {
        return ProductResource::collection(
            $this->productService->getByState($state, $type, $request->getPerPage())
        );
    }

    public function getByCategory(PaginationRequest $request, $categoryId)
    {
        return ProductResource::collection(
            $this->productService->getByCategory($categoryId, $request->getPerPage())
        );
    }

    public function byStoreType(PaginationRequest $request, string $type)
    {
        return ProductResource::collection(
            $this->productService->getByStoreType($type, $request->getPerPage())
        );
    }

    public function search(PaginationRequest $request)
    {
        $filters = $request->only([
            'category_id',
            'min_price',
            'max_price',
            'store_id',
            'store_type',
            'university_id',
            'state',
            'country',
            'keyword'
        ]);

        return ProductResource::collection(
            $this->productService->search($filters, $request->getPerPage())
        );
    }
}
