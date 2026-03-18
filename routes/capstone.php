<?php

use App\Http\Controllers\Capstone\CapstoneController;
use Illuminate\Support\Facades\Route;

/**
 * UC 22: Đăng ký đề tài đồ án (Student)
 * Tác nhân: Sinh viên (student)
 */
Route::middleware(['auth:sanctum', 'role:student'])->prefix('capstones')->group(function () {
    // Bước 1: Sinh viên đăng ký đề tài từ ngân hàng
    Route::post('/register-topic', [CapstoneController::class, 'registerTopic']);
    
    // Bước 2: Sinh viên đề xuất đề tài mới
    Route::post('/propose-topic', [CapstoneController::class, 'proposeTopic']);
    
    // Bước 3: Sinh viên xem trạng thái đơn đăng ký của mình
    Route::get('/my-requests', [CapstoneController::class, 'getMyRequests']);
    Route::get('/my-status', [CapstoneController::class, 'getMyCapstoneStatus']);
});

/**
 * UC 23: Xác nhận hướng dẫn đồ án
 * Tác nhân: Giảng viên (lecturer)
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('capstones')->group(function () {

    // Bước 3: Giảng viên xem danh sách sinh viên đang đăng ký mình hướng dẫn
    Route::get('/pending-requests', [CapstoneController::class, 'index']);

    // Bước 5: Giảng viên thực hiện Xác nhận hoặc Từ chối yêu cầu
    Route::post('/requests/{id}/confirm', [CapstoneController::class, 'confirmRegistration']);
});
