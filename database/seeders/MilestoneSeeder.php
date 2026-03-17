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
            ['semester_id' => 8, 'phase_name' => 'Register internship phase',
             'description' => 'Đăng ký tham gia đợt thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2025-01-01 00:00:00', 'end_date' => '2025-01-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Register internship company',
             'description' => 'Đăng ký doanh nghiệp thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2025-01-16 00:00:00', 'end_date' => '2025-01-31 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Submit internship proposal',
             'description' => 'Nộp đề cương chi tiết cho quá trình thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2025-02-01 00:00:00', 'end_date' => '2025-02-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Submit internship report',
             'description' => 'Nộp báo cáo tổng kết quá trình thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2025-04-01 00:00:00', 'end_date' => '2025-04-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Grade internship',
             'description' => 'Giảng viên chấm điểm báo cáo và quá trình thực tập',
             'type' => 'INTERNSHIP', 'start_date' => '2025-04-16 00:00:00', 'end_date' => '2025-04-30 23:59:00'],

            // CAPSTONE
            ['semester_id' => 8, 'phase_name' => 'Register project phase',
             'description' => 'Đăng ký tham gia đợt làm đồ án',
             'type' => 'CAPSTONE', 'start_date' => '2025-01-01 00:00:00', 'end_date' => '2025-01-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Register project topic',
             'description' => 'Đăng ký đề tài thực hiện đồ án tốt nghiệp',
             'type' => 'CAPSTONE', 'start_date' => '2025-01-16 00:00:00', 'end_date' => '2025-01-31 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Register project supervisor',
             'description' => 'Đăng ký giảng viên hướng dẫn đồ án',
             'type' => 'CAPSTONE', 'start_date' => '2025-02-01 00:00:00', 'end_date' => '2025-02-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Submit project report 1',
             'description' => 'Báo cáo tiến độ đồ án lần 1',
             'type' => 'CAPSTONE', 'start_date' => '2025-02-16 00:00:00', 'end_date' => '2025-02-28 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Submit project report 2',
             'description' => 'Báo cáo tiến độ đồ án lần 2',
             'type' => 'CAPSTONE', 'start_date' => '2025-03-01 00:00:00', 'end_date' => '2025-03-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Submit project report 3',
             'description' => 'Báo cáo tiến độ đồ án lần 3',
             'type' => 'CAPSTONE', 'start_date' => '2025-03-16 00:00:00', 'end_date' => '2025-03-31 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Submit project report 4',
             'description' => 'Báo cáo tiến độ đồ án lần 4',
             'type' => 'CAPSTONE', 'start_date' => '2025-04-01 00:00:00', 'end_date' => '2025-04-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Grade project',
             'description' => 'Giảng viên chấm điểm và bảo vệ đồ án tốt nghiệp',
             'type' => 'CAPSTONE', 'start_date' => '2025-04-16 00:00:00', 'end_date' => '2025-04-30 23:59:00'],
        ];

        foreach ($rows as $row) {
            DB::table('milestones')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
