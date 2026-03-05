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
            ['semester_id' => 8, 'phase_name' => 'Đăng ký đề tài & giảng viên hướng dẫn',
             'description' => 'Sinh viên đăng ký đề tài và giảng viên hướng dẫn đồ án tốt nghiệp',
             'type' => 'CAPSTONE', 'deadline' => '2025-02-20 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp đề cương đồ án',
             'description' => 'Nộp đề cương chi tiết sau khi được duyệt đề tài',
             'type' => 'CAPSTONE', 'deadline' => '2025-03-10 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Báo cáo tiến độ lần 1',
             'description' => 'Báo cáo tiến độ đồ án lần 1 - hoàn thành 30%',
             'type' => 'CAPSTONE', 'deadline' => '2025-03-31 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Báo cáo tiến độ lần 2',
             'description' => 'Báo cáo tiến độ đồ án lần 2 - hoàn thành 60%',
             'type' => 'CAPSTONE', 'deadline' => '2025-04-30 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp báo cáo chính thức',
             'description' => 'Nộp toàn bộ báo cáo đồ án hoàn chỉnh để xét điều kiện phản biện',
             'type' => 'CAPSTONE', 'deadline' => '2025-05-20 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Bảo vệ đồ án tốt nghiệp',
             'description' => 'Sinh viên bảo vệ đồ án trước hội đồng',
             'type' => 'CAPSTONE', 'deadline' => '2025-06-10 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Đăng ký công ty thực tập',
             'description' => 'Đăng ký công ty thực tập trong danh sách đối tác hoặc tự đề xuất',
             'type' => 'INTERNSHIP', 'deadline' => '2025-02-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp kế hoạch thực tập',
             'description' => 'Nộp kế hoạch và mục tiêu thực tập sau khi được duyệt công ty',
             'type' => 'INTERNSHIP', 'deadline' => '2025-03-05 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Báo cáo giữa kỳ thực tập',
             'description' => 'Báo cáo tiến độ thực tập giữa kỳ',
             'type' => 'INTERNSHIP', 'deadline' => '2025-04-15 23:59:00'],

            ['semester_id' => 8, 'phase_name' => 'Nộp báo cáo thực tập cuối kỳ',
             'description' => 'Nộp báo cáo tổng kết thực tập tốt nghiệp',
             'type' => 'INTERNSHIP', 'deadline' => '2025-05-30 23:59:00'],

            ['semester_id' => 10, 'phase_name' => 'Đăng ký đề tài & giảng viên hướng dẫn',
             'description' => 'Đăng ký đề tài và giảng viên kỳ 1 năm 2025-2026',
             'type' => 'CAPSTONE', 'deadline' => '2025-09-20 23:59:00'],

            ['semester_id' => 10, 'phase_name' => 'Đăng ký công ty thực tập',
             'description' => 'Đăng ký công ty thực tập kỳ 1 năm 2025-2026',
             'type' => 'INTERNSHIP', 'deadline' => '2025-09-15 23:59:00'],
        ];

        foreach ($rows as $row) {
            DB::table('milestones')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
