<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipReportSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['internship_id' => 1, 'milestone_id' => 9, 'status' => 'APPROVED',
             'description' => 'Tháng đầu tham gia dự án website bán hàng, thực hiện các tính năng frontend với ReactJS',
             'lecturer_feedback' => 'Báo cáo đầy đủ, tiếp tục cố gắng',
             'file_path' => 'reports/internship/1/giuaky.pdf',
             'submission_date' => '2025-04-10 15:30:00'],

            ['internship_id' => 1, 'milestone_id' => 10, 'status' => 'APPROVED',
             'description' => 'Hoàn thành module giỏ hàng và thanh toán, tham gia deploy lên production',
             'lecturer_feedback' => 'Báo cáo chi tiết, đạt yêu cầu',
             'file_path' => 'reports/internship/1/cuoiky.pdf',
             'submission_date' => '2025-05-28 14:00:00'],

            ['internship_id' => 2, 'milestone_id' => 9, 'status' => 'APPROVED',
             'description' => 'Phát triển API RESTful cho hệ thống quản lý nội dung sử dụng Java Spring Boot',
             'lecturer_feedback' => 'Xuất sắc',
             'file_path' => 'reports/internship/2/giuaky.pdf',
             'submission_date' => '2025-04-12 10:00:00'],

            ['internship_id' => 2, 'milestone_id' => 10, 'status' => 'APPROVED',
             'description' => 'Hoàn thành module xác thực JWT, tối ưu query database, viết unit test đạt 85% coverage',
             'lecturer_feedback' => 'Rất tốt, kết quả ấn tượng',
             'file_path' => 'reports/internship/2/cuoiky.pdf',
             'submission_date' => '2025-05-27 09:00:00'],

            ['internship_id' => 3, 'milestone_id' => 9, 'status' => 'PENDING',
             'description' => null, 'lecturer_feedback' => null,
             'file_path' => 'reports/internship/3/giuaky.pdf',
             'submission_date' => '2025-04-14 20:00:00'],
        ];

        foreach ($rows as $row) {
            DB::table('internship_reports')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
