<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use App\Models\LecturerRequest;
use App\Models\LecturerLeave;
use App\Http\Resources\LecturerResource;
use App\Http\Requests\Lecturer\SearchLecturerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Lecturer\LecturerSearchResource;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class LecturerController extends Controller
{
    /**
     * UC 48 - Bước 1: Lấy danh sách giảng viên (kèm thông tin nghỉ phép)
     * GET /faculty/lecturers
     */
    public function index()
    {
        // Load tất cả lecturer với leaves và requests để frontend có thể detect trạng thái
        $lecturers = Lecturer::with([
            'leaves' => function ($q) {
                // Chỉ load leave records có status LEAVE_ACTIVE hoặc APPROVED_PENDING
                $q->whereIn('status', ['LEAVE_ACTIVE', 'APPROVED_PENDING']);
            },
            'requests' => function ($q) {
                // Chỉ load pending LEAVE_REQ
                $q->where('type', 'LEAVE_REQ')
                  ->where('status', 'PENDING');
            },
            'expertises' // Load chuyên môn
        ])->get();

        // Transform data để frontend dễ sử dụng
        $transformedLecturers = $lecturers->map(function ($lecturer) {
            return [
                'lecturer_id' => $lecturer->lecturer_id,
                'usercode' => $lecturer->usercode,
                'full_name' => $lecturer->full_name,
                'email' => $lecturer->email,
                'phone_number' => $lecturer->phone_number,
                'degree' => $lecturer->degree,
                'department' => $lecturer->department,
                'is_active' => $lecturer->is_active,
                'gender' => $lecturer->gender,
                'dob' => $lecturer->dob,
                'expertises' => $lecturer->expertises->map(fn($e) => [
                    'expertise_id' => $e->expertise_id,
                    'name' => $e->name
                ])->toArray(),
                // Thêm thông tin leaves để frontend detect "NGHỈ PHÉP"
                'leaves' => $lecturer->leaves->map(fn($leave) => [
                    'leave_id' => $leave->leave_id,
                    'status' => $leave->status,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date
                ])->toArray(),
                'requests' => $lecturer->requests->toArray(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedLecturers,
            'message' => 'Lấy danh sách giảng viên thành công'
        ]);
    }

    /**
     * UC 47: Tìm kiếm giảng viên
     */
    public function search(SearchLecturerRequest $request)
    {
        // Sử dụng eager loading và count để tối ưu tốc độ < 2s (NFR-1)
        $query = Lecturer::with(['expertises', 'leaves'])
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
                    $q->where('lecturer_leaves.status', 'LEAVE_ACTIVE');
                })->orHavingRaw('(internships_count + capstones_count) >= 30');
            } else {
                // Không nghỉ phép VÀ còn slot
                $query->whereDoesntHave('leaves', function ($q) {
                    $q->where('lecturer_leaves.status', 'LEAVE_ACTIVE');
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

            // 2a. Tạo bản ghi lecturer_leaves (Mới thêm theo yêu cầu)
            $leaveStart = $leaveReq->start_date ?? $request->input('start_date') ?? now();
            $leaveEnd = $leaveReq->end_date ?? $request->input('end_date') ?? now()->addDays(7);
            
            LecturerLeave::create([
                'request_id' => $leaveReq->request_id,
                'start_date' => $leaveStart,
                'end_date' => $leaveEnd,
                'status' => 'APPROVED_PENDING',
                'delegate_completed' => 0,
            ]);

            // 2b. Cập nhật trạng thái giảng viên (Bước 6: is_active = 0)
            $lecturer = Lecturer::findOrFail($id);
            $lecturer->update(['is_active' => 0]);

            // [Gợi ý] Bước 7 & 8: Gửi thông báo cho GV và Sinh viên qua Notification/Mail

            return response()->json([
                'success' => true,
                'message' => 'Phê duyệt nghỉ phép thành công. Giảng viên đã chuyển sang trạng thái Nghỉ phép.'
            ]);
        });
    }

    /**
     * UC 48 - Từ chối yêu cầu nghỉ phép
     */
    public function rejectLeave(Request $request, $id)
    {
        $feedback = $request->input('feedback', 'Yêu cầu nghỉ phép bị từ chối');

        return DB::transaction(function () use ($id, $feedback) {
            // Cập nhật trạng thái yêu cầu thành REJECTED
            $leaveReq = LecturerRequest::where('lecturer_id', $id)
                ->where('type', LecturerRequest::TYPE_LEAVE_REQ)
                ->where('status', LecturerRequest::STATUS_PENDING)
                ->firstOrFail();

            $leaveReq->update([
                'status' => LecturerRequest::STATUS_REJECTED,
                'faculty_feedback' => $feedback,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Từ chối yêu cầu nghỉ phép thành công.'
            ]);
        });
    }
}
