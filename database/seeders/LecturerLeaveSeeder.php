<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LecturerLeaveSeeder extends Seeder
{
    public function run(): void
    {
        // Chỉ tạo leave cho request đã APPROVED (request_id = 1)
        $rows = [
            [
                'request_id'          => 1,
                'start_date'          => '2025-05-10',
                'end_date'            => '2025-05-15',
                'status'              => 'COMPLETED',
                'delegate_completed'  => 1,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('lecturer_leaves')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
