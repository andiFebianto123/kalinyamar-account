<?php

namespace App\Console\Commands;

use App\Http\Helpers\CustomHelper;
use App\Http\Helpers\CustomVoid;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class ImportVoucherRollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voucher:import-rollback {path : Path to the excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import voucher data from Excel (no_voucher, job_name, date_voucher), update database, and run rollback & rebuild voids';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("File tidak ditemukan di path: {$path}");
            return 1;
        }

        $this->info("Membaca file Excel...");

        try {
            // Helper class untuk membaca array dari excel
            $import = new class implements ToArray, WithHeadingRow {
                public function array(array $array)
                {
                    return $array;
                }
            };

            $data = Excel::toArray($import, $path);

            if (empty($data) || empty($data[0])) {
                $this->error("File Excel kosong atau tidak memiliki data.");
                return 1;
            }

            $rows = $data[0];
            $this->info("Ditemukan " . count($rows) . " baris data. Memulai proses...");

            $bar = $this->output->createProgressBar(count($rows));
            $bar->start();

            $successCount = 0;
            $failCount = 0;

            foreach ($rows as $index => $row) {
                // Normalisasi semua key di row menjadi lowercase
                $row = array_change_key_case($row, CASE_LOWER);

                // Mendukung variasi penamaan kolom di Excel
                $kodeVoucher    = $row['kode_voucher'] ?? $row['kode voucher'] ?? $row['no_voucher'] ?? $row['no voucher'] ?? null;
                $namaVoucher    = $row['nama_voucher'] ?? $row['nama voucher'] ?? $row['job_name'] ?? $row['nama pekerjaan'] ?? null;
                $tanggalVoucher = $row['tanggal_voucher'] ?? $row['tanggal voucher'] ?? $row['date_voucher'] ?? $row['tanggal'] ?? null;

                if (!$kodeVoucher) {
                    $this->warn("\nBaris " . ($index + 2) . ": Kode Voucher kosong. Dilewati.");
                    $failCount++;
                    $bar->advance();
                    continue;
                }

                DB::beginTransaction();
                try {
                    // Cari voucher berdasarkan kode
                    $voucher = Voucher::where('no_voucher', $kodeVoucher)->first();

                    if (!$voucher) {
                        throw new \Exception("Voucher '{$kodeVoucher}' tidak ditemukan di database.");
                    }

                    // Update nama voucher (job_name) jika diinputkan di Excel
                    if ($namaVoucher !== null && $namaVoucher !== '') {
                        // $voucher->job_name = $namaVoucher;
                    }

                    // Update tanggal voucher jika diinputkan di Excel
                    if ($tanggalVoucher !== null && $tanggalVoucher !== '') {
                        if (is_numeric($tanggalVoucher)) {
                            // Konversi jika tanggal berupa serial number Excel
                            $parsedDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalVoucher));
                        } else {
                            $parsedDate = Carbon::parse($tanggalVoucher);
                        }
                        // $voucher->date_voucher = $parsedDate->format('Y-m-d');
                    }

                    // $voucher->save();

                    $castAccount = $voucher->account_source;
                    // Jalankan void accounting: Rollback terlebih dahulu, lalu recreate voucher & PPh
                    CustomVoid::rollbackPayment(Voucher::class, $voucher->id);
                    CustomVoid::voucherCreate($voucher);
                    CustomVoid::voucherAllPph($voucher);
                    // add payment plan
                    CustomVoid::voucherPaymentPlan($voucher, 1);
                    // add payment voucher
                    CustomVoid::voucherPayment($voucher);
                    $balance_out = CustomHelper::balanceAccount($castAccount->account->code);
                    if ($balance_out < 0) {
                        throw new \Exception(trans('backpack::crud.cash_account.field_transfer.errors.balance_not_enough', ['castname' => $castAccount->name]));
                    }

                    DB::commit();
                    $successCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("\nError di baris " . ($index + 2) . " [{$kodeVoucher}]: " . $e->getMessage());
                    $failCount++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Selesai! Berhasil memproses: {$successCount}, Gagal/Dilewati: {$failCount}");

            return 0;
        } catch (\Exception $e) {
            $this->error("Kesalahan fatal: " . $e->getMessage());
            return 1;
        }
    }
}
