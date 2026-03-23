<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapstoneSeeder extends Seeder
{
    /**
     * Seed the capstones table với dữ liệu test cho tất cả các trạng thái
     * 
     * Trạng thái theo schema:
     * - INITIALIZED: mới được khởi tạo
     * - LECTURER_APPROVED: đã được giảng viên đồng ý hướng dẫn / văn phòng khoa phân cho giảng viên hướng dẫn
     * - TOPIC_APPROVED: đề tài đăng ký được duyệt
     * - REPORTING: đang trong quá trình nộp báo cáo đồ án
     * - OFFICIAL_SUBMITTED: nộp bản báo cáo cuối cùng
     * - REVIEW_ELIGIBLE: đủ điều kiện đến vòng phản biện
     * - DEFENSE_ELIGIBLE: đủ điều kiện đến vòng bảo vệ đồ án
     * - CANCEL: đã bị hủy
     * - FAILED: đã trượt
     * - COMPLETED: đã hoàn tất
     */
    public function run(): void
    {
        // --- Dữ liệu gốc: các trạng thái khác nhau ---
        $rows = [
            // Sinh viên 1: COMPLETED (hoàn tất)
            [
                'topic_id' => 1, 'student_id' => 1, 'lecturer_id' => 1, 'council_id' => 1, 'semester_id' => 11,
                'status' => 'COMPLETED',
                'instructor_grade' => 8.5,
                'council_grade' => 8.0,
                'defense_order' => 1,
            ],

            // Sinh viên 2: COMPLETED
            [
                'topic_id' => 2, 'student_id' => 2, 'lecturer_id' => 1, 'council_id' => 1, 'semester_id' => 11,
                'status' => 'COMPLETED',
                'instructor_grade' => 9.0,
                'council_grade' => 8.5,
                'defense_order' => 2,
            ],

            // Sinh viên 3: DEFENSE_ELIGIBLE (sẵn sàng bảo vệ)
            [
                'topic_id' => 6, 'student_id' => 3, 'lecturer_id' => 2, 'council_id' => 1, 'semester_id' => 11,
                'status' => 'DEFENSE_ELIGIBLE',
                'instructor_grade' => null,
                'council_grade' => null,
                'defense_order' => null,
            ],

            // Sinh viên 4: REVIEW_ELIGIBLE (sẵn sàng phản biện)
            [
                'topic_id' => 7, 'student_id' => 4, 'lecturer_id' => 3, 'council_id' => null, 'semester_id' => 11,
                'status' => 'REVIEW_ELIGIBLE',
                'instructor_grade' => 7.5,
                'council_grade' => null,
                'defense_order' => null,
            ],

            // Sinh viên 5: REPORTING (đang nộp báo cáo)
            [
                'topic_id' => 4, 'student_id' => 5, 'lecturer_id' => 3, 'council_id' => null, 'semester_id' => 11,
                'status' => 'REPORTING',
                'instructor_grade' => null,
                'council_grade' => null,
                'defense_order' => null,
            ],

            // Sinh viên 6: REPORTING (đang nộp báo cáo)
            [
                'topic_id' => 4, 'student_id' => 6, 'lecturer_id' => 3, 'council_id' => null, 'semester_id' => 11,
                'status' => 'REPORTING',
                'instructor_grade' => null,
                'council_grade' => null,
                'defense_order' => null,
            ],

            // Sinh viên 7: TOPIC_APPROVED (đề tài đã duyệt, chờ hoàn thành)
            [
                'topic_id' => 11, 'student_id' => 7, 'lecturer_id' => 5, 'council_id' => null, 'semester_id' => 11,
                'status' => 'TOPIC_APPROVED',
                'instructor_grade' => null,
                'council_grade' => null,
                'defense_order' => null,
            ],

            // Sinh viên 8: LECTURER_APPROVED (chờ văn phòng khoa phê duyệt)
            [
                'topic_id' => 3, 'student_id' => 8, 'lecturer_id' => 2, 'council_id' => null, 'semester_id' => 11,
                'status' => 'LECTURER_APPROVED',
                'instructor_grade' => null,
                'council_grade' => null,
                'defense_order' => null,
            ],

            // Sinh viên 9: INITIALIZED (mới được khởi tạo)
            [
                'topic_id' => 5, 'student_id' => 9, 'lecturer_id' => null, 'council_id' => null, 'semester_id' => 11,
                'status' => 'INITIALIZED',
                'instructor_grade' => null,
                'council_grade' => null,
                'defense_order' => null,
            ],

            // Sinh viên 10: CANCEL (đã bị hủy)
            [
                'topic_id' => 8, 'student_id' => 10, 'lecturer_id' => 4, 'council_id' => null, 'semester_id' => 11,
                'status' => 'CANCEL',
                'instructor_grade' => null,
                'council_grade' => null,
                'defense_order' => null,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('capstones')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Sinh thêm đồ án cho sinh viên 11 đến 50 ---
        $statuses     = ['INITIALIZED', 'LECTURER_APPROVED', 'TOPIC_APPROVED', 'REPORTING', 'DEFENSE_ELIGIBLE', 'COMPLETED'];
        $topicIds     = range(1, 12);
        $lecturerIds  = range(1, 6);
        $councilIds   = [1, 2, 3, 4];

        for ($studentId = 11; $studentId <= 50; $studentId++) {
            $status      = $statuses[array_rand($statuses)];
            $isCompleted = $status === 'COMPLETED';
            $isDefense   = $status === 'DEFENSE_ELIGIBLE' || $status === 'COMPLETED';
            
            // Chỉ có lecturer và council khi đã phê duyệt
            $hasLecturer = in_array($status, ['LECTURER_APPROVED', 'TOPIC_APPROVED', 'REPORTING', 'DEFENSE_ELIGIBLE', 'COMPLETED']);
            $hasCouncil  = $isDefense;

            DB::table('capstones')->insertOrIgnore([
                'topic_id'         => $topicIds[array_rand($topicIds)],
                'student_id'       => $studentId,
                'lecturer_id'      => $hasLecturer ? $lecturerIds[array_rand($lecturerIds)] : null,
                'council_id'       => $hasCouncil ? $councilIds[array_rand($councilIds)] : null,
                'semester_id'      => 8,
                'status'           => $status,
                'instructor_grade' => $isCompleted ? round(rand(65, 100) / 10, 1) : null,
                'council_grade'    => $isCompleted ? round(rand(65, 100) / 10, 1) : null,
                'defense_order'    => $isCompleted ? ($studentId - 10) : null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
