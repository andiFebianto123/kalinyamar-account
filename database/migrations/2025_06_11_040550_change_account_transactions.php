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
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropColumn('no_transaction');
            $table->text('description')->nullable()->after('date_transaction');
            $table->string('kdp', 50)->nullable()->after('description');
            $table->string('job_name', 100)->nullable()->after('kdp');
            $table->unsignedBigInteger('account_id')->nullable()->after('job_name');

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
