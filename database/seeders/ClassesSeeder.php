<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
