<?php

namespace App\Http\Resources\v1\products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\v1\products\ProductImageResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store' => $this->store->name,
            'store_image' => $this->store->image,
            'category' => $this->category->name,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'images' => ProductImageResource::collection($this->images),
            'user' => [
                'name' => $this->store->user->first_name,
                'email' =>auth('sanctum')->user()?->id ? $this->store->user->email : null,
                'phone' => auth('sanctum')->user()?->id ? $this->store->user->phone : null,
            ],
            'average_rating' => isset($this->reviews)
    ? round($this->reviews->avg('rating'), 1)
    : round($this->reviews()->avg('rating'), 1),
        ];
    }
}
