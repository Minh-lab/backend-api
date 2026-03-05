<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'usercode'  => 'AD001',
                'username'  => 'admin',
                'password'  => Hash::make('Admin@123'),
                'email'     => 'admin@tlu.edu.vn',
                'full_name' => 'Quản trị viên hệ thống',
            ],
            [
                'usercode'  => 'AD002',
                'username'  => 'admin2',
                'password'  => Hash::make('Admin@123'),
                'email'     => 'admin2@tlu.edu.vn',
                'full_name' => 'Quản trị viên 2',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('admins')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
