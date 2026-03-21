<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dữ liệu gốc (giữ nguyên để test tài khoản cụ thể) ---
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

        // --- Faker: Sinh thêm 24 giảng viên ngẫu nhiên ---
        $faker      = \Faker\Factory::create('vi_VN');
        $genders    = ['Nam', 'Nữ'];
        $degrees    = ['Tiến sĩ', 'Thạc sĩ', 'Giáo sư', 'Phó Giáo sư'];
        $departments = [
            'Khoa Công nghệ thông tin',
            'Khoa Điện tử Viễn thông',
            'Khoa Toán - Tin học',
            'Khoa Khoa học Máy tính',
        ];

        for ($i = 7; $i <= 30; $i++) {
            $num      = str_pad($i, 3, '0', STR_PAD_LEFT);
            $usercode = "GV{$num}";
            $username = strtolower($faker->unique()->userName);
            $gender   = $genders[array_rand($genders)];

            DB::table('lecturers')->insertOrIgnore([
                'usercode'     => $usercode,
                'username'     => $username,
                'password'     => Hash::make('Lecturer@123'),
                'email'        => "{$username}@tlu.edu.vn",
                'full_name'    => $faker->name,
                'gender'       => $gender,
                'dob'          => $faker->dateTimeBetween('1965-01-01', '1990-12-31')->format('Y-m-d'),
                'phone_number' => '09' . $faker->numerify('########'),
                'degree'       => $degrees[array_rand($degrees)],
                'department'   => $departments[array_rand($departments)],
                'is_active'    => 1,
                'first_login'  => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
