<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouncilSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['semester_id' => 8, 'name' => 'Hội đồng 01 - CNTT',
             'buildings' => 'Nhà A1', 'rooms' => 'A101',
             'start_date' => '2025-06-05 08:00:00', 'end_date' => '2025-06-05 17:00:00'],

            ['semester_id' => 8, 'name' => 'Hội đồng 02 - KTPM',
             'buildings' => 'Nhà A1', 'rooms' => 'A102',
             'start_date' => '2025-06-06 08:00:00', 'end_date' => '2025-06-06 17:00:00'],
        ];

        foreach ($rows as $row) {
            DB::table('councils')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
