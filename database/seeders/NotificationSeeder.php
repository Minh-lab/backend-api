<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['title' => 'Thông báo mở đăng ký đề tài đồ án tốt nghiệp HK2 2024-2025',
             'content' => 'Sinh viên năm 4 đủ điều kiện thực hiện đăng ký đề tài và giảng viên hướng dẫn. Thời hạn đến 20/02/2025.'],

            ['title' => 'Thông báo mở đăng ký thực tập tốt nghiệp HK2 2024-2025',
             'content' => 'Sinh viên đủ điều kiện đăng ký công ty thực tập. Ưu tiên công ty trong danh sách đối tác. Thời hạn đến 15/02/2025.'],

            ['title' => 'Nhắc nhở nộp báo cáo tiến độ lần 1',
             'content' => 'Hạn nộp báo cáo tiến độ đồ án lần 1 là ngày 31/03/2025. Sinh viên chưa nộp cần nộp sớm.'],

            ['title' => 'Lịch bảo vệ đồ án tốt nghiệp HK2 2024-2025',
             'content' => 'Lịch bảo vệ đã được công bố. Sinh viên xem chi tiết phòng và thứ tự bảo vệ tại hệ thống. Ngày bảo vệ: 05-06/06/2025.'],

            ['title' => 'Thông báo nộp hồ sơ tốt nghiệp',
             'content' => 'Sinh viên đã hoàn thành đồ án/thực tập cần nộp hồ sơ tốt nghiệp trước ngày 30/06/2025.'],
        ];

        foreach ($rows as $row) {
            DB::table('notifications')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
