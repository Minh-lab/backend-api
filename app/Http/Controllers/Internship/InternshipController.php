<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

// Models
use App\Models\Internship;
use App\Models\Milestone;
use App\Models\Company;
use App\Models\ProposedCompany;
use App\Models\InternshipRequest;
use App\Models\InternshipReport;
use App\Models\Lecturer;
use App\Models\LecturerLeave;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Models\Semester;

// Requests
use App\Http\Requests\Internship\RegisterInternshipRequest;
use App\Http\Requests\Internship\SearchInternshipRequest;
use App\Http\Requests\Internship\AssignCompanyRequest;
use App\Http\Requests\Internship\AssignLecturerRequest;
use App\Http\Requests\Internship\GradeInternshipRequest;
use App\Http\Requests\Internship\EvaluateStudentRequest;
use App\Http\Requests\Internship\ReviewCancelRequest;
use App\Http\Requests\Internship\CancelInternshipRequest;
use App\Http\Requests\Internship\RegisterCompanyRequest;
use App\Http\Requests\Internship\ApproveCompanyRequest;
use App\Http\Requests\Internship\SubmitReportRequest;
use App\Http\Requests\Internship\ReviewReportRequest;
use App\Http\Requests\Internship\StatisticInternshipRequest;
use App\Http\Requests\Internship\ConfirmStudentRequest;

// Resources
use App\Http\Resources\Internship\InternshipResource;
use App\Http\Resources\Internship\InternshipSearchResource;
use App\Http\Resources\Internship\CompanySlotResource;
use App\Http\Resources\Internship\LecturerSlotResource;
use App\Http\Resources\Internship\InternshipGradeResource;
use App\Http\Resources\Internship\CompanyInternshipResource;
use App\Http\Resources\Internship\CancelRequestDetailResource;
use App\Http\Resources\Internship\CancelRequestResource;
use App\Http\Resources\Internship\InternshipRequestResource;
use App\Http\Resources\Internship\CompanyPendingResource;
use App\Http\Resources\Internship\ReportReviewResource;
use App\Http\Resources\Internship\InternshipStatisticResource;
use App\Http\Resources\Internship\BusinessStudentResource;
use App\Http\Resources\Internship\InternshipReportResource;

