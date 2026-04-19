<?php
/*
 * File: 2026_04_19_115019_change_job_name_type_in_account_transactions.php
 * Created: 2026-04-19 11:50:19
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->text('job_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->string('job_name', 200)->nullable()->change();
        });
    }
};
