<?php

namespace App\Http\Controllers\Capstone;

use App\Http\Controllers\Controller;
use App\Models\{Notification, UserNotification, Semester};

class CapstoneBaseController extends Controller
{
    /**
     * Helper: Gửi thông báo cho người dùng
     */
    protected function sendNotification($userId, $roleId, $content)
    {
        $notification = Notification::create([
            'title'   => 'Thông báo hệ thống Đồ án',
            'content' => $content
        ]);

        UserNotification::create([
            'notification_id' => $notification->notification_id,
            'user_id'         => $userId,
            'role_id'         => $roleId,
        ]);
    }

    /**
     * Helper: Gửi thông báo cho sinh viên hoặc giảng viên
     * Phương pháp này là alias của sendNotification với cách gọi linh hoạt
     */
    protected function notifyStudent($userId, $roleId, $content)
    {
        $notification = Notification::create([
            'title'   => 'Thông báo hệ thống Đồ án',
            'content' => $content
        ]);

        UserNotification::create([
            'notification_id' => $notification->notification_id,
            'user_id'         => $userId,
            'role_id'         => $roleId // Sử dụng biến $roleId truyền vào
        ]);
    }

    /**
     * Helper: Lấy semester ID hiện tại
     */
    protected function resolveCurrentSemesterId(): ?int
    {
        $currentSemester = Semester::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if ($currentSemester) {
            return $currentSemester->semester_id;
        }

        $latestSemester = Semester::orderByDesc('start_date')
            ->orderByDesc('semester_id')
            ->first();

        return $latestSemester?->semester_id;
    }
}
