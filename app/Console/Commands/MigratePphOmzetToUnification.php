<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\Account;
use App\Http\Helpers\CustomHelper;
use Illuminate\Support\Facades\DB;

class MigratePphOmzetToUnification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:pph-omzet-to-unification {--key= : Batch key untuk logging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data journal_entries dari PPH_OMZET (account_id 177) ke UNIFICATION di table journal_entries.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Memulai proses migrasi account_id dari PPH_OMZET ke UNIFICATION...");

        $batchKey = $this->option('key') ?: uniqid('migrate-pph-');
        $this->warn("Batch Key untuk proses ini: {$batchKey}");

        // 1. Dapatkan Source Account dari mapping PPH_OMZET
        $sourceCode = CustomHelper::getAccountMapping('PPH_OMZET');
        $sourceAccount = Account::where('code', $sourceCode)->first();

        if (!$sourceAccount) {
            $this->error("Account dengan code PPH_OMZET ({$sourceCode}) tidak ditemukan.");
            return 1;
        }

        // 2. Dapatkan Target Account dari mapping UNIFICATION
        $targetCode = CustomHelper::getAccountMapping('UNIFICATION');
        $targetAccount = Account::where('code', $targetCode)->first();

        if (!$targetAccount) {
            $this->error("Account dengan code UNIFICATION ({$targetCode}) tidak ditemukan.");
            return 1;
        }

        $this->info("Source Account: {$sourceAccount->name} (ID: {$sourceAccount->id}, Code: {$sourceAccount->code})");
        $this->info("Target Account: {$targetAccount->name} (ID: {$targetAccount->id}, Code: {$targetAccount->code})");

        // 3. Ambil data journal entries sesuai kriteria user
        $journals = JournalEntry::where('account_id', $sourceAccount->id)
            ->where('reference_type', "App\\Models\\Voucher")
            ->get();

        if ($journals->isEmpty()) {
            $this->info("Tidak ditemukan data journal_entries dengan account_id {$sourceAccount->id} ({$sourceAccount->code}) dan reference_type Voucher.");
            return 0;
        }

        $this->info("Ditemukan " . $journals->count() . " data untuk diproses.");
        $bar = $this->output->createProgressBar($journals->count());
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($journals as $journal) {
                // Log action untuk rollback (opsional, user menonaktifkan ini di diff sebelumnya)
                // DB::table('action_logs')->insert([
                //     'batch_key' => $batchKey,
                //     'loggable_type' => get_class($journal),
                //     'loggable_id' => $journal->id,
                //     'metadata' => json_encode([
                //         'old_account_id' => $journal->account_id,
                //         'new_account_id' => $targetAccount->id
                //     ]),
                //     'created_at' => now(),
                //     'updated_at' => now(),
                // ]);

                // Update account_id
                $journal->account_id = $targetAccount->id;
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
            $this->newLine();
            $this->error("Gagal melakukan migrasi: " . $e->getMessage());
            return 1;
        }
    }
}
