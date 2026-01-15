<?php

namespace App\Http\Helpers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Account;
use App\Models\Voucher;
use App\Models\Approval;
use App\Models\LogPayment;
use App\Models\CastAccount;
use App\Models\JournalEntry;
use App\Models\InvoiceClient;
use App\Models\PaymentVoucher;
use App\Models\GlobalChangedLogs;
use App\Models\AccountTransaction;
use App\Models\PaymentVoucherPlan;

class CustomVoid
{
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

                // $invoice->status = 'Paid';
                // $invoice->save();

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

    public static function voucherAllPph(Voucher $voucher)
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

    public static function voucherPaymentPlan(Voucher $voucher)
    {
        $log_payment = [];
        $type = '';
        if ($voucher->payment_type == 'NON RUTIN') {
            $type = 'NON RUTIN';
        } else {
            $type = 'SUBKON';
        }
        $payment_voucher = new PaymentVoucher();
        $payment_voucher->voucher_id = $voucher->id;
        $payment_voucher->payment_type = $type;
        $payment_voucher->save();

        $log_payment[] = [
            'id' => $payment_voucher->id,
            'account_id' => 0,
            'reference_id' => $voucher->id,
            'reference_type' => Voucher::class,
            'description' => 'Pembayaran Voucher',
            'date' => Carbon::now(),
            'type' => PaymentVoucher::class,
        ];

        $payment_voucher_plan = new PaymentVoucherPlan();
        $payment_voucher_plan->payment_voucher_id  = $payment_voucher->id;
        $payment_voucher_plan->save();

        $log_payment[] = [
            'id' => $payment_voucher_plan->id,
            'account_id' => 0,
            'reference_id' => $voucher->id,
            'reference_type' => Voucher::class,
            'description' => 'Pembayaran Voucher',
            'date' => Carbon::now(),
            'type' => PaymentVoucherPlan::class,
        ];

        if (sizeof($log_payment) > 0) {
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = Voucher::class;
            $newLogPayment->reference_id = $voucher->id;
            $newLogPayment->name = "CREATE_PLAN_PAYMENT_VOUCHER";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();
        }

        $user_approval = User::permission('APPROVE RENCANA BAYAR')
            ->orderBy('no_order', 'ASC')->get();

        foreach ($user_approval as $key => $user) {
            $approval = new Approval;
            $approval->model_type = PaymentVoucherPlan::class;
            $approval->model_id = $payment_voucher_plan->id;
            $approval->no_apprv = $key + 1;
            $approval->user_id = $user->id;
            $approval->position = '';
            $approval->status = Approval::PENDING;
            $approval->save();
        }
    }

    public static function voucherPayment(Voucher $voucher)
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

        $payment_date = Carbon::now();

        $transaksi = new AccountTransaction;
        $transaksi->cast_account_id = $voucher->account_source_id;
        $transaksi->reference_type = Voucher::class;
        $transaksi->reference_id = $voucher->id;
        $transaksi->date_transaction = $payment_date;
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

        // voucher
        if (sizeof($log_payment) > 0) {
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = Voucher::class;
            $newLogPayment->reference_id = $voucher->id;
            $newLogPayment->name = "CREATE_PAYMENT_VOUCHER";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();
        }

        // old voucher
        $old_voucher = clone $voucher;
        // update voucher
        $voucher->payment_status = 'BAYAR';
        $voucher->payment_date = $payment_date;
        $voucher->save();

        // add capture
        GlobalChangedLogs::addCapture([
            'payment_status',
            'payment_date',
        ], $old_voucher, $voucher, $newLogPayment->id);

        foreach ($log_payment as $log) {
            $log['reference_id'] = $transaksi->id;
            $log['reference_type'] = AccountTransaction::class;
        }

        // account transaction
        if (sizeof($log_payment) > 0) {
            $newLogPayment = new LogPayment;
            $newLogPayment->reference_type = AccountTransaction::class;
            $newLogPayment->reference_id = $transaksi->id;
            $newLogPayment->name = "CREATE_TRANSACTION";
            $newLogPayment->snapshot = json_encode($log_payment);
            $newLogPayment->save();

            GlobalChangedLogs::addCapture([
                'payment_status',
                'payment_date',
            ], $old_voucher, $voucher, $newLogPayment->id);
        }
    }

    /* INVOICE VOID */

    public static function invoiceMakeVoucherMoveAccount(InvoiceClient $invoice)
    {
        $before_invoice_id = InvoiceClient::where('id', '!=', $invoice->id)
            ->where('client_po_id', $invoice->client_po_id)
            ->first();

        if ($before_invoice_id == null) {
            // jika invoice pertama
            $voucher = Voucher::where('client_po_id', $invoice->client_po_id)
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

    public static function invoiceCreate(InvoiceClient $invoice)
    {
        $log_payment = [];
        $invoice_id = $invoice->id;
        // ambil voucher yang belum dibayar

        $piutang = Account::where('code', '10201')->first();
        if ($piutang) {
            $trans_3 = CustomHelper::updateOrCreateJournalEntry([
                'account_id' => $piutang->id,
                'reference_id' => $invoice->id,
                'reference_type' => InvoiceClient::class,
                'description' => "Piutang invoice " . $invoice->invoice_number,
                'date' => Carbon::now(),
                'debit' => $invoice->price_total,
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
                'debit' => $invoice->price_total,
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

        CustomVoid::rollbackPayment(InvoiceClient::class, $invoice->id, "CREATE_INVOICE");

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
                    CustomVoid::rollbackPayment(Voucher::class, $voucher->id, "BALANCE_VOUCHER_WITH_INVOICE");
                }
                // pindahkan biaya pending voucher pada invoice baru
                CustomVoid::invoiceMakeVoucherMoveAccount($invoice);
            }
        }

        CustomVoid::invoiceCreate($invoice);
    }

    public static function invoiceDelete(InvoiceClient $invoice)
    {
        $another_invoice = InvoiceClient::where('client_po_id', $invoice->client_po_id)
            ->where('id', '!=', $invoice->id)->first();

        if ($another_invoice == null) {
            $voucher = Voucher::where('client_po_id', $invoice->client_po_id)->get();
            foreach ($voucher as $voucher) {
                CustomVoid::rollbackPayment(Voucher::class, $voucher->id, "BALANCE_VOUCHER_WITH_INVOICE");
            }
        }

        CustomVoid::rollbackPayment(InvoiceClient::class, $invoice->id, "CREATE_INVOICE");
    }

    public static function rollbackPayment($reference_type, $reference_id, $name = null)
    {
        $payment = LogPayment::where('reference_type', $reference_type)
            ->where('reference_id', $reference_id);
        if ($name) {
            $payment = $payment->where('name', $name);
        }
        $payment = $payment->orderBy('id', 'desc')->get();
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

            // rolling back capture edited data
            if ($pay->global_changed_logs->count() > 0) {
                $logChanged = $pay->global_changed_logs->sortBy([
                    ['id', 'desc']
                ]);
                foreach ($logChanged as $log) {
                    $old_stage = (array) json_decode($log->old_values);
                    $objTableEdited = new $log->reference_type;
                    if (is_object($objTableEdited)) {
                        $objTableEdited::where('id', $log->reference_id)
                            ->update($old_stage);
                    }
                    $log->delete();
                }
            }

            // delete header log transaction
            $pay->delete();
        }
        return 1;
    }
}
