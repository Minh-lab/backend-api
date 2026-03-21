<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // role_id: 3=lecturer, 4=student
        // --- Dữ liệu gốc ---
        $rows = [
            // Thông báo 1 - gửi tất cả sinh viên
            ['notification_id' => 1, 'user_id' => 1,  'role_id' => 4, 'is_read' => 1],
            ['notification_id' => 1, 'user_id' => 2,  'role_id' => 4, 'is_read' => 1],
            ['notification_id' => 1, 'user_id' => 3,  'role_id' => 4, 'is_read' => 0],
            ['notification_id' => 1, 'user_id' => 4,  'role_id' => 4, 'is_read' => 1],
            ['notification_id' => 1, 'user_id' => 5,  'role_id' => 4, 'is_read' => 1],
            ['notification_id' => 1, 'user_id' => 6,  'role_id' => 4, 'is_read' => 0],
            ['notification_id' => 1, 'user_id' => 7,  'role_id' => 4, 'is_read' => 0],
            ['notification_id' => 1, 'user_id' => 8,  'role_id' => 4, 'is_read' => 1],
            ['notification_id' => 1, 'user_id' => 9,  'role_id' => 4, 'is_read' => 0],
            ['notification_id' => 1, 'user_id' => 10, 'role_id' => 4, 'is_read' => 0],
            // Thông báo 2 - gửi sinh viên thực tập
            ['notification_id' => 2, 'user_id' => 4,  'role_id' => 4, 'is_read' => 1],
            ['notification_id' => 2, 'user_id' => 5,  'role_id' => 4, 'is_read' => 1],
            ['notification_id' => 2, 'user_id' => 8,  'role_id' => 4, 'is_read' => 0],
            ['notification_id' => 2, 'user_id' => 9,  'role_id' => 4, 'is_read' => 1],
            // Thông báo 3 - nhắc sinh viên làm đồ án
            ['notification_id' => 3, 'user_id' => 1,  'role_id' => 4, 'is_read' => 0],
            ['notification_id' => 3, 'user_id' => 2,  'role_id' => 4, 'is_read' => 1],
            ['notification_id' => 3, 'user_id' => 3,  'role_id' => 4, 'is_read' => 0],
            // Thông báo 4 - gửi giảng viên
            ['notification_id' => 4, 'user_id' => 1,  'role_id' => 3, 'is_read' => 1],
            ['notification_id' => 4, 'user_id' => 2,  'role_id' => 3, 'is_read' => 1],
            ['notification_id' => 4, 'user_id' => 3,  'role_id' => 3, 'is_read' => 0],
            ['notification_id' => 4, 'user_id' => 4,  'role_id' => 3, 'is_read' => 1],
            // Thông báo 5 - tất cả sinh viên
            ['notification_id' => 5, 'user_id' => 1,  'role_id' => 4, 'is_read' => 0],
            ['notification_id' => 5, 'user_id' => 2,  'role_id' => 4, 'is_read' => 0],
            ['notification_id' => 5, 'user_id' => 3,  'role_id' => 4, 'is_read' => 0],
        ];

        foreach ($rows as $row) {
            DB::table('user_notifications')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Sinh thêm: Gửi thông báo 6-15 cho toàn bộ 50 sinh viên đầu ---
        for ($notifId = 6; $notifId <= 15; $notifId++) {
            for ($studentId = 1; $studentId <= 50; $studentId++) {
                DB::table('user_notifications')->insertOrIgnore([
                    'notification_id' => $notifId,
                    'user_id'         => $studentId,
                    'role_id'         => 4,
                    'is_read'         => rand(0, 1),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // --- Gửi một số thông báo cho giảng viên GV001-GV006 ---
        foreach ([7, 8, 9, 11, 12] as $notifId) {
            for ($lecturerId = 1; $lecturerId <= 6; $lecturerId++) {
                DB::table('user_notifications')->insertOrIgnore([
                    'notification_id' => $notifId,
                    'user_id'         => $lecturerId,
                    'role_id'         => 3,
                    'is_read'         => rand(0, 1),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }
}
