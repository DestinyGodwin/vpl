<?php

namespace App\Http\Controllers\v1\products;

use App\Http\Controllers\Controller;
use App\Services\v1\products\ProductRequestMessageService;
use App\Http\Requests\v1\products\StoreProductRequestMessageRequest;

class ProductRequestMessageController extends Controller
{
    
    public function __construct(protected ProductRequestMessageService $service) {}

    public function store(StoreProductRequestMessageRequest $request)
    {
        $message = $this->service->store($request);
        return new ProductRequestMessageResource($message);
    }

    public function index($productRequestId)
    {
        $messages = $this->service->getMessagesForRequest($productRequestId);
        return ProductRequestMessageResource::collection($messages);
    }
}
