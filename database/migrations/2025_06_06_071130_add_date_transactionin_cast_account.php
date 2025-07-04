<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('account_transactions', 'date_transaction')) {
            Schema::table('account_transactions', function (Blueprint $table) {
                $table->date('date_transaction')->after('cast_account_destination_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('account_transactions', 'date_transaction')){
            Schema::table('account_transactions', function (Blueprint $table) {
                $table->dropColumn('date_transaction');
            });
        }
    }
};
