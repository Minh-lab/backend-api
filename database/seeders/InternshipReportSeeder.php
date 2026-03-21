<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipReportSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dữ liệu gốc ---
        $original = [
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

        foreach ($original as $row) {
            DB::table('internship_reports')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Sinh thêm báo cáo cho các internship_id 4 đến 30 ---
        $statuses          = ['PENDING', 'APPROVED', 'REJECTED'];
        $descriptions      = [
            'Tham gia phát triển module quản lý người dùng và phân quyền.',
            'Xây dựng giao diện responsive theo thiết kế Figma của team design.',
            'Viết unit test và integration test cho các tính năng chính.',
            'Tham gia meeting sprint planning và daily standup mỗi ngày.',
            'Hoàn thành tích hợp API bên thứ 3 (Google Maps, Payment Gateway).',
            'Tham gia code review và đưa ra đề xuất cải tiến kỹ thuật.',
        ];
        $feedbacks         = [
            'Báo cáo đầy đủ, đúng hạn.', 'Cần bổ sung thêm thông tin về kết quả đạt được.',
            'Xuất sắc, tiếp tục phát huy.', null,
        ];
        // milestone_id 9=Nộp đề cương, 10=Nộp báo cáo cuối kỳ
        $milestoneIds = [9, 10];

        for ($internshipId = 4; $internshipId <= 30; $internshipId++) {
            $reportCount    = rand(1, 2);
            $usedMilestones = array_slice($milestoneIds, 0, $reportCount);

            foreach ($usedMilestones as $milestoneId) {
                $status = $statuses[array_rand($statuses)];
                DB::table('internship_reports')->insertOrIgnore([
                    'internship_id'     => $internshipId,
                    'milestone_id'      => $milestoneId,
                    'status'            => $status,
                    'description'       => $descriptions[array_rand($descriptions)],
                    'lecturer_feedback' => $status !== 'PENDING' ? $feedbacks[array_rand($feedbacks)] : null,
                    'file_path'         => "reports/internship/{$internshipId}/milestone{$milestoneId}.pdf",
                    'submission_date'   => '2025-0' . rand(3, 5) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT) . ' 10:00:00',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }
}
