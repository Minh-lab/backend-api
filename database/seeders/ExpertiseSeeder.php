<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpertiseSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Lập trình Web',                      'description' => 'Phát triển ứng dụng web Frontend và Backend'],
            ['name' => 'Lập trình Di động',                  'description' => 'Phát triển ứng dụng Android, iOS, Cross-platform'],
            ['name' => 'Trí tuệ nhân tạo & Machine Learning','description' => 'AI, ML, Deep Learning ứng dụng thực tế'],
            ['name' => 'Khoa học dữ liệu',                   'description' => 'Data Mining, Big Data, Business Intelligence'],
            ['name' => 'An toàn thông tin',                  'description' => 'Bảo mật hệ thống, mạng, mã hóa'],
            ['name' => 'Điện toán đám mây',                  'description' => 'Hệ thống Cloud AWS, Azure, GCP'],
            ['name' => 'IoT & Hệ thống nhúng',               'description' => 'IoT, vi điều khiển, hệ thống nhúng'],
            ['name' => 'Phát triển phần mềm',                'description' => 'Quy trình phát triển PM, kiểm thử, QA'],
        ];

        foreach ($rows as $row) {
            DB::table('expertises')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
