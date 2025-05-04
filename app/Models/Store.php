<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasUuids, HasFactory;


    protected $fillable = [
       'user_id',
        'university_id',
        'name',
        'image',
        'type',
        'description',
        'status',
        'next_payment_due',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'next_payment_due' => 'datetime',
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function university() {
        return $this->belongsTo(University::class);
    }
    
  
}
