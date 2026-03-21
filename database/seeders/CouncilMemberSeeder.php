<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouncilMemberSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Council 1 gốc
            ['council_id' => 1, 'lecturer_id' => 1, 'position' => 'chairman'],
            ['council_id' => 1, 'lecturer_id' => 2, 'position' => 'secretary'],
            ['council_id' => 1, 'lecturer_id' => 4, 'position' => 'reviewer_member'],
            // Council 2 gốc
            ['council_id' => 2, 'lecturer_id' => 3, 'position' => 'chairman'],
            ['council_id' => 2, 'lecturer_id' => 5, 'position' => 'secretary'],
            ['council_id' => 2, 'lecturer_id' => 6, 'position' => 'reviewer_member'],
            // Council 3 mới
            ['council_id' => 3, 'lecturer_id' => 2, 'position' => 'chairman'],
            ['council_id' => 3, 'lecturer_id' => 4, 'position' => 'secretary'],
            ['council_id' => 3, 'lecturer_id' => 6, 'position' => 'reviewer_member'],
            // Council 4 mới
            ['council_id' => 4, 'lecturer_id' => 1, 'position' => 'chairman'],
            ['council_id' => 4, 'lecturer_id' => 3, 'position' => 'secretary'],
            ['council_id' => 4, 'lecturer_id' => 5, 'position' => 'reviewer_member'],
            // Council 5 mới
            ['council_id' => 5, 'lecturer_id' => 4, 'position' => 'chairman'],
            ['council_id' => 5, 'lecturer_id' => 6, 'position' => 'secretary'],
            ['council_id' => 5, 'lecturer_id' => 2, 'position' => 'reviewer_member'],
            // Council 6 - HK1 2024-2025
            ['council_id' => 6, 'lecturer_id' => 1, 'position' => 'chairman'],
            ['council_id' => 6, 'lecturer_id' => 5, 'position' => 'secretary'],
            ['council_id' => 6, 'lecturer_id' => 3, 'position' => 'reviewer_member'],
            // Council 7 - HK1 2024-2025
            ['council_id' => 7, 'lecturer_id' => 2, 'position' => 'chairman'],
            ['council_id' => 7, 'lecturer_id' => 6, 'position' => 'secretary'],
            ['council_id' => 7, 'lecturer_id' => 4, 'position' => 'reviewer_member'],
        ];

        foreach ($rows as $row) {
            DB::table('council_members')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
