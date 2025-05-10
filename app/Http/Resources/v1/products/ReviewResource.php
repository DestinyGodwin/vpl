<?php

namespace App\Http\Resources\v1\products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
             'rating' => $this->rating,
             'comment' => $this->comment,
             'user' => [
                 'name' => $this->user->first_name,
               
             ],
             'created_at' => $this->created_at->diffForHumans(),
         ];
     }
}
