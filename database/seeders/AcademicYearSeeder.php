<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['year_name' => '2022-2023', 'start_year' => 2022, 'end_year' => 2023],
            ['year_name' => '2023-2024', 'start_year' => 2023, 'end_year' => 2024],
            ['year_name' => '2024-2025', 'start_year' => 2024, 'end_year' => 2025],
            ['year_name' => '2025-2026', 'start_year' => 2025, 'end_year' => 2026],
        ];

        foreach ($rows as $row) {
            DB::table('academic_years')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
