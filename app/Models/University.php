<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class University extends Model
{
    use HasUuids,HasFactory;
    protected $fillable = [
        'name',
        'address',
       
        'state',
        'country', 
    ];
  
}
