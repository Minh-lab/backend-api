<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MilestoneSeeder extends Seeder
{
    public function run(): void
    {
        // semester_id=8 là Kỳ 2 năm 2024-2025
        $rows = [
            // INTERNSHIP
            ['semester_id' => 8, 'phase_name' => 'Đăng ký đợt thực tập',
             'description' => 'Đăng ký tham gia đợt thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2026-01-01 00:00:00', 'end_date' => '2026-01-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Đăng ký doanh nghiệp thực tập',
             'description' => 'Đăng ký doanh nghiệp thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2026-01-16 00:00:00', 'end_date' => '2026-01-31 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp đề cương thực tập',
             'description' => 'Nộp đề cương chi tiết cho quá trình thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2026-02-01 00:00:00', 'end_date' => '2026-02-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp báo cáo thực tập',
             'description' => 'Nộp báo cáo tổng kết quá trình thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2026-04-01 00:00:00', 'end_date' => '2026-04-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Chấm điểm thực tập',
             'description' => 'Giảng viên chấm điểm báo cáo và quá trình thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2026-04-16 00:00:00', 'end_date' => '2026-04-30 23:59:00'],

            // CAPSTONE
            ['semester_id' => 8, 'phase_name' => 'Đăng ký đợt đồ án',
             'description' => 'Đăng ký tham gia đợt làm đồ án',
             'type' => 'CAPSTONE', 'start_date' => '2026-01-01 00:00:00', 'end_date' => '2026-01-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Đăng ký đề tài',
             'description' => 'Đăng ký đề tài thực hiện đồ án tốt nghiệp',
             'type' => 'CAPSTONE', 'start_date' => '2026-01-16 00:00:00', 'end_date' => '2026-01-31 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Đăng ký GVHDDA',
             'description' => 'Đăng ký giảng viên hướng dẫn đồ án',
             'type' => 'CAPSTONE', 'start_date' => '2026-02-01 00:00:00', 'end_date' => '2026-02-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp báo cáo đồ án 1',
             'description' => 'Báo cáo tiến độ đồ án lần 1',
             'type' => 'CAPSTONE', 'start_date' => '2026-02-16 00:00:00', 'end_date' => '2026-02-28 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp báo cáo đồ án 2',
             'description' => 'Báo cáo tiến độ đồ án lần 2',
             'type' => 'CAPSTONE', 'start_date' => '2026-03-01 00:00:00', 'end_date' => '2026-03-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp báo cáo đồ án 3',
             'description' => 'Báo cáo tiến độ đồ án lần 3',
             'type' => 'CAPSTONE', 'start_date' => '2026-03-16 00:00:00', 'end_date' => '2026-03-31 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp báo cáo đồ án 4',
             'description' => 'Báo cáo tiến độ đồ án lần 4',
             'type' => 'CAPSTONE', 'start_date' => '2026-04-01 00:00:00', 'end_date' => '2026-04-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Chấm điểm đồ án',
             'description' => 'Giảng viên chấm điểm và bảo vệ đồ án tốt nghiệp',
             'type' => 'CAPSTONE', 'start_date' => '2026-03-15 00:00:00', 'end_date' => '2026-06-30 23:59:00'],
        ];

        foreach ($rows as $row) {
            DB::table('milestones')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
