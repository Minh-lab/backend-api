<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{CapstoneReport, Lecturer, LecturerLeave};
use App\Http\Requests\Capstone\ApproveCapstoneReportRequest;
use App\Http\Resources\Capstone\CapstoneReportDetailResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportApprovalController extends CapstoneBaseController
{
    /**
     * UC 25 - Bước 3: Danh sách báo cáo cần duyệt (Chỉ những SV mình hướng dẫn - BR-1)
     */
    public function getPendingReports()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // 2a: Kiểm tra trạng thái nghỉ phép (Sửa lỗi ambiguous status)
        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép (2a1).'], 403);
        }

        // BR-1: Chỉ lấy báo cáo của các đồ án do giảng viên này hướng dẫn
        $reports = CapstoneReport::whereHas('capstone', function ($query) use ($lecturerId) {
            $query->where('lecturer_id', $lecturerId);
        })
            ->where('status', CapstoneReport::STATUS_PENDING) // Chỉ lấy báo cáo chưa duyệt
            ->with(['capstone.student', 'capstone.topic', 'milestone'])
            ->get();

        return CapstoneReportDetailResource::collection($reports);
    }

    /**
     * UC 25 - Bước 6-9: Thực hiện phê duyệt/từ chối báo cáo
     */
    public function approveReport(ApproveCapstoneReportRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $lecturerId = auth()->id();

            // Tìm báo cáo và kiểm tra quyền hướng dẫn (BR-1)
            $report = CapstoneReport::whereHas('capstone', function ($query) use ($lecturerId) {
                $query->where('lecturer_id', $lecturerId);
            })->findOrFail($id);

            // BR-3: Không cho phép thay đổi nếu báo cáo đã được duyệt/từ chối trước đó
            if ($report->status !== CapstoneReport::STATUS_PENDING) {
                return response()->json(['message' => 'Báo cáo này đã được xử lý và không thể thay đổi nội dung (BR-3).'], 400);
            }

            // Bước 7: Cập nhật trạng thái và nhận xét
            $report->update([
                'status'            => $request->status,
                'lecturer_feedback' => $request->feedback,
                'updated_at'        => Carbon::now()
            ]);

            // Bước 9: Gửi thông báo cho sinh viên
            $statusText = $request->status === 'APPROVED' ? 'được phê duyệt' : 'bị từ chối';
            $msg = "Báo cáo giai đoạn của bạn đã {$statusText} bởi Giảng viên hướng dẫn.";

            $this->notifyStudent($report->capstone->student_id, 1, $msg);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái báo cáo thành công (Bước 8).',
                'data'    => new CapstoneReportDetailResource($report)
            ]);
        });
    }
}
