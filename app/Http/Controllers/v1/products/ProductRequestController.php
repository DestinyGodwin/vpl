<?php

namespace App\Http\Controllers\v1\products;

use App\Http\Controllers\Controller;
use App\Services\v1\products\ProductRequestService;
use App\Http\Requests\v1\products\UpdateProductRequest;
use App\Http\Resources\v1\products\ProductRequestResource;
use App\Http\Requests\v1\products\StoreProductRequestRequest;

class ProductRequestController extends Controller
{
    public function __construct(protected ProductRequestService $service) {}

    public function store(StoreProductRequestRequest $request)
    {
        $productRequest = $this->service->store($request);
        return response()->json(['data' => $productRequest], 201);
    }
    public function index()
    {
        return ProductRequestResource::collection($this->service->index());
    }

    public function show($id)
    {
        return new ProductRequestResource($this->service->show($id));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        return new ProductRequestResource($this->service->update($request, $id));
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }
}