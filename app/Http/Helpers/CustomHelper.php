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
}
