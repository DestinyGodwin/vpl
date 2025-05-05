<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\auth\AuthController;
use App\Http\Controllers\v1\UniversityController;
use App\Http\Controllers\v1\stores\StoreController;
use App\Http\Controllers\v1\products\ReviewController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::apiResource('universities', UniversityController::class);


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

    });
    Route::prefix('stores')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/regular', [StoreController::class, 'regularStores']);
        Route::get('/food', [StoreController::class, 'foodStores']);
    
        Route::get('/user/my', [StoreController::class, 'myStores']);
    
        Route::get('/university/{universityId}', [StoreController::class, 'byUniversity']);
        Route::get('/university/{universityId}/regular', [StoreController::class, 'regularByUniversity']);
        Route::get('/university/{universityId}/food', [StoreController::class, 'foodByUniversity']);
    
        Route::get('/country/{countryId}', [StoreController::class, 'byCountry']);
        Route::get('/country/{countryId}/regular', [StoreController::class, 'regularByCountry']);
        Route::get('/country/{countryId}/food', [StoreController::class, 'foodByCountry']);
    
        Route::post('/', [StoreController::class, 'store']);
        Route::get('/{id}', [StoreController::class, 'show']);
        Route::put('/{id}', [StoreController::class, 'update']);
        Route::delete('/{id}', [StoreController::class, 'destroy']);
    });
    Route::prefix('reviews')->group(function () {
        Route::get('product/{productId}', [ReviewController::class, 'byProduct']);
        Route::post('/', [ReviewController::class, 'store'])->middleware('auth:sanctum');
        Route::delete('{id}', [ReviewController::class, 'destroy'])->middleware('auth:sanctum');
    });
});