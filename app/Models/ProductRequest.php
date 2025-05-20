<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProductRequest extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = [ 'user_id' ,'category_id', 'name', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function messages() {
        return $this->hasMany(ProductRequestMessage::class);
    }
    public function images()
{
    return $this->hasMany(ProductRequestImage::class);
}
}
