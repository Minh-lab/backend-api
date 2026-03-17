<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $user       = $request->user();
        $primaryKey = $user->getKeyName();
        $userId     = $user->$primaryKey;

        $notifications = UserNotification::with('notification')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($un) => [
                'id'         => $un->notification->notification_id,
                'title'      => $un->notification->title,
                'content'    => $un->notification->content,
                'is_read'    => (bool) $un->is_read,
                'created_at' => $un->notification->created_at,
            ]);

        return response()->json([
            'success'      => true,
            'data'         => $notifications,
            'total'        => $notifications->count(),
            'unread_count' => $notifications->where('is_read', false)->count(),
        ], 200);
    }


    // UC8 - Đánh dấu đã đọc

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user       = $request->user();
        $primaryKey = $user->getKeyName();
        $userId     = $user->$primaryKey;

        $userNotification = UserNotification::where('notification_id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$userNotification) {
            return response()->json([
                'success' => false,
                'message' => 'Thông báo không tồn tại.',
            ], 404);
        }

        $userNotification->update(['is_read' => 1]);

        $notification = $userNotification->notification;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $notification->notification_id,
                'title'      => $notification->title,
                'content'    => $notification->content,
                'is_read'    => true,
                'created_at' => $notification->created_at,
            ],
        ], 200);
    }
}