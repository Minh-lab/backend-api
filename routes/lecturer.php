<?php

use App\Http\Controllers\Lecturer\LecturerController;
use App\Http\Controllers\Internship\InternshipController;
use App\Http\Controllers\Lecturer\LeaveRequestController;
use App\Http\Controllers\Lecturer\ProfileController;
use Illuminate\Support\Facades\Route;

// UC6 - Chuyên môn | UC7 - Nghỉ phép (Lecturer)
Route::prefix('lecturer')
    ->middleware(['auth:sanctum', 'role:lecturer'])
    ->group(function () {
        Route::get('/expertises',      [ProfileController::class, 'getExpertises']);
        Route::put('/expertises',      [ProfileController::class, 'updateExpertises']);
        Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    });

// UC48 - VPK duyệt nghỉ phép
Route::prefix('vpk')
    ->middleware(['auth:sanctum', 'role:vpk'])
    ->group(function () {
        Route::get('/lecturers',               [LecturerController::class, 'index']);
        Route::get('/lecturers/{id}',          [LecturerController::class, 'show']);
        Route::post('/lecturers/{id}/approve', [LecturerController::class, 'approveLeave']);
    });

// UC47 - Tìm kiếm giảng viên (VPK, Admin, Student)
Route::middleware(['auth:sanctum', 'role:vpk,admin,student'])->group(function () {
    Route::get('/lecturers/search', [LecturerController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {
    Route::get('/pending-reports', [InternshipController::class, 'getReportsToReview']); // UC 40
    Route::post('/reports/{id}/review', [InternshipController::class, 'reviewReport']);   // UC 40
});

//UC 36
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {
    // Tìm kiếm trong phạm vi SV hướng dẫn
    Route::get('/search', [InternshipController::class, 'search']);
});
//UC41
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {

    // Bước 3: Danh sách sinh viên cần chấm điểm
    Route::get('/grading-list', [InternshipController::class, 'getStudentsForGrading']);

    // Bước 7: Thực hiện gửi điểm
    Route::post('/{id}/grade', [InternshipController::class, 'submitGrade']);
});
//UC 39
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {
    // UC 39.1
    Route::get('/pending-cancels', [InternshipController::class, 'getPendingCancelLecturer']);
    Route::post('/review-cancel/{id}', [InternshipController::class, 'reviewCancelLecturer']);
});

