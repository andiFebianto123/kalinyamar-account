<?php

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
        if (!Schema::hasColumn('cast_accounts', 'account_id')) {
            Schema::table('cast_accounts', function (Blueprint $table) {
                $table->unsignedBigInteger('account_id')->nullable()->after('total_saldo');
                $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cast_accounts', 'account_id')){
            Schema::table('cast_accounts', function (Blueprint $table) {
                $table->dropColumn('account_id');
            });
        }
    }
};
