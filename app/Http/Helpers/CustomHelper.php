<?php
namespace App\Http\Helpers;

use App\Models\ClientPo;
use App\Models\JournalEntry;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

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
        $vendor_po = PurchaseOrder::select(DB::raw('YEAR(date_po) as year'))
        ->distinct()->get();
        $results = [];

        foreach($vendor_po as $po){
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getYearOptionsClient(){
        $dataset = ClientPo::select(DB::raw("YEAR(date_invoice) as year"))
        ->distinct()->get();

        $results = [];

        foreach($dataset as $po){
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getPaidOptions(){
        return [
            'Paid',
            'Unpaid',
        ];
    }

    public static function formatRupiah($number, $decimal_digits = 0) {
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

    // update or create journal_entry
    public static function updateOrCreateJournalEntry($payload, $reference){
        $journal = JournalEntry::class;
        $journal::updateOrCreate($reference, $payload);
        return $journal::where($reference)->first();
    }

    public static function deleteJournalEntry($reference){
        return JournalEntry::where($reference)->delete();
    }

}
