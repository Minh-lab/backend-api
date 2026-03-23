<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{Capstone, CapstoneRequest, Lecturer, Notification, UserNotification, Milestone, LecturerLeave};
use App\Http\Requests\Capstone\ConfirmRegistrationRequest;
use App\Http\Resources\Capstone\CapstoneRegistrationResource;
use Illuminate\Support\Facades\DB;

class RegistrationController extends CapstoneBaseController
{
    /**
     * UC 23 - Lấy danh sách đăng ký hướng dẫn
     */
    public function getPendingRegistrations()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // Kiểm tra nghỉ phép - Xử lý lỗi ambiguous status
        if ($lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists()) {
            return response()->json(['message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép.'], 403);
        }

        // Kiểm tra thời hạn theo start_date và end_date
        $milestone = Milestone::capstone()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$milestone) {
            return response()->json(['message' => 'Hiện không nằm trong thời gian đăng ký GVHD.'], 400);
        }

        $requests = CapstoneRequest::where('lecturer_id', $lecturerId)
            ->whereIn('type', [
                CapstoneRequest::TYPE_LECTURER_REG,
                CapstoneRequest::TYPE_TOPIC_BANK,
            ])
            ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
            ->orderBy('created_at', 'asc')
            ->with(['capstone.student.studentClass'])
            ->get();

        return CapstoneRegistrationResource::collection($requests);
    }

    /**
     * UC 24.1: Lấy danh sách đăng ký chờ VPK duyệt đề tài
     */
    public function getPendingRegistrationsVPK()
    {
        try {
            $page = request()->get('page', 1);
            $itemsPerPage = request()->get('itemsPerPage', 10);
            $search = request()->get('search', '');
            $status = request()->get('status', 'PENDING_FACULTY');
            $major = request()->get('major', '');

            $query = CapstoneRequest::whereIn('type', [
                CapstoneRequest::TYPE_TOPIC_PROP,
                CapstoneRequest::TYPE_TOPIC_BANK
            ])->where('status', $status)
            ->with(['capstone.student.studentClass', 'capstone.topic.expertise', 'capstone.lecturer'])
            ->orderBy('created_at', 'desc');

            if ($search) {
                $query->whereHas('capstone.student', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%$search%")
                      ->orWhere('usercode', 'like', "%$search%");
                });
            }

            if ($major) {
                $query->whereHas('capstone.topic.expertise', function ($q) use ($major) {
                    $q->where('expertise_id', $major);
                });
            }

            $registrations = $query->paginate($itemsPerPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'registrations' => $registrations->items(),
                    'pagination' => [
                        'current_page' => $registrations->currentPage(),
                        'total' => $registrations->total(),
                        'per_page' => $registrations->perPage(),
                        'last_page' => $registrations->lastPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bước 4-8: Xử lý Xác nhận
     */
    public function confirmRegistration(ConfirmRegistrationRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $lecturerId = auth()->id();
            $lecturer = Lecturer::findOrFail($lecturerId);
            $capReq = CapstoneRequest::where('lecturer_id', $lecturerId)
                ->whereIn('type', [
                    CapstoneRequest::TYPE_LECTURER_REG,
                    CapstoneRequest::TYPE_TOPIC_BANK,
                ])
                ->findOrFail($id);

            $feedback = trim((string) $request->input('feedback', ''));

            if ($request->action === 'APPROVE') {
                // 5a. Kiểm tra giới hạn slot (BR-2: Giả định 30)
                $maxSlots = 30;
                if ($lecturer->capstones()->count() >= $maxSlots) {
                    return response()->json(['message' => 'Đã nhận đủ số lượng sinh viên tối đa (5a1).'], 400);
                }

                // Bước 6: Cập nhật giảng viên vào đồ án & Trạng thái yêu cầu
                $capReq->update([
                    'status' => CapstoneRequest::STATUS_APPROVED,
                    'lecturer_feedback' => $feedback !== '' ? $feedback : null
                ]);
                $capReq->capstone->update([
                    'lecturer_id' => $lecturerId,
                    'status'      => Capstone::STATUS_LECTURER_APPROVED
                ]);

                $notifyMsg = "Giảng viên {$lecturer->full_name} đã chấp nhận hướng dẫn đồ án của bạn.";
            } else {
                // 4a. Từ chối
                $capReq->update([
                    'status' => CapstoneRequest::STATUS_REJECTED,
                    'lecturer_feedback' => $feedback !== '' ? $feedback : null
                ]);
                $notifyMsg = "Giảng viên {$lecturer->full_name} đã từ chối yêu cầu hướng dẫn đồ án.";
            }

            if ($feedback !== '') {
                $notifyMsg .= " Lời nhắn: {$feedback}";
            }

            // Bước 8: Gửi thông báo cho sinh viên
            $this->notifyStudent($capReq->capstone->student_id, 1, $notifyMsg);

            return response()->json(['success' => true, 'message' => 'Xử lý xác nhận thành công.']);
        });
    }
}
