<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Models\{Notification, UserNotification};

class InternshipBaseController extends Controller
{
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
