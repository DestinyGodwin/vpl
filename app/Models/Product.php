<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = [
        'store_id',
        'name',
        'category_id',
        'description',
        'price',
        'status',
    ];
    public function store() {
        return $this->belongsTo(Store::class);
    }
    
    public function images() {
        return $this->hasMany(ProductImage::class);
    }
    
    public function category() {
        return $this->belongsTo(Category::class);
    }
    public function reviews()
{
    return $this->hasMany(Review::class);
}
}
