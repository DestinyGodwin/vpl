<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\products\ProductController;

// Route::get('/', function () {
//     return view('welcome');
    
// });
Route::get('/', [ProductController::class, 'index']);
