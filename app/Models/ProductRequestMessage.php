<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductRequestMessage extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = ['product_request_id', 'sender_id', 'message'];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function productRequest() {
        return $this->belongsTo(ProductRequest::class);
    }
}
