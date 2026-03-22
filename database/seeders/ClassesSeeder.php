<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dữ liệu gốc (class_id 1-6) ---
        $rows = [
            ['lecturer_id' => 1, 'class_name' => '63CNTT1', 'major_id' => 1],
            ['lecturer_id' => 2, 'class_name' => '63CNTT2', 'major_id' => 1],
            ['lecturer_id' => 3, 'class_name' => '63KTPM1', 'major_id' => 2],
            ['lecturer_id' => 4, 'class_name' => '63HTTT1', 'major_id' => 3],
            ['lecturer_id' => 5, 'class_name' => '64CNTT1', 'major_id' => 1],
            ['lecturer_id' => 6, 'class_name' => '64KTPM1', 'major_id' => 2],
        ];

        foreach ($rows as $row) {
            DB::table('classes')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Sinh thêm lớp mới cho các khoá 61, 62, 64, 65 ---
        $extraClasses = [
            ['lecturer_id' => 1, 'class_name' => '64CNTT2', 'major_id' => 1],
            ['lecturer_id' => 2, 'class_name' => '64HTTT1', 'major_id' => 3],
            ['lecturer_id' => 3, 'class_name' => '65CNTT1', 'major_id' => 1],
            ['lecturer_id' => 4, 'class_name' => '65CNTT2', 'major_id' => 1],
            ['lecturer_id' => 5, 'class_name' => '65KTPM1', 'major_id' => 2],
            ['lecturer_id' => 6, 'class_name' => '65HTTT1', 'major_id' => 3],
            ['lecturer_id' => 1, 'class_name' => '62CNTT1', 'major_id' => 1],
            ['lecturer_id' => 2, 'class_name' => '62CNTT2', 'major_id' => 1],
            ['lecturer_id' => 3, 'class_name' => '62KTPM1', 'major_id' => 2],
            ['lecturer_id' => 4, 'class_name' => '62HTTT1', 'major_id' => 3],
        ];

        foreach ($extraClasses as $row) {
            DB::table('classes')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
