<?php

namespace App\Http\Helpers;

use Carbon\Carbon;
use App\Models\Spk;
use App\Models\Asset;
use App\Models\Account;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Voucher;
use App\Models\ClientPo;
use App\Models\LogPayment;
use App\Models\CastAccount;
use App\Models\SetupClient;
use App\Models\JournalEntry;
use App\Models\InvoiceClient;
use App\Models\PurchaseOrder;
use App\Models\ProjectProfitLost;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;

class CustomHelper
{

    public static $settings;

    static function init()
    {
        self::$settings = Setting::first();
    }

    public static function getBanks()
    {
        return \App\Models\Bank::orderBy('name', 'ASC')->pluck('name', 'name')->toArray();
    }

    public static function getYearOptions()
    {
        $vendor_po = PurchaseOrder::select(DB::raw('YEAR(date_po) as year'))
            ->distinct()->get();
        $results = [];

        foreach ($vendor_po as $po) {
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getYearOptionsSpk()
    {
        $spk = Spk::select(DB::raw("
            YEAR(date_spk) as year
        "))->distinct()->get();
        $results = [];
        foreach ($spk as $po) {
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getYearOptionsClient()
    {
        $dataset = ClientPo::select(DB::raw("YEAR(end_date) as year"))
            ->distinct()->get();

        $results = [];

        foreach ($dataset as $po) {
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getYearOptionsAsset()
    {
        $dataset = Asset::select(DB::raw("YEAR(year_acquisition) as year"))
            ->distinct()->get();
        $results = [];

        foreach ($dataset as $po) {
            $results[] = $po->year;
        }
        return $results;
    }

    public static function getOptionProject()
    {
        $dataset = Project::select(DB::raw("category"))
            ->distinct()->get();
        $results = [];

        foreach ($dataset as $po) {
            $results[] = $po->category;
        }
        return $results;
    }

    public static function getOptionProjectClient()
    {
        $dataset = SetupClient::select(DB::raw("id, name"))
            ->distinct()->get();
        $results = [];

        foreach ($dataset as $po) {
            $results[] = [
                'id' => $po->id,
                'text' => $po->name,
            ];
        }
        return $results;
    }

    public static function getPaidOptions()
    {
        return [
            'Paid',
            'Unpaid',
        ];
    }

    public static function formatRupiah($number, $decimal_digits = 0)
    {
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

    public static function formatRupiahExcel($number, $decimal_digits = 0)
    {
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

    public static function formatRupiahWithCurrency($number, $decimal_digits = 0)
    {
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
        return (self::$settings?->currency_symbol) ? self::$settings->currency_symbol . ' ' . $nominal : 'Rp.' . $nominal;
    }

    // update or create journal_entry
    public static function updateOrCreateJournalEntry($payload, $reference)
    {
        $journal = JournalEntry::class;
        $journal::updateOrCreate($reference, $payload);
        return $journal::where($reference)->first();
    }

    public static function insertJournalEntry($payload)
    {
        return JournalEntry::create($payload);
    }

    public static function deleteJournalEntry($reference)
    {
        return JournalEntry::where($reference)->delete();
    }

    public static function total_balance_cast_account($id, $status)
    {
        if ($status == CastAccount::CASH) {
            $listCashAccounts = CastAccount::leftJoin('account_transactions', function ($q) use ($id) {
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
            if ($listCashAccounts) {
                foreach ($listCashAccounts as $cash) {
                    if ($cash->id == $id) {
                        return ($cash->total_saldo_enter - $cash->total_saldo_out);
                    }
                }
            }
        } else if ($status == CastAccount::LOAN) {
            $journal_ = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function ($q) use ($id) {
                $q->where('cast_account_id', $id);
            })->orWhereHasMorph('reference', CastAccount::class, function ($q) use ($id) {
                $q->where('id', $id);
            })
                ->select(DB::raw('SUM(debit) - SUM(credit) as total'))
                ->get();
            if ($journal_) {
                foreach ($journal_ as $journal) {
                    return $journal->total;
                }
            }
        }
    }

    public static function total_balance_cast_account_edit($id_cast_account, $id_transaction, $status)
    {
        if ($status == CastAccount::CASH) {
            $listCashAccounts = CastAccount::leftJoin('account_transactions', function ($q) use ($id_cast_account) {
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
            if ($listCashAccounts) {
                foreach ($listCashAccounts as $cash) {
                    if ($cash->id == $id_cast_account) {
                        return ($cash->total_saldo_enter - $cash->total_saldo_out);
                    }
                }
            }
        } else if ($status == CastAccount::LOAN) {
            $journal_ = JournalEntry::whereHasMorph('reference', AccountTransaction::class, function ($q) use ($id_cast_account) {
                $q->where('cast_account_id', $id_cast_account);
            })->orWhereHasMorph('reference', CastAccount::class, function ($q) use ($id_cast_account) {
                $q->where('id', $id_cast_account);
            })
                ->select(DB::raw('SUM(debit) - SUM(credit) as total'))
                ->get();
            if ($journal_) {
                foreach ($journal_ as $journal) {
                    return $journal->total;
                }
            }
        }
    }

    public static function getSettings()
    {
        $setting = Setting::first();
        return $setting;
    }

    public static function clean_html($string)
    {
        $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        $string = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $string);

        return strip_tags($string);
    }

    public static function invoiceEntry($invoice)
    {
        $log_payment = [];
        $invoice_id = $invoice->id;
        // ambil voucher yang belum dibayar

        $log_payment = [];
        // end invoice
        $piutang = Account::where('code', '10201')->first();
        if ($piutang) {
            $trans_3 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "Piutang invoice " . $invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->price_total_exclude_ppn,
                'credit' => 0,
            ], [
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
            ]);
            $log_payment[] = [
                'id' => $trans_3->id,
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "Piutang invoice " . $invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->price_total_exclude_ppn,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }

        $acct_ppn = Account::where('code', "20301")->first();
        if ($acct_ppn) {
            $price_ppn = $invoice->price_total_exclude_ppn * ($invoice->tax_ppn / 100);
            $trans_4 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "PPN invoice " . $invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $price_ppn,
                'credit' => 0,
            ], [
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
            ]);
            $log_payment[] = [
                'id' => $trans_4->id,
                'account_id' => $acct_ppn->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "PPN invoice " . $invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $price_ppn,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }
        if (sizeof($log_payment) > 0) {
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = InvoiceClient::class;
            $newLogPayment->reference_id = $invoice_id;
            $newLogPayment->name = "CREATE_INVOICE";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();
        }
    }

    public static function invoiceUpdate(InvoiceClient $invoice, $old_client_po_id)
    {

        CustomHelper::rollbackPayment(InvoiceClient::class, $invoice->id, "CREATE_INVOICE");

        // invoice client po lama

        if ($old_client_po_id != $invoice->client_po_id) {
            // jika terdapat perbedaan client lama dengan baru

            // cek apakah ada invoice yang menggunakan client po lama
            $exists_other_invoice_old_client_po = InvoiceClient::where('client_po_id', $old_client_po_id)
                ->where('id', '!=', $invoice->id)
                ->first();

            if ($exists_other_invoice_old_client_po == null) {
                // kondisi voucher jadi hutang karena kosong karena invoice tidak exists
                $voucher = Voucher::where('client_po_id', $old_client_po_id)
                    ->get();
                foreach ($voucher as $voucher) {
                    // hapus semua transaksi voucher
                    CustomHelper::rollbackPayment(Voucher::class, $voucher->id);
                    // lalu insert kembali menjadi voucher tanpa invoice
                    CustomHelper::voucherEntry($voucher);
                    CustomHelper::voucherCreate($voucher, true);

                    $voucher->payment_status = 'BELUM BAYAR';
                    $voucher->save();
                }
                // pindahkan biaya pending voucher pada invoice baru
                CustomHelper::invoiceMakeVoucherMoveAccount($invoice);
            }
        }

        CustomHelper::invoiceEntry($invoice);
    }

    public static function invoiceDelete(InvoiceClient $invoice)
    {
        $another_invoice = InvoiceClient::where('client_po_id', $invoice->client_po_id)
            ->where('id', '!=', $invoice->id)->first();

        if ($another_invoice == null) {
            $voucher = Voucher::where('client_po_id', $invoice->client_po_id)->get();
            foreach ($voucher as $voucher) {
                CustomHelper::rollbackPayment(Voucher::class, $voucher->id);
                CustomHelper::voucherEntry($voucher);
                // paksa agar journal voucher menjadi tanpa invoice
                CustomHelper::voucherCreate($voucher, true);

                $voucher->payment_status = 'BELUM BAYAR';
                $voucher->save();
            }
        }

        CustomHelper::rollbackPayment(InvoiceClient::class, $invoice->id, "CREATE_INVOICE");
    }

    public static function invoicePaymentTransaction($transaction, $invoice, $log_payment, $status = 'out')
    {
        if ($invoice != null) {
            $piutang = Account::where('code', '10201')->first();
            if ($piutang) {
                // Tentukan debit/credit berdasarkan status transaksi
                // Status OUT (Keluar) = Pembayaran invoice = Piutang berkurang (CREDIT)
                // Status ENTER (Masuk) = Pengembalian/Refund = Piutang bertambah (DEBIT)
                $is_out = ($status == AccountTransaction::OUT || $status == 'out');

                $description = $is_out
                    ? "Keluar piutang invoice " . $invoice->invoice_number
                    : "Masuk piutang invoice " . $invoice->invoice_number;

                $piutang_trans = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $piutang->id,
                    'reference_id' => $transaction->id,
                    'reference_type' => AccountTransaction::class,
                    'description' => $description,
                    'date' => Carbon::now(),
                    'debit' => $is_out ? 0 : $invoice->price_total_exclude_ppn,      // DEBIT jika ENTER
                    'credit' => $is_out ? $invoice->price_total_exclude_ppn : 0,     // CREDIT jika OUT
                ], [
                    'account_id' => $piutang->id,
                    'reference_id' => $transaction->id,
                    'reference_type' => AccountTransaction::class,
                ]);
                $log_payment[] = [
                    'id' => $piutang_trans->id,
                    'reference_id' => $transaction->id,
                    'reference_type' => AccountTransaction::class,
                    'type' => JournalEntry::class,
                ];
            }
        }
    }

    public static function voucherEntry($voucher)
    {
        $log_payment = [];
        $voucher_id = $voucher->id;

        $price_unifikasi = $voucher->discount_pph_23 + $voucher->discount_pph_4;
        if ($price_unifikasi > 0) {
            $account_unifikasi = Account::where('code', '20304')->first();
            $trans_0 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $account_unifikasi->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "tambahan pph unifikasi " . $voucher->no_voucher,
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
                'description' => "tambahan pph unifikasi " . $voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => $price_unifikasi,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }

        $hutang = Account::where('code', '20101')->first();
        if ($hutang) {
            $trans_1 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "piutang voucher " . $voucher->no_voucher,
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
                'description' => "piutang voucher " . $voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => $voucher->payment_transfer,
                'credit' => 0,
                'type' => JournalEntry::class,
            ];
        }
        if ($voucher->total > 0) {
            $ppn = Account::where('code', '50303')->first();
            $total_ppn = $voucher->bill_value * ($voucher->tax_ppn / 100);
            if ($ppn) {
                $trans_2 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $ppn->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPN voucher " . $voucher->no_voucher,
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
                    'description' => "PPN voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $total_ppn,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if ($voucher->discount_pph_23 > 0) {
            $pph_23 = Account::where('code', '50306')->first();
            if ($pph_23) {
                $trans_3 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $pph_23->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 23 voucher " . $voucher->no_voucher,
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
                    'description' => "PPH 23 voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_23,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if ($voucher->discount_pph_4 > 0) {
            $pph_4 = Account::where('code', '50307')->first();
            if ($pph_4) {
                $trans_4 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $pph_4->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 4 voucher " . $voucher->no_voucher,
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
                    'description' => "PPH 4 voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_4,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if ($voucher->discount_pph_21 > 0) {
            $pph_21 = Account::where('code', '50301')->first();
            if ($pph_21) {
                $trans_5 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $pph_21->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "PPH 21 voucher " . $voucher->no_voucher,
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
                    'description' => "PPH 21 voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $voucher->discount_pph_21,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }
        if (sizeof($log_payment) > 0) {
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = Voucher::class;
            $newLogPayment->reference_id = $voucher_id;
            $newLogPayment->name = "CREATE_VOUCHER";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();
        }
    }

    public static function invoiceMakeVoucherMoveAccount(InvoiceClient $invoice)
    {
        $before_invoice_id = InvoiceClient::where('id', '!=', $invoice->id)
            ->where('client_po_id', $invoice->client_po_id)
            ->first();

        if ($before_invoice_id == null) {
            // jika invoice pertama
            $voucher = Voucher::where('client_po_id', $invoice->client_po_id)
                ->where('payment_status', 'BELUM BAYAR')
                ->get();
            if ($voucher->count() > 0) {
                foreach ($voucher as $v) {
                    $log_payment_voucher = [];
                    $account_beban = Account::where('code', "50401")->first();
                    $payment_transfer = $v->payment_transfer;
                    $trans_1 = CustomHelper::insertJournalEntry([
                        'account_id' => $account_beban->id,
                        'reference_id' => $v->id,
                        'reference_type' => Voucher::class,
                        'description' => "Beban pekerjaan voucher " . $v->no_voucher,
                        'date' => Carbon::now(),
                        'debit' => 0,
                        'credit' => $payment_transfer,
                    ]);
                    $log_payment_voucher[] = [
                        'id' => $trans_1->id,
                        'account_id' => $account_beban->id,
                        'reference_id' => $v->id,
                        'reference_type' => Voucher::class,
                        'description' => "Beban pekerjaan voucher " . $v->no_voucher,
                        'date' => Carbon::now(),
                        'debit' => 0,
                        'credit' => $payment_transfer,
                        'type' => JournalEntry::class,
                    ];

                    $account_pokok = Account::where('id', $v->account_id)->first();
                    $trans_2 = CustomHelper::insertJournalEntry([
                        'account_id' => $account_pokok->id,
                        'reference_id' => $v->id,
                        'reference_type' => Voucher::class,
                        'description' => $v?->client_po?->work_code,
                        'date' => Carbon::now(),
                        'debit' => $payment_transfer,
                        'credit' => 0,
                    ]);
                    $log_payment_voucher[] = [
                        'id' => $trans_2->id,
                        'account_id' => $account_pokok->id,
                        'reference_id' => $v->id,
                        'reference_type' => Voucher::class,
                        'description' => $v?->client_po?->work_code,
                        'date' => Carbon::now(),
                        'debit' => $payment_transfer,
                        'credit' => 0,
                        'type' => JournalEntry::class,
                    ];

                    if (sizeof($log_payment_voucher) > 0) {
                        $newLogPayment = new LogPayment;
                        $newLogPayment->reference_type = Voucher::class;
                        $newLogPayment->reference_id = $v->id;
                        $newLogPayment->name = "BALANCE_VOUCHER_WITH_INVOICE";
                        $newLogPayment->snapshot = json_encode($log_payment_voucher);
                        $newLogPayment->save();
                    }
                }
            }
        }
    }

    public static function voucherPayment($voucher)
    {
        $client_po = $voucher->client_po;
        $cast_account = CastAccount::where('id', $voucher->account_source_id)->first();
        $log_payment = [];

        // kurangi hutang
        $hutang = Account::where('code', '20101')->first();
        if ($hutang) {
            $trans_1 = CustomHelper::insertJournalEntry([
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "piutang voucher " . $voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => 0,
                'credit' => $voucher->payment_transfer,
            ]);
            $log_payment[] = [
                'id' => $trans_1->id,
                'account_id' => $hutang->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "piutang voucher " . $voucher->no_voucher,
                'date' => Carbon::now(),
                'type' => JournalEntry::class,
                'debit' => 0,
                'credit' => $voucher->payment_transfer,
            ];
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

        $log_payment[] = [
            'id' => $transaksi->id,
            'account_id' => $hutang->id,
            'reference_id' => $voucher->id,
            'reference_type' => Voucher::class,
            'description' => 'Pembayaran Voucher',
            'date' => Carbon::now(),
            'type' => AccountTransaction::class,
        ];

        $accountBank = Account::where('id', $cast_account->account_id)->first();
        if ($accountBank) {
            $trans_2 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $accountBank->id,
                'reference_id' => $transaksi->id,
                'reference_type' => AccountTransaction::class,
                'description' => "Saldo berkurang " . $voucher->no_voucher,
                'date' => Carbon::now(),
                'debit' => 0,
                'credit' => $transaksi->nominal_transaction,
            ], [
                'account_id' => $accountBank->id,
                'reference_id' => $transaksi->id,
                'reference_type' => AccountTransaction::class,
            ]);
            $log_payment[] = [
                'id' => $trans_2->id,
                'account_id' => $accountBank->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "Saldo berkurang " . $voucher->no_voucher,
                'date' => Carbon::now(),
                'type' => JournalEntry::class,
                'debit' => 0,
                'credit' => $transaksi->nominal_transaction,
            ];
        }

        if (sizeof($log_payment) > 0) {
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = Voucher::class;
            $newLogPayment->reference_id = $voucher->id;
            $newLogPayment->name = "CREATE_PAYMENT_VOUCHER";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();
        }
    }

    public static function balanceAccount($account_code)
    {
        $code = $account_code;

        $sum_accounts = \App\Models\Account::selectRaw("
            (SUM(journal_entries.debit) - SUM(journal_entries.credit)) as balance
        ")
            ->leftJoin('journal_entries', 'journal_entries.account_id', '=', 'accounts.id')
            ->where('accounts.code', 'LIKE', "" . $code . "%")
            ->first();
        return $sum_accounts->balance ?? 0;
    }

    public static function voucherCreate(Voucher $voucher, $invoice_not_exists = false)
    {
        $log_payment = [];
        // $voucher = Voucher::where('id', $voucher_id)->first();
        $invoice = InvoiceClient::where('client_po_id', $voucher->client_po_id)->first();
        $client_po = $voucher->client_po;
        $payment_transfer = $voucher->payment_transfer;

        if ($client_po->status == 'TANPA PO') {
            // ada po
            $account = Account::where('code', "50222")->first();

            $trans_1 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $account->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "Transaksi tanpa PO " . $client_po->work_code,
                'date' => Carbon::now(),
                'debit' => $payment_transfer,
                // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
            ], [
                'account_id' => $account->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
            ]);
            $log_payment[] = [
                'id' => $trans_1->id,
                'account_id' => $account->id,
                'reference_id' => $voucher->id,
                'reference_type' => Voucher::class,
                'description' => "Transaksi tanpa PO " . $client_po->work_code,
                'date' => Carbon::now(),
                'debit' => $payment_transfer,
                'type' => JournalEntry::class,
            ];
        }

        // periksa jenis voucher
        if ($voucher->reference_type == "App\Models\PurchaseOrder" || $voucher->reference_type == "App\Models\Spk") {
            if ($invoice == null || $invoice_not_exists == true) {
                $account = Account::where('code', "50401")->first();
                $trans_2 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_2->id,
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            } else {
                $account = Account::where('id', $voucher->account_id)->first();
                $payment_transfer = $voucher->payment_transfer;

                $trans_3 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Transaksi voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_3->id,
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Transaksi voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        } else if ($voucher->reference_type == "App\Models\ClientPo") {
            if ($invoice == null || $invoice_not_exists == true) {
                // jika tidak ada invoice di PO
                $account = Account::where('code', "50401")->first();
                $payment_transfer = $voucher->payment_transfer;
                $trans_4 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_4->id,
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Beban dalam proses pekerjaan voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            } else {
                $account = Account::where('id', $voucher->account_id)->first();
                $payment_transfer = $voucher->payment_transfer;

                $invoice->status = 'Paid';
                $invoice->save();

                $trans_5 = CustomHelper::updateOrCreateJournalEntry([
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Transaksi voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    // 'credit' => ($status == CastAccount::OUT) ? $nominal_transaction : 0,
                ], [
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                ]);
                $log_payment[] = [
                    'id' => $trans_5->id,
                    'account_id' => $account->id,
                    'reference_id' => $voucher->id,
                    'reference_type' => Voucher::class,
                    'description' => "Transaksi voucher " . $voucher->no_voucher,
                    'date' => Carbon::now(),
                    'debit' => $payment_transfer,
                    'credit' => 0,
                    'type' => JournalEntry::class,
                ];
            }
        }

        if (sizeof($log_payment) > 0) {
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = Voucher::class;
            $newLogPayment->reference_id = $voucher->id;
            $newLogPayment->name = "CREATE_VOUCHER";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();
        }
    }

    public static function profitLostRepository()
    {
        $voucher = DB::table('vouchers')->select(
            'client_po_id',
            DB::raw("SUM(payment_transfer) as payment_transfer"),
            DB::raw("SUM(total) as biaya")
        )->groupBy('client_po_id');

        $invoice = DB::table('invoice_clients')
            ->select(
                'invoice_clients.client_po_id',
                DB::raw("GROUP_CONCAT(invoice_clients.invoice_date SEPARATOR ',') AS invoice_date"),
                DB::raw("SUM(invoice_clients.price_total_exclude_ppn) as price_job_exlude_ppn"),
                DB::raw("SUM(invoice_clients.price_total_include_ppn) as price_job_include_ppn")
            )
            ->groupBy('invoice_clients.client_po_id');

        $client_po_query_exclude_ppn = DB::table("client_po")
            ->leftJoinSub($invoice, 'invoice', function ($join) {
                $join->on('invoice.client_po_id', '=', 'client_po.id');
            })->select(
                "client_po.id as client_po_id",
                "client_po.work_code",
                "client_po.po_number",
                "client_po.reimburse_type",
                "client_po.job_name",
                "client_po.job_value",
                "client_po.category",
                "client_po.job_value_include_ppn",
                "invoice.invoice_date",
                "invoice.price_job_exlude_ppn as invoice_price_job_exlude_ppn",
                "invoice.price_job_include_ppn as invoice_price_job_include_ppn",
                DB::raw("IF(invoice.invoice_date IS NULL, client_po.job_value, invoice.price_job_exlude_ppn) as price_job_exlude_ppn_logic")
            );
        $profitLost = ProjectProfitLost::leftJoinSub($client_po_query_exclude_ppn, 'client_po', function ($join) {
            $join->on('client_po.client_po_id', '=', 'project_profit_lost.client_po_id');
        });
        $profitLost = $profitLost->leftJoinSub($voucher, 'vouchers', function ($join) {
            $join->on('vouchers.client_po_id', '=', 'project_profit_lost.client_po_id');
        });
        $profitLost = $profitLost->select(
            DB::raw("
                project_profit_lost.price_after_year,
                project_profit_lost.price_general,
                vouchers.payment_transfer as payment_voucher,
                vouchers.biaya as voucher_biaya,
                client_po.invoice_price_job_exlude_ppn,
                client_po.invoice_date,
                client_po.price_job_exlude_ppn_logic,
                client_po.invoice_price_job_include_ppn,
                client_po.work_code as work_code,
                client_po.po_number as po_number,
                client_po.reimburse_type as reimburse_type,
                client_po.job_name as job_name,
                client_po.job_value as job_value,
                client_po.job_value_include_ppn as job_value_include_ppn,
                IFNULL(project_profit_lost.price_small_cash, 0) as total_small_cash,
                (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0)) as price_total_str,
                (client_po.price_job_exlude_ppn_logic - (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0))) as price_profit_lost_str,
                ((client_po.price_job_exlude_ppn_logic - (IFNULL(project_profit_lost.price_after_year, 0) + IFNULL(vouchers.biaya, 0) + IFNULL(project_profit_lost.price_small_cash, 0))) - IFNULL(project_profit_lost.price_general, 0)) as price_prift_lost_final_str
            ")
        );

        return $profitLost;
    }

    public static function rollbackPayment($reference_type, $reference_id, $name = null)
    {
        $payment = LogPayment::where('reference_type', $reference_type)
            ->where('reference_id', $reference_id);
        if ($name) {
            $payment = $payment->where('name', $name);
        }
        $payment = $payment->get();
        foreach ($payment as $pay) {
            // call child for all header log transaction
            $snapshots = json_decode($pay->snapshot);
            foreach ($snapshots as $snapshot) {
                $snap_id = $snapshot->id;
                $objTable = new $snapshot->type;
                if (is_object($objTable)) {
                    $dataset = $objTable::find($snap_id);
                    if ($dataset) {
                        $dataset->delete();
                    }
                }
            }
            // delete header log transaction
            $pay->delete();
        }
        return 1;
    }
}
