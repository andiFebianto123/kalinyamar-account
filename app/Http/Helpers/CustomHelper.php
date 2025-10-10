<?php
namespace App\Http\Helpers;

use Carbon\Carbon;
use App\Models\Spk;
use App\Models\Asset;
use App\Models\Account;
use App\Models\Project;
use App\Models\Setting;
use App\Models\ClientPo;
use App\Models\CastAccount;
use App\Models\SetupClient;
use App\Models\JournalEntry;
use App\Models\InvoiceClient;
use App\Models\PurchaseOrder;
use App\Models\AccountTransaction;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast;

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

    public static function invoiceEntry($invoice){

        $piutang = Account::where('code', '10201')->first();
        if($piutang){
            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "Piutang invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->total_price,
                'credit' => 0,
            ], [
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
            ]);
        }

        $acct_ppn = Account::where('code', "20301")->first();
        if($acct_ppn){
            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "PPN invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->price_total_include_ppn,
                'credit' => 0,
            ], [
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
            ]);
        }

    }

    public static function invoicePaymentTransaction($transaction, $invoice){
        if($invoice != null){
            $piutang = Account::where('code', '10201')->first();
            if($piutang){
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $piutang->id,
                    'reference_id' => $transaction->id,
                    'reference_type' => AccountTransaction::class,
                    'description' => "Keluar piutang invoice ".$invoice->invoice_number,
                    'date' => Carbon::now(),
                    'debit' => 0,
                    'credit' => $invoice->total_price,
                ], [
                    'account_id' => $piutang->id,
                    'reference_id' => $transaction->id,
                    'reference_type' => AccountTransaction::class,
                ]);
            }
        }
    }

    public static function voucherEntry($voucher){

        $journalDelete = JournalEntry::where('reference_id', $voucher->id)
        ->where('reference_type', Voucher::class)->delete();

        $hutang = Account::where('code', '20101')->first();
        if($hutang){
            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "piutang voucher ".$voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => $voucher->payment_transfer,
                'credit' => 0,
            ], [
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
            ]);
        }
        if($voucher->total > 0){
            $ppn = Account::where('code', '50303')->first();
            if($ppn){
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $ppn->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPN voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->total,
                    'credit' => 0,
                ], [
                    'account_id' => $ppn->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
            }
        }
        if($voucher->discount_pph_23 > 0){
            $pph_23 = Account::where('code', '50306')->first();
            if($pph_23){
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $pph_23->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 23 voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_23,
                    'credit' => 0,
                ], [
                    'account_id' => $pph_23->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
            }
        }
        if($voucher->discount_pph_4 > 0){
            $pph_4 = Account::where('code', '50307')->first();
            if($pph_4){
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $pph_4->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 4 voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_4,
                    'credit' => 0,
                ], [
                    'account_id' => $pph_4->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
            }
        }
        if($voucher->discount_pph_21 > 0){
            $pph_21 = Account::where('code', '50301')->first();
            if($pph_21){
                CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $pph_21->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 21 voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_21,
                    'credit' => 0,
                ], [
                    'account_id' => $pph_21->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
            }
        }
    }

    public static function voucherPayment($voucher){
        $client_po = $voucher->client_po;
        $cast_account = CastAccount::where('id', $voucher->account_source_id)->first();
        // kurangi hutang
        $hutang = Account::where('code', '20101')->first();
        if($hutang){
            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "piutang voucher ".$voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => 0,
                'credit' => $voucher->payment_transfer,
            ], [
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
            ]);
        }

        $transaksi = new AccountTransaction;
        $transaksi->cast_account_id = $voucher->account_source_id;
        $transaksi->reference_type = Voucher::class;
        $transaksi->reference_id = $voucher->id;
        $transaksi->date_transaction = Carbon::now()->format('Y-m-d');
        $transaksi->nominal_transaction = $voucher->payment_transfer;
        $transaksi->total_saldo_before = 0;
        $transaksi->total_saldo_after = 0;
        $transaksi->status = CastAccount::OUT;
        $transaksi->kdp = $client_po?->work_code;
        $transaksi->job_name = $voucher?->job_name;
        $transaksi->save();

        $accountBank = Account::where('id', $cast_account->account_id)->first();
        if($accountBank){
            CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $accountBank->id,
                'reference_id' => $transaksi->id,
                'reference_type' => AccountTransaction::class,
                'description' => "Saldo berkurang ".$voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => 0,
                'credit' => $transaksi->nominal_transaction,
            ], [
                'account_id' => $accountBank->id,
                'reference_id' => $transaksi->id,
                'reference_type' => AccountTransaction::class,
            ]);
        }

    }

}
