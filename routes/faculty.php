<?php

use App\Http\Controllers\Faculty\MilestoneController;
use App\Http\Controllers\Faculty\SemesterController;
use App\Http\Controllers\Shared\MilestoneCheckController;
use App\Http\Controllers\Faculty\Council\CouncilController;
use App\Http\Controllers\Faculty\Council\CouncilMemberController;
use App\Http\Controllers\Faculty\Council\CouncilGradeController;
use App\Http\Controllers\Faculty\Council\CouncilScheduleController;
use App\Http\Controllers\Faculty\LecturerController;
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

        // UC: Lấy danh sách mốc thời gian nhập điểm hay không
        Route::post('/milestones/check', [MilestoneCheckController::class, 'check']);

        // MILESTONE CRUD
        Route::post('milestones',            [MilestoneController::class, 'store']);
        Route::put('milestones/{milestone}', [MilestoneController::class, 'update']);

        // UC: Lấy danh sách hội đồng có phân trang
        Route::get('/councils', [CouncilController::class, 'index']);

        // UC: Lấy danh sách giảng viên (cho phép chọn thành viên hội đồng)
        Route::get('/lecturers', [LecturerController::class, 'index']);

        // UC: Tạo hội đồng chấm thi
        Route::post('/councils', [CouncilMemberController::class, 'create']);

        // UC: Thay đổi thành viên hội đồng chấm thi
        Route::put('/councils/{councilId}', [CouncilMemberController::class, 'update']);

        // UC: Xếp lịch bảo vệ (thời gian, địa điểm)
        Route::put('/councils/{councilId}/schedule', [CouncilScheduleController::class, 'scheduleDefense']);

        // UC: Lấy danh sách thành viên hội đồng
        Route::get('/councils/{councilId}/members', [CouncilController::class, 'getMembers']);

        // UC: Xem danh sách sinh viên đủ điều kiện bảo vệ trong hội đồng
        Route::get('/councils/{councilId}/capstones', [CouncilGradeController::class, 'showCapstones']);

        // UC: Cập nhật điểm hội đồng bảo vệ cho sinh viên
        Route::put('/councils/{councilId}/capstones/{capstoneId}/council-grade', [CouncilGradeController::class, 'updateGrade']);
    });
