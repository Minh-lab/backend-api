<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FacultyStaffSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'usercode'     => 'FS001',
                'username'     => 'vanphong01',
                'password'     => Hash::make('Staff@123'),
                'email'        => 'vanphong01@tlu.edu.vn',
                'full_name'    => 'Nguyễn Thị Hương',
                'phone_number' => '0912345601',
            ],
            [
                'usercode'     => 'FS002',
                'username'     => 'vanphong02',
                'password'     => Hash::make('Staff@123'),
                'email'        => 'vanphong02@tlu.edu.vn',
                'full_name'    => 'Trần Thị Mai',
                'phone_number' => '0912345602',
            ],
            [
                'usercode'     => 'FS003',
                'username'     => 'vanphong03',
                'password'     => Hash::make('Staff@123'),
                'email'        => 'vanphong03@tlu.edu.vn',
                'full_name'    => 'Lê Thị Lan',
                'phone_number' => '0912345603',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('faculty_staffs')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
