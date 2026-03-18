<?php

use App\Http\Controllers\Faculty\MilestoneController;
use App\Http\Controllers\Faculty\SemesterController;
use Illuminate\Support\Facades\Route;
 

Route::middleware(['auth:sanctum', 'role:faculty_staff'])
    ->prefix('faculty')
    ->group(function () {

        // SEMESTER 
        Route::get('semesters',      [SemesterController::class, 'index']); // Danh sách + phân trang
        Route::post('semesters',     [SemesterController::class, 'store']); // Thêm mới
        Route::get('semesters/{id}', [SemesterController::class, 'show']);  // Chi tiết + year_name

        //  MILESTONE theo SEMESTER 
        Route::get('semesters/{id}/milestones',               [SemesterController::class, 'milestones']);
        Route::get('semesters/{id}/milestones/{milestoneId}', [SemesterController::class, 'showMilestone']);
        Route::post('semesters/{id}/milestones',              [MilestoneController::class, 'storeForSemester']);

        // MILESTONE CRUD
        Route::post('milestones',            [MilestoneController::class, 'store']);
        Route::put('milestones/{milestone}', [MilestoneController::class, 'update']);
    });