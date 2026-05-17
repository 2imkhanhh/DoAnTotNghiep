<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FollowController;

use App\Http\Controllers\Api\LocationController;

Route::get('/locations/provinces', [LocationController::class, 'getProvinces']);
Route::get('/locations/wards/{provinceCode}', [LocationController::class, 'getWards']);

Route::group(['prefix' => 'auth'], function () {
    // Các route không cần đăng nhập
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // Các route bắt buộc phải có Token (auth:api)
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'changePassword']);
    });
});

// Các route yêu cầu đăng nhập nhưng không nằm trong prefix 'auth'
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user/posts', [PostController::class, 'userPosts']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}/edit', [PostController::class, 'edit']);
    Route::put('/posts/{id}', [PostController::class, 'update']);

    Route::post('/posts/{id}/favorite', [FavoriteController::class, 'toggleFavorite']);
    Route::get('/user/favorites', [FavoriteController::class, 'getFavorites']);
    Route::post('/users/{id}/follow', [FollowController::class, 'toggleFollow']);
    Route::get('/users/{id}/followers', [FollowController::class, 'getFollowers']);
    Route::get('/users/{id}/followings', [FollowController::class, 'getFollowings']);
});

// Nhóm route quản trị (Admin) - Đưa ra ngoài prefix 'auth' để URL ngắn gọn hơn
Route::group(['middleware' => ['auth:api', 'admin']], function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::post('/categories/{id}/attributes', [CategoryController::class, 'storeAttribute']);
    Route::put('/categories/{id}/attributes/{attribute_id}', [CategoryController::class, 'updateAttribute']);
    Route::delete('/categories/{id}/attributes/{attribute_id}', [CategoryController::class, 'destroyAttribute']);

    Route::get('/admin/posts', [PostController::class, 'adminIndex']);
    Route::put('/posts/{id}/status', [PostController::class, 'updateStatus']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});

Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/featured', [CategoryController::class, 'getFeaturedCategories']); // Thêm route danh mục nổi bật
Route::get('/categories/{id}/attributes', [CategoryController::class, 'getAttributes']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::get('/seller/{id}', [ProfileController::class, 'showPublic']);
