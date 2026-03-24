<?php

namespace App\Http\Controllers\Internship;

use App\Models\{Internship, Milestone, InternshipRequest, InternshipReport, Company, ProposedCompany, Notification, UserNotification, Semester};
use App\Http\Requests\Internship\RegisterInternshipRequest;
use App\Http\Requests\Internship\RegisterCompanyRequest;
use App\Http\Requests\Internship\SubmitReportRequest;
use App\Http\Requests\Internship\CancelInternshipRequest;
use App\Http\Requests\Internship\ConfirmStudentRequest;
use App\Http\Resources\Internship\{InternshipResource, InternshipReportResource, BusinessStudentResource};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentInternshipController extends InternshipBaseController
{
    /**
     * UC 33: Lấy đợt đăng ký thực tập đang mở (dành cho sinh viên chưa đăng ký)
     */
    public function getMilestone()
    {
        $semesterId = $this->resolveCurrentSemesterId();
        if (!$semesterId) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy học kỳ hiện tại.'], 404);
        }

        // Ưu tiên đợt đang mở
        $milestone = Milestone::where('semester_id', $semesterId)
            ->where('type', Milestone::TYPE_INTERNSHIP)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        // Nếu không có đợt nào đang mở, lấy đợt "Đăng ký đợt thực tập" (Phase 1) để cho phép test/đăng ký
        if (!$milestone) {
            $milestone = Milestone::where('semester_id', $semesterId)
                ->where('type', Milestone::TYPE_INTERNSHIP)
                ->where('phase_name', 'like', '%Đăng ký đợt thực tập%')
                ->first();
        }

        // Nếu vẫn không thấy, lấy bất kỳ đợt nào của INTERNSHIP
        if (!$milestone) {
            $milestone = Milestone::where('semester_id', $semesterId)
                ->where('type', Milestone::TYPE_INTERNSHIP)
                ->first();
        }

        if (!$milestone) {
            return response()->json(['success' => false, 'message' => 'Hiện không có đợt thực tập nào cho học kỳ này.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'milestone_id' => $milestone->milestone_id,
                'semester_id' => $milestone->semester_id,
                'phase_name' => $milestone->phase_name,
                'description' => $milestone->description,
                'start_date' => $milestone->start_date,
                'end_date' => $milestone->end_date,
                'is_open' => (now() >= $milestone->start_date && now() <= $milestone->end_date) 
                             || str_contains($milestone->phase_name, 'Đăng ký đợt thực tập'), // Force open for testing Bước 1
            ]
        ]);
    }

    /**
     * UC 33: Đăng ký tham gia đợt thực tập
     */
    public function register(RegisterInternshipRequest $request)
    {
        $studentId = auth()->id();
        $semesterId = $this->resolveCurrentSemesterId();

        if (!$semesterId) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy học kỳ hiện tại.'], 404);
        }

        // Kiểm tra xem sinh viên đã có internship nào đang hoạt động TRONG HỌC KỲ NÀY chưa
        $existingInternship = Internship::where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->whereNotIn('status', [Internship::STATUS_CANCEL])
            ->first();

        if ($existingInternship) {
            return response()->json([
                'success' => false, 
                'message' => 'Bạn đã đăng ký đợt thực tập cho học kỳ này rồi (Trạng thái: ' . $existingInternship->status . ').'
            ], 400);
        }

        $internship = Internship::create([
            'student_id' => $studentId,
            'semester_id' => $semesterId,
            'status' => Internship::STATUS_INITIALIZED,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký đợt thực tập thành công.',
            'data' => $internship
        ], 201);
    }

    /**
     * UC 34: Kiểm tra trạng thái đăng ký doanh nghiệp
     */
    public function checkCompany()
    {
        $studentId = auth()->id();
        $internship = Internship::where('student_id', $studentId)
            ->whereNotIn('status', [Internship::STATUS_CANCEL, Internship::STATUS_COMPLETED])
            ->first();

        if (!$internship) {
            return response()->json(['success' => false, 'message' => 'Bạn chưa đăng ký đợt thực tập.'], 404);
        }

        $request = InternshipRequest::where('internship_id', $internship->internship_id)
            ->where('type', InternshipRequest::TYPE_COMPANY_REG)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'internship' => new InternshipResource($internship),
            'registration' => $request
        ]);
    }

    /**
     * UC 34: Đăng ký doanh nghiệp
     */
    public function registerCompany(RegisterCompanyRequest $request)
    {
        $studentId = auth()->id();
        $internship = Internship::where('student_id', $studentId)
            ->whereNotIn('status', [Internship::STATUS_CANCEL, Internship::STATUS_COMPLETED])
            ->first();

        if (!$internship) {
            return response()->json(['success' => false, 'message' => 'Bạn chưa đăng ký đợt thực tập.'], 400);
        }

        return DB::transaction(function () use ($request, $internship) {
            // Lưu file nếu có
            $filePath = null;
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('company_requests', 'public');
            }

            // Tạo công ty đề xuất (nếu là quy trình đề xuất)
            $proposedCompany = ProposedCompany::create([
                'tax_code' => $request->tax_code,
                'name' => $request->name,
                'address' => $request->address,
                'contact_email' => $request->email,
            ]);

            // Tạo request đăng ký doanh nghiệp
            $internshipRequest = InternshipRequest::create([
                'internship_id' => $internship->internship_id,
                'proposed_company_id' => $proposedCompany->proposed_company_id,
                'type' => InternshipRequest::TYPE_COMPANY_REG,
                'status' => InternshipRequest::STATUS_PENDING_COMPANY,
                'student_message' => $request->student_message,
                'file_path' => $filePath,
            ]);

            // Cập nhật trạng thái internship và vị trí thực tập
            $internship->update([
                'status' => Internship::STATUS_PENDING,
                'position' => $request->position,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký doanh nghiệp thành công, vui lòng chờ duyệt.',
                'data' => $internshipRequest
            ]);
        });
    }

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
     * UC 35: Lấy danh sách tất cả các mốc thời gian (Milestones) của đợt thực tập
     */
    public function getMilestones()
    {
        $studentId = auth()->id();
        
        // Lấy internship hiện tại của sinh viên
        $internship = Internship::where('student_id', $studentId)
            ->latest()
            ->first();

        if (!$internship) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng ký đợt thực tập.'
            ], 404);
        }

        // Lấy milestones của học kỳ này, loại INTERNSHIP, sắp xếp theo start_date
        $milestones = Milestone::where('semester_id', $internship->semester_id)
            ->where('type', Milestone::TYPE_INTERNSHIP)
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(function ($milestone) {
                return [
                    'milestone_id' => $milestone->milestone_id,
                    'phase_name' => $milestone->phase_name,
                    'description' => $milestone->description,
                    'start_date' => $milestone->start_date,
                    'end_date' => $milestone->end_date,
                    'type' => $milestone->type,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $milestones
        ]);
    }

    /**
     * UC 35: Lấy lịch sử nộp báo cáo theo milestone_id
     */
    public function getReportHistory(Request $request)
    {
        $studentId = auth()->id();
        $milestoneId = $request->query('milestone_id');

        // Lấy internship hiện tại của sinh viên
        $internship = Internship::where('student_id', $studentId)
            ->latest()
            ->first();

        if (!$internship) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng ký đợt thực tập.'
            ], 404);
        }

        // Lấy báo cáo của sinh viên cho milestone này (hoặc tất cả nếu không có milestoneId)
        $query = InternshipReport::where('internship_id', $internship->internship_id);
        
        if ($milestoneId) {
            $query->where('milestone_id', $milestoneId);
        }

        $reports = $query->with('milestone')
            ->orderBy('submission_date', 'desc')
            ->get()
            ->map(function ($report) {
                return [
                    'report_id' => $report->report_id,
                    'milestone_id' => $report->milestone_id,
                    'phase_name' => $report->milestone->phase_name ?? 'N/A',
                    'status' => $report->status,
                    'description' => $report->description,
                    'file_url' => $report->file_path ? asset('storage/' . $report->file_path) : null,
                    'submission_date' => $report->submission_date?->format('Y-m-d H:i:s'),
                    'lecturer_feedback' => $report->lecturer_feedback,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * UC 35: Nộp báo cáo thực tập
     */
    public function submitReport(SubmitReportRequest $request)
    {
        $studentId = auth()->id();
        $internshipId = $request->input('internship_id');
        $milestoneId = $request->input('milestone_id');
        $description = $request->input('description', null);
        $file = $request->file('file');

        // Validate internship belongs to student
        $internship = Internship::where('internship_id', $internshipId)
            ->where('student_id', $studentId)
            ->first();

        if (!$internship) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin thực tập không hợp lệ'
            ], 403);
        }

        // Validate milestone
        $milestone = Milestone::find($milestoneId);
        if (!$milestone) {
            return response()->json([
                'success' => false,
                'message' => 'Mốc thời gian không hợp lệ'
            ], 404);
        }

        // Check if deadline has passed or not yet started
        if (now() < $milestone->start_date) {
            return response()->json([
                'success' => false,
                'message' => 'Đợt nộp báo cáo chưa bắt đầu'
            ], 400);
        }

        if (now() > $milestone->end_date) {
            return response()->json([
                'success' => false,
                'message' => 'Hạn nộp báo cáo đã qua'
            ], 400);
        }

        try {
            // Upload file
            $filePath = null;
            if ($file) {
                $filePath = $file->store('internship-reports', 'public');
            }

            // Create report
            $report = InternshipReport::create([
                'internship_id' => $internshipId,
                'milestone_id' => $milestoneId,
                'status' => InternshipReport::STATUS_PENDING,
                'description' => $description,
                'file_path' => $filePath,
                'submission_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nộp báo cáo thành công',
                'data' => new InternshipReportResource($report)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi nộp báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }
}
