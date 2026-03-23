<?php

namespace App\Http\Controllers\Internship;

use App\Models\{Lecturer, Internship, LecturerLeave, Notification, UserNotification};
use App\Http\Requests\Internship\AssignLecturerRequest;
use App\Http\Resources\Internship\LecturerSlotResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LecturerAssignmentController extends InternshipBaseController
{
    /**
     * UC 43 - Bước 3: Lấy danh sách giảng viên kèm chỉ tiêu và trạng thái nghỉ phép
     */
    public function getLecturerSlots(\Illuminate\Http\Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 5); // 5 lecturers per page

        $query = Lecturer::query();

        // Search by name, usercode, or major
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('usercode', 'LIKE', "%{$search}%")
                  ->orWhere('major', 'LIKE', "%{$search}%");
            });
        }

        $lecturers = $query->paginate($perPage, ['*'], 'page', $page);
        return LecturerSlotResource::collection($lecturers);
    }

    /**
     * UC 43 - Bước 5-10: Thực hiện phân công GVHD
     */
    public function assignLecturer(AssignLecturerRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $lecturer = Lecturer::findOrFail($request->lecturer_id);
            $internshipIds = $request->internship_ids;
            $countSelected = count($internshipIds);

            // 6b & BR-1: Kiểm tra trạng thái nghỉ phép
            $isOnLeave = $lecturer->leaves()->where('lecturer_leaves.status', LecturerLeave::STATUS_LEAVE_ACTIVE)->exists();
            if ($isOnLeave) {
                return response()->json([
                    'message' => 'Không thể phân công cho giảng viên đang nghỉ phép (6b1).'
                ], 400);
            }

            // 6a: Kiểm tra chỉ tiêu (Slot)
            $currentGuiding = $lecturer->internships()->count();
            if (($currentGuiding + $countSelected) > 30) {
                return response()->json([
                    'message' => 'Giảng viên không thể tiếp nhận thêm, vui lòng chọn giảng viên khác (6a1).'
                ], 400);
            }

            // Bước 8: Cập nhật giảng viên hướng dẫn cho sinh viên
            Internship::whereIn('internship_id', $internshipIds)->update([
                'lecturer_id' => $lecturer->lecturer_id,
                'status' => 'LECTURER_APPROVED',
                'updated_at' => Carbon::now()
            ]);

            // Bước 9: Gửi thông báo cho cả Sinh viên và Giảng viên
            $notification = Notification::create([
                'title' => 'Thông báo phân công GVHD thực tập',
                'content' => "Hệ thống đã phân công Giảng viên {$lecturer->full_name} hướng dẫn thực tập."
            ]);

            // Gửi cho Giảng viên
            UserNotification::create([
                'notification_id' => $notification->notification_id,
                'user_id' => $lecturer->lecturer_id,
                'role_id' => 2,
            ]);

            // Gửi cho danh sách Sinh viên
            $studentIds = Internship::whereIn('internship_id', $internshipIds)->pluck('student_id');
            foreach ($studentIds as $sId) {
                UserNotification::create([
                    'notification_id' => $notification->notification_id,
                    'user_id' => $sId,
                    'role_id' => 1,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Phân công thành công (Bước 10)']);
        });
    }
}
