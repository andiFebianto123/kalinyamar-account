<?php
namespace App\Http\Controllers\Operation;

use App\Http\Helpers\CustomHelper;

trait FormaterExport {

    function priceFormatExport($type_url, $price){
        if($type_url == 'pdf'){
            return CustomHelper::formatRupiahWithCurrency($price);
        }else if($type_url == 'excel'){
            return str_replace('.00', '', $price);
        }
        return str_replace('.00', '', $price);
    }
}