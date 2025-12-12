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
use App\Models\LogPayment;
use App\Models\Voucher;
use Illuminate\Container\Attributes\Log;
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
        $results = [];
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
        $log_payment_invoice = [];
        $log_payment_voucher = [];
        $invoice_id = $invoice->id;
        $voucher = Voucher::where('client_po_id', $invoice->client_po_id)->first();
        if($voucher){
            CustomHelper::rollbackPayment(Voucher::class, $voucher->id, "VOUCHER_PAYMENT_WITH_INVOICE");
            if($voucher->payment_status == 'BAYAR'){
                $account_beban = Account::where('code', "50401")->first();
                $payment_transfer = $voucher->payment_transfer;
                $trans_1 = CustomHelper::insertJournalEntry([
                    'account_id' => $account_beban->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban pekerjaan voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => 0,
                    'credit' => $payment_transfer,
                ]);
                $log_payment_voucher[] = [
                    'id' => $trans_1->id,
                    'account_id' => $account_beban->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban pekerjaan voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => 0,
                    'credit' => $payment_transfer,
                    'type' => JournalEntry::class,
                ];
                // akun beban pokok
                $account_pokok = Account::where('code', $voucher->account_id)->first();

                $trans_2 = CustomHelper::insertJournalEntry([
                    'account_id' => $account_pokok->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => $voucher?->client_po?->work_code,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                ]);
                $log_payment_voucher[] = [
                    'id' => $trans_2->id,
                    'account_id' => $account_pokok->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => $voucher?->client_po?->work_code,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];

                $voucher->save();
                $invoice->status = 'Paid';
            }else{
                $invoice->status = 'Unpaid';
            }
            if(sizeof($log_payment_voucher) > 0){
                $newLogPayment = new LogPayment;
                $newLogPayment->reference_type = Voucher::class;
                $newLogPayment->reference_id = $voucher->id;
                $newLogPayment->name = "VOUCHER_PAYMENT_WITH_INVOICE";
                $newLogPayment->snapshot = json_encode($log_payment_voucher);
                $newLogPayment->save();
            }
        }else{
            $invoice->status = 'Unpaid';
        }
        $invoice->save();

        // end invoice
        $piutang = Account::where('code', '10201')->first();
        if($piutang){
            $trans_3 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "Piutang invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->price_total,
                'credit' => 0,
            ], [
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
            ]);
            $log_payment_invoice[] = [
                'id' => $trans_3->id,
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "Piutang invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->price_total,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }

        $acct_ppn = Account::where('code', "20301")->first();
        if($acct_ppn){
            $price_ppn = $invoice->price_total_exclude_ppn * ($invoice->tax_ppn / 100);
            $trans_4 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "PPN invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $price_ppn,
                'credit' => 0,
            ], [
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
            ]);
            $log_payment_invoice[] = [
                'id' => $trans_4->id,
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "PPN invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $price_ppn,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }
        if(sizeof($log_payment_invoice) > 0){
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = InvoiceClient::class;
            $newLogPayment->reference_id = $invoice_id;
            $newLogPayment->name = "CREATE_INVOICE";
            $newLogPayment->snapshot = json_encode($log_payment_invoice);
            $newLogPayment->save();
        }
    }

    public static function invoiceUpdate($invoice){
        $log_payment_invoice = [];
        $log_payment_voucher = [];
        $invoice_id = $invoice->id;
        $voucher = Voucher::where('client_po_id', $invoice->client_po_id)->whereExists(function ($query) use ($invoice) {
            $query->select(DB::raw(1))
            ->from('payment_vouchers')
            ->whereColumn('payment_vouchers.voucher_id', 'vouchers.id')
            ->whereExists(function ($query) use ($invoice) {
                $query->select(DB::raw(1))
                ->from('payment_voucher_plan')
                ->whereColumn('payment_voucher_plan.payment_voucher_id', 'payment_vouchers.id');
            });
        })->first();
        if($voucher){
            CustomHelper::rollbackPayment(Voucher::class, $voucher->id, "VOUCHER_PAYMENT_WITH_INVOICE");
            if($voucher->payment_status == 'BAYAR'){
                $account_beban = Account::where('code', "50401")->first();
                $payment_transfer = $voucher->payment_transfer;
                $trans_1 = CustomHelper::insertJournalEntry([
                    'account_id' => $account_beban->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban pekerjaan voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => 0,
                    'credit' => $payment_transfer,
                ]);
                $log_payment_voucher[] = [
                    'id' => $trans_1->id,
                    'account_id' => $account_beban->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban pekerjaan voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => 0,
                    'credit' => $payment_transfer,
                    'type' => JournalEntry::class,
                ];
                // akun beban pokok
                $account_pokok = Account::where('code', $voucher->account_id)->first();

                $trans_2 = CustomHelper::insertJournalEntry([
                    'account_id' => $account_pokok->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => $voucher?->client_po?->work_code,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                ]);
                $log_payment_voucher[] = [
                    'id' => $trans_2->id,
                    'account_id' => $account_pokok->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => $voucher?->client_po?->work_code,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];

                $voucher->save();
                $invoice->status = 'Paid';
            }else{
                $invoice->status = 'Unpaid';
            }
            if(sizeof($log_payment_voucher) > 0){
                $newLogPayment = new LogPayment;
                $newLogPayment->reference_type = Voucher::class;
                $newLogPayment->reference_id = $voucher->id;
                $newLogPayment->name = "VOUCHER_PAYMENT_WITH_INVOICE";
                $newLogPayment->snapshot = json_encode($log_payment_voucher);
                $newLogPayment->save();
            }
        }else{
            $invoice->status = 'Unpaid';
        }
        $invoice->save();

        // end invoice
        $piutang = Account::where('code', '10201')->first();
        if($piutang){
            $trans_3 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "Piutang invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->price_total,
                'credit' => 0,
            ], [
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
            ]);
            $log_payment_invoice[] = [
                'id' => $trans_3->id,
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "Piutang invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->price_total,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }

        $acct_ppn = Account::where('code', "20301")->first();
        if($acct_ppn){
            $price_ppn = $invoice->price_total_include_ppn * ($invoice->tax_ppn / 100);
            $trans_4 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "PPN invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $price_ppn,
                'credit' => 0,
            ], [
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
            ]);
            $log_payment_invoice[] = [
                'id' => $trans_4->id,
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "PPN invoice ".$invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $price_ppn,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }
        if(sizeof($log_payment_invoice) > 0){
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = InvoiceClient::class;
            $newLogPayment->reference_id = $invoice_id;
            $newLogPayment->name = "CREATE_INVOICE";
            $newLogPayment->snapshot = json_encode($log_payment_invoice);
            $newLogPayment->save();
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
                    'credit' => $invoice->price_total,
                ], [
                    'account_id' => $piutang->id,
                    'reference_id' => $transaction->id,
                    'reference_type' => AccountTransaction::class,
                ]);
            }
        }
    }

    public static function voucherEntry($voucher){
        $log_payment = [];
        $voucher_id = $voucher->id;
        $journalDelete = JournalEntry::where('reference_id', $voucher->id)
        ->where('reference_type', Voucher::class)->delete();

        $price_unifikasi = $voucher->discount_pph_23 + $voucher->discount_pph_4;
        if($price_unifikasi > 0){
            $account_unifikasi = Account::where('code', '20304')->first();
            $trans_0 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $account_unifikasi->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "tambahan pph unifikasi ".$voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => $price_unifikasi,
                'credit' => 0,
            ], [
                'account_id' => $account_unifikasi->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
            ]);
            $log_payment[] = [
                'id' => $trans_0->id,
                'account_id' => $account_unifikasi->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "tambahan pph unifikasi ".$voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => $price_unifikasi,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }

        $hutang = Account::where('code', '20101')->first();
        if($hutang){
            $trans_1 = CustomHelper::updateOrCreateJournalEntry([
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
            $log_payment[] = [
                'id' => $trans_1->id,
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "piutang voucher ".$voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => $voucher->payment_transfer,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }
        if($voucher->total > 0){
            $ppn = Account::where('code', '50303')->first();
            $total_ppn = $voucher->bill_value * ($voucher->tax_ppn / 100);
            if($ppn){
                $trans_2 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $ppn->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPN voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $total_ppn,
                    'credit' => 0,
                ], [
                    'account_id' => $ppn->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_2->id,
                    'account_id' => $ppn->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPN voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $total_ppn,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if($voucher->discount_pph_23 > 0){
            $pph_23 = Account::where('code', '50306')->first();
            if($pph_23){
                $trans_3 = CustomHelper::updateOrCreateJournalEntry([
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
                $log_payment[] = [
                    'id' => $trans_3->id,
                    'account_id' => $pph_23->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 23 voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_23,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if($voucher->discount_pph_4 > 0){
            $pph_4 = Account::where('code', '50307')->first();
            if($pph_4){
                $trans_4 = CustomHelper::updateOrCreateJournalEntry([
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
                $log_payment[] = [
                    'id' => $trans_4->id,
                    'account_id' => $pph_4->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 4 voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_4,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if($voucher->discount_pph_21 > 0){
            $pph_21 = Account::where('code', '50301')->first();
            if($pph_21){
                $trans_5 = CustomHelper::updateOrCreateJournalEntry([
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
                $log_payment[] = [
                    'id' => $trans_5->id,
                    'account_id' => $pph_21->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 21 voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_21,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if(sizeof($log_payment) > 0){
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = Voucher::class;
            $newLogPayment->reference_id = $voucher_id;
            $newLogPayment->name = "CREATE_VOUCHER";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();
        }
    }

    public static function voucherPayment($voucher){
        $client_po = $voucher->client_po;
        $cast_account = CastAccount::where('id', $voucher->account_source_id)->first();
        // kurangi hutang
        $hutang = Account::where('code', '20101')->first();
        if($hutang){
            CustomHelper::insertJournalEntry([
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "piutang voucher ".$voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => 0,
                'credit' => $voucher->payment_transfer,
            ]);
        }

        $transaksi = new AccountTransaction;
        $transaksi->cast_account_id = $voucher->account_source_id;
        $transaksi->reference_type = Voucher::class;
        $transaksi->reference_id = $voucher->id;
        $transaksi->date_transaction = $voucher->payment_date;
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

    public static function balanceAccount($account_code){
        $code = $account_code;

        $sum_accounts = \App\Models\Account::selectRaw("
            (SUM(journal_entries.debit) - SUM(journal_entries.credit)) as balance
        ")
        ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
        ->where('accounts.code', 'LIKE', "".$code."%")
        ->first();
        return $sum_accounts->balance ?? 0;
    }

    public static function voucherCreate($voucher_id, $invoice_not_exists = false){
        $log_payment = [];
        $voucher = Voucher::where('id', $voucher_id)->first();
        $invoice = InvoiceClient::where('client_po_id', $voucher->client_po_id)->first();
        $client_po = $voucher->client_po;
        $payment_transfer = $voucher->payment_transfer;

        if($client_po->status == 'TANPA PO'){
            // ada po
            $account = Account::where('code', "50222")->first();

            $trans_1 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $account->id,
                'reference_id' => $voucher_id,
                'reference_type' => Voucher::class,
                'description' => "Transaksi tanpa PO ".$client_po->work_code,
                'date' => Carbon::now(),
                'debit' => $payment_transfer,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $account->id,
                'reference_id' => $voucher_id,
                'reference_type' => Voucher::class,
            ]);
            $log_payment[] = [
                'id' => $trans_1->id,
                'account_id' => $account->id,
                'reference_id' => $voucher_id,
                'reference_type' => Voucher::class,
                'description' => "Transaksi tanpa PO ".$client_po->work_code,
                'date' => Carbon::now(),
                'debit' => $payment_transfer,
                'type' => JournalEntry::class,
            ];
        }

        // periksa jenis voucher
        if($voucher->reference_type == "App\Models\PurchaseOrder" || $voucher->reference_type == "App\Models\Spk"){
            if($invoice == null || $invoice_not_exists == true){
                $account = Account::where('code', "50401")->first();
                $trans_2 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_2->id,
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }else{
                $account = Account::where('id', $voucher->account_id)->first();
                $payment_transfer = $voucher->payment_transfer;

                $trans_3 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Transaksi voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_3->id,
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Transaksi voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }else if($voucher->reference_type == "App\Models\ClientPo"){
            if($invoice == null || $invoice_not_exists == true){
                // jika tidak ada invoice di PO
                $account = Account::where('code', "50401")->first();
                $payment_transfer = $voucher->payment_transfer;
                $trans_4 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_4->id,
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }else{
                $account = Account::where('id', $voucher->account_id)->first();
                $payment_transfer = $voucher->payment_transfer;

                $invoice->status = 'Paid';
                $invoice->save();

                $trans_5 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Transaksi voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher_id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_5->id,
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Transaksi voucher ".$voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if(sizeof($log_payment) > 0){
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = Voucher::class;
            $newLogPayment->reference_id = $voucher_id;
            $newLogPayment->name = "CREATE_VOUCHER";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();
        }
    }

    public static function rollbackPayment($reference_type, $reference_id, $name = null){
        $payment = LogPayment::where('reference_type', $reference_type)
        ->where('reference_id', $reference_id);
        if($name){
            $payment = $payment->where('name', $name);
        }
        $payment = $payment->get();
        foreach($payment as $pay){
            $snapshots = json_decode($pay->snapshot);
            foreach($snapshots as $snapshot){
                $snap_id = $snapshot->id;
                if($snapshot->type == JournalEntry::class){
                    $journal = JournalEntry::find($snap_id);
                    if($journal){
                        $journal->delete();
                    }
                }else if($snapshot->type == AccountTransaction::class){
                    AccountTransaction::find($snap_id)->delete();
                }
            }
            // hapus historical transaksi
            $pay->delete();
        }
        return 1;
    }

}
