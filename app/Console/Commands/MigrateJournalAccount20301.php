<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class MigrateJournalAccount20301 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:account-20301-to-50303 {--key= : Batch key untuk logging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data journal_entries dari akun 20301 ke 50303 dengan memindahkan debit ke credit.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Memulai proses migrasi akun jurnal...");

        $batchKey = $this->option('key') ?: uniqid('migrate-20301-');
        $this->warn("Batch Key untuk proses ini: {$batchKey}");

        // 1. Dapatkan ID akun
        $sourceAccount = Account::where('code', '20301')->first();
        $targetAccount = Account::where('code', '50303')->first();

        if (!$sourceAccount || !$targetAccount) {
            $this->error("Akun 20301 atau 50303 tidak ditemukan.");
            return 1;
        }

        $this->info("Source ID: {$sourceAccount->id}, Target ID: {$targetAccount->id}");

        // 2. Ambil data journal entries
        $journals = JournalEntry::where('account_id', $sourceAccount->id)->get();

        if ($journals->isEmpty()) {
            $this->info("Tidak ada data journal dengan akun 20301.");
            return 0;
        }

        $this->info("Ditemukan " . $journals->count() . " data untuk diproses.");
        $bar = $this->output->createProgressBar($journals->count());
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($journals as $journal) {
                // Simpan ID untuk logging
                DB::table('action_logs')->insert([
                    'batch_key' => $batchKey,
                    'loggable_type' => get_class($journal),
                    'loggable_id' => $journal->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Lakukan perubahan:
                // - account_id jadi 50303
                // - debit pindah ke credit
                // - debit di-nolkan
                $journal->account_id = $targetAccount->id;
                $journal->credit = $journal->debit;
                $journal->debit = 0;
                $journal->save();

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine();
            $this->info("Selesai! Batch Key: [{$batchKey}]");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal melakukan migrasi: " . $e->getMessage());
            return 1;
        }
    }
}
