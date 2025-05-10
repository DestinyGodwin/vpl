<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductRequestImage extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = [
'product_request_id',
        'path',
    ];
    public function productRequest()
    {
        return $this->belongsTo(ProductRequest::class);
    }
}
