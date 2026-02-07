<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Models\CastAccount;
use App\Http\Helpers\CustomHelper;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed from hardcoded list in CustomHelper (using the keys as bank names)
        $hardcodedBanks = CustomHelper::getBanks();
        foreach ($hardcodedBanks as $key => $name) {
            Bank::firstOrCreate(['name' => $key]);
        }

        // 2. Seed from existing data in cast_accounts table
        $existingBanks = CastAccount::whereNotNull('bank_name')
            ->where('bank_name', '!=', '')
            ->distinct()
            ->pluck('bank_name');

        foreach ($existingBanks as $name) {
            Bank::firstOrCreate(['name' => $name]);
        }
    }
}
