<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SettingSeeder extends Seeder
{
    public function run()
    {
        DB::table('settings')->insert([
            'logo_dark' => null,
            'logo_light' => null,
            'favicon' => null,
            'currency' => 'IDR',
            'currency_symbol' => 'Rp',
            'position_currency_symbol' => 'prefix',
            'format_decimal_number' => '0,00',
            'po_prefix' => 'PO',
            'spk_prefix' => 'SPK',
            'work_code_prefix' => 'WRK',
            'vouhcer_prefix' => 'VCR',
            'faktur_prefix' => 'FKT',
            'invoice_prefix' => 'INV',
            'name_company' => 'PT Contoh Perusahaan',
            'address' => 'Jl. Raya Semarang No.123',
            'city' => 'Semarang',
            'province' => 'Jawa Tengah',
            'zip_code' => '50123',
            'country' => 'Indonesia',
            'telp' => '0241234567',
            'no_register_company' => '1234567890',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'no_fax' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
