<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Voucher;
use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use App\Http\Helpers\CustomVoid;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;

class RebuildVoucherJournals extends Command
{
    /**
     * Signature command untuk menjalankan via terminal:
     * php artisan voucher:rebuild-journals
     * php artisan voucher:rebuild-journals --file=storage/app/vouchers.xlsx
     * php artisan voucher:rebuild-journals --id=123
     */
    protected $signature = 'voucher:rebuild-journals 
                            {--id= : ID Voucher spesifik yang ingin di-reload}
                            {--file= : Path file Excel (.xlsx/.xls/csv) yang berisi daftar kode voucher di Kolom A}';

    /**
     * Deskripsi command
     */
    protected $description = 'Menghapus seluruh entri jurnal & transaksi akun dari Voucher, lalu melakukan generate ulang dari awal (dapat menggunakan file Excel).';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $voucherId = $this->option('id');
        $filePath = $this->option('file');

        if ($filePath) {
            // Membaca file Excel
            if (!file_exists($filePath)) {
                // Cek juga relatif dari folder storage/app atau root project jika dipanggil singkat
                $altPath = base_path($filePath);
                if (file_exists($altPath)) {
                    $filePath = $altPath;
                } else {
                    $this->error("File Excel tidak ditemukan di path: {$filePath}");
                    return 1;
                }
            }

            $this->info("Membaca file Excel: {$filePath} ...");

            try {
                $import = new class implements ToArray {
                    public function array(array $array)
                    {
                        return $array;
                    }
                };

                $excelData = Excel::toArray($import, $filePath);
                $rows = $excelData[0] ?? [];

                if (empty($rows)) {
                    $this->error("File Excel tidak berisi data.");
                    return 1;
                }

                $voucherCodes = [];
                foreach ($rows as $index => $row) {
                    // Ambil nilai dari Kolom A (indeks 0)
                    $val = is_array($row) ? ($row[0] ?? reset($row)) : $row;
                    $code = trim((string)$val);

                    // Skip header jika ada (misal: "kode_voucher", "no_voucher", "kode voucher", dll)
                    $lower = strtolower($code);
                    if ($code !== '' && !in_array($lower, ['kode_voucher', 'no_voucher', 'kode voucher', 'no voucher', 'kode', 'voucher'])) {
                        $voucherCodes[] = $code;
                    }
                }

                $voucherCodes = array_unique($voucherCodes);
                $this->info("Ditemukan " . count($voucherCodes) . " kode voucher dari Excel.");

                $vouchers = Voucher::whereIn('no_voucher', $voucherCodes)->orderBy('id', 'asc')->get();

                // Peringatan jika ada kode voucher dari Excel yang tidak ditemukan di DB
                $foundCodes = $vouchers->pluck('no_voucher')->toArray();
                $notFound = array_diff($voucherCodes, $foundCodes);
                if (!empty($notFound)) {
                    $this->warn("Beberapa kode voucher berikut tidak ditemukan di DB (" . count($notFound) . " voucher):");
                    $this->warn(implode(', ', array_slice($notFound, 0, 10)) . (count($notFound) > 10 ? ' ...dan seterusnya' : ''));
                }

                if ($vouchers->isEmpty()) {
                    $this->error("Tidak ada voucher cocok di database dari daftar Excel tersebut.");
                    return 1;
                }
            } catch (\Exception $e) {
                $this->error("Gagal membaca file Excel: " . $e->getMessage());
                return 1;
            }
        } elseif ($voucherId) {
            $vouchers = Voucher::where('id', $voucherId)->get();
            if ($vouchers->isEmpty()) {
                $this->error("Voucher dengan ID {$voucherId} tidak ditemukan.");
                return 1;
            }
        } else {
            $this->warn("Perhatian: Tidak ada opsi --file atau --id. Akan memproses SELURUH Voucher di database.");
            if (!$this->confirm('Apakah Anda yakin ingin melakukan rebuild seluruh jurnal voucher?', false)) {
                $this->info("Proses dibatalkan.");
                return 0;
            }
            $vouchers = Voucher::orderBy('id', 'asc')->get();
        }

        $total = $vouchers->count();
        $this->info("Memulai proses reset & generate ulang jurnal untuk {$total} Voucher...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($vouchers as $voucher) {
            DB::beginTransaction();
            try {
                // 1. Cobalah rollback via LogPayment jika record log_payment ada
                CustomVoid::rollbackPayment(Voucher::class, $voucher->id);

                // 2. Penghapusan Rencana Bayar (Menu: Voucher Payment Plan) beserta Approval-nya
                $paymentVouchers = \App\Models\PaymentVoucher::where('voucher_id', $voucher->id)->get();
                foreach ($paymentVouchers as $pv) {
                    $planIds = \App\Models\PaymentVoucherPlan::where('payment_voucher_id', $pv->id)->pluck('id');
                    if ($planIds->isNotEmpty()) {
                        \App\Models\Approval::whereIn('model_type', [\App\Models\PaymentVoucherPlan::class, 'App\Models\PaymentVoucherPlan'])
                            ->whereIn('model_id', $planIds)
                            ->delete();
                        \App\Models\PaymentVoucherPlan::whereIn('id', $planIds)->delete();
                    }
                    $pv->delete();
                }

                // 3. Penghapusan Transaksi Kas/Bank (Menu: Voucher Payment & Cast Account)
                $accountTransactions = AccountTransaction::whereIn('reference_type', [Voucher::class, 'App\Models\Voucher'])
                    ->where('reference_id', $voucher->id)
                    ->get();

                foreach ($accountTransactions as $trans) {
                    // Hapus Jurnal yang menempel pada transaksi Kas/Bank ini
                    JournalEntry::whereIn('reference_type', [AccountTransaction::class, 'App\Models\AccountTransaction'])
                        ->where('reference_id', $trans->id)
                        ->delete();

                    $trans->delete();
                }

                // 4. Penghapusan Jurnal Utama Voucher (Menu: Jurnal Voucher)
                JournalEntry::whereIn('reference_type', [Voucher::class, 'App\Models\Voucher'])
                    ->where('reference_id', $voucher->id)
                    ->delete();

                // 5. Hapus header LogPayment sisa jika ada
                \App\Models\LogPayment::whereIn('reference_type', [Voucher::class, 'App\Models\Voucher'])
                    ->where('reference_id', $voucher->id)
                    ->delete();

                // 6. Generate ulang jurnal pengakuan voucher (Create & PPh)
                CustomVoid::voucherCreate($voucher);
                CustomVoid::voucherAllPph($voucher);

                // 7. Generate ulang jurnal pembayaran jika status voucher sudah dibayar
                if ($voucher->payment_status === 'BAYAR' || !empty($voucher->payment_date)) {
                    CustomVoid::voucherPaymentPlan($voucher, 1);
                    CustomVoid::voucherPayment($voucher, $voucher->payment_date);
                }

                DB::commit();
                $success++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("\n[ERROR] Voucher No: {$voucher->no_voucher} (ID: {$voucher->id}) - " . $e->getMessage());
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai! Berhasil diproses: {$success}, Gagal: {$failed}");

        return 0;
    }
}
