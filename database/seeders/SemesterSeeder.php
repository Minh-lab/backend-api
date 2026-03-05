<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['year_id' => 1, 'semester_name' => 'Kỳ 1', 'start_date' => '2022-09-01', 'end_date' => '2023-01-15'],
            ['year_id' => 1, 'semester_name' => 'Kỳ 2', 'start_date' => '2023-02-01', 'end_date' => '2023-06-30'],
            ['year_id' => 1, 'semester_name' => 'Kỳ Hè', 'start_date' => '2023-07-01', 'end_date' => '2023-08-31'],

            ['year_id' => 2, 'semester_name' => 'Kỳ 1', 'start_date' => '2023-09-01', 'end_date' => '2024-01-15'],
            ['year_id' => 2, 'semester_name' => 'Kỳ 2', 'start_date' => '2024-02-01', 'end_date' => '2024-06-30'],
            ['year_id' => 2, 'semester_name' => 'Kỳ Hè', 'start_date' => '2024-07-01', 'end_date' => '2024-08-31'],

            ['year_id' => 3, 'semester_name' => 'Kỳ 1', 'start_date' => '2024-09-01', 'end_date' => '2025-01-15'],
            ['year_id' => 3, 'semester_name' => 'Kỳ 2', 'start_date' => '2025-02-01', 'end_date' => '2025-06-30'],
            ['year_id' => 3, 'semester_name' => 'Kỳ Hè', 'start_date' => '2025-07-01', 'end_date' => '2025-08-31'],

            ['year_id' => 4, 'semester_name' => 'Kỳ 1', 'start_date' => '2025-09-01', 'end_date' => '2026-01-15'],
            ['year_id' => 4, 'semester_name' => 'Kỳ 2', 'start_date' => '2026-02-01', 'end_date' => '2026-06-30'],
        ];

        foreach ($rows as $row) {
            DB::table('semesters')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
