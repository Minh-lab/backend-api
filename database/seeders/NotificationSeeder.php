<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Thông báo gốc
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

            // Thông báo bổ sung
            ['title' => 'Thông báo mở đăng ký đề tài đồ án tốt nghiệp HK1 2025-2026',
             'content' => 'Sinh viên năm 4 đủ điều kiện bắt đầu đăng ký đề tài từ ngày 01/09/2025. Thời hạn đến 15/09/2025.'],

            ['title' => 'Danh sách sinh viên đủ điều kiện bảo vệ đồ án HK2 2024-2025',
             'content' => 'Phòng đào tạo đã công bố danh sách sinh viên đủ điều kiện bảo vệ. Sinh viên vui lòng kiểm tra trên hệ thống.'],

            ['title' => 'Thông báo gia hạn nộp báo cáo thực tập',
             'content' => 'Do nhiều sinh viên gặp khó khăn, phòng khoa quyết định gia hạn nộp báo cáo thực tập đến ngày 01/05/2025.'],

            ['title' => 'Cập nhật quy định về đề tài đồ án tốt nghiệp 2025',
             'content' => 'Phòng đào tạo thông báo cập nhật một số quy định mới về yêu cầu đề tài và báo cáo đồ án tốt nghiệp năm 2025.'],

            ['title' => 'Nhắc nhở nộp báo cáo tiến độ lần 2',
             'content' => 'Hạn nộp báo cáo tiến độ đồ án lần 2 là ngày 30/04/2025. Sinh viên chưa nộp cần nộp sớm để tránh bị đánh trượt.'],

            ['title' => 'Thông báo lịch chấm điểm thực tập HK2 2024-2025',
             'content' => 'Giảng viên hướng dẫn và doanh nghiệp vui lòng hoàn thành chấm điểm thực tập trước ngày 20/05/2025.'],

            ['title' => 'Khai mạc Hội thảo Khoa học Công nghệ thông tin 2025',
             'content' => 'Khoa CNTT thông báo tổ chức Hội thảo Khoa học ngày 10/04/2025. Sinh viên và giảng viên đăng ký tham dự trước 05/04/2025.'],

            ['title' => 'Thông báo về yêu cầu plagiarism cho báo cáo đồ án',
             'content' => 'Kể từ HK2 2024-2025, tất cả báo cáo đồ án phải kiểm tra đạo văn trước khi nộp. Tỷ lệ trùng lặp không quá 20%.'],

            ['title' => 'Mở đăng ký đồ án nhóm HK2 2024-2025',
             'content' => 'Khoa CNTT cho phép sinh viên thực hiện đồ án theo nhóm tối đa 2 người từ HK này. Đăng ký nhóm trước 28/02/2025.'],

            ['title' => 'Thông báo điều chỉnh mốc thời gian đợt thực tập HK2',
             'content' => 'Do một số doanh nghiệp yêu cầu, khoa điều chỉnh mốc thời gian bắt đầu thực tập từ 01/03/2025 sang 15/03/2025.'],
        ];

        foreach ($rows as $row) {
            DB::table('notifications')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
