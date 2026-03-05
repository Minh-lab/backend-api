<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProposedTopicSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'expertise_id'         => 1,
                'proposed_title'       => 'Website quản lý câu lạc bộ sinh viên',
                'proposed_description' => 'Hệ thống quản lý hoạt động, thành viên và sự kiện của câu lạc bộ sinh viên trong trường đại học',
                'technologies'         => 'React, Laravel, MySQL',
            ],
            [
                'expertise_id'         => 2,
                'proposed_title'       => 'App theo dõi sức khỏe và nhắc uống thuốc',
                'proposed_description' => 'Ứng dụng di động giúp theo dõi sức khỏe hàng ngày và nhắc nhở lịch uống thuốc cho người dùng',
                'technologies'         => 'Flutter, Firebase, SQLite',
            ],
            [
                'expertise_id'         => 3,
                'proposed_title'       => 'Hệ thống phân loại rác thải tự động bằng Computer Vision',
                'proposed_description' => 'Ứng dụng nhận diện và phân loại rác thải qua camera sử dụng Deep Learning',
                'technologies'         => 'Python, YOLO, TensorFlow, FastAPI',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('proposed_topics')->insertOrIgnore($row);
        }
    }
}
