<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'usercode'     => '0100111000',
                'username'     => 'fpt_software',
                'password'     => Hash::make('Company@123'),
                'email'        => 'tuyendung@fpt.com.vn',
                'name'         => 'Công ty Cổ phần Phần mềm FPT',
                'address'      => 'Tòa nhà FPT, Phố Duy Tân, Cầu Giấy, Hà Nội',
                'website'      => 'https://fptsoftware.com',
                'is_active'    => 1,
                'first_login'  => 0,
                'is_partnered' => 1,
            ],
            [
                'usercode'     => '0100109106',
                'username'     => 'viettel_solutions',
                'password'     => Hash::make('Company@123'),
                'email'        => 'hr@viettel.com.vn',
                'name'         => 'Tổng Công ty Giải pháp Doanh nghiệp Viettel',
                'address'      => 'Số 1 Giang Văn Minh, Ba Đình, Hà Nội',
                'website'      => 'https://viettel.com.vn',
                'is_active'    => 1,
                'first_login'  => 0,
                'is_partnered' => 1,
            ],
            [
                'usercode'     => '0106820579',
                'username'     => 'nashtech',
                'password'     => Hash::make('Company@123'),
                'email'        => 'recruit@nashtech.com',
                'name'         => 'NashTech Vietnam',
                'address'      => 'Tầng 9, Tòa nhà Charmvit, 117 Trần Duy Hưng, Cầu Giấy, Hà Nội',
                'website'      => 'https://nashtechglobal.com',
                'is_active'    => 1,
                'first_login'  => 0,
                'is_partnered' => 1,
            ],
            [
                'usercode'     => '0312345678',
                'username'     => 'tomochain',
                'password'     => Hash::make('Company@123'),
                'email'        => 'hr@tomochain.com',
                'name'         => 'Công ty TNHH TomoChain',
                'address'      => '72 Lê Thánh Tôn, Bến Nghé, Quận 1, TP.HCM',
                'website'      => 'https://tomochain.com',
                'is_active'    => 1,
                'first_login'  => 0,
                'is_partnered' => 0,
            ],
            [
                'usercode'     => '0105678901',
                'username'     => 'sun_asterisk',
                'password'     => Hash::make('Company@123'),
                'email'        => 'recruit@sun-asterisk.com',
                'name'         => 'Sun* Inc. Vietnam',
                'address'      => 'Tầng 7, 2A-4A Tôn Đức Thắng, Đống Đa, Hà Nội',
                'website'      => 'https://sun-asterisk.vn',
                'is_active'    => 1,
                'first_login'  => 0,
                'is_partnered' => 1,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('companies')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
