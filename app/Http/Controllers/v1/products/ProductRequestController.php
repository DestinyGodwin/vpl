<?php

namespace App\Http\Controllers\v1\products;

use App\Http\Controllers\Controller;
use App\Services\v1\products\ProductRequestService;
use App\Http\Requests\v1\products\StoreProductRequestRequest;

class ProductRequestController extends Controller
{
    public function __construct(protected ProductRequestService $service) {}

    public function store(StoreProductRequestRequest $request)
    {
        $productRequest = $this->service->store($request);
        return response()->json(['data' => $productRequest], 201);
    }
}
