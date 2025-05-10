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
                'name' => $this->user->first_name,
    
            ],
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'path' => $image->path,
                    'url' => asset('storage/' . $image->path),
                ];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
    }
}
