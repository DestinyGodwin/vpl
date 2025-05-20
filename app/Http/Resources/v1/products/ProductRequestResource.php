<?php

namespace App\Http\Resources\v1\products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductRequestResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'store_type' => $this->category->store_type,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
                'phone' => $this->user->phone,
            ],
            'images' => $this->images->map(fn($image) => [
                'id' => $image->id,
                'url' => asset('storage/' . $image->path),
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
