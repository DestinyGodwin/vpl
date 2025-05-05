<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Country extends Model
{
   use  HasUuids,HasApiTokens;
   protected $fillable = 
   [
    'name'
   ]
}
