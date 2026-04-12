<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class NormalizeJournalExpense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'normalize:journal-expense {--key= : Key unik untuk proses logging/rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalisasi data beban (account 107) untuk Voucher berdasarkan kondisi balance dan invoice.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Memulai proses normalisasi data beban...");

        // Ambil Batch Key dari option atau generate otomatis jika kosong
        $batchKey = $this->option('key') ?: uniqid('normalize-');
        $this->warn("Batch Key untuk proses ini: {$batchKey}");

        // 1. Ambil data voucher dengan balance tidak nol pada account 107
        $results = DB::select('
            SELECT je.reference_id as voucher_id, 
            COUNT(id) as total_same, SUM(debit - credit) as balance 
            FROM journal_entries je 
            WHERE account_id = 107 AND je.reference_type = "App\\\\Models\\\\Voucher" 
            GROUP BY je.reference_id
            HAVING balance <> 0
        ');

        if (empty($results)) {
            $this->info("Tidak ditemukan data yang perlu dinormalisasi.");
            return 0;
        }

        $this->info("Ditemukan " . count($results) . " data voucher untuk diproses.");
        $bar = $this->output->createProgressBar(count($results));
        $bar->start();

        foreach ($results as $row) {
            $voucherId = $row->voucher_id;
            $balance = (float) $row->balance;

            // Ambil data voucher untuk mendapatkan no_voucher
            $voucher = DB::table('vouchers')->where('id', $voucherId)->first();
            $noVoucher = $voucher ? $voucher->no_voucher : '-';

            // Cari data journal asli untuk menduplikasi field metadata (date, description, etc)
            $template = JournalEntry::where('reference_id', $voucherId)
                ->where('reference_type', "App\\Models\\Voucher")
                ->where('account_id', 107)
                ->first();

            if (!$template) {
                $bar->advance();
                continue;
            }

            if ($balance > 0) {
                // Cek invoice dengan query yang ditentukan
                $invoice = DB::selectOne('
                    select invoice_clients.* from invoice_clients 
                    INNER JOIN vouchers v ON v.client_po_id = invoice_clients.client_po_id
                    WHERE v.id = ?
                ', [$voucherId]);

                if ($invoice) {
                    // Jika terdapat invoice, insert journal penyeimbang (Credit diisi balance)
                    $description = "Beban pekerjaan voucher " . $noVoucher;
                    $this->registerNormalization($template, 0, $balance, $description, $batchKey);
                }
            } else {
                // Jika balance minus, langsung tambahkan journal penyeimbang (Debit diisi abs balance)
                $description = "Beban dalam proses pekerjaan voucher " . $noVoucher;
                $this->registerNormalization($template, abs($balance), 0, $description, $batchKey);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Proses normalisasi data beban selesai.");
        $this->info("Gunakan Batch Key [{$batchKey}] jika ingin melakukan rollback.");

        return 0;
    }

    /**
     * Helper untuk menyimpan entry journal baru berbasis template metadata
     */
    private function registerNormalization($template, $debit, $credit, $description, $batchKey)
    {
        $newEntry = $template->replicate();
        $newEntry->debit = $debit;
        $newEntry->credit = $credit;
        $newEntry->description = $description;
        $newEntry->save();

        // Log action untuk rollback
        DB::table('action_logs')->insert([
            'batch_key' => $batchKey,
            'loggable_type' => get_class($newEntry),
            'loggable_id' => $newEntry->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
