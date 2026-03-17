<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use App\Models\LecturerRequest;
use App\Http\Resources\LecturerResource;
use App\Http\Requests\Lecturer\SearchLecturerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    /**
     * UC 47: Tìm kiếm giảng viên
     */
    public function index(SearchLecturerRequest $request)
    {
        $query = Lecturer::query()->with('expertises');

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('usercode', 'like', '%' . $request->keyword . '%');
            });
        }

        $lecturers = $query->paginate($request->get('per_page', 10));
        return LecturerResource::collection($lecturers);
    }

    /**
     * UC 48 - Bước 4: Xem chi tiết yêu cầu nghỉ phép
     */
    public function show($id)
    {
        // Eager load các request loại LEAVE_REQ đang PENDING
        $lecturer = Lecturer::with(['requests' => function ($q) {
            $q->where('type', LecturerRequest::TYPE_LEAVE_REQ)
                ->where('status', LecturerRequest::STATUS_PENDING);
        }])->findOrFail($id);

        return new LecturerResource($lecturer);
    }

    /**
     * UC 48 - Bước 5 & 6: Phê duyệt nghỉ phép
     */
    public function approveLeave(Request $request, $id)
    {
        return DB::transaction(function () use ($id) {
            // 1. Cập nhật trạng thái yêu cầu (Bảng lecturer_requests)
            $leaveReq = LecturerRequest::where('lecturer_id', $id)
                ->where('type', LecturerRequest::TYPE_LEAVE_REQ)
                ->where('status', LecturerRequest::STATUS_PENDING)
                ->firstOrFail();

            $leaveReq->update([
                'status' => LecturerRequest::STATUS_APPROVED,
                'faculty_feedback' => 'Đã phê duyệt nghỉ phép',
            ]);

            // 2. Cập nhật trạng thái giảng viên (Bước 6: is_active = 0)
            $lecturer = Lecturer::findOrFail($id);
            $lecturer->update(['is_active' => 0]);

            // [Gợi ý] Bước 7 & 8: Gửi thông báo cho GV và Sinh viên qua Notification/Mail

            return response()->json([
                'success' => true,
                'message' => 'Phê duyệt nghỉ phép thành công. Giảng viên đã chuyển sang trạng thái Nghỉ phép.'
            ]);
        });
    }
}
