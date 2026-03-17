<?php

use App\Http\Controllers\Admin\AccountController;
use Illuminate\Support\Facades\Route;



//UC 7-12: Admin Account Management Routes


Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/accounts',        [AccountController::class, 'index']);          // UC 9 - Tìm kiếm
    Route::get('/accounts/{id}',   [AccountController::class, 'getAccountById']); // Lấy chi tiết tài khoản
    Route::post('/accounts',        [AccountController::class, 'store']);          // UC 10 - Thêm
    Route::put('/accounts/{id}',   [AccountController::class, 'update']);        // UC 11 - Sửa
    Route::delete('/accounts/{id}',   [AccountController::class, 'destroy']);       // UC 12 - Xoá
});

//UC 13-16: Topics Management Routes
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff')->group(function () {
    Route::get('/topics',        [TopicController::class, 'index']);          // UC 13 - Tìm kiếm
    Route::post('/topics',        [TopicController::class, 'store']);          // UC 14 - Thêm
    Route::put('/topics/{id}',   [TopicController::class, 'update']);        // UC 15 - Sửa
    Route::delete('/topics/{id}',   [TopicController::class, 'destroy']);       // UC 16 - Xoá
});
