<?php

use App\Http\Controllers\Capstone\{
    StudentCapstoneController,
    RegistrationController,
    SupervisorAssignmentController,
    TopicApprovalController,
    ReportApprovalController,
    GradingController,
    ReviewController,
    CouncilController,
    CancellationController,
    StatisticsController
};
use Illuminate\Support\Facades\Route;

/**
 * UC 22: Đăng ký đề tài đồ án (Student)
 * Tác nhân: Sinh viên (student)
 */
Route::middleware(['auth:sanctum', 'role:student'])->prefix('capstones')->group(function () {
    // Bước 1: Sinh viên đăng ký đề tài từ ngân hàng
    Route::post('/register-topic', [StudentCapstoneController::class, 'registerTopic']);
    
    // Bước 2: Sinh viên đề xuất đề tài mới
    Route::post('/propose-topic', [StudentCapstoneController::class, 'proposeTopic']);
    
    // Bước 3: Sinh viên xem trạng thái đơn đăng ký của mình
    Route::get('/my-requests', [StudentCapstoneController::class, 'getMyRequests']);
    Route::get('/my-status', [StudentCapstoneController::class, 'getMyCapstoneStatus']);
});

/**
 * UC 23: Xác nhận hướng dẫn đồ án
 * Tác nhân: Giảng viên (lecturer)
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('capstones')->group(function () {

    // Bước 3: Giảng viên xem danh sách sinh viên đang đăng ký mình hướng dẫn
    Route::get('/pending-requests', [RegistrationController::class, 'getPendingRegistrations']);

    // Bước 5: Giảng viên thực hiện Xác nhận hoặc Từ chối yêu cầu
    Route::post('/requests/{id}/confirm', [RegistrationController::class, 'confirmRegistration']);
});

/**
 * UC 24.1: Giảng viên duyệt đề tài
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('capstones')->group(function () {
    Route::get('/pending-topics', [TopicApprovalController::class, 'getPendingTopicsLecturer']);
    Route::post('/topics/{id}/review', [TopicApprovalController::class, 'reviewTopicLecturer']);
});

/**
 * UC 24.2: Faculty Staff duyệt đề tài và danh sách đăng ký
 */
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/capstones')->group(function () {
    Route::get('/registrations', [RegistrationController::class, 'getPendingRegistrationsVPK']);
    Route::get('/pending-topics', [TopicApprovalController::class, 'getPendingTopicsVPK']);
    Route::post('/topics/{id}/confirm', [TopicApprovalController::class, 'confirmTopicVPK']);
});

/**
 * UC 25: Giảng viên duyệt báo cáo
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('capstones')->group(function () {
    Route::get('/pending-reports', [ReportApprovalController::class, 'getPendingReports']);
    Route::post('/reports/{id}/approve', [ReportApprovalController::class, 'approveReport']);
});

/**
 * UC 26: Giảng viên chấm điểm
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('capstones')->group(function () {
    Route::get('/grading-list', [GradingController::class, 'getGradingList']);
    Route::post('/submit-grade/{id}', [GradingController::class, 'submitGrade']);
});

/**
 * UC 27: Giảng viên phản biện
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('capstones')->group(function () {
    Route::get('/reviewing-list', [ReviewController::class, 'getReviewingList']);
    Route::post('/submit-review-grade/{capstoneId}', [ReviewController::class, 'submitReviewGrade']);
});

/**
 * UC 32: Thống kê và xuất báo cáo
 * IMPORTANT: Must be BEFORE UC 28 to prevent /{id} matching /statistics
 */
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/capstones')->group(function () {
    // Các endpoint để lấy dữ liệu cho dropdown filter
    Route::get('/filter/semesters', [StatisticsController::class, 'getSemesters']);
    Route::get('/filter/lecturers', [StatisticsController::class, 'getLecturers']);
    Route::get('/filter/councils', [StatisticsController::class, 'getCouncils']);
    
    // Các endpoint chính
    Route::get('/statistics', [StatisticsController::class, 'indexStatistics']);
    Route::get('/statistics/export', [StatisticsController::class, 'exportStatistics']);
});

/**
 * UC 28: Faculty Staff quản lý danh sách đồ án + phân công GVHD
 */
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/capstones')->group(function () {
    // RECOMMENDED: Sử dụng endpoint mới cho VPK (với xử lý cancel request riêng)
    Route::get('/', [SupervisorAssignmentController::class, 'getVPKCapstonesList']);
    
    Route::get('/lecturers-slots', [SupervisorAssignmentController::class, 'getLecturerAssignmentList']);
    Route::get('/advisors', [SupervisorAssignmentController::class, 'getAdvisorsWithSlots']);
    Route::get('/{id}', [SupervisorAssignmentController::class, 'getCapstoneDetail']);
    Route::post('/assign-supervisor', [SupervisorAssignmentController::class, 'assignSupervisor']);
});

/**
 * UC 29: Faculty Staff phân công hội đồng và GVPB
 */
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/capstones')->group(function () {
    Route::get('/councils-list', [CouncilController::class, 'getCouncilsForAssignment']);
    Route::post('/assign-council', [CouncilController::class, 'assignCouncilAndReviewers']);
});

/**
 * UC 30: Sinh viên yêu cầu hủy đồ án
 */
Route::middleware(['auth:sanctum', 'role:student'])->prefix('/student/capstones')->group(function () {
    Route::post('/request-cancel', [CancellationController::class, 'requestCancel']);
});

/**
 * UC 31.1: Giảng viên duyệt yêu cầu hủy
 */
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('capstones')->group(function () {
    Route::get('/pending-cancellations', [CancellationController::class, 'getPendingCancellationsLecturer']);
    Route::post('/cancellations/{id}/review', [CancellationController::class, 'reviewCancellationLecturer']);
});

/**
 * UC 31.2: Faculty Staff duyệt hủy cuối cùng (API cũ - DEPRECATED)
 */
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/capstones')->group(function () {
    Route::get('/cancellations', [CancellationController::class, 'getPendingCancellationsVPK']);
    Route::post('/cancellations/{id}/confirm', [CancellationController::class, 'reviewCancellationVPK']);
});

/**
 * UC 31.2: Faculty Staff duyệt hủy cuối cùng (API mới - RECOMMENDED)
 * Path: /faculty_staff/capstones/cancel-requests
 * Riêng biệt, không share logic với lecturer
 */
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff/capstones')->group(function () {
    Route::get('/cancel-requests/pending', [CancellationController::class, 'getVPKPendingCancelRequests']);
    Route::post('/cancel-requests/{capstone_request_id}/process', [CancellationController::class, 'processVPKCancellationRequest']);
});
