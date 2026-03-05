<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatedTopicSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'expertise_id' => 1,
                'title'        => 'Xây dựng website thương mại điện tử tích hợp AI gợi ý sản phẩm',
                'description'  => 'Phát triển hệ thống e-commerce có tính năng gợi ý sản phẩm dựa trên hành vi người dùng',
                'technologies' => 'Laravel, React, Python, TensorFlow, MySQL',
            ],
            [
                'expertise_id' => 3,
                'title'        => 'Hệ thống nhận diện khuôn mặt điểm danh tích hợp hệ thống trường học',
                'description'  => 'Bổ sung tích hợp với hệ thống điểm danh có sẵn của trường qua API',
                'technologies' => 'Python, OpenCV, TensorFlow, Flask, MySQL, REST API',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('updated_topics')->insertOrIgnore($row);
        }
    }
}
