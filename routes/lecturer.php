<?php

use App\Http\Controllers\Lecturer\LecturerController;
use App\Http\Controllers\Lecturer\LeaveRequestController;
use App\Http\Controllers\Lecturer\ProfileController;
use Illuminate\Support\Facades\Route;

// UC6 - Chuyên môn | UC7 - Nghỉ phép (Lecturer)
Route::prefix('lecturer')
    ->middleware(['auth:sanctum', 'role:lecturer'])
    ->group(function () {
        Route::get('/expertises', [ProfileController::class , 'getExpertises']);
        Route::put('/expertises', [ProfileController::class , 'updateExpertises']);
        Route::post('/leave-requests', [LeaveRequestController::class , 'store']);
    });

// UC48 - VPK duyệt nghỉ phép
Route::prefix('vpk')
    ->middleware(['auth:sanctum', 'role:faculty_staff'])
    ->group(function () {
        Route::get('/lecturers', [LecturerController::class , 'index']);
        Route::get('/lecturers/{id}', [LecturerController::class , 'show']);
        Route::post('/lecturers/{id}/approve', [LecturerController::class , 'approveLeave']);
    });

// UC47 - Tìm kiếm giảng viên (VPK, Admin, Student)
Route::middleware(['auth:sanctum', 'role:faculty_staff,admin,student'])->group(function () {
    Route::get('/lecturers/search', [LecturerController::class , 'index']);
});