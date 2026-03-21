<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dữ liệu gốc (giữ nguyên để test tài khoản cụ thể) ---
        $rows = [
            ['usercode' => '6351012001', 'username' => 'sv6351012001', 'password' => Hash::make('Student@123'),
             'email' => '6351012001@sv.tlu.edu.vn', 'full_name' => 'Nguyễn Văn Anh',
             'gender' => 'Nam', 'dob' => '2002-05-10', 'phone_number' => '0981234501',
             'class_id' => 1, 'gpa' => 3.20, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6351012002', 'username' => 'sv6351012002', 'password' => Hash::make('Student@123'),
             'email' => '6351012002@sv.tlu.edu.vn', 'full_name' => 'Trần Thị Bảo',
             'gender' => 'Nữ', 'dob' => '2002-08-15', 'phone_number' => '0981234502',
             'class_id' => 1, 'gpa' => 3.50, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6351012003', 'username' => 'sv6351012003', 'password' => Hash::make('Student@123'),
             'email' => '6351012003@sv.tlu.edu.vn', 'full_name' => 'Lê Minh Châu',
             'gender' => 'Nam', 'dob' => '2002-03-22', 'phone_number' => '0981234503',
             'class_id' => 1, 'gpa' => 2.95, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6351022001', 'username' => 'sv6351022001', 'password' => Hash::make('Student@123'),
             'email' => '6351022001@sv.tlu.edu.vn', 'full_name' => 'Phạm Văn Đức',
             'gender' => 'Nam', 'dob' => '2002-11-05', 'phone_number' => '0981234504',
             'class_id' => 2, 'gpa' => 3.10, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6351022002', 'username' => 'sv6351022002', 'password' => Hash::make('Student@123'),
             'email' => '6351022002@sv.tlu.edu.vn', 'full_name' => 'Hoàng Thị Nga',
             'gender' => 'Nữ', 'dob' => '2002-07-18', 'phone_number' => '0981234505',
             'class_id' => 2, 'gpa' => 3.75, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6352012001', 'username' => 'sv6352012001', 'password' => Hash::make('Student@123'),
             'email' => '6352012001@sv.tlu.edu.vn', 'full_name' => 'Vũ Thành Long',
             'gender' => 'Nam', 'dob' => '2002-01-28', 'phone_number' => '0981234506',
             'class_id' => 3, 'gpa' => 3.40, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6352012002', 'username' => 'sv6352012002', 'password' => Hash::make('Student@123'),
             'email' => '6352012002@sv.tlu.edu.vn', 'full_name' => 'Đỗ Thị Minh',
             'gender' => 'Nữ', 'dob' => '2002-09-14', 'phone_number' => '0981234507',
             'class_id' => 3, 'gpa' => 3.60, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6353012001', 'username' => 'sv6353012001', 'password' => Hash::make('Student@123'),
             'email' => '6353012001@sv.tlu.edu.vn', 'full_name' => 'Ngô Văn Nhân',
             'gender' => 'Nam', 'dob' => '2002-06-20', 'phone_number' => '0981234508',
             'class_id' => 4, 'gpa' => 2.80, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6353012002', 'username' => 'sv6353012002', 'password' => Hash::make('Student@123'),
             'email' => '6353012002@sv.tlu.edu.vn', 'full_name' => 'Bùi Thị Oanh',
             'gender' => 'Nữ', 'dob' => '2002-12-03', 'phone_number' => '0981234509',
             'class_id' => 4, 'gpa' => 3.30, 'is_active' => 1, 'first_login' => 0],

            ['usercode' => '6451012001', 'username' => 'sv6451012001', 'password' => Hash::make('Student@123'),
             'email' => '6451012001@sv.tlu.edu.vn', 'full_name' => 'Đinh Văn Phong',
             'gender' => 'Nam', 'dob' => '2003-04-07', 'phone_number' => '0981234510',
             'class_id' => 5, 'gpa' => 3.15, 'is_active' => 1, 'first_login' => 0],
        ];

        foreach ($rows as $row) {
            DB::table('students')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // --- Faker: Sinh thêm 90 sinh viên ngẫu nhiên ---
        $faker   = \Faker\Factory::create('vi_VN');
        $genders = ['Nam', 'Nữ'];

        for ($i = 11; $i <= 100; $i++) {
            $num      = str_pad($i, 3, '0', STR_PAD_LEFT);
            $usercode = "6351F{$num}";
            $username = "sv{$usercode}";
            $classId  = rand(1, 5);
            $gender   = $genders[array_rand($genders)];
            $gpa      = round(rand(200, 400) / 100, 2);

            DB::table('students')->insertOrIgnore([
                'usercode'     => $usercode,
                'username'     => $username,
                'password'     => Hash::make('Student@123'),
                'email'        => "{$usercode}@sv.tlu.edu.vn",
                'full_name'    => $faker->name,
                'gender'       => $gender,
                'dob'          => $faker->dateTimeBetween('2001-01-01', '2004-12-31')->format('Y-m-d'),
                'phone_number' => '09' . $faker->numerify('########'),
                'class_id'     => $classId,
                'gpa'          => $gpa,
                'is_active'    => 1,
                'first_login'  => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
