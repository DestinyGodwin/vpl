<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\CategoryController;
use App\Http\Controllers\v1\auth\AuthController;
use App\Http\Controllers\v1\UniversityController;
use App\Http\Controllers\v1\stores\StoreController;
use App\Http\Controllers\v1\products\ReviewController;
use App\Http\Controllers\v1\products\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    // Authentication Routes (Public)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // University Resource
    Route::apiResource('universities', UniversityController::class);


    // Authenticated routes
    Route::middleware('auth:sanctum', 'verified.otp')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::apiResource('universities', UniversityController::class)->only('store');
        // Store (write operations)
        Route::prefix('stores')->group(function () {
            Route::post('/', [StoreController::class, 'store']);
            Route::put('/{id}', [StoreController::class, 'update']);
            Route::delete('/{id}', [StoreController::class, 'destroy']);
            Route::get('/user/my', [StoreController::class, 'myStores']);
            

        });

        // Product (write operations)
        Route::prefix('products')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
        });

        // Reviews (write operations)
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store']);
            Route::delete('/{id}', [ReviewController::class, 'destroy']);
        });
    });

    // Store (public GET routes)
    Route::prefix('stores')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/{id}', [StoreController::class, 'show']);
    
        Route::get('/type/{type}', [StoreController::class, 'byType']); // handles regular/food
        Route::get('/university/{universityId}/{type?}', [StoreController::class, 'byUniversity']);
        Route::get('/country/{countryId}/{type?}', [StoreController::class, 'byCountry']);
    });

    // Product (public GET routes)
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/regular', [ProductController::class, 'regular']);
        Route::get('/food', [ProductController::class, 'food']);

        Route::get('/user/my', [ProductController::class, 'myProducts']);

        Route::get('/university/{id}', [ProductController::class, 'byUniversity']);
        Route::get('/university/{id}/regular', [ProductController::class, 'regularByUniversity']);
        Route::get('/university/{id}/food', [ProductController::class, 'foodByUniversity']);

        Route::get('/country/{id}', [ProductController::class, 'byCountry']);
        Route::get('/country/{id}/regular', [ProductController::class, 'regularByCountry']);
        Route::get('/country/{id}/food', [ProductController::class, 'foodByCountry']);

        Route::get('/category/{categoryId}', [ProductController::class, 'byCategory']);
        Route::get('/category/{categoryId}/regular', [ProductController::class, 'regularByCategory']);
        Route::get('/category/{categoryId}/food', [ProductController::class, 'foodByCategory']);

        Route::get('/{id}', [ProductController::class, 'show']);
    });

    // Reviews (public GET routes)
    Route::prefix('reviews')->group(function () {
        Route::get('/product/{productId}', [ReviewController::class, 'byProduct']);
    });
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    
        Route::get('/store-type/{store_type}', [CategoryController::class, 'getByStoreType']);
    });
});