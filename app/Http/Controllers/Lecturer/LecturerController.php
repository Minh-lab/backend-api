<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use App\Models\LecturerRequest;
use App\Http\Resources\LecturerResource;
use App\Http\Requests\Lecturer\SearchLecturerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Lecturer\LecturerSearchResource;
use Illuminate\Database\Eloquent\Builder;

class LecturerController extends Controller
{
    /**
     * UC 47: Tìm kiếm giảng viên
     */
    public function search(SearchLecturerRequest $request)
    {
        // Sử dụng eager loading và count để tối ưu tốc độ < 2s (NFR-1)
        $query = Lecturer::with(['expertises'])
            ->withCount(['internships', 'capstones']);

        // 1. Tìm theo tên giảng viên (Bước 2)
        if ($request->filled('keyword')) {
            $query->where('full_name', 'like', '%' . $request->keyword . '%');
        }

        // 2. Lọc theo chuyên môn (expertise)
        if ($request->filled('expertise_id')) {
            $query->whereHas('expertises', function (Builder $q) use ($request) {
                $q->where('expertises.expertise_id', $request->expertise_id);
            });
        }

        // 3. Lọc theo trạng thái Slot (Còn/Hết)
        if ($request->filled('slot_status')) {
            $max = 30;
            if ($request->slot_status === 'full') {
                $query->havingRaw('(internships_count + capstones_count) >= ?', [$max]);
            } else {
                $query->havingRaw('(internships_count + capstones_count) < ?', [$max]);
            }
        }

        // 4. Lọc theo trạng thái tiếp nhận (Nhận thêm / Không nhận)
        if ($request->filled('acceptance_status')) {
            if ($request->acceptance_status === 'busy') {
                // Đang nghỉ phép hoặc đã hết slot
                $query->whereHas('leaves', function ($q) {
                    $q->where('status', 'LEAVE_ACTIVE');
                })->orHavingRaw('(internships_count + capstones_count) >= 30');
            } else {
                // Không nghỉ phép VÀ còn slot
                $query->whereDoesntHave('leaves', function ($q) {
                    $q->where('status', 'LEAVE_ACTIVE');
                })->havingRaw('(internships_count + capstones_count) < 30');
            }
        }

        $lecturers = $query->paginate(10);

        return LecturerSearchResource::collection($lecturers);
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
