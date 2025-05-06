<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Category extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'store_type'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
