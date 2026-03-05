<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouncilMemberSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['council_id' => 1, 'lecturer_id' => 1, 'position' => 'chairman'],
            ['council_id' => 1, 'lecturer_id' => 2, 'position' => 'secretary'],
            ['council_id' => 1, 'lecturer_id' => 4, 'position' => 'reviewer_member'],
            ['council_id' => 2, 'lecturer_id' => 3, 'position' => 'chairman'],
            ['council_id' => 2, 'lecturer_id' => 5, 'position' => 'secretary'],
            ['council_id' => 2, 'lecturer_id' => 6, 'position' => 'reviewer_member'],
        ];

        foreach ($rows as $row) {
            DB::table('council_members')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
