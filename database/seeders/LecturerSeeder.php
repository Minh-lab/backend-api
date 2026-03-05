<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'usercode'     => 'GV001', 'username' => 'nguyenvana',
                'password'     => Hash::make('Lecturer@123'),
                'email'        => 'nguyenvana@tlu.edu.vn',
                'full_name'    => 'Nguyễn Văn An',
                'gender'       => 'Nam', 'dob' => '1975-03-15',
                'phone_number' => '0901234501', 'degree' => 'Tiến sĩ',
                'department'   => 'Khoa Công nghệ thông tin',
                'is_active'    => 1, 'first_login' => 0,
            ],
            [
                'usercode'     => 'GV002', 'username' => 'tranthib',
                'password'     => Hash::make('Lecturer@123'),
                'email'        => 'tranthib@tlu.edu.vn',
                'full_name'    => 'Trần Thị Bình',
                'gender'       => 'Nữ', 'dob' => '1980-07-22',
                'phone_number' => '0901234502', 'degree' => 'Tiến sĩ',
                'department'   => 'Khoa Công nghệ thông tin',
                'is_active'    => 1, 'first_login' => 0,
            ],
            [
                'usercode'     => 'GV003', 'username' => 'levanc',
                'password'     => Hash::make('Lecturer@123'),
                'email'        => 'levanc@tlu.edu.vn',
                'full_name'    => 'Lê Văn Cường',
                'gender'       => 'Nam', 'dob' => '1978-11-10',
                'phone_number' => '0901234503', 'degree' => 'Thạc sĩ',
                'department'   => 'Khoa Công nghệ thông tin',
                'is_active'    => 1, 'first_login' => 0,
            ],
            [
                'usercode'     => 'GV004', 'username' => 'phamthid',
                'password'     => Hash::make('Lecturer@123'),
                'email'        => 'phamthid@tlu.edu.vn',
                'full_name'    => 'Phạm Thị Dung',
                'gender'       => 'Nữ', 'dob' => '1982-05-18',
                'phone_number' => '0901234504', 'degree' => 'Tiến sĩ',
                'department'   => 'Khoa Công nghệ thông tin',
                'is_active'    => 1, 'first_login' => 0,
            ],
            [
                'usercode'     => 'GV005', 'username' => 'hoangvane',
                'password'     => Hash::make('Lecturer@123'),
                'email'        => 'hoangvane@tlu.edu.vn',
                'full_name'    => 'Hoàng Văn Em',
                'gender'       => 'Nam', 'dob' => '1976-09-25',
                'phone_number' => '0901234505', 'degree' => 'Thạc sĩ',
                'department'   => 'Khoa Công nghệ thông tin',
                'is_active'    => 1, 'first_login' => 0,
            ],
            [
                'usercode'     => 'GV006', 'username' => 'vuthif',
                'password'     => Hash::make('Lecturer@123'),
                'email'        => 'vuthif@tlu.edu.vn',
                'full_name'    => 'Vũ Thị Phương',
                'gender'       => 'Nữ', 'dob' => '1985-01-30',
                'phone_number' => '0901234506', 'degree' => 'Thạc sĩ',
                'department'   => 'Khoa Công nghệ thông tin',
                'is_active'    => 1, 'first_login' => 0,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('lecturers')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
