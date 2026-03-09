<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;


// Public 

// UC1 - Đăng nhập
Route::post('/auth/login', [AuthController::class, 'login']);

// UC3 - Quên mật khẩu
Route::post('/password/otp-requests', [AuthController::class, 'forgotPassword']); // Gửi OTP
Route::post('/password/otp/verifications', [AuthController::class, 'verifyOtp']);      // Xác thực OTP
Route::put('/password/reset', [AuthController::class, 'resetPassword']);  // Đặt lại mật khẩu

// Protected (cần token)
Route::middleware('auth:sanctum')->group(function () {

    // UC2 - Đăng xuất
    Route::delete('/auth/logout', [AuthController::class, 'logout']);

    // UC4 - Đổi mật khẩu
    Route::put('/profile/password', [AuthController::class, 'changePassword']);

    // UC5 - Xem thông tin cá nhân
    Route::get('/profile', [AuthController::class, 'me']);

    // UC6 - Chuyên môn giảng viên
    Route::get('/expertises', [AuthController::class, 'getExpertises']);//  Lấy danh sách
    Route::put('/lecturer/expertises', [AuthController::class, 'updateExpertise']); // Cập nhật
    // UC7 - Yêu cầu nghỉ phép dài hạn
    Route::post('/lecturer/leave-requests', [AuthController::class, 'createLeaveRequest']);
    // UC8 - Thông báo
    Route::get('/notifications', [AuthController::class, 'getNotifications']); // Lấy danh sách
    Route::put('/notifications/{id}/read', [AuthController::class, 'markAsRead']);      // Đánh dấu đã xem
});