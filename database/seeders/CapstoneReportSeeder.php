<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapstoneReportSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['capstone_id' => 1, 'milestone_id' => 2, 'status' => 'APPROVED',
             'file_path' => 'reports/capstone/1/decuong.pdf',
             'lecturer_feedback' => 'Đề cương rõ ràng, bắt đầu thực hiện được',
             'submission_date' => '2025-03-08 14:30:00'],

            ['capstone_id' => 1, 'milestone_id' => 3, 'status' => 'APPROVED',
             'file_path' => 'reports/capstone/1/tiendo1.pdf',
             'lecturer_feedback' => 'Tiến độ tốt, tiếp tục phát huy',
             'submission_date' => '2025-03-28 10:00:00'],

            ['capstone_id' => 1, 'milestone_id' => 4, 'status' => 'APPROVED',
             'file_path' => 'reports/capstone/1/tiendo2.pdf',
             'lecturer_feedback' => 'Hoàn thành 70%, cần bổ sung phần testing',
             'submission_date' => '2025-04-25 09:15:00'],

            ['capstone_id' => 1, 'milestone_id' => 5, 'status' => 'APPROVED',
             'file_path' => 'reports/capstone/1/final.pdf',
             'lecturer_feedback' => 'Báo cáo hoàn chỉnh, đủ điều kiện bảo vệ',
             'submission_date' => '2025-05-15 16:00:00'],

            ['capstone_id' => 2, 'milestone_id' => 2, 'status' => 'APPROVED',
             'file_path' => 'reports/capstone/2/decuong.pdf',
             'lecturer_feedback' => 'Đề cương chi tiết, tốt',
             'submission_date' => '2025-03-07 11:00:00'],

            ['capstone_id' => 2, 'milestone_id' => 3, 'status' => 'APPROVED',
             'file_path' => 'reports/capstone/2/tiendo1.pdf',
             'lecturer_feedback' => 'Xuất sắc',
             'submission_date' => '2025-03-29 15:00:00'],

            ['capstone_id' => 4, 'milestone_id' => 3, 'status' => 'PENDING',
             'file_path' => 'reports/capstone/4/tiendo1.pdf',
             'lecturer_feedback' => null,
             'submission_date' => '2025-03-30 22:00:00'],
        ];

        foreach ($rows as $row) {
            DB::table('capstone_reports')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
