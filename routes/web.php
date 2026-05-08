<?php

use Illuminate\Support\Facades\Route;

// Bắt tất cả các route (trừ API) và trả về vỏ bọc SPA (app.blade.php)
// Frontend (Vue Router) sẽ tự động xử lý các đường dẫn này
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
