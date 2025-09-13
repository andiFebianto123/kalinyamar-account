<?php
namespace App\Http\Helpers;

use App\Models\Spk;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Setting;
use App\Models\ClientPo;
use App\Models\CastAccount;
use App\Models\SetupClient;
use App\Models\JournalEntry;
use App\Models\PurchaseOrder;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;

class CustomHelper {

    public static $settings;

    static function init()
    {
        self::$settings = Setting::first();
    }

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

    public static function getYearOptionsSpk(){
        $spk = Spk::select(DB::raw("
            YEAR(date_spk) as year
        "))->distinct()->get();
        foreach($spk as $po){
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getYearOptionsClient(){
        $dataset = ClientPo::select(DB::raw("YEAR(end_date) as year"))
        ->distinct()->get();

        $results = [];

        foreach($dataset as $po){
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getYearOptionsAsset(){
        $dataset = Asset::select(DB::raw("YEAR(year_acquisition) as year"))
        ->distinct()->get();
        $results = [];

        foreach($dataset as $po){
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getOptionProject(){
        $dataset = Project::select(DB::raw("category"))
        ->distinct()->get();
        $results = [];

        foreach($dataset as $po){
            $results[] = $po->category;
        }
        return $results;
    }

    public static function getOptionProjectClient(){
        $dataset = SetupClient::select(DB::raw("id, name"))
        ->distinct()->get();
        $results = [];

        foreach($dataset as $po){
            $results[] = [
                'id' => $po->id,
                'text' => $po->name,
            ];
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

    public static function formatRupiahExcel($number, $decimal_digits = 0) {
        $is_negative = $number < 0;

        $absolute = abs($number);

        $formatted = number_format(
            $absolute,
            $decimal_digits,
            '',
            ''
        );

        return $is_negative ? '-' . $formatted : $formatted;
    }

    public static function formatRupiahWithCurrency($number, $decimal_digits = 0) {
        self::init();
        $is_negative = $number < 0;

        $absolute = abs($number);

        $formatted = number_format(
            $absolute,
            $decimal_digits,
            ',',
            '.'
        );

        $nominal = $is_negative ? '-' . $formatted : $formatted;
        return (self::$settings?->currency_symbol) ? self::$settings->currency_symbol . ' ' . $nominal : 'Rp.'.$nominal;
    }

    // update or create journal_entry
    public static function updateOrCreateJournalEntry($payload, $reference){
        $journal = JournalEntry::class;
        $journal::updateOrCreate($reference, $payload);
        return $journal::where($reference)->first();
    }

    public static function insertJournalEntry($payload){
        return JournalEntry::create($payload);
    }

    public static function deleteJournalEntry($reference){
        return JournalEntry::where($reference)->delete();
    }

    public static function total_balance_cast_account($id, $status){
        if($status == CastAccount::CASH){
            $listCashAccounts = CastAccount::leftJoin('account_transactions', function($q) use($id){
                // $q->on('account_transactions.cast_account_destination_id', '=', 'cast_accounts.id')
                $q->on('account_transactions.cast_account_id', '=', 'cast_accounts.id');
            })
            ->where('cast_accounts.status', CastAccount::CASH)
            ->where('cast_accounts.id', $id)
            ->groupBy('cast_accounts.id')
            ->orderBy('cast_accounts.id', 'ASC')
            ->select(DB::raw("
                cast_accounts.id,
                SUM(IF(account_transactions.status = 'enter', account_transactions.nominal_transaction, 0)) as total_saldo_enter,
                SUM(IF(account_transactions.status = 'out', account_transactions.nominal_transaction, 0)) as total_saldo_out
            "))
            ->get();
            if($listCashAccounts){
                foreach($listCashAccounts as $cash){
                    if($cash->id == $id){
                        return ($cash->total_saldo_enter - $cash->total_saldo_out);
                    }
                }
            }
        }else if($status == CastAccount::LOAN){
            $journal_ = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function($q) use($id){
                $q->where('cast_account_id', $id);
            })->orWhereHasMorph('reference', CastAccount::class, function($q) use($id){
                $q->where('id', $id);
            })
            ->select(DB::raw('SUM(debit) - SUM(credit) as total'))
            ->get();
            if($journal_){
                foreach($journal_ as $journal){
                    return $journal->total;
                }
            }
        }
    }

    public static function total_balance_cast_account_edit($id_cast_account, $id_transaction , $status){
        if($status == CastAccount::CASH){
            $listCashAccounts = CastAccount::leftJoin('account_transactions', function($q) use($id_cast_account){
                // $q->on('account_transactions.cast_account_destination_id', '=', 'cast_accounts.id')
                $q->on('account_transactions.cast_account_id', '=', 'cast_accounts.id');
            })
            ->where('cast_accounts.status', CastAccount::CASH)
            ->where('cast_accounts.id', $id_cast_account)
            ->where('account_transactions.id', '!=', $id_transaction)
            ->groupBy('cast_accounts.id')
            ->orderBy('cast_accounts.id', 'ASC')
            ->select(DB::raw("
                cast_accounts.id,
                SUM(IF(account_transactions.status = 'enter', account_transactions.nominal_transaction, 0)) as total_saldo_enter,
                SUM(IF(account_transactions.status = 'out', account_transactions.nominal_transaction, 0)) as total_saldo_out
            "))
            ->get();
            if($listCashAccounts){
                foreach($listCashAccounts as $cash){
                    if($cash->id == $id_cast_account){
                        return ($cash->total_saldo_enter - $cash->total_saldo_out);
                    }
                }
            }
        }else if($status == CastAccount::LOAN){
            $journal_ = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function($q) use($id){
                $q->where('cast_account_id', $id);
            })->orWhereHasMorph('reference', CastAccount::class, function($q) use($id){
                $q->where('id', $id);
            })
            ->select(DB::raw('SUM(debit) - SUM(credit) as total'))
            ->get();
            if($journal_){
                foreach($journal_ as $journal){
                    return $journal->total;
                }
            }
        }
    }

    public static function getSettings(){
        $setting = Setting::first();
        return $setting;
    }

    public static function clean_html($string) {
        $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        $string = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $string);

        return strip_tags($string);
    }

}