class InternshipController extends Controller
{
    /**
     * UC 33: Lấy trạng thái thực tập hiện tại của sinh viên
     */
    public function getStatus()
    {
        $studentId = auth()->id();
        $internship = Internship::where('student_id', $studentId)
            ->with(['company', 'semester', 'requests'])
            ->latest()
            ->first();

        if (!$internship) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng ký đợt thực tập.'
            ], 404);
        }

        return new InternshipResource($internship);
    }

    /**
     * UC 33: Lấy đợt đăng ký thực tập đang mở
     */
    public function getRegisterMilestone()
    {
        $milestone = Milestone::where('type', Milestone::TYPE_INTERNSHIP)
            ->upcoming()
            ->first();

        if (!$milestone) {
            return response()->json([
                'success' => false,
                'message' => 'Hiện không có đợt đăng ký thực tập nào đang mở.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $milestone
        ]);
    }

    public function register(RegisterInternshipRequest $request)
    {
        $studentId = auth()->id();
        $milestone = Milestone::findOrFail($request->milestone_id);

        // 1. Ngoại lệ 3b: Kiểm tra hết hạn đăng ký
        if (Carbon::now()->gt($milestone->end_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã hết hạn đăng ký thực tập.'
            ], 400);
        }

        // 2. Ngoại lệ 3a (BR-1): Kiểm tra đã đăng ký thực tập trước đó trong học kỳ này chưa
        $alreadyRegistered = Internship::where('student_id', $studentId)
            ->where('semester_id', $milestone->semester_id)
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đăng ký đợt thực tập.'
            ], 400);
        }

        // 3. Luồng chính: Tạo bản ghi thực tập mới
        return DB::transaction(function () use ($studentId, $milestone) {
            $internship = Internship::create([
                'student_id' => $studentId,
                'semester_id' => $milestone->semester_id,
                'status' => 'INITIALIZED', // Trạng thái khởi tạo bản ghi
            ]);

            return (new InternshipResource($internship))
                ->additional(['message' => 'Đã đăng ký đợt thực tập thành công.']);
        });
    }
    /**
     * UC 34 - Bước 5 & 6: Kiểm tra Mã số thuế trong DB
     */
    public function checkCompany(Request $request)
    {
        $taxCode = $request->query('tax_code');

        // Tìm trong DN chính thức (Dùng usercode làm MST theo Model Company)
        $official = Company::where('usercode', $taxCode)->first();
        if ($official) {
            return response()->json([
                'exists' => true,
                'type' => 'OFFICIAL',
                'readonly' => true, // BR-1
                'data' => $official
            ]);
        }

        // Tìm trong DN do sinh viên đề xuất
        $proposed = ProposedCompany::where('tax_code', $taxCode)->first();
        if ($proposed) {
            return response()->json([
                'exists' => true,
                'type' => 'PROPOSED',
                'readonly' => false, // BR-1
                'data' => $proposed
            ]);
        }

        return response()->json(['exists' => false, 'type' => 'NEW', 'readonly' => false]);
    }

    /**
     * UC 34 - Bước 8-11: Lưu thông tin đăng ký doanh nghiệp
     */
    public function registerCompany(RegisterCompanyRequest $request)
    {
        $studentId = auth()->id();
        // Bước 9: Kiểm tra tính hợp lệ về thời gian (BR-3)
        $milestone = Milestone::where('type', Milestone::TYPE_INTERNSHIP)->upcoming()->first();
        if (!$milestone) {
            return response()->json(['message' => 'Đã hết hạn đăng ký doanh nghiệp (BR-3)'], 400);
        }

        return DB::transaction(function () use ($request) {
            $companyId = null;
            $proposedId = null;

            // Xử lý thông tin doanh nghiệp (BR-1)
            $official = Company::where('usercode', $request->tax_code)->first();
            if ($official) {
                $companyId = $official->company_id;
            }
            else {
                $proposed = ProposedCompany::updateOrCreate(
                ['tax_code' => $request->tax_code],
                [
                    'name' => $request->name,
                    'address' => $request->address,
                    'contact_email' => $request->email,
                ]
                );
                $proposedId = $proposed->proposed_company_id;
            }

            // Upload file minh chứng (Bước 7)
            $path = $request->file('file')->store('internship/proofs', 'public');

            // Bước 10: Lưu bản ghi đăng ký
            $internReq = InternshipRequest::create([
                'internship_id' => $request->internship_id,
                'company_id' => $companyId,
                'proposed_company_id' => $proposedId,
                'type' => InternshipRequest::TYPE_COMPANY_REG,
                'status' => InternshipRequest::STATUS_PENDING_FACULTY,
                'student_message' => $request->position, // Lưu vị trí thực tập
                'file_path' => $path,
            ]);

            return (new InternshipRequestResource($internReq))
                ->additional(['success' => true, 'message' => 'Đăng ký doanh nghiệp thành công (Bước 11)']);
        });
    }
    /**
     * UC 42 - Bước 2: Hiển thị danh sách doanh nghiệp chờ duyệt
     */
    public function getPendingRequests()
    {
        // BR-1: Chức năng duyệt chỉ mở sau khi đóng cổng đăng ký
        $isClosed = Milestone::where('type', Milestone::TYPE_INTERNSHIP)
            ->where('end_date', '<', Carbon::now())
            ->exists();

        if (!$isClosed) {
            return response()->json(['message' => 'Cổng đăng ký của sinh viên chưa đóng (BR-1).'], 400);
        }

        $requests = InternshipRequest::with(['company', 'proposedCompany', 'internship.student'])
            ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
            ->get();

        return CompanyPendingResource::collection($requests);
    }

    /**
     * UC 42 - Bước 6-10: Xử lý Duyệt hoặc Từ chối
     */
    public function approveRequest(ApproveCompanyRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $internReq = InternshipRequest::findOrFail($id);

            if ($request->status === InternshipRequest::STATUS_APPROVED) {
                // BR-2: Xử lý cấp tài khoản doanh nghiệp nếu chưa có
                if (!$internReq->company_id && $internReq->proposed_company_id) {
                    $proposed = $internReq->proposedCompany;

                    $newCompany = Company::create([
                        'usercode' => $proposed->tax_code,
                        'name' => $request->company_name ?? $proposed->name,
                        'email' => $request->company_email ?? $proposed->contact_email,
                        'address' => $request->company_address ?? $proposed->address,
                        'password' => Hash::make($proposed->tax_code), // Pass mặc định là MST
                        'is_active' => true,
                        'is_partnered' => true,
                    ]);

                    $internReq->company_id = $newCompany->company_id;
                }

                // Cập nhật trạng thái yêu cầu
                $internReq->update(['status' => InternshipRequest::STATUS_APPROVED]);

                // Cập nhật thông tin doanh nghiệp vào bản ghi Internship của các SV được chọn (BR-4)
                $internReq->internship()->update([
                    'company_id' => $internReq->company_id,
                    'status' => 'COMPANY_APPROVED'
                ]);

            // Bước 9: Gửi email (Giả định có Class Mail)
            // Mail::to($internReq->company->email)->send(new InternshipResultMail($request->status));

            }
            else {
                // 6a: Từ chối
                $internReq->update([
                    'status' => InternshipRequest::STATUS_REJECTED,
                    'feedback' => $request->feedback
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Duyệt doanh nghiệp thành công (Bước 10)']);
        });
    }
    /**
     * UC 35 - Bước 4: Lấy lịch sử nộp báo cáo của sinh viên
     */
    public function getReportHistory(Request $request)
    {
        $studentId = auth()->id(); // Hoặc thay bằng 1 để test nhanh
        $milestoneId = $request->query('milestone_id');

        $reports = InternshipReport::whereHas('internship', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })
            ->where('milestone_id', $milestoneId)
            ->orderBy('submission_date', 'desc')
            ->get();

        return InternshipReportResource::collection($reports);
    }

    /**
     * UC 35 - Bước 6-9: Thực hiện nộp báo cáo
     */
    public function submitReport(SubmitReportRequest $request)
    {
        $studentId = auth()->id(); // Hoặc thay bằng 1 để test nhanh

        // Tiền điều kiện: Sinh viên phải có bản ghi thực tập và đã có GVHD
        $internship = Internship::where('student_id', $studentId)->first();
        if (!$internship || !$internship->lecturer_id) {
            return response()->json(['message' => 'Bạn chưa được phân công giảng viên hướng dẫn.'], 403);
        }

        $milestone = Milestone::findOrFail($request->milestone_id);

        // 3a & 8a1: Kiểm tra thời hạn nộp bài
        if (Carbon::now()->gt($milestone->end_date)) {
            return response()->json(['message' => 'Đã hết thời gian nộp (3a).'], 400);
        }

        // 3b & 8a2: Kiểm tra số lần nộp (Tối đa 5 lần - BR-2)
        $submissionCount = InternshipReport::where('internship_id', $internship->internship_id)
            ->where('milestone_id', $milestone->milestone_id)
            ->count();

        if ($submissionCount >= 5) {
            return response()->json(['message' => 'Bạn đã nộp tối đa 5 lần cho hạng mục này (3b).'], 400);
        }

        return DB::transaction(function () use ($request, $internship, $milestone) {
            // Bước 8: Lưu trữ tệp tin
            $path = $request->file('file')->store('internship/reports', 'public');

            // Tạo bản ghi báo cáo mới (Lưu lịch sử các version cũ - BR-2)
            $report = InternshipReport::create([
                'internship_id' => $internship->internship_id,
                'milestone_id' => $milestone->milestone_id,
                'status' => InternshipReport::STATUS_PENDING,
                'description' => $request->description,
                'file_path' => $path,
                'submission_date' => Carbon::now(),
            ]);

            return (new InternshipReportResource($report))
                ->additional(['success' => true, 'message' => 'Nộp bài thành công (Bước 9)']);
        });
    }
    /**
     * UC 40 - Bước 3: Danh sách báo cáo cần duyệt (Dành cho GV)
     */
    public function getReportsToReview()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // Ngoại lệ 2a: Kiểm tra trạng thái nghỉ phép
        if ($lecturer->is_on_leave) { // Giả định cột is_on_leave trong bảng lecturers
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép.'
            ], 403);
        }

        // BR-1: Chỉ lấy các báo cáo đang ở trạng thái PENDING (chưa duyệt)
        $reports = InternshipReport::whereHas('internship', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })
            ->where('status', InternshipReport::STATUS_PENDING)
            ->with(['internship.student', 'milestone'])
            ->get();

        return ReportReviewResource::collection($reports);
    }

    /**
     * UC 40 - Bước 7-9: Xử lý Duyệt hoặc Từ chối
     */
    public function reviewReport(ReviewReportRequest $request, $id)
    {
        $lecturerId = auth()->id();

        $report = InternshipReport::whereHas('internship', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })->findOrFail($id);

        // Cập nhật trạng thái và nhận xét (Bước 8)
        $report->update([
            'status' => $request->status,
            'lecturer_feedback' => $request->feedback,
            'updated_at' => Carbon::now()
        ]);

        // Bước 9: Gửi thông báo cho sinh viên (Có thể dùng Queue/Notification)
        // Notification::send($report->internship->student, new ReportReviewedNotification($report));

        return response()->json([
            'success' => true,
            'message' => $request->status === 'APPROVED' ? 'Đã duyệt báo cáo thành công.' : 'Đã từ chối báo cáo.',
            'data' => new ReportReviewResource($report)
        ]);
    }
    /**
     * UC 36: Tìm kiếm và lọc danh sách thực tập
     */
    public function search(SearchInternshipRequest $request)
    {
        $user = auth()->user();
        $role = $request->get('current_role'); // Lấy từ RoleMiddleware

        // 5. Thực hiện truy vấn với Eager Loading để đảm bảo hiệu năng (NFR-1)
        $query = Internship::with(['student.studentClass', 'company', 'lecturer']);

        // 4. Xác định phạm vi dữ liệu theo vai trò (BR-1)
        if ($role === 'lecturer') {
            $query->where('lecturer_id', $user->lecturer_id);
        }

        // Lọc theo từ khóa (Tên hoặc MSSV)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('student', function (Builder $q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('usercode', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo học kỳ
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo doanh nghiệp
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // BR-2: Phân trang kết quả (10 bản ghi/trang)
        $results = $query->paginate(10);

        return InternshipSearchResource::collection($results);
    }
    // UC 37
    /**
     * Bước 4: Lấy danh sách doanh nghiệp kèm số lượng slot
     */
    public function getAvailableCompanies()
    {
        $companies = Company::all();
        return CompanySlotResource::collection($companies);
    }

    /**
     * Bước 7-11: Thực hiện phân công doanh nghiệp cho sinh viên
     */
    public function assignCompany(AssignCompanyRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // BR-3: Kiểm tra thời hạn đăng ký của sinh viên đã kết thúc chưa
            $milestone = Milestone::where('type', Milestone::TYPE_INTERNSHIP)->upcoming()->first();
            if ($milestone) {
                return response()->json(['message' => 'Thời hạn tự đăng ký chưa kết thúc (BR-3).'], 400);
            }

            $company = Company::findOrFail($request->company_id);
            $internshipIds = $request->internship_ids;
            $countSelected = count($internshipIds);

            // 7a: Kiểm tra slot còn lại của doanh nghiệp (BR-2)
            $currentInterns = $company->internships()->count();
            if (($currentInterns + $countSelected) > 20) {
                return response()->json(['message' => 'Doanh nghiệp không đủ slot (7a1).'], 400);
            }

            // Bước 8 & BR-1: Cập nhật trạng thái cho những SV "Chưa có doanh nghiệp" (INITIALIZED)
            $affected = Internship::whereIn('internship_id', $internshipIds)
                ->where('status', 'INITIALIZED') // BR-1
                ->update([
                'company_id' => $company->company_id,
                'status' => 'COMPANY_APPROVED', // Trạng thái sau phân công
                'updated_at' => Carbon::now()
            ]);

            if ($affected === 0) {
                return response()->json(['message' => 'Không có sinh viên hợp lệ để phân công.'], 400);
            }

            // Bước 11: Gửi thông báo cho sinh viên
            $notification = Notification::create([
                'title' => 'Thông báo phân công thực tập',
                'content' => "Bạn đã được phân công thực tập tại doanh nghiệp: {$company->name}."
            ]);

            $studentIds = Internship::whereIn('internship_id', $internshipIds)->pluck('student_id');
            foreach ($studentIds as $id) {
                UserNotification::create([
                    'notification_id' => $notification->notification_id,
                    'user_id' => $id,
                    'role_id' => 1, // Giả định 1 là Role Student
                    'is_read' => false
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Phân công thành công (Bước 10)']);
        });
    }
    /**
     * UC 38 - Gửi yêu cầu hủy học phần thực tập
     */
    public function requestCancelInternship(CancelInternshipRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $studentId = auth()->id();

            // Tìm bản ghi thực tập của sinh viên
            $internship = Internship::where('student_id', $studentId)
                ->findOrFail($request->internship_id);

            // Kiểm tra Tiền điều kiện: Trạng thái không phải đã hủy hoặc hoàn thành
            if (in_array($internship->status, [Internship::STATUS_CANCEL, Internship::STATUS_COMPLETED])) {
                return response()->json(['message' => 'Học phần này không ở trạng thái có thể hủy.'], 400);
            }

            // Bước 4 & BR-1: Kiểm tra thời hạn 14 ngày kể từ khi mở cổng (Milestone created_at)
            $milestone = Milestone::where('semester_id', $internship->semester_id)
                ->where('type', Milestone::TYPE_INTERNSHIP)
                ->first();

            if (!$milestone) {
                return response()->json(['message' => 'Không tìm thấy thông tin đợt đăng ký.'], 404);
            }

            $startDate = $milestone->created_at; // Ngày hệ thống mở cổng đăng ký
            $now = Carbon::now();

            // Nếu thời gian hiện tại vượt quá 14 ngày kể từ ngày mở cổng
            if ($now->diffInDays($startDate) > 14) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã hết thời gian cho phép yêu cầu hủy học phần thực tập (4a1).'
                ], 400);
            }

            // Kiểm tra BR-3: Tránh gửi trùng lặp nếu đã có yêu cầu PENDING
            $exists = InternshipRequest::where('internship_id', $internship->internship_id)
                ->where('type', InternshipRequest::TYPE_CANCEL_REQ)
                ->whereIn('status', [InternshipRequest::STATUS_PENDING_FACULTY, InternshipRequest::STATUS_PENDING_TEACHER])
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'Yêu cầu hủy của bạn đã được gửi trước đó và đang chờ xử lý.'], 400);
            }

            // Bước 5: Tạo yêu cầu hủy
            $cancelReq = InternshipRequest::create([
                'internship_id' => $internship->internship_id,
                'type' => InternshipRequest::TYPE_CANCEL_REQ,
                'status' => InternshipRequest::STATUS_PENDING_FACULTY, // Chờ VPK xử lý
                'student_message' => 'Sinh viên yêu cầu hủy học phần thực tập.',
            ]);

            // Cập nhật trạng thái học phần thực tập (Hậu điều kiện)
            $internship->update(['status' => 'CANCEL_PENDING']); // Trạng thái tạm thời chờ duyệt

            return (new CancelRequestResource($cancelReq))
                ->additional(['success' => true, 'message' => 'Gửi yêu cầu hủy thành công, vui lòng đợi duyệt (Bước 7).']);
        });
    }
    // ===================== UC 39.1: GIẢNG VIÊN DUYỆT =====================

    public function getPendingCancelLecturer()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // BR-2: Giảng viên nghỉ phép không có quyền truy cập
        if ($lecturer->leaves()->active()->exists()) {
            return response()->json(['message' => 'Bạn đang trong trạng thái nghỉ phép (2a1).'], 403);
        }

        // BR-1: Chỉ lấy yêu cầu của SV được phân công hướng dẫn
        $requests = InternshipRequest::where('type', InternshipRequest::TYPE_CANCEL_REQ)
            ->where('status', InternshipRequest::STATUS_PENDING_TEACHER)
            ->whereHas('internship', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })->get();

        return CancelRequestDetailResource::collection($requests);
    }

    public function reviewCancelLecturer(ReviewCancelRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $lecturerId = auth()->id();
            $cancelReq = InternshipRequest::where('type', InternshipRequest::TYPE_CANCEL_REQ)
                ->where('status', InternshipRequest::STATUS_PENDING_TEACHER)
                ->whereHas('internship', function ($q) use ($lecturerId) {
                $q->where('lecturer_id', $lecturerId);
            }
            )->findOrFail($id);

            if ($request->status === 'APPROVED') {
                // Bước 5: Chuyển lên VPK (PENDING_FACULTY)
                $cancelReq->update(['status' => InternshipRequest::STATUS_PENDING_FACULTY]);
            // Bước 6: Thông báo cho VPK (Giả định role_id của VPK là 3)
            // logic gửi thông báo cho VPK...
            }
            else {
                // 4a: Từ chối -> Kết thúc yêu cầu
                $cancelReq->update(['status' => InternshipRequest::STATUS_REJECTED, 'feedback' => $request->feedback]);
                $this->notifyStudent($cancelReq->internship->student_id, "Yêu cầu hủy thực tập bị từ chối bởi GVHD.");
            }

            return response()->json(['success' => true, 'message' => 'Xử lý yêu cầu thành công.']);
        });
    }

    // ===================== UC 39.2: VPK DUYỆT CUỐI =====================

    public function getPendingCancelVPK()
    {
        // BR-1: Chỉ duyệt yêu cầu đã qua bước GV duyệt (PENDING_FACULTY)
        $requests = InternshipRequest::where('type', InternshipRequest::TYPE_CANCEL_REQ)
            ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
            ->get();

        return CancelRequestDetailResource::collection($requests);
    }

    public function reviewCancelVPK(ReviewCancelRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $cancelReq = InternshipRequest::where('type', InternshipRequest::TYPE_CANCEL_REQ)
                ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
                ->findOrFail($id);

            if ($request->status === 'APPROVED') {
                $cancelReq->update(['status' => InternshipRequest::STATUS_APPROVED]);

                // Hậu điều kiện: Chính thức hủy học phần
                $internship = $cancelReq->internship;
                $internship->update(['status' => Internship::STATUS_CANCEL]);

                // BR-2: Slot doanh nghiệp tự động tăng lên do count() chỉ tính SV active

                $this->notifyStudent($internship->student_id, "Học phần thực tập của bạn đã chính thức được hủy.");
            }
            else {
                // 3a: Từ chối
                $cancelReq->update(['status' => InternshipRequest::STATUS_REJECTED, 'feedback' => $request->feedback]);
                $this->notifyStudent($cancelReq->internship->student_id, "VPK từ chối yêu cầu hủy thực tập của bạn.");
            }

            return response()->json(['success' => true, 'message' => 'Phê duyệt cuối cùng thành công.']);
        });
    }

    private function notifyStudent($studentId, $content)
    {
        $notification = Notification::create(['title' => 'Kết quả yêu cầu hủy thực tập', 'content' => $content]);
        UserNotification::create(['notification_id' => $notification->notification_id, 'user_id' => $studentId, 'role_id' => 1]);
    }
    /**
     * UC 43 - Bước 3: Lấy danh sách giảng viên kèm chỉ tiêu và trạng thái nghỉ phép
     */
    public function getLecturerSlots()
    {
        $lecturers = Lecturer::all();
        return LecturerSlotResource::collection($lecturers);
    }

    /**
     * UC 43 - Bước 5-10: Thực hiện phân công GVHD
     */
    public function assignLecturer(AssignLecturerRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $lecturer = Lecturer::findOrFail($request->lecturer_id);
            $internshipIds = $request->internship_ids;
            $countSelected = count($internshipIds);

            // 6b & BR-1: Kiểm tra trạng thái nghỉ phép
            $isOnLeave = $lecturer->leaves()->where('status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists();
            if ($isOnLeave) {
                return response()->json([
                    'message' => 'Không thể phân công cho giảng viên đang nghỉ phép (6b1).'
                ], 400);
            }

            // 6a: Kiểm tra chỉ tiêu (Slot) - Giả định tối đa 30
            $currentGuiding = $lecturer->internships()->count();
            if (($currentGuiding + $countSelected) > 30) {
                return response()->json([
                    'message' => 'Giảng viên không thể tiếp nhận thêm, vui lòng chọn giảng viên khác (6a1).'
                ], 400);
            }

            // Bước 8: Cập nhật giảng viên hướng dẫn cho sinh viên
            Internship::whereIn('internship_id', $internshipIds)->update([
                'lecturer_id' => $lecturer->lecturer_id,
                'status' => 'LECTURER_APPROVED', // Cập nhật trạng thái
                'updated_at' => Carbon::now()
            ]);

            // Bước 9: Gửi thông báo cho cả Sinh viên và Giảng viên
            $notification = Notification::create([
                'title' => 'Thông báo phân công GVHD thực tập',
                'content' => "Hệ thống đã phân công Giảng viên {$lecturer->full_name} hướng dẫn thực tập."
            ]);

            // Gửi cho Giảng viên
            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id' => $lecturer->lecturer_id,
                'role_id' => 2, // Giả định 2 là Lecturer
            ]);

            // Gửi cho danh sách Sinh viên
            $studentIds = Internship::whereIn('internship_id', $internshipIds)->pluck('student_id');
            foreach ($studentIds as $sId) {
                UserNotification::create([
                    'notification_id' => $notification->notification_id,
                    'user_id' => $sId,
                    'role_id' => 1, // Giả định 1 là Student
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Phân công thành công (Bước 10)']);
        });
    }
    /**
     * UC 41 - Bước 3: Danh sách sinh viên cần chấm điểm
     */
    public function getStudentsForGrading()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // 2a: Kiểm tra trạng thái nghỉ phép
        if ($lecturer->leaves()->active()->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép (2a1).'], 403);
        }

        // BR-2: Sinh viên đã nộp báo cáo và trong thời hạn chấm điểm
        $gradingMilestone = Milestone::where('type', Milestone::TYPE_INTERNSHIP)->upcoming()->first();
        if (!$gradingMilestone) {
            return response()->json(['message' => 'Ngoài thời hạn chấm điểm quy định (BR-2).'], 400);
        }

        $students = Internship::where('lecturer_id', $lecturerId)
            ->whereHas('internshipReports') // BR-2: Đã nộp báo cáo
            ->with(['student.studentClass'])
            ->get();

        return InternshipGradeResource::collection($students);
    }

    /**
     * UC 41 - Bước 7-12: Thực hiện chấm điểm
     */
    public function submitGrade(GradeInternshipRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $lecturerId = auth()->id();

            // BR-1: Chỉ giảng viên hướng dẫn mới có quyền chấm
            $internship = Internship::where('lecturer_id', $lecturerId)->findOrFail($id);

            // BR-3: Điểm đã gửi thành công không được phép chỉnh sửa
            if (!is_null($internship->university_grade)) {
                return response()->json(['message' => 'Điểm số đã được ghi nhận trước đó và không thể chỉnh sửa (BR-3).'], 400);
            }

            // Bước 9: Cập nhật điểm thi và nhận xét
            $internship->university_grade = $request->university_grade;
            $internship->university_feedback = $request->feedback;

            // Bước 10: Tính toán điểm cuối cùng (Giả định công thức trong yêu cầu)
            $finalGrade = ($internship->company_grade + $request->university_grade) / 2;

            // Bước 11: Cập nhật trạng thái thực tập dựa trên điểm (VD: >= 4.0 là Đạt)
            $internship->status = ($finalGrade >= 4) ? 'COMPLETED' : 'FAILED';
            $internship->updated_at = Carbon::now();
            $internship->save();

            // Bước 12: Gửi thông báo cho sinh viên
            $notification = Notification::create([
                'title' => 'Thông báo kết quả điểm thực tập',
                'content' => "Kết quả thực tập của bạn đã có. Điểm tổng kết: {$finalGrade}. Trạng thái: {$internship->status}."
            ]);

            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id' => $internship->student_id,
                'role_id' => 1, // Student
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chấm điểm thành công (Bước 12)',
                'data' => new InternshipGradeResource($internship)
            ]);
        });
    }
    /**
     * UC 45 - Bước 2: Hiển thị danh sách sinh viên đang chờ doanh nghiệp xác nhận
     */
    public function getWaitingStudents()
    {
        // Lấy ID doanh nghiệp đang đăng nhập
        $companyId = auth()->id();

        // Lấy sinh viên có trạng thái 'COMPANY_APPROVED' (VPK đã duyệt/phân công nhưng DN chưa xác nhận)
        $students = Internship::where('company_id', $companyId)
            ->where('status', 'COMPANY_APPROVED')
            ->with(['student.studentClass'])
            ->get();

        return BusinessStudentResource::collection($students);
    }

    /**
     * UC 45 - Bước 4-7: Doanh nghiệp xác nhận Tiếp nhận hoặc Từ chối
     */
    public function confirmStudent(ConfirmStudentRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $companyId = auth()->id();

            // Tìm bản ghi thực tập thuộc doanh nghiệp này
            $internship = Internship::where('company_id', $companyId)
                ->where('status', 'COMPANY_APPROVED')
                ->findOrFail($id);

            if ($request->status === 'ACCEPT') {
                // Bước 5: Cập nhật trạng thái 'INTERNING' (Đang thực tập)
                $internship->status = 'INTERNING';
                $messageTitle = "Doanh nghiệp đã tiếp nhận";
                $messageContent = "Chúc mừng! Doanh nghiệp đã xác nhận tiếp nhận bạn vào thực tập.";
            }
            else {
                // Luồng 4a: Từ chối -> Chuyển về trạng thái 'CANCEL' để SV tìm chỗ khác
                $internship->status = 'CANCEL';
                $messageTitle = "Doanh nghiệp từ chối tiếp nhận";
                $messageContent = "Doanh nghiệp đã từ chối yêu cầu thực tập của bạn. Vui lòng liên hệ VPK hoặc tìm đơn vị khác.";
            }

            $internship->updated_at = Carbon::now();
            $internship->save();

            // Bước 7 & 4a3: Gửi thông báo cho sinh viên
            $notification = Notification::create([
                'title' => $messageTitle,
                'content' => $messageContent
            ]);

            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id' => $internship->student_id,
                'role_id' => 1, // Student
                'is_read' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->status === 'ACCEPT' ? 'Đã tiếp nhận sinh viên thành công.' : 'Đã từ chối sinh viên.'
            ]);
        });
    }
    /**
     * UC 46 - Bước 2: Hiển thị danh sách sinh viên đang thực tập tại doanh nghiệp
     */
    public function getInterns()
    {
        $companyId = auth()->id();

        $interns = Internship::where('company_id', $companyId)
            ->whereIn('status', [Internship::STATUS_INTERNING, 'BUSINESS_EVALUATED'])
            ->with(['student.studentClass'])
            ->get();

        return CompanyInternshipResource::collection($interns);
    }

    /**
     * UC 46 - Bước 6-10: Thực hiện đánh giá và chấm điểm
     */
    public function evaluateStudent(EvaluateStudentRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $companyId = auth()->id();

            // Tìm bản ghi thực tập thuộc quyền quản lý của doanh nghiệp này
            $internship = Internship::where('company_id', $companyId)
                ->findOrFail($id);

            // Bước 8: Cập nhật điểm, nhận xét và trạng thái
            $internship->update([
                'company_grade' => $request->company_grade,
                'company_feedback' => $request->company_feedback,
                'status' => 'BUSINESS_EVALUATED', // Trạng thái theo đặc tả UC
                'updated_at' => Carbon::now()
            ]);

            // Bước 9: Gửi thông báo cho sinh viên
            $notification = Notification::create([
                'title' => 'Thông báo kết quả đánh giá từ doanh nghiệp',
                'content' => "Doanh nghiệp đã hoàn tất đánh giá quá trình thực tập của bạn với điểm số: {$request->company_grade}."
            ]);

            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id' => $internship->student_id,
                'role_id' => 1, // Student
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá sinh viên thành công (Bước 10)',
                'data' => new CompanyInternshipResource($internship)
            ]);
        });
    }
    /**
     * UC 44 - Bước 1-6: Thống kê danh sách theo bộ lọc
     */
    public function statistics(StatisticInternshipRequest $request)
    {
        $query = $this->applyFilters($request);

        // NFR-1: Tối ưu truy vấn bằng Eager Loading để đảm bảo tốc độ < 5s
        $data = $query->with(['student.studentClass', 'company', 'lecturer'])->get();

        return InternshipStatisticResource::collection($data);
    }

    /**
     * UC 44 - Bước 7-10: Xuất tệp tin Excel
     */
    public function exportExcel(StatisticInternshipRequest $request)
    {
        $query = $this->applyFilters($request);
        $data = $query->get();

        // 8a: Kiểm tra dữ liệu hiện có
        if ($data->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không có dữ liệu xuất (8a1).'], 404);
        }

        // Bước 9: Trả về link tải tệp (Giả định bạn dùng Laravel Excel hoặc Export Service)
        // Ví dụ: return Excel::download(new InternshipExport($data), 'danh-sach-thuc-tap.xlsx');

        return response()->json([
            'success' => true,
            'download_url' => url('/storage/exports/danh_sach_thuc_tap_' . now()->timestamp . '.xlsx'),
            'message' => 'Tệp tin Excel đang được khởi tạo.'
        ]);
    }

    /**
     * Logic lọc dùng chung (BR-2: Đảm bảo đồng nhất giữa xem và xuất)
     */
    private function applyFilters(Request $request)
    {
        $query = Internship::query();

        // BR-1: Ưu tiên học kỳ hiện tại nếu không chọn học kỳ cụ thể
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        else {
            $currentSemester = Semester::where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->first();
            if ($currentSemester) {
                $query->where('semester_id', $currentSemester->semester_id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('lecturer_id')) {
            $query->where('lecturer_id', $request->lecturer_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        return $query;
    }
}
