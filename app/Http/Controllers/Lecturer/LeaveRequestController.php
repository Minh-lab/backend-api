<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LeaveRequest;
use App\Models\FacultyStaff;
use App\Models\LecturerRequest;
use App\Models\Notification;
use App\Models\Role;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{

    // UC7 - Tạo yêu cầu nghỉ phép
 
    public function store(LeaveRequest $request): JsonResponse
    {
        $lecturer   = $request->user();
        $lecturerId = $lecturer->lecturer_id;

        // Upload file đơn nghỉ phép
        $filePath = $request->file('file')
            ->store("leave_requests/{$lecturerId}", 'public');

        $leaveRequest = LecturerRequest::create([
            'lecturer_id' => $lecturerId,
            'type'        => 'LEAVE_REQ',
            'status'      => 'PENDING',
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'file_path'   => $filePath,
            'start_date'  => $request->input('start_date'),
            'end_date'    => $request->input('end_date'),
        ]);

        // Gửi thông báo cho toàn bộ FacultyStaff
        $this->notifyFacultyStaff($lecturer->full_name, $leaveRequest->request_id);

        return response()->json([
            'success' => true,
            'message' => 'Gửi yêu cầu thành công.',
            'data'    => [
                'request_id' => $leaveRequest->request_id,
                'title'      => $leaveRequest->title,
                'status'     => $leaveRequest->status,
                'start_date' => $leaveRequest->start_date,
                'end_date'   => $leaveRequest->end_date,
                'file_url'   => Storage::url($filePath),
            ],
        ], 201);
    }

 
    // Helper: Gửi thông báo cho FacultyStaff

    private function notifyFacultyStaff(string $lecturerName, int $requestId): void
    {
        $notification = Notification::create([
            'title'   => 'Yêu cầu nghỉ phép mới',
            'content' => "Giảng viên {$lecturerName} đã gửi yêu cầu nghỉ phép dài hạn. Mã yêu cầu: #{$requestId}",
        ]);

        $facultyStaffList = FacultyStaff::all();
        $now              = now();

        $userNotifications = $facultyStaffList->map(fn($fs) => [
            'notification_id' => $notification->notification_id,
            'user_id'         => $fs->faculty_staff_id,
            'is_read'         => 0,
            'created_at'      => $now,
            'updated_at'      => $now,
        ])->toArray();

        UserNotification::insert($userNotifications);
    }
}