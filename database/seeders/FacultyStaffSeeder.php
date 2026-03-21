<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FacultyStaffSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dữ liệu gốc ---
        $rows = [
            [
                'usercode'     => 'FS001',
                'username'     => 'vanphong01',
                'password'     => Hash::make('Staff@123'),
                'email'        => 'vanphong01@tlu.edu.vn',
                'full_name'    => 'Nguyễn Thị Hương',
                'gender'       => 'Nữ',
                'dob'          => '1990-03-08',
                'phone_number' => '0912345601',
            ],
            [
                'usercode'     => 'FS002',
                'username'     => 'vanphong02',
                'password'     => Hash::make('Staff@123'),
                'email'        => 'vanphong02@tlu.edu.vn',
                'full_name'    => 'Trần Thị Mai',
                'gender'       => 'Nữ',
                'dob'          => '1992-07-20',
                'phone_number' => '0912345602',
            ],
            [
                'usercode'     => 'FS003',
                'username'     => 'vanphong03',
                'password'     => Hash::make('Staff@123'),
                'email'        => 'vanphong03@tlu.edu.vn',
                'full_name'    => 'Lê Thị Lan',
                'gender'       => 'Nữ',
                'dob'          => '1995-11-30',
                'phone_number' => '0912345603',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('faculty_staffs')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Faker: Sinh thêm 12 nhân viên văn phòng ---
        $faker   = \Faker\Factory::create('vi_VN');
        $genders = ['Nam', 'Nữ'];

        for ($i = 4; $i <= 15; $i++) {
            $num      = str_pad($i, 3, '0', STR_PAD_LEFT);
            $usercode = "FS{$num}";
            $username = "vanphong{$num}";
            $gender   = $genders[array_rand($genders)];

            DB::table('faculty_staffs')->insertOrIgnore([
                'usercode'     => $usercode,
                'username'     => $username,
                'password'     => Hash::make('Staff@123'),
                'email'        => "{$username}@tlu.edu.vn",
                'full_name'    => $faker->name,
                'gender'       => $gender,
                'dob'          => $faker->dateTimeBetween('1985-01-01', '2000-12-31')->format('Y-m-d'),
                'phone_number' => '09' . $faker->numerify('########'),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}