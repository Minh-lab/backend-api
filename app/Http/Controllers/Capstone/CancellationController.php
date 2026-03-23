<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{Capstone, CapstoneRequest, Lecturer, Milestone, LecturerLeave};
use App\Http\Requests\Capstone\ReviewCancellationRequest;
use App\Http\Resources\Capstone\CapstoneCancellationResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CancellationController extends CapstoneBaseController
{
    /**
     * UC 30: Sinh viên gửi yêu cầu hủy đồ án
     */
    public function requestCancel(Request $request)
    {
        // 1. Lấy thông tin sinh viên đang đăng nhập
        $studentId = auth()->id();

        // 2. Tìm đồ án của sinh viên (đảm bảo sinh viên có đồ án trong hệ thống)
        $capstone = Capstone::where('student_id', $studentId)->first();

        if (!$capstone) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn hiện không có học phần đồ án nào để yêu cầu hủy.'
            ], 404);
        }

        // 3. Kiểm tra xem đã gửi yêu cầu hủy trước đó chưa
        $existingRequest = CapstoneRequest::where('capstone_id', $capstone->capstone_id)
            ->where('type', CapstoneRequest::TYPE_CANCEL_REQ)
            ->whereIn('status', [
                CapstoneRequest::STATUS_PENDING_TEACHER,
                CapstoneRequest::STATUS_PENDING_FACULTY,
                CapstoneRequest::STATUS_APPROVED, 
            ])
            ->first();
        
        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã gửi yêu cầu hủy trước đó, vui lòng chờ xử lý.'
            ], 400);
        }

        // 4. Kiểm tra thời hạn (BR-1: Trong vòng 14 ngày kể từ ngày bắt đầu đợt đồ án)
        $milestone = Milestone::where('semester_id', $capstone->semester_id)
            ->where('type', 'CAPSTONE')
            ->first();

        if (!$milestone) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin đợt đồ án để kiểm tra thời hạn.'
            ], 404);
        }

        $startDate = Carbon::parse($milestone->start_date);
        $now = Carbon::now();

        // Kiểm tra logic 14 ngày (BR-1)
        if ($now->diffInDays($startDate, false) > 14 || $now->lt($startDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã hết thời gian yêu cầu hủy học phần đồ án tốt nghiệp (4a1).'
            ], 400);
        }

        return DB::transaction(function () use ($capstone) {
            // 5. Lưu yêu cầu hủy vào bảng capstone_requests (không thay đổi capstone.status)
            CapstoneRequest::create([
                'capstone_id' => $capstone->capstone_id,
                'type' => CapstoneRequest::TYPE_CANCEL_REQ,
                'status' => CapstoneRequest::STATUS_PENDING_TEACHER,
                'student_message' => 'Sinh viên yêu cầu hủy học phần đồ án.',
            ]);

            // 6. Gửi thông báo cho giảng viên hướng dẫn
            $this->sendNotification(
                $capstone->lecturer_id,
                2, // Role Lecturer
                "Sinh viên {$capstone->student->full_name} đã gửi yêu cầu hủy học phần đồ án. Vui lòng xem xét và phê duyệt/từ chối."
            );

            return response()->json([
                'success' => true,
                'message' => 'Gửi yêu cầu hủy học phần thành công, vui lòng chờ giảng viên hướng dẫn phê duyệt.'
            ]);
        });
    }

    /**
     * UC 31.1: Hiển thị danh sách yêu cầu hủy chờ giảng viên xét duyệt (Dành cho GV hướng dẫn)
     */
    public function getPendingCancellationsLecturer()
    {
        $user = auth()->user();
        $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // Kiểm tra trạng thái nghỉ phép (Ngoại lệ 2a)
        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép.'], 403);
        }

        // Lấy danh sách yêu cầu hủy của những sinh viên mình hướng dẫn
        // src/capstone_requests với type=CANCEL_REQ, status=PENDING_TEACHER
        $list = CapstoneRequest::where('type', CapstoneRequest::TYPE_CANCEL_REQ)
            ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
            ->whereHas('capstone', function ($query) use ($lecturerId) {
                $query->where('lecturer_id', $lecturerId);
            })
            ->with(['capstone.student.studentClass', 'capstone.topic'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $list->map(function ($request) {
                $capstone = $request->capstone;
                return [
                    'request_id' => $request->capstone_request_id,
                    'capstone_id' => $capstone->capstone_id,
                    'student_code' => $capstone->student->usercode ?? 'N/A',
                    'student_name' => $capstone->student->full_name ?? 'N/A',
                    'class_name' => $capstone->student->studentClass->class_name ?? 'N/A',
                    'topic_title' => $capstone->topic->title ?? 'N/A',
                    'reason' => $request->student_message ?? 'Yêu cầu hủy đồ án',
                    'status' => $request->status,
                    'created_at' => optional($request->created_at)->format('Y-m-d H:i:s'),
                    'topic' => $capstone->topic ? [
                        'topic_id' => $capstone->topic->topic_id,
                        'title' => $capstone->topic->title,
                        'description' => $capstone->topic->description,
                        'technologies' => $capstone->topic->technologies,
                    ] : null,
                ];
            })
        ]);
    }

    /**
     * UC 31.1: Giảng viên xét duyệt yêu cầu hủy (APPROVED -> PENDING_FACULTY, REJECTED -> REJECTED)
     */
    public function reviewCancellationLecturer(ReviewCancellationRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = auth()->user();
            $lecturerId = $user->lecturer_id ?? $user->getAuthIdentifier();
            
            // Lấy capstone_request với type=CANCEL_REQ, status=PENDING_TEACHER
            $cancellationRequest = CapstoneRequest::where('capstone_request_id', $id)
                ->where('type', CapstoneRequest::TYPE_CANCEL_REQ)
                ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
                ->with('capstone')
                ->firstOrFail();
            
            // Kiểm tra quyền: yêu cầu phải của sinh viên mình hướng dẫn
            if ($cancellationRequest->capstone->lecturer_id != $lecturerId) {
                return response()->json(['message' => 'Bạn không có quyền xét duyệt yêu cầu này'], 403);
            }

            if ($request->status === 'APPROVED') {
                // Duyệt: Chuyển sang trạng thái PENDING_FACULTY (chờ VPK phê duyệt)
                $cancellationRequest->update([
                    'status' => CapstoneRequest::STATUS_PENDING_FACULTY,
                    'lecturer_feedback' => $request->feedback ?? ''
                ]);

                // Gửi thông báo cho VPK
                $this->sendNotification(
                    1, // Giả định: ID nhân viên VPK chính
                    3, // Role VPK
                    "Giảng viên {$cancellationRequest->capstone->lecturer->full_name} đã phê duyệt yêu cầu hủy của SV {$cancellationRequest->capstone->student->full_name}. Đợi bạn phê duyệt cuối cùng."
                );

                return response()->json(['success' => true, 'message' => 'Đã phê duyệt yêu cầu hủy. Chuyển sang Văn phòng khoa xét duyệt.']);
            } else {
                // Từ chối: Status = REJECTED
                $cancellationRequest->update([
                    'status' => CapstoneRequest::STATUS_REJECTED,
                    'lecturer_feedback' => $request->feedback ?? ''
                ]);

                // Gửi thông báo cho sinh viên
                $this->sendNotification(
                    $cancellationRequest->capstone->student_id,
                    1, // Role Student
                    "Giảng viên hướng dẫn đã từ chối yêu cầu hủy đồ án của bạn. Lý do: {$request->feedback}"
                );

                return response()->json(['success' => true, 'message' => 'Đã từ chối yêu cầu hủy đồ án.']);
            }
        });
    }

    /**
     * UC 31.2: Hiển thị danh sách yêu cầu hủy chờ VPK phê duyệt cuối cùng
     */
    public function getPendingCancellationsVPK()
    {
        // Lấy tất cả capstone_requests với type=CANCEL_REQ, status=PENDING_FACULTY
        $list = CapstoneRequest::where('type', CapstoneRequest::TYPE_CANCEL_REQ)
            ->where('status', CapstoneRequest::STATUS_PENDING_FACULTY)
            ->with(['capstone.student.studentClass', 'capstone.topic', 'capstone.lecturer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $list->map(function ($request) {
                $capstone = $request->capstone;
                return [
                    'request_id' => $request->capstone_request_id,
                    'capstone_id' => $capstone->capstone_id,
                    'student_code' => $capstone->student->usercode ?? 'N/A',
                    'student_name' => $capstone->student->full_name ?? 'N/A',
                    'class_name' => $capstone->student->studentClass->class_name ?? 'N/A',
                    'topic_title' => $capstone->topic->title ?? 'N/A',
                    'lecturer_name' => $capstone->lecturer->full_name ?? 'N/A',
                    'lecturer_feedback' => $request->lecturer_feedback,
                    'reason' => $request->student_message ?? 'Yêu cầu hủy đồ án',
                    'status' => $request->status,
                    'created_at' => optional($request->created_at)->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    /**
     * UC 31.2: VPK phê duyệt cuối cùng (APPROVED -> APPROVED + set capstone.status=CANCEL, REJECTED -> REJECTED)
     */
    public function reviewCancellationVPK(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            // Lấy capstone_request với type=CANCEL_REQ, status=PENDING_FACULTY
            $cancellationRequest = CapstoneRequest::where('capstone_request_id', $id)
                ->where('type', CapstoneRequest::TYPE_CANCEL_REQ)
                ->where('status', CapstoneRequest::STATUS_PENDING_FACULTY)
                ->with('capstone')
                ->firstOrFail();

            if ($request->action === 'APPROVE') {
                // Phê duyệt: Cập nhật status request = APPROVED, cập nhật capstone.status = CANCEL
                $cancellationRequest->update([
                    'status' => CapstoneRequest::STATUS_APPROVED,
                    'lecturer_feedback' => $request->feedback ?? $cancellationRequest->lecturer_feedback
                ]);
                
                $cancellationRequest->capstone->update(['status' => Capstone::STATUS_CANCEL]);

                $msg = "Văn phòng khoa đã chính thức phê duyệt yêu cầu hủy. Học phần đồ án của bạn đã bị hủy trên hệ thống.";
                $resMsg = "Phê duyệt hủy đồ án thành công.";
            } else {
                // Từ chối: Cập nhật status request = REJECTED, capstone.status giữ nguyên
                $cancellationRequest->update([
                    'status' => CapstoneRequest::STATUS_REJECTED,
                    'lecturer_feedback' => $request->feedback ?? $cancellationRequest->lecturer_feedback
                ]);

                $msg = "Văn phòng khoa đã từ chối yêu cầu hủy đồ án của bạn. Lý do: {$request->feedback}";
                $resMsg = "Đã từ chối yêu cầu hủy đồ án.";
            }

            // Gửi thông báo cho sinh viên
            $this->sendNotification(
                $cancellationRequest->capstone->student_id,
                1, // Role Student
                $msg
            );

            return response()->json(['success' => true, 'message' => $resMsg]);
        });
    }
}
