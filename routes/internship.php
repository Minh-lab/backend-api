<?php

use App\Http\Controllers\Internship\InternshipController;
use Illuminate\Support\Facades\Route;

// Chỉ dành cho Sinh viên đã đăng nhập
Route::middleware(['auth:sanctum', 'role:student'])->prefix('internships')->group(function () {

    // UC 33: Đăng ký đợt thực tập
    Route::post('/register', [InternshipController::class, 'register']);
    // UC 34: Đăng ký doanh nghiệp
    Route::get('/check-company', [InternshipController::class, 'checkCompany']); // Bước 5
    Route::post('/register-company', [InternshipController::class, 'registerCompany']); // Bước 8

    // UC 35:
    // Lấy lịch sử nộp (Bước 4)
    Route::get('/reports/history', [InternshipController::class, 'getReportHistory']);

    // Thực hiện nộp báo cáo (Bước 6)
    Route::post('/reports/submit', [InternshipController::class, 'submitReport']);
});

// Group cho Văn phòng khoa (UC 42)
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/internships')->group(function () {
    Route::get('/pending', [InternshipController::class, 'getPendingRequests']);
    Route::post('/approve/{id}', [InternshipController::class, 'approveRequest']);
});
//(UC 36)
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/internships')->group(function () {
    // Tìm kiếm toàn hệ thống
    Route::get('/search', [InternshipController::class, 'search']);
});
// UC 37
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/internships')->group(function () {

    // Bước 4: Lấy danh sách doanh nghiệp để hiển thị trong Dialog
    Route::get('/available-companies', [InternshipController::class, 'getAvailableCompanies']);

    // Bước 6: Thực hiện nút "Phân công"
    Route::post('/assign-company', [InternshipController::class, 'assignCompany']);
    // UC 38: Yêu cầu hủy thực tập
    Route::post('/request-cancel', [InternshipController::class, 'requestCancelInternship']);
});
//UC 43
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/internships')->group(function () {

    // Bước 3: Lấy danh sách giảng viên để hiển thị trong Dialog phân công
    Route::get('/lecturer-slots', [InternshipController::class, 'getLecturerSlots']);

    // Bước 5: Thực hiện phân công GVHD cho danh sách sinh viên đã chọn
    Route::post('/assign-lecturer', [InternshipController::class, 'assignLecturer']);
});
//UC 44
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/internships')->group(function () {

    // Bước 5: Xem thống kê
    Route::get('/statistics', [InternshipController::class, 'statistics']);

    // Bước 7: Xuất báo cáo Excel
    Route::get('/export', [InternshipController::class, 'exportExcel']);
});

//UC 39
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/internships')->group(function () {
    // UC 39.2
    Route::get('/pending-cancels', [InternshipController::class, 'getPendingCancelVPK']);
    Route::post('/review-cancel/{id}', [InternshipController::class, 'reviewCancelVPK']);
});
