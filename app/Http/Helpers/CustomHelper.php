<?php
namespace App\Http\Helpers;

class CustomHelper {
    public static function getBanks(){
        return [
            'BCA' => 'Bank BCA',
            'BNI' => 'Bank BNI',
            'BRI' => 'Bank BRI',
            'Mandiri' => 'Bank Mandiri',
            'CIMB Niaga' => 'CIMB Niaga',
            'Danamon' => 'Bank Danamon',
            'Permata' => 'Bank Permata',
            'Maybank' => 'Maybank Indonesia',
        ];
    }

    public static function getYearOptions(){
        return [
            2025,
            2024,
            2023,
            2022,
            2021,
            2020,
            2019,
            2018,
            2017
        ];
    }

    public static function formatRupiah($number, $decimal_digits = 2) {
        $is_negative = $number < 0;

        $absolute = abs($number);

        $formatted = number_format(
            $absolute,
            $decimal_digits,
            ',',
            '.'
        );

        return $is_negative ? '-' . $formatted : $formatted;
    }
}
