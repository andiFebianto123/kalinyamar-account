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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('subkon_id')->nullable()->after('bussines_entity_name');
            $table->date('payment_date')->nullable()->after('payment_status');
            $table->unsignedBigInteger('account_source_id')->after('work_code');

            $table->foreign('subkon_id')->references('id')->on('subkons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {

        });
    }
};
