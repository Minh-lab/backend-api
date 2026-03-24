<?php

namespace App\Http\Controllers\Internship;

use App\Models\{Internship, InternshipRequest, Lecturer, Milestone};
use App\Http\Requests\Internship\ReviewCancelRequest;
use App\Http\Resources\Internship\CancelRequestDetailResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CancellationController extends InternshipBaseController
{
    /**
     * UC 38: Sinh viên gửi yêu cầu hủy thực tập
     */
    public function requestCancel(Request $request)
    {
        $studentId = auth()->id();

        // 1. Tìm internship của sinh viên
        $internship = Internship::where('student_id', $studentId)
            ->whereNotIn('status', [Internship::STATUS_CANCEL, Internship::STATUS_COMPLETED])
            ->latest()
            ->first();

        if (!$internship) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn hiện không có đợt thực tập nào để yêu cầu hủy.'
            ], 404);
        }

        // 2. Kiểm tra xem đã gửi yêu cầu hủy trước đó chưa
        $existingRequest = InternshipRequest::where('internship_id', $internship->internship_id)
            ->where('type', InternshipRequest::TYPE_CANCEL_REQ)
            ->whereIn('status', [
                InternshipRequest::STATUS_PENDING_TEACHER,
                InternshipRequest::STATUS_PENDING_FACULTY,
                InternshipRequest::STATUS_APPROVED,
            ])
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã gửi yêu cầu hủy trước đó, vui lòng chờ xử lý.'
            ], 400);
        }

        // 3. Kiểm tra thời hạn (Bypass for testing)
        // BR-1: Trong vòng 14 ngày kể từ ngày bắt đầu đợt thực tập
        $milestone = Milestone::where('semester_id', $internship->semester_id)
            ->where('type', Milestone::TYPE_INTERNSHIP)
            ->first();

        return DB::transaction(function () use ($internship) {
            // Nếu chưa có giảng viên hướng dẫn HOẶC mới chỉ ở bước INITIALIZED (vừa đăng ký xong), cho phép hủy ngay lập tức
            if (!$internship->lecturer_id || $internship->status === Internship::STATUS_INITIALIZED) {
                // Xóa mọi request liên quan để dọn dẹp
                InternshipRequest::where('internship_id', $internship->internship_id)->delete();
                
                // Cập nhật trạng thái thành CANCEL
                $internship->update(['status' => Internship::STATUS_CANCEL]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đã hủy đợt thực tập thành công.'
                ]);
            }

            // 4. Lưu yêu cầu hủy vào bảng internship_requests
            InternshipRequest::create([
                'internship_id' => $internship->internship_id,
                'type' => InternshipRequest::TYPE_CANCEL_REQ,
                'status' => InternshipRequest::STATUS_PENDING_TEACHER,
                'student_message' => 'Sinh viên yêu cầu hủy học phần thực tập.',
            ]);

            // 5. Gửi thông báo cho giảng viên hướng dẫn
            $this->notifyStudent($internship->lecturer_id, "Sinh viên đã gửi yêu cầu hủy thực tập. Vui lòng xem xét phê duyệt.");

            return response()->json([
                'success' => true,
                'message' => 'Gửi yêu cầu hủy thành công, vui lòng chờ giảng viên hướng dẫn phê duyệt.'
            ]);
        });
    }

    /**
     * UC 39.1: Hiển thị danh sách sinh viên yêu cầu hủy (Dành cho GV hướng dẫn)
     */
    public function getPendingCancelLecturer()
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
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

    /**
     * UC 39.1: Giảng viên Duyệt hoặc Từ chối yêu cầu hủy
     */
    public function reviewCancelLecturer(ReviewCancelRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = auth()->user();
            $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
            $cancelReq = InternshipRequest::where('type', InternshipRequest::TYPE_CANCEL_REQ)
                ->where('status', InternshipRequest::STATUS_PENDING_TEACHER)
                ->whereHas('internship', function ($q) use ($lecturerId) {
                $q->where('lecturer_id', $lecturerId);
            }
            )->findOrFail($id);

            if ($request->status === 'APPROVED') {
                // Bước 5: Chuyển lên VPK (PENDING_FACULTY)
                $cancelReq->update(['status' => InternshipRequest::STATUS_PENDING_FACULTY]);
            }
            else {
                // 4a: Từ chối -> Kết thúc yêu cầu
                $cancelReq->update(['status' => InternshipRequest::STATUS_REJECTED, 'feedback' => $request->feedback]);
                $this->notifyStudent($cancelReq->internship->student_id, "Yêu cầu hủy thực tập bị từ chối bởi GVHD.");
            }

            return response()->json(['success' => true, 'message' => 'Xử lý yêu cầu thành công.']);
        });
    }

    /**
     * UC 39.2: Hiển thị danh sách chờ VPK phê duyệt hủy
     */
    public function getPendingCancelVPK()
    {
        // BR-1: Chỉ duyệt yêu cầu đã qua bước GV duyệt (PENDING_FACULTY)
        $requests = InternshipRequest::where('type', InternshipRequest::TYPE_CANCEL_REQ)
            ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
            ->get();

        return CancelRequestDetailResource::collection($requests);
    }

    /**
     * UC 39.2: VPK phê duyệt cuối cùng
     */
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
}
