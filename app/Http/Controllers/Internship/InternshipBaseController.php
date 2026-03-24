<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Models\{Notification, UserNotification, Semester};

class InternshipBaseController extends Controller
{
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

    /**
     * Gửi thông báo cho người dùng
     */
    protected function notifyStudent($userId, $content)
    {
        $notification = Notification::create([
            'title' => 'Kết quả yêu cầu hủy thực tập',
            'content' => $content
        ]);

        UserNotification::create([
            'notification_id' => $notification->notification_id,
            'user_id' => $userId,
            'role_id' => 1
        ]);
    }
}
