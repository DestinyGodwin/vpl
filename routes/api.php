<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\CategoryController;
use App\Http\Controllers\v1\WishlistController;
use App\Http\Controllers\v1\auth\AuthController;
use App\Http\Controllers\v1\auth\AdminController;
use App\Http\Controllers\v1\UniversityController;
use App\Http\Controllers\v1\stores\StoreController;
use App\Http\Controllers\v1\products\ReviewController;
use App\Http\Controllers\v1\products\ProductController;
use App\Http\Controllers\v1\products\NotificationController;
use App\Http\Controllers\v1\products\ProductRequestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', [ProductController::class, 'index']);

Route::prefix('v1')->group(function () {

    // Authentication Routes (Public)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/login', [AuthController::class, 'login'])->middleware(['logged.in', 'verified.otp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Public university routes
    Route::apiResource('universities', UniversityController::class)->only(['index', 'show']);

    // Public category routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::get('/store-type/{store_type}', [CategoryController::class, 'getByStoreType']);
    });

    Route::middleware(['auth:sanctum', 'verified.otp'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'getProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('profile/update', [AuthController::class, 'updateProfile']);

        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist', [WishlistController::class, 'store']);
        Route::delete('wishlist/{productId}', [WishlistController::class, 'destroy']);
        Route::get('wishlist/{productId}', [WishlistController::class, 'show']);

        // Store (write operations)
        Route::prefix('stores')->group(function () {
            Route::post('/', [StoreController::class, 'store']);
            Route::put('/{id}', [StoreController::class, 'update']);
            Route::delete('/{id}', [StoreController::class, 'destroy']);
            Route::get('/user/my', [StoreController::class, 'myStores']);
        });

        Route::prefix('product-requests')->group(function () {
            Route::get('/', [ProductRequestController::class, 'index']);
            Route::get('/{id}', [ProductRequestController::class, 'show']);
            Route::post('/', [ProductRequestController::class, 'store']);
            Route::put('/{id}', [ProductRequestController::class, 'update']);
            Route::delete('/{id}', [ProductRequestController::class, 'destroy']);
        });

        // Product (write operations)
        Route::prefix('products')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
            Route::get('/user', [ProductController::class, 'getByUser']);
            Route::get('/user/{type}', [ProductController::class, 'getByUser']);
        });

        // Reviews (write operations)
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store']);
            Route::delete('/{id}', [ReviewController::class, 'destroy']);
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread', [NotificationController::class, 'unread']);
            Route::get('/{id}', [NotificationController::class, 'show']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
            Route::delete('/delete-all', [NotificationController::class, 'destroyAll']);
        });
    });

    // Product read-only routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::get('/store/{storeId}', [ProductController::class, 'getByStore']);
        Route::get('/store/{storeId}/{type}', [ProductController::class, 'getByStore']);
        Route::get('/university/{universityId}', [ProductController::class, 'getByUniversity']);
        Route::get('/university/{universityId}/{type}', [ProductController::class, 'getByUniversity']);
        Route::get('/country/{country}', [ProductController::class, 'getByCountry']);
        Route::get('/country/{country}/{type}', [ProductController::class, 'byCountryWithType']);
        Route::get('/state/{state}', [ProductController::class, 'getByState']);
        Route::get('/state/{state}/{type}', [ProductController::class, 'byStateWithType']);
        Route::get('/type/{type}', [ProductController::class, 'byStoreType']);
        Route::get('/user/category/{categoryId}', [ProductController::class, 'getUserProductsByCategory']);
        Route::get('/university/{universityId}/category/{categoryId}', [ProductController::class, 'getUniversityProductsByCategory']);
        Route::get('/store/{storeId}/category/{categoryId}', [ProductController::class, 'getStoreProductsByCategory']);
        Route::get('/state/{state}/category/{categoryId}', [ProductController::class, 'getStateProductsByCategory']);
    });

    // Reviews read-only
    Route::prefix('reviews')->group(function () {
        Route::get('/product/{productId}', [ReviewController::class, 'byProduct']);
    });

    // Category write operations (admin only)
    Route::prefix('categories')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    // University write operations (admin only)
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::apiResource('universities', UniversityController::class)->only(['store', 'update', 'destroy']);
    });

    // Store read-only
    Route::prefix('stores')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/{id}', [StoreController::class, 'show']);
        Route::get('/type/{type}', [StoreController::class, 'byType']);
        Route::get('/university/{universityId}/{type?}', [StoreController::class, 'byUniversity']);
        Route::get('/country/{countryId}/{type?}', [StoreController::class, 'byCountry']);
    });

    // Admin routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/users', [AdminController::class, 'allUsers']);
        Route::get('/user/by-id', [AdminController::class, 'getById']);
        Route::get('/user/by-email', [AdminController::class, 'getByEmail']);
        Route::get('/users/university/{universityId}', [AdminController::class, 'usersByUniversity']);
        Route::get('/users/state/{state}', [AdminController::class, 'usersByState']);
        Route::get('/users/country/{country}', [AdminController::class, 'usersByCountry']);
        Route::post('/notify/users', [AdminController::class, 'notifyUsers']);
        Route::post('/notify/users/by-email', [AdminController::class, 'notifyUsersByEmail']);
        Route::post('/notify/university/{universityId}', [AdminController::class, 'notifyUniversity']);
        Route::post('/notify/state/{state}', [AdminController::class, 'notifyState']);
        Route::post('/notify/country/{country}', [AdminController::class, 'notifyCountry']);
    });
});
