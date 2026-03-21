<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouncilSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Hội đồng gốc
            ['semester_id' => 8, 'name' => 'Hội đồng 01 - CNTT',
             'buildings' => 'Nhà A1', 'rooms' => 'A101',
             'start_date' => '2025-06-05 08:00:00', 'end_date' => '2025-06-05 17:00:00'],

            ['semester_id' => 8, 'name' => 'Hội đồng 02 - KTPM',
             'buildings' => 'Nhà A1', 'rooms' => 'A102',
             'start_date' => '2025-06-06 08:00:00', 'end_date' => '2025-06-06 17:00:00'],

            // Hội đồng bổ sung HK2 năm 2024-2025 (semester_id=8)
            ['semester_id' => 8, 'name' => 'Hội đồng 03 - HTTT',
             'buildings' => 'Nhà A2', 'rooms' => 'A201',
             'start_date' => '2025-06-07 08:00:00', 'end_date' => '2025-06-07 17:00:00'],

            ['semester_id' => 8, 'name' => 'Hội đồng 04 - CNTT (Chiều)',
             'buildings' => 'Nhà A2', 'rooms' => 'A202',
             'start_date' => '2025-06-07 13:00:00', 'end_date' => '2025-06-07 17:00:00'],

            ['semester_id' => 8, 'name' => 'Hội đồng 05 - Dự bị',
             'buildings' => 'Nhà B1', 'rooms' => 'B101',
             'start_date' => '2025-06-10 08:00:00', 'end_date' => '2025-06-10 12:00:00'],

            // Hội đồng cho HK1 2024-2025 (semester_id=7)
            ['semester_id' => 7, 'name' => 'Hội đồng 01 HK1 2024-2025 - CNTT',
             'buildings' => 'Nhà A1', 'rooms' => 'A101',
             'start_date' => '2025-01-10 08:00:00', 'end_date' => '2025-01-10 17:00:00'],

            ['semester_id' => 7, 'name' => 'Hội đồng 02 HK1 2024-2025 - KTPM',
             'buildings' => 'Nhà A1', 'rooms' => 'A103',
             'start_date' => '2025-01-11 08:00:00', 'end_date' => '2025-01-11 17:00:00'],
        ];

        foreach ($rows as $row) {
            DB::table('councils')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
