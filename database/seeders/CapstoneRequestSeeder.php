<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapstoneRequestSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['proposed_topic_id' => null, 'capstone_id' => 1, 'lecturer_id' => 1, 'topic_id' => 1,
             'type' => 'LECTURER_REG', 'status' => 'APPROVED',
             'student_message' => 'Em xin đăng ký thầy An hướng dẫn đề tài này ạ',
             'lecturer_feedback' => 'Đồng ý hướng dẫn', 'file_path' => null],

            ['proposed_topic_id' => null, 'capstone_id' => 1, 'lecturer_id' => null, 'topic_id' => 1,
             'type' => 'TOPIC_BANK', 'status' => 'APPROVED',
             'student_message' => 'Em xin đăng ký đề tài quản lý bán hàng',
             'lecturer_feedback' => 'Chấp nhận', 'file_path' => null],

            ['proposed_topic_id' => 1, 'capstone_id' => 4, 'lecturer_id' => 3, 'topic_id' => null,
             'type' => 'TOPIC_PROP', 'status' => 'APPROVED',
             'student_message' => 'Em muốn đề xuất đề tài website quản lý câu lạc bộ sinh viên',
             'lecturer_feedback' => 'Đồng ý, đề tài thú vị', 'file_path' => null],

            ['proposed_topic_id' => null, 'capstone_id' => 5, 'lecturer_id' => 5, 'topic_id' => 11,
             'type' => 'LECTURER_REG', 'status' => 'APPROVED',
             'student_message' => 'Em xin đăng ký thầy Em hướng dẫn',
             'lecturer_feedback' => 'Đồng ý', 'file_path' => null],
        ];

        foreach ($rows as $row) {
            DB::table('capstone_requests')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
