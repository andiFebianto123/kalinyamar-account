<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AccountTransaction;
use App\Models\JournalEntry;
use App\Models\Voucher;
use App\Models\InvoiceClient;
use App\Models\LoanTransactionFlag;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class UpdateTransactionDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:update-dates 
                            {--file=storage/app/public/all-transaction-change-date.xlsx : Path to the excel file}
                            {--dry-run : Test the updates without saving to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update transaction dates and associated journal entry dates based on an Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path($this->option('file'));
        $dryRun = $this->option('dry-run');

        if (!file_exists($filePath)) {
            $this->error("Excel file not found at: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Loading Excel file: {$filePath}");
        if ($dryRun) {
            $this->warn("!!! DRY RUN MODE: No database changes will be saved !!!");
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            $this->error("Failed to read Excel file: " . $e->getMessage());
            return Command::FAILURE;
        }

        // Remove header row
        unset($rows[1]);

        $this->info("Found " . count($rows) . " rows to process.");

        DB::beginTransaction();

        $processed = 0;
        $notFound = 0;

        foreach ($rows as $index => $row) {
            $txId = str_replace(',', '', trim($row['A']));
            $newDateRaw = trim($row['B']);

            if (empty($txId) || empty($newDateRaw)) {
                continue;
            }

            // Parse date
            try {
                $newDate = Carbon::createFromFormat('d/m/Y', $newDateRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->error("Row {$index}: Invalid date format '{$newDateRaw}'. Expected DD/MM/YYYY.");
                continue;
            }

            // Find transaction
            $transaction = AccountTransaction::find($txId);

            if (!$transaction) {
                $this->warn("Row {$index}: Transaction ID {$txId} not found in database.");
                $notFound++;
                continue;
            }

            $oldTxDate = $transaction->date_transaction;
            $this->line("Processing ID {$txId}: Date {$oldTxDate} => {$newDate}");

            // 1. Update AccountTransaction Date
            if (!$dryRun) {
                $transaction->date_transaction = $newDate;
                $transaction->save();
            }

            // 2. Update Direct Journal Entries (associated with AccountTransaction)
            $directJournals = JournalEntry::where('reference_type', AccountTransaction::class)
                ->where('reference_id', $transaction->id)
                ->get();

            foreach ($directJournals as $dj) {
                $this->line("  -> Updating Direct Journal ID {$dj->id} (Account: {$dj->account_id}) Date: {$dj->date} => {$newDate}");
                if (!$dryRun) {
                    $dj->date = $newDate;
                    $dj->save();
                }
            }

            // 3. Update Indirect / Referenced Document Journal Entries and Dates
            if ($transaction->reference_type && $transaction->reference_id) {
                $refType = $transaction->reference_type;
                $refId = $transaction->reference_id;

                $refJournals = JournalEntry::where('reference_type', $refType)
                    ->where('reference_id', $refId)
                    ->get();

                foreach ($refJournals as $rj) {
                    $this->line("  -> Updating Ref Journal ID {$rj->id} (Ref: " . basename(str_replace('\\', '/', $refType)) . " #{$refId}) Date: {$rj->date} => {$newDate}");
                    if (!$dryRun) {
                        $rj->date = $newDate;
                        $rj->save();
                    }
                }

                // If reference is a Voucher, update its payment_date
                if ($refType === Voucher::class) {
                    $voucher = Voucher::find($refId);
                    if ($voucher) {
                        $this->line("  -> Updating Voucher #{$refId} payment_date: {$voucher->payment_date} => {$newDate}");
                        if (!$dryRun) {
                            $voucher->payment_date = $newDate;
                            $voucher->save();
                        }
                    }
                }
            }

            $processed++;
        }

        if ($dryRun) {
            DB::rollBack();
            $this->info("Dry run completed. Processed: {$processed}, Not found: {$notFound}. Database changes rolled back.");
        } else {
            DB::commit();
            $this->info("Successfully updated transaction dates. Processed: {$processed}, Not found: {$notFound}. Database changes committed.");
        }

        return Command::SUCCESS;
    }
}
