<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\ConversationController;

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Broadcast;

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

    // Các routes Chat Real-time
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{id}/messages', [ConversationController::class, 'messages']);
    Route::post('/conversations/{id}/messages', [ConversationController::class, 'sendMessage']);
    Route::post('/conversations/{id}/read', [ConversationController::class, 'markAsRead']);
    Route::get('/conversations/{id}/active-transactions', [ConversationController::class, 'activeTransactions']);

    // Route xác thực WebSockets bằng JWT
    Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
        return Broadcast::auth($request);
    });

    // Các routes Giao dịch (Transaction)
    Route::post('/transactions/request', [TransactionController::class, 'requestTransaction']);
    Route::put('/transactions/{id}/start', [TransactionController::class, 'startTransaction']);
    Route::put('/transactions/{id}/complete', [TransactionController::class, 'completeTransaction']);
    Route::put('/transactions/{id}/cancel', [TransactionController::class, 'cancelTransaction']);

    // Các routes Đánh giá (Review)
    Route::post('/users/{id}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
});

// Nhóm route quản trị (Admin)
Route::group(['middleware' => ['auth:api', 'admin']], function () {
    
    // Category Admin Routes
    Route::get('/admin/categories', [CategoryController::class, 'indexAll']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    // Category Attribute Admin Routes
    Route::post('/categories/{id}/attributes', [CategoryController::class, 'storeAttribute']);
    Route::put('/categories/attributes/{id}', [CategoryController::class, 'updateAttribute']);
    Route::delete('/categories/attributes/{id}', [CategoryController::class, 'destroyAttribute']);
    
    // Admin Posts Management
    Route::get('/admin/posts', [PostController::class, 'adminIndex']);
    Route::put('/admin/posts/{id}/status', [PostController::class, 'updateStatus']);

    // Admin Banners Management
    Route::get('/admin/banners', [BannerController::class, 'index']);
    Route::post('/admin/banners', [BannerController::class, 'store']);
    Route::post('/admin/banners/update-order', [BannerController::class, 'updateOrder']);
    Route::post('/admin/banners/{id}', [BannerController::class, 'update']);
    Route::delete('/admin/banners/{id}', [BannerController::class, 'destroy']);
    Route::patch('/admin/banners/{id}/toggle-active', [BannerController::class, 'toggleActive']);

    // Admin Users
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::patch('/admin/users/{id}/toggle-role', [AdminUserController::class, 'toggleRole']);
    Route::patch('/admin/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
});

Route::delete('/posts/{id}', [PostController::class, 'destroy']);

Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/featured', [CategoryController::class, 'getFeaturedCategories']); // Thêm route danh mục nổi bật
Route::get('/categories/{id}/attributes', [CategoryController::class, 'getAttributes']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::get('/posts/id/{id}', [PostController::class, 'showById']);
Route::get('/seller/{id}', [ProfileController::class, 'showPublic']);
Route::get('/users/{id}/reviews', [ReviewController::class, 'index']);

// Route cho Chatbot
Route::post('/chatbot/chat', [ChatbotController::class, 'chat']);
Route::get('/chatbot/history', [ChatbotController::class, 'history']);
Route::post('/chatbot/reset', [ChatbotController::class, 'reset']);

// Banners
Route::get('/banners/active', [BannerController::class, 'active']);