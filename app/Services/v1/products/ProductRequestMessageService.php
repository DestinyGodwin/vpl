<?php

namespace App\Services\v1\products;

use Illuminate\Support\Facades\Auth;
use App\Models\ProductRequestMessage;

class ProductRequestMessageService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function store( $request): ProductRequestMessage
    {
        return ProductRequestMessage::create([
            'product_request_id' => $request->product_request_id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
        ]);
    }

    public function getMessagesForRequest($productRequestId)
    {
        return ProductRequestMessage::where('product_request_id', $productRequestId)
            ->with('sender')
            ->latest()
            ->get();
    }
}
