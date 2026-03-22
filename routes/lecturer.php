<?php
use App\Http\Controllers\Lecturer\LecturerController;
use App\Http\Controllers\Internship\InternshipController;
use App\Http\Controllers\Capstone\CapstoneController;
use App\Http\Controllers\Lecturer\ProfileController;
use App\Http\Controllers\Lecturer\LeaveRequestController as LecturerLeaveRequestController;
use Illuminate\Support\Facades\Route;


// (Duyệt nghỉ phép - UC 48)
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk')->group(function () {
    Route::get('/lecturers', [LecturerController::class, 'index']);           // Bước 2: Danh sách
    Route::get('/lecturers/{id}', [LecturerController::class, 'show']);       // Bước 4: Chi tiết
    Route::post('/lecturers/{id}/approve', [LecturerController::class, 'approveLeave']); // Bước 5: Duyệt
});

// UC 47: Tìm kiếm giảng viên - Dành cho Sinh viên và VPK
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/lecturers/search', [LecturerController::class, 'search']);
});

//UC 40


Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {
    Route::get('/pending-reports', [InternshipController::class, 'getReportsToReview']); // UC 40
    Route::post('/reports/{id}/review', [InternshipController::class, 'reviewReport']);   // UC 40
});

//UC 36
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {
    // Tìm kiếm trong phạm vi SV hướng dẫn
    Route::get('/search', [InternshipController::class, 'search']);
});
//UC 41
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

// UC 23
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/capstones')->group(function () {

    // Bước 3: Xem danh sách chờ
    Route::get('/pending-registrations', [CapstoneController::class, 'getPendingRegistrations']);

    // Bước 4: Thực hiện Đồng ý/Từ chối
    Route::post('/registrations/{id}/confirm', [CapstoneController::class, 'confirmRegistration']);
});
// UC 24.1
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/capstones')->group(function () {
    // UC 24.1
    Route::get('/pending-topics', [CapstoneController::class, 'getPendingTopicsLecturer']);
    Route::post('/topics/{id}/review', [CapstoneController::class, 'reviewTopicLecturer']);
});

// UC 25: Phê duyệt báo cáo đồ án
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/capstones')->group(function () {

    // Bước 3: Xem danh sách báo cáo chờ duyệt
    Route::get('/pending-reports', [CapstoneController::class, 'getPendingReports']);

    // Bước 4-5: Xem chi tiết (Sử dụng chung Resource)
    Route::get('/reports/{id}', function ($id) {
        $report = \App\Models\CapstoneReport::findOrFail($id);
        return new \App\Http\Resources\Capstone\CapstoneReportDetailResource($report);
    });

    // Bước 6: Phê duyệt hoặc Từ chối
    Route::post('/reports/{id}/approve', [CapstoneController::class, 'approveReport']);
});
//UC 26
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/capstones')->group(function () {

    // UC 26: Chấm điểm đồ án
    // Bước 3: Lấy danh sách SV cần chấm
    Route::get('/grading', [CapstoneController::class, 'getGradingList']);

    // Bước 5: Xem chi tiết (Tận dụng Resource đã viết)
    Route::get('/grading/{id}', function ($id) {
        $capstone = \App\Models\Capstone::where('lecturer_id', auth()->id())->findOrFail($id);
        return new \App\Http\Resources\Capstone\CapstoneGradingResource($capstone);
    });

    // Bước 7: Thực hiện gửi điểm
    Route::post('/grading/{id}/submit', [CapstoneController::class, 'submitGrade']);
});
// UC 27
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/capstones')->group(function () {

    // UC 27: Chấm điểm phản biện
    // Bước 1 & 3: Lấy danh sách SV cần phản biện
    Route::get('/reviewing', [CapstoneController::class, 'getReviewingList']);

    // Bước 4 & 5: Xem chi tiết (Tận dụng Resource)
    Route::get('/reviewing/{capstoneId}', function ($capstoneId) {
        $assignment = \App\Models\CapstoneReviewer::where('lecturer_id', auth()->id())
            ->where('capstone_id', $capstoneId)
            ->firstOrFail();
        return new \App\Http\Resources\Capstone\CapstoneReviewResource($assignment);
    });

    // Bước 7: Gửi điểm phản biện
    Route::post('/reviewing/{capstoneId}/submit', [CapstoneController::class, 'submitReviewGrade']);
});
//UC 31
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('/lecturer/capstones')->group(function () {

    // UC 31.1: Xem và duyệt yêu cầu hủy
    Route::get('/cancellations', [CapstoneController::class, 'getPendingCancellationsLecturer']);
    Route::post('/cancellations/{id}/review', [CapstoneController::class, 'reviewCancellationLecturer']);


// UC6 - Chuyên môn | UC7 - Nghỉ phép (Lecturer)
});
Route::prefix('lecturer')
    ->middleware(['auth:sanctum', 'role:lecturer'])
    ->group(function () {
        Route::get('/expertises', [ProfileController::class , 'getExpertises']);
        Route::put('/expertises', [ProfileController::class , 'updateExpertises']);
        Route::post('/leave-requests', [LecturerLeaveRequestController::class , 'store']);
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
