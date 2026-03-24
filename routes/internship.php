<?php

use App\Http\Controllers\Internship\{
    StudentInternshipController,
    CompanyAssignmentController,
    CompanyApprovalController,
    LecturerAssignmentController,
    InternshipSearchController,
    CancellationController,
    GradingController,
    ReportReviewController,
    InternshipDetailController,
    InternshipStatisticsController
};
use Illuminate\Support\Facades\Route;

// Chỉ dành cho Sinh viên đã đăng nhập
Route::middleware(['auth:sanctum', 'role:student'])->prefix('internships')->group(function () {

    // UC 33: Lấy đợt đăng ký thực tập đang mở
    Route::get('/milestone', [StudentInternshipController::class, 'getMilestone']);
    // Lấy danh sách tất cả các đợt (Milestones) thực tập
    Route::get('/milestones', [StudentInternshipController::class, 'getMilestones']);
    // UC 33: Lấy trạng thái thực tập hiện tại
    Route::get('/status', [StudentInternshipController::class, 'getStatus']);
    // UC 33: Đăng ký đợt thực tập
    Route::post('/register', [StudentInternshipController::class, 'register']);
    // UC 34: Đăng ký doanh nghiệp
    Route::get('/check-company', [StudentInternshipController::class, 'checkCompany']); // Bước 5
    Route::post('/register-company', [StudentInternshipController::class, 'registerCompany']); // Bước 8

    // UC 35:
    // Lấy lịch sử nộp (Bước 4)
    Route::get('/reports/history', [StudentInternshipController::class, 'getReportHistory']);

    // Thực hiện nộp báo cáo (Bước 6)
    Route::post('/reports/submit', [StudentInternshipController::class, 'submitReport']);

    // UC 37: Lấy danh sách doanh nghiệp đối tác
    Route::get('/available-companies', [CompanyAssignmentController::class, 'getAvailableCompanies']);

    // UC 38: Yêu cầu hủy thực tập
    Route::post('/request-cancel', [CancellationController::class, 'requestCancel']);
});

// Group cho Văn phòng khoa (UC 42)
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/internships')->group(function () {
    Route::get('/pending', [CompanyApprovalController::class, 'getPendingRequests']);
    Route::get('/pending/{id}', [CompanyApprovalController::class, 'getRequestDetail']);
    Route::post('/approve/{id}', [CompanyApprovalController::class, 'approveRequest']);
    Route::put('/proposed-companies/{proposedCompanyId}', [CompanyApprovalController::class, 'updateProposedCompany']);
});
//(UC 36)
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/internships')->group(function () {
    // Tìm kiếm toàn hệ thống
    Route::get('/search', [InternshipSearchController::class, 'search']);
});
// UC 37
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/internships')->group(function () {

    // Bước 4: Lấy danh sách doanh nghiệp để hiển thị trong Dialog
    Route::get('/available-companies', [CompanyAssignmentController::class, 'getAvailableCompanies']);

    // Bước 6: Thực hiện nút "Phân công"
    Route::post('/assign-company', [CompanyAssignmentController::class, 'assignCompany']);
});
//UC 43
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/internships')->group(function () {

    // Bước 3: Lấy danh sách giảng viên để hiển thị trong Dialog phân công
    Route::get('/lecturer-slots', [LecturerAssignmentController::class, 'getLecturerSlots']);

    // Bước 5: Thực hiện phân công GVHD cho danh sách sinh viên đã chọn
    Route::post('/assign-lecturer', [LecturerAssignmentController::class, 'assignLecturer']);
});
//UC 44 - Thống kê và xuất báo cáo
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/internships')->group(function () {
    // Lấy danh sách học kỳ để lọc
    Route::get('/semesters', [InternshipStatisticsController::class, 'getSemesters']);
    
    // Lấy danh sách giảng viên để lọc
    Route::get('/filter/lecturers', [InternshipStatisticsController::class, 'getLecturersForFilter']);
    
    // Lấy danh sách doanh nghiệp để lọc
    Route::get('/filter/companies', [InternshipStatisticsController::class, 'getCompaniesForFilter']);
    
    // Bước 5: Xem thống kê
    Route::get('/statistics', [InternshipStatisticsController::class, 'getStatistics']);

    // Bước 7: Xuất báo cáo Excel/CSV
    Route::get('/export', [InternshipStatisticsController::class, 'exportReport']);
});

/**
 * UC 39.1: Lecturer duyệt yêu cầu hủy thực tập
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('internships')->group(function () {
    Route::get('/pending-cancellations', [CancellationController::class, 'getPendingCancelLecturer']);
    Route::post('/cancellations/{id}/review', [CancellationController::class, 'reviewCancelLecturer']);
});

/**
 * UC 40: Lecturer chấm điểm thực tập
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('internships')->group(function () {
    Route::get('/grading-list', [GradingController::class, 'getStudentsForGrading']);
    Route::post('/submit-grade/{id}', [GradingController::class, 'submitGrade']);
});

/**
 * UC 41: Lecturer duyệt báo cáo thực tập
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('internships')->group(function () {
    Route::get('/reports-to-review', [ReportReviewController::class, 'getReportsToReview']);
    Route::post('/reports/{id}/review', [ReportReviewController::class, 'reviewReport']);
});

//UC 39
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/internships')->group(function () {
    // UC 39.2
    Route::get('/pending-cancels', [CancellationController::class, 'getPendingCancelVPK']);
    Route::post('/review-cancel/{id}', [CancellationController::class, 'reviewCancelVPK']);
});

/**
 * Chi tiết thực tập - Dành cho Faculty Staff & Lecturer
 */
Route::middleware(['auth:sanctum'])->prefix('internships')->group(function () {
    Route::get('/{id}', [InternshipDetailController::class, 'show']);
});
