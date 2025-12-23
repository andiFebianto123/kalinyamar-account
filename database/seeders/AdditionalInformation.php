<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdditionalInformation as InformationModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdditionalInformation extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataset = [
            [
                'key' => ['id' => 1],
                'value' => [
                    'name' => 'Jadikan rekening utama'
                ]
            ],
            [
                'key' => ['id' => 2],
                'value' => [
                    'name' => 'Dapat menerima saldo dari rekening lain',
                ]
            ],
            [
                'key' => ['id' => 3],
                'value' => [
                    'name' => 'Dapat memindahkan saldo ke rekening lain',
                ]
            ],
            [
                'key' => ['id' => 4],
                'value' => [
                    'name' => 'Digunakan sebagai pembayaran langsung',
                ]
            ]
        ];

        foreach($dataset as $seed){
            InformationModel::updateOrCreate($seed['key'], $seed['value']);
        }
    }
}
