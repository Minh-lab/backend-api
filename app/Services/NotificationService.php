<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Gửi thông báo cho một hoặc nhiều người dùng
     * 
     * @param string $title
     * @param string $content
     * @param array $recipients Format: [
     *     ['user_id' => 1, 'role_id' => 3],
     *     ['user_id' => 2, 'role_id' => 3],
     * ]
     * @return bool
     */
    public function send(string $title, string $content, array $recipients): bool
    {
        try {
            // Tạo notification
            $notification = Notification::create([
                'title' => $title,
                'content' => $content,
            ]);

            // Tạo user notification cho từng người nhận
            foreach ($recipients as $recipient) {
                UserNotification::create([
                    'notification_id' => $notification->notification_id,
                    'user_id' => $recipient['user_id'],
                    'role_id' => $recipient['role_id'],
                    'is_read' => false,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi thông báo cho một người dùng
     * 
     * @param string $title
     * @param string $content
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function sendToUser(string $title, string $content, int $userId, int $roleId): bool
    {
        return $this->send($title, $content, [
            ['user_id' => $userId, 'role_id' => $roleId]
        ]);
    }
}
