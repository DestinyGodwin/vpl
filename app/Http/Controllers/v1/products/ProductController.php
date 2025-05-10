<?php

namespace App\Http\Controllers\v1\products;

use App\Http\Controllers\Controller;
use App\Services\v1\products\ProductService;
use App\Http\Requests\v1\products\ProductRequest;
use App\Http\Resources\v1\products\ProductResource;
use App\Http\Requests\v1\products\UpdateProductRequest;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index() { return ProductResource::collection($this->productService->getAll()); }

   
    
    public function store(ProductRequest $request) { return new ProductResource($this->productService->create($request)); }

    public function show($id) { return new ProductResource($this->productService->findById($id)); }

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
    public function getByUser($type = null)
{
    return ProductResource::collection(
        $this->productService->getByUser($type)
    );
}

public function getByStore($storeId, $type = null)
{
    return ProductResource::collection(
        $this->productService->getByStore($storeId, $type)
    );
}

public function getByUniversity($universityId, $type = null)
{
    return ProductResource::collection(
        $this->productService->getByUniversity($universityId, $type)
    );
}

public function getByCountry(string $country)
{
    return ProductResource::collection(
        $$this->productService->getByCountry($country));
}
public function byCountryWithType(string $country, string $type)
{
    return ProductResource::collection($this->productService->getByCountry($country, $type));
}
public function getByState($countryId, $type = null)
{
    return ProductResource::collection(
        $this->productService->getByCountry($countryId, $type)
    );
}
public function byStateWithType(string $state, string $type)
{
    return ProductResource::collection($this->productService->getByState($state, $type));
}

public function getByCategory($categoryId, $type = null)
{
    return ProductResource::collection(
        $this->productService->getByCategory($categoryId, $type)
    );
}
public function byStoreType(string $type)
{
    return ProductResource::collection($this->productService->getByStoreType($type));
}

}
