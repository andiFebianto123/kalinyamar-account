<?php

namespace App\Console\Commands;

use App\Http\Helpers\CustomVoid;
use App\Models\InvoiceClient;
use App\Models\InvoiceClientDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class UpdateInvoiceExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:update-excel {path : Path to the excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update invoice data from Excel (Nominal, PPN, PPh, WAPU) and trigger accounting logic';

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
                // Normalisasi semua key di row menjadi lowercase agar lebih aman
                $row = array_change_key_case($row, CASE_LOWER);

                // Header yang diharapkan:
                // kode_invoice, nominal_exclude_ppn, ppn, pph, wajib_pungut
                $noInvoice      = $row['kode_invoice'] ?? null;
                $nominalExclude = $row['nominal_exclude_ppn'] ?? null;
                $taxPpn         = $row['ppn'] ?? null;
                $taxPph         = $row['pph'] ?? null;
                $wajibPungut    = $row['wajib_pungut'] ?? null;
                dd($row);

                if (!$noInvoice) {
                    $this->warn("\nBaris " . ($index + 2) . ": Kode Invoice kosong. Dilewati.");
                    $failCount++;
                    $bar->advance();
                    continue;
                }

                DB::beginTransaction();
                try {
                    // Cari invoice berdasarkan invoice_number atau KDP
                    $invoice = InvoiceClient::where('invoice_number', $noInvoice)
                        ->orWhere('kdp', $noInvoice)
                        ->first();

                    if (!$invoice) {
                        throw new \Exception("Invoice '{$noInvoice}' tidak ditemukan di database.");
                    }

                    $oldPoId = $invoice->client_po_id;

                    // Update data dasar (Hanya jika ada nilai di Excel)
                    if ($nominalExclude !== null && $nominalExclude !== '') {
                        $invoice->price_total_exclude_ppn = (float) $nominalExclude;
                    }
                    if ($taxPpn !== null && $taxPpn !== '') {
                        $invoice->tax_ppn = (float) $taxPpn;
                    }
                    $invoice->pph = (float) ($taxPph ?? 0);

                    if ($wajibPungut) {
                        // Pastikan format sesuai (WAPU atau NON WAPU)
                        $statusWapu = strtoupper(trim($wajibPungut));
                        if ($statusWapu === 'NON WAPU' || $statusWapu === 'WAPU') {
                            $invoice->withholding_agent = $statusWapu;
                        }
                    }

                    // Hitung ulang nominal-nominal seperti di controller
                    $billValue = $invoice->price_total_exclude_ppn;
                    $nilaiPpn  = ($billValue * ($invoice->tax_ppn / 100));
                    $totalIncludePpn = $billValue + $nilaiPpn;
                    $diskonPph = ($billValue * ($invoice->pph / 100));

                    $invoice->price_total_include_ppn = $totalIncludePpn;
                    $invoice->discount_pph = $diskonPph;

                    // Ambil total harga dari item detail (jika ada) agar total_price tetap akurat
                    $itemPricesSum = InvoiceClientDetail::where('invoice_client_id', $invoice->id)->sum('price');

                    // Rumus: (Include PPN + Item Details) - Diskon PPh
                    $invoice->price_total = ($totalIncludePpn + $itemPricesSum) - $diskonPph;

                    $invoice->save();

                    // Panggil fungsi krusial untuk update akuntansi (jurnal, balance, dll)
                    CustomVoid::invoiceUpdate($invoice, $oldPoId);

                    DB::commit();
                    $successCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("\nError di baris " . ($index + 2) . " [{$noInvoice}]: " . $e->getMessage());
                    $failCount++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Selesai! Berhasil: {$successCount}, Gagal: {$failCount}");

            return 0;
        } catch (\Exception $e) {
            $this->error("Kesalahan fatal: " . $e->getMessage());
            return 1;
        }
    }
}
