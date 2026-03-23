<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{Capstone, CapstoneRequest, Lecturer, Milestone, LecturerLeave};
use App\Http\Requests\Capstone\ReviewTopicRequest;
use App\Http\Resources\Capstone\ProposedTopicResource;
use Illuminate\Support\Facades\DB;

class TopicApprovalController extends CapstoneBaseController
{
    /**
     * UC 24.1 - Danh sách đề tài chờ duyệt (Giảng viên)
     */
    public function getPendingTopicsLecturer()
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
        $lecturer = Lecturer::findOrFail($lecturerId);

        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn đang trong trạng thái nghỉ phép.'], 403);
        }

        // Kiểm tra thời hạn theo end_date
        $milestone = Milestone::capstone()->where('end_date', '>', now())->first();
        if (!$milestone) {
            return response()->json(['message' => 'Thời gian phê duyệt đề tài đã kết thúc.'], 400);
        }

        $requests = CapstoneRequest::where('type', CapstoneRequest::TYPE_TOPIC_PROP)
            ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
            ->whereHas('capstone', function ($query) use ($lecturerId) {
                $query->where('lecturer_id', $lecturerId);
            })
            ->with(['capstone.student.studentClass', 'proposedTopic'])
            ->get();

        return ProposedTopicResource::collection($requests);
    }

    /**
     * UC 24.1 - Bước 6-9: Giảng viên duyệt/từ chối
     */
    public function reviewTopicLecturer(ReviewTopicRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = auth()->user();
            $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
            $capReq = CapstoneRequest::where('type', CapstoneRequest::TYPE_TOPIC_PROP)
                ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
                ->whereHas('capstone', function ($query) use ($lecturerId) {
                    $query->where('lecturer_id', $lecturerId);
                })
                ->findOrFail($id);

            if ($request->status === 'APPROVED') {
                // Bước 8: Chuyển trạng thái chờ VPK duyệt
                $capReq->update([
                    'status' => CapstoneRequest::STATUS_PENDING_FACULTY,
                    'lecturer_feedback' => $request->feedback,
                ]);
                $msg = "Đề tài của bạn đã được Giảng viên duyệt và đang chờ Văn phòng khoa phê duyệt cuối cùng.";
            } else {
                // 6a: Từ chối
                $capReq->update([
                    'status' => CapstoneRequest::STATUS_REJECTED,
                    'lecturer_feedback' => $request->feedback
                ]);
                $msg = "Đề tài của bạn đã bị Giảng viên từ chối.";
            }

            $this->notifyStudent($capReq->capstone->student_id, 1, $msg);
            return response()->json(['success' => true, 'message' => 'Xử lý thành công.']);
        });
    }

    /**
     * UC 24.2 - Bước 3: Danh sách đề tài đã qua GV duyệt (Dành cho VPK)
     */
    public function getPendingTopicsVPK()
    {
        // Kiểm tra thời hạn VPK duyệt (có thể lấy từ Milestone riêng)
        $requests = CapstoneRequest::where('status', CapstoneRequest::STATUS_PENDING_FACULTY)
            ->with(['capstone.student.studentClass', 'proposedTopic'])
            ->get();

        return ProposedTopicResource::collection($requests);
    }

    /**
     * UC 24.2 - Bước 4-7: VPK duyệt cuối cùng
     */
    public function confirmTopicVPK(ReviewTopicRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $capReq = CapstoneRequest::where('status', CapstoneRequest::STATUS_PENDING_FACULTY)->findOrFail($id);

            if ($request->status === 'APPROVED') {
                $capReq->update(['status' => CapstoneRequest::STATUS_APPROVED]);

                // Cập nhật vào bảng Capstone (BR-1 phân hệ 24.2)
                $capstone = $capReq->capstone;
                $capstone->update(['status' => Capstone::STATUS_TOPIC_APPROVED]);

                $msg = "Đề tài đồ án của bạn đã chính thức được Văn phòng khoa phê duyệt.";
            } else {
                $capReq->update(['status' => CapstoneRequest::STATUS_REJECTED, 'lecturer_feedback' => $request->feedback]);
                $msg = "Văn phòng khoa đã từ chối đề tài của bạn.";
            }

            $this->notifyStudent($capReq->capstone->student_id, 1, $msg);
            return response()->json(['success' => true, 'message' => 'Phê duyệt thành công.']);
        });
    }
}
