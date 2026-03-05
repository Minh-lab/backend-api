<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProposedCompanySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Công ty TNHH TechStart Việt Nam',
             'address' => '45 Nguyễn Huệ, Hoàn Kiếm, Hà Nội',
             'website' => 'https://techstart.vn', 'tax_code' => '0123456789',
             'contact_email' => 'hr@techstart.vn'],

            ['name' => 'Startup ABC Solutions',
             'address' => '12 Láng Hạ, Đống Đa, Hà Nội',
             'website' => null, 'tax_code' => '0987654321',
             'contact_email' => 'contact@abcsolutions.io'],
        ];

        foreach ($rows as $row) {
            DB::table('proposed_companies')->insertOrIgnore($row);
        }
    }
}
