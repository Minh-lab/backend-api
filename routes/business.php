<?php

use App\Http\Controllers\Internship\InternshipController;
use Illuminate\Support\Facades\Route;

//UC 45
Route::middleware(['auth:sanctum', 'role:company'])->prefix('business/internships')->group(function () {

    // Bước 2: Danh sách chờ xác nhận
    Route::get('/waiting-list', [InternshipController::class, 'getWaitingStudents']);

    // Bước 4: Xác nhận (Đồng ý/Từ chối)
    Route::post('/{id}/confirm', [InternshipController::class, 'confirmStudent']);
});
//UC 46
Route::middleware(['auth:sanctum', 'role:company'])->prefix('business/internships')->group(function () {

    // Bước 2: Danh sách sinh viên đang thực tập
    Route::get('/interns', [InternshipController::class, 'getInterns']);

    // Bước 6: Gửi đánh giá và điểm số
    Route::post('/{id}/evaluate', [InternshipController::class, 'evaluateStudent']);
});
