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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo_dark', 50)->nullable();
            $table->string('logo_light', 50)->nullable();
            $table->string('favicon', 50)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('currency_symbol', 10)->nullable();
            $table->string('position_currency_symbol', 10)->nullable();
            $table->string('format_decimal_number', 5)->nullable();
            $table->string('po_prefix', 10)->nullable();
            $table->string('spk_prefix', 10)->nullable();
            $table->string('work_code_prefix', 10)->nullable();
            $table->string('vouhcer_prefix', 10)->nullable();
            $table->string('faktur_prefix', 10)->nullable();
            $table->string('invoice_prefix', 10)->nullable();
            $table->string('name_company', 100)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('city', 30)->nullable();
            $table->string('province', 30)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country', 30)->nullable();
            $table->string('telp', 25)->nullable();
            $table->string('no_register_company', 20)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('no_fax', 5)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
