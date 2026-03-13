<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['role_name' => 'admin'],
            ['role_name' => 'faculty_staff'],       
            ['role_name' => 'lecturer'],
            ['role_name' => 'student'],
            ['role_name' => 'company'], 
        ];

        foreach ($rows as $row) {
            DB::table('roles')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}