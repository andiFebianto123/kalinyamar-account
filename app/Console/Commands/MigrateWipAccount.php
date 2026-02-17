<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class MigrateWipAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:wip-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all data from account 50401 to 10601 and update references';

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
        $oldCode = '50401';
        $newCode = '10601';

        $this->info("Starting migration from account $oldCode to $newCode...");

        $oldAccount = Account::where('code', $oldCode)->first();

        if (!$oldAccount) {
            $this->error("Source account code $oldCode not found in accounts table.");
            return 1;
        }

        $newAccount = Account::where('code', $newCode)->first();

        if (!$newAccount) {
            $this->info("Creating new account $newCode (Persediaan Pekerjaan Dalam Proses)...");
            $newAccount = Account::create([
                'code' => $newCode,
                'name' => 'Persediaan Pekerjaan Dalam Proses',
                'level' => $oldAccount->level,
                'type' => 'Assets',
                'finance_statement' => 'Balance Sheet',
                'is_active' => 1,
            ]);
            $this->info("New account created with ID: {$newAccount->id}");
        } else {
            $this->info("Target account $newCode already exists with ID: {$newAccount->id}");
        }

        $this->warn("This will update all records in journal_entries, account_transactions, and vouchers tables.");
        if (!$this->confirm('Do you wish to continue?', true)) {
            $this->info("Migration cancelled.");
            return 0;
        }

        DB::beginTransaction();
        try {
            // 1. Update Journal Entries
            $journalsUpdated = JournalEntry::where('account_id', $oldAccount->id)
                ->update(['account_id' => $newAccount->id]);
            $this->info("- $journalsUpdated records updated in journal_entries table.");

            // 2. Update Account Transactions
            $transactionsUpdated = AccountTransaction::where('account_id', $oldAccount->id)
                ->update(['account_id' => $newAccount->id]);
            $this->info("- $transactionsUpdated records updated in account_transactions table.");

            // 3. Update Vouchers
            $vouchersUpdated = Voucher::where('account_id', $oldAccount->id)
                ->update(['account_id' => $newAccount->id]);
            $this->info("- $vouchersUpdated records updated in vouchers table.");

            DB::commit();
            $this->info("Database records migrated successfully!");

            $this->warn("IMPORTANT: You still need to update hardcoded '50401' references in your PHP files to '$newCode'.");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Migration failed: " . $e->getMessage());
            return 1;
        }
    }
}
