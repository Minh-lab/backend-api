<?php

use App\Http\Controllers\Internship\BusinessInternshipController;
use Illuminate\Support\Facades\Route;

//UC 45
Route::middleware(['auth:sanctum', 'role:company'])->prefix('business/internships')->group(function () {

    // Bước 2: Danh sách chờ xác nhận
    Route::get('/waiting-list', [BusinessInternshipController::class, 'getWaitingStudents']);

    // Bước 4: Xác nhận (Đồng ý/Từ chối)
    Route::post('/{id}/confirm', [BusinessInternshipController::class, 'confirmStudent']);
});
//UC 46
Route::middleware(['auth:sanctum', 'role:company'])->prefix('business/internships')->group(function () {

    // Bước 2: Danh sách sinh viên đang thực tập
    Route::get('/interns', [BusinessInternshipController::class, 'getInterns']);

    // Bước 6: Gửi đánh giá và điểm số
    Route::post('/{id}/evaluate', [BusinessInternshipController::class, 'evaluateStudent']);
});
