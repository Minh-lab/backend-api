<?php

namespace App\Http\Controllers\Internship;

use App\Models\{InternshipReport, Lecturer};
use App\Http\Requests\Internship\ReviewReportRequest;
use App\Http\Resources\Internship\ReportReviewResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportReviewController extends InternshipBaseController
{
    /**
     * UC 40 - Bước 3: Danh sách báo cáo cần duyệt (Dành cho GV)
     */
    public function getReportsToReview()
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // Ngoại lệ 2a: Kiểm tra trạng thái nghỉ phép
        if ($lecturer->is_on_leave) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép.'
            ], 403);
        }

        // BR-1: Chỉ lấy các báo cáo đang ở trạng thái PENDING (chưa duyệt)
        $reports = InternshipReport::whereHas('internship', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })
            ->whereIn('status', [
                InternshipReport::STATUS_PENDING,
                'STATUS_PENDING',
            ])
            ->with(['internship.student', 'milestone'])
            ->get();

        return ReportReviewResource::collection($reports);
    }

    /**
     * UC 40 - Bước 7-9: Xử lý Duyệt hoặc Từ chối
     */
    public function reviewReport(ReviewReportRequest $request, $id)
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();

        $report = InternshipReport::whereHas('internship', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })->findOrFail($id);

        // Cập nhật trạng thái và nhận xét (Bước 8)
        $report->update([
            'status' => $request->status,
            'lecturer_feedback' => $request->feedback,
            'updated_at' => Carbon::now()
        ]);

        // Bước 9: Gửi thông báo cho sinh viên
        // Notification::send($report->internship->student, new ReportReviewedNotification($report));

        return response()->json([
            'success' => true,
            'message' => $request->status === 'APPROVED' ? 'Đã duyệt báo cáo thành công.' : 'Đã từ chối báo cáo.',
            'data' => new ReportReviewResource($report)
        ]);
    }
}
