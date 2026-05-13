<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;

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

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']); // Dùng kèm _method=PUT khi có upload File
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        Route::post('/categories/{id}/attributes', [CategoryController::class, 'storeAttribute']);
        Route::put('categories/{id}/attributes/{attribute_id}', [CategoryController::class, 'updateAttribute']); // Thêm route Sửa
        Route::delete('categories/{id}/attributes/{attribute_id}', [CategoryController::class, 'destroyAttribute']); // Thêm route Xóa

        Route::post('/posts', [PostController::class, 'store']);
    });
});

Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}/attributes', [CategoryController::class, 'getAttributes']);
