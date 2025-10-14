<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsolidateIncomeHeader extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $headers = [
            ['name' => 'Pendapatan Usaha'],
            ['name' => 'Beban Usaha'],
            ['name' => 'Laba Usaha (Operating Profit)'],
            ['name' => 'Pendapatan Lain - lain'],
            ['name' => 'Beban Lain - lain'],
            ['name' => 'Laba Sebelum Pajak'],
            ['name' => 'Laba Bersih'],
        ];

        DB::table('consolidate_income_headers')->insert($headers);
    }
}
