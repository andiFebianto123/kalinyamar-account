<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeveloperActionRollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'action:rollback {batch_key : Key unik yang didapat saat menjalankan proses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback data yang ter-insert berdasarkan Batch Key dari tabel action_logs.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $batchKey = $this->argument('batch_key');

        // Ambil log berdasarkan batch_key
        $logs = DB::table('action_logs')->where('batch_key', $batchKey)->get();

        if ($logs->isEmpty()) {
            $this->error("Tidak ditemukan log untuk Batch Key: [{$batchKey}]");
            return 1;
        }

        $this->warn("Ditemukan " . $logs->count() . " record yang akan dihapus.");
        if (!$this->confirm('Apakah Anda yakin ingin melakukan rollback? Data yang sudah di-insert akan dihapus permanen.', false)) {
            $this->info('Proses rollback dibatalkan.');
            return 0;
        }

        DB::beginTransaction();
        try {
            foreach ($logs as $log) {
                $type = $log->loggable_type;
                $id = $log->loggable_id;

                if (class_exists($type)) {
                    // Jika loggable_type adalah class Model
                    $type::where('id', $id)->delete();
                } else {
                    // Jika loggable_type adalah nama tabel langsung
                    DB::table($type)->where('id', $id)->delete();
                }
            }

            // Bersihkan log setelah berhasil rollback
            DB::table('action_logs')->where('batch_key', $batchKey)->delete();

            DB::commit();
            $this->info("Rollback untuk Batch Key [{$batchKey}] berhasil dilakukan.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal melakukan rollback: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
