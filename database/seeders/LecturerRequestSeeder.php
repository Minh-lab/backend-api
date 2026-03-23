<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LecturerRequestSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dữ liệu gốc ---
        $rows = [
            [
                'lecturer_id'      => 3,
                'updated_topic_id' => null,
                'topic_id'         => null,
                'type'             => 'LEAVE_REQ',
                'status'           => 'APPROVED',
                'title'            => 'Xin nghỉ phép tham dự hội thảo quốc tế',
                'description'      => 'Tôi xin nghỉ phép 5 ngày để tham dự hội thảo ICSE 2025 tại Singapore',
                'file_path'        => 'lecturer-requests/gv003/req_1.pdf',
                'start_date'       => '2025-05-10',
                'end_date'         => '2025-05-15',
                'faculty_feedback' => 'Đồng ý, chúc hội thảo thành công',
            ],
            [
                'lecturer_id'      => 1,
                'updated_topic_id' => null,
                'topic_id'         => null,
                'type'             => 'TOPIC_ADD',
                'status'           => 'APPROVED',
                'title'            => 'Đề xuất thêm đề tài: Hệ thống gợi ý sản phẩm với Collaborative Filtering',
                'description'      => 'Đề tài ứng dụng thuật toán Collaborative Filtering để xây dựng hệ thống gợi ý sản phẩm',
                'file_path'        => null,
                'start_date'       => null,
                'end_date'         => null,
                'faculty_feedback' => 'Chấp nhận, đề tài có tính ứng dụng cao',
            ],
            [
                'lecturer_id'      => 2,
                'updated_topic_id' => null,
                'topic_id'         => 6,
                'type'             => 'TOPIC_EDIT',
                'status'           => 'PENDING',
                'title'            => 'Cập nhật mô tả đề tài nhận diện khuôn mặt',
                'description'      => 'Bổ sung thêm yêu cầu tích hợp với hệ thống điểm danh có sẵn của trường',
                'file_path'        => null,
                'start_date'       => null,
                'end_date'         => null,
                'faculty_feedback' => null,
            ],
            [
                'lecturer_id'      => 5,
                'updated_topic_id' => null,
                'topic_id'         => null,
                'type'             => 'LEAVE_REQ',
                'status'           => 'REJECTED',
                'title'            => 'Xin nghỉ phép cá nhân',
                'description'      => 'Xin nghỉ 3 ngày do việc gia đình',
                'file_path'        => null,
                'start_date'       => '2025-03-20',
                'end_date'         => '2025-03-22',
                'faculty_feedback' => 'Từ chối do trùng với lịch chấm báo cáo sinh viên',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('lecturer_requests')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Sinh thêm 20 yêu cầu LEAVE_REQ ---
        $leaveTitles = [
            'Xin nghỉ phép dự hội thảo công nghệ',
            'Xin nghỉ phép học nâng cao trình độ',
            'Xin nghỉ phép chăm sóc sức khỏe',
            'Xin nghỉ phép tham gia nghiên cứu khoa học',
        ];

        for ($i = 5; $i <= 24; $i++) {
            $lecturerId = rand(1, 6);
            $title      = $leaveTitles[array_rand($leaveTitles)];

            DB::table('lecturer_requests')->insertOrIgnore([
                'lecturer_id'      => $lecturerId,
                'updated_topic_id' => null,
                'topic_id'         => null,
                'type'             => 'LEAVE_REQ',
                'status'           => 'PENDING',
                'title'            => $title,
                'description'      => 'Chi tiết yêu cầu số ' . $i . ' được tạo tự động.',
                'file_path'        => 'lecturer-requests/gv' . str_pad($lecturerId, 3, '0', STR_PAD_LEFT) . '/req_' . $i . '.pdf',
                'start_date'       => '2025-0' . rand(1, 9) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                'end_date'         => '2025-0' . rand(1, 9) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                'faculty_feedback' => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
