<?php

namespace App\Http\Resources\v1\stores;

use auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'user' => [
                'name' => $this->user->first_name,
                'phone' => auth('sanctum')->user()?->id ? $this->user->phone : null,
            ],
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'image_url' => asset('storage/' . $this->image),
        
            'university' => $this->university->name ?? null,
          
        ];
    }
}
