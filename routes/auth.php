<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;


// PUBLIC - Không cần token


// UC1 - Đăng nhập
Route::post('/auth/login', [AuthController::class, 'login']);

// UC3 - Quên mật khẩu (3 bước)
Route::post('/password/otp-requests',       [AuthController::class, 'forgotPassword']);  // Bước 1: Gửi OTP
Route::post('/password/otp/verifications',  [AuthController::class, 'verifyOtp']);       // Bước 2: Xác thực OTP
Route::put('/password/reset',               [AuthController::class, 'resetPassword']);   // Bước 3: Đặt mật khẩu mới
//Reset

// PROTECTED - Cần token

Route::middleware('auth:sanctum')->group(function () {

    // UC2 - Đăng xuất
    Route::delete('/auth/logout', [AuthController::class, 'logout']);

    // UC4 - Đổi mật khẩu (đang đăng nhập)
    Route::put('/profile/password', [AuthController::class, 'changePassword']);

    // UC5 - Xem thông tin cá nhân
    Route::get('/profile', [AuthController::class, 'me']);
});