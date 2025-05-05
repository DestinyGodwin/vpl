<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Review extends Model
{
    use  HasUuids;
    protected $fillable = ['product_id', 'user_id', 'rating', 'comment'];



public function user()
{
    return $this->belongsTo(User::class);
}

public function product()
{
    return $this->belongsTo(Product::class);
}

}
