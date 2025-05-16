<?php

namespace App\Http\Controllers\v1\products;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\products\ProductService;
use App\Http\Requests\v1\products\ProductRequest;
use App\Http\Resources\v1\products\ProductResource;
use App\Http\Requests\v1\products\UpdateProductRequest;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    // public function index(Request $request)
    // {
    //     return ProductResource::collection($this->productService->getAll($request->query('per_page')));
    // }
public function index(Request $request)
{
    $perPage = (int) $request->query('per_page', 50);
    return ProductResource::collection($this->productService->getAll($perPage));
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

    public function getByUser(Request $request, $type = null)
    {
        return ProductResource::collection(
            $this->productService->getByUser($type, $request->query('per_page'))
        );
    }

    public function getByStore(Request $request, $storeId, $type = null)
    {
        return ProductResource::collection(
            $this->productService->getByStore($storeId, $type, $request->query('per_page'))
        );
    }

    public function getByUniversity(Request $request, $universityId, $type = null)
    {
        return ProductResource::collection(
            $this->productService->getByUniversity($universityId, $type, $request->query('per_page'))
        );
    }

    public function getByCountry(Request $request, string $country)
    {
        return ProductResource::collection(
            $this->productService->getByCountry($country, null, $request->query('per_page'))
        );
    }

    public function byCountryWithType(Request $request, string $country, string $type)
    {
        return ProductResource::collection(
            $this->productService->getByCountry($country, $type, $request->query('per_page'))
        );
    }

    public function getByState(Request $request, $state, $type = null)
    {
        return ProductResource::collection(
            $this->productService->getByState($state, $type, $request->query('per_page'))
        );
    }

    public function byStateWithType(Request $request, string $state, string $type)
    {
        return ProductResource::collection(
            $this->productService->getByState($state, $type, $request->query('per_page'))
        );
    }

    public function getByCategory(Request $request, $categoryId)
    {
        return ProductResource::collection(
            $this->productService->getByCategory($categoryId, $request->query('per_page'))
        );
    }

    public function byStoreType(Request $request, string $type)
    {
        return ProductResource::collection(
            $this->productService->getByStoreType($type, $request->query('per_page'))
        );
    }
    public function search(Request $request)
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

    $perPage = $request->query('per_page', 50);

    return ProductResource::collection(
        $this->productService->search($filters, $perPage)
    );
}

}