<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LecturerRequestSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'lecturer_id'      => 3,
                'updated_topic_id' => null,
                'topic_id'         => null,
                'type'             => 'LEAVE_REQ',
                'status'           => 'APPROVED',
                'title'            => 'Xin nghỉ phép tham dự hội thảo quốc tế',
                'description'      => 'Tôi xin nghỉ phép 5 ngày để tham dự hội thảo ICSE 2025 tại Singapore',
                'file_path'        => 'requests/leave/gv003_icse2025.pdf',
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
    }
}
