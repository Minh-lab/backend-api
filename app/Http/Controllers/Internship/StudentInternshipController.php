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

        if (!$milestoneId) {
            return response()->json([
                'success' => false,
                'message' => 'milestone_id là bắt buộc'
            ], 400);
        }

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

        // Lấy tất cả báo cáo của sinh viên cho milestone này
        $reports = InternshipReport::where('internship_id', $internship->internship_id)
            ->where('milestone_id', $milestoneId)
            ->with('milestone')
            ->orderBy('submission_date', 'desc')
            ->get()
            ->map(function ($report) {
                return [
                    'report_id' => $report->report_id,
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

        // Check if deadline has passed
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
