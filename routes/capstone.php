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
    Route::get('/pending-requests', [CapstoneController::class, 'getPendingRegistrations']);

    // Bước 5: Giảng viên thực hiện Xác nhận hoặc Từ chối yêu cầu
    Route::post('/requests/{id}/confirm', [CapstoneController::class, 'confirmRegistration']);
});
// UC 28
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/capstones')->group(function () {

    // Lấy danh sách giảng viên để phân công
    Route::get('/lecturers-slots', [CapstoneController::class, 'getLecturerAssignmentList']);

    // Thực hiện phân công hàng loạt
    Route::post('/assign-supervisor', [CapstoneController::class, 'assignSupervisor']);
});

// UC24.2
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/capstones')->group(function () {
    // UC 24.2
    Route::get('/pending-topics', [CapstoneController::class, 'getPendingTopicsVPK']);
    Route::post('/topics/{id}/confirm', [CapstoneController::class, 'confirmTopicVPK']);
});

// UC 29
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/capstones')->group(function () {

    // Lấy dữ liệu hội đồng cho Dialog (Bước 3, 5)
    Route::get('/councils-list', [CapstoneController::class, 'getCouncilsForAssignment']);

    // Thực hiện phân công (Bước 7)
    Route::post('/assign-council', [CapstoneController::class, 'assignCouncilAndReviewers']);
});
// UC 30
Route::middleware(['auth:sanctum', 'role:student'])->prefix('/student/capstones')->group(function () {

    // API yêu cầu hủy đồ án (UC 30)
    Route::post('/request-cancel', [CapstoneController::class, 'requestCancel']);
});
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('/vpk/capstones')->group(function () {

    // UC 31.2: Phê duyệt hủy cuối cùng
    Route::get('/cancellations', [CapstoneController::class, 'getPendingCancellationsVPK']);
    Route::post('/cancellations/{id}/confirm', [CapstoneController::class, 'reviewCancellationVPK']);
});
// UC 32
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('/vpk/capstones')->group(function () {
    Route::get('/statistics', [CapstoneController::class, 'indexStatistics']);
    Route::get('/statistics/export', [CapstoneController::class, 'exportStatistics']);
});
