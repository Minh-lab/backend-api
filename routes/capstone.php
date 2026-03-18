<?php

use App\Http\Controllers\Capstone\CapstoneController;
use Illuminate\Support\Facades\Route;

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
