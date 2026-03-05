<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['major_name' => 'Công nghệ thông tin'],
            ['major_name' => 'Kỹ thuật phần mềm'],
            ['major_name' => 'Hệ thống thông tin'],
            ['major_name' => 'Khoa học máy tính'],
            ['major_name' => 'An toàn thông tin'],
            ['major_name' => 'Trí tuệ nhân tạo'],
        ];

        foreach ($rows as $row) {
            DB::table('majors')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
