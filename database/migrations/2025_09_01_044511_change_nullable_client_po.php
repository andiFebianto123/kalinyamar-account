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
        Schema::table('client_po', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->string('po_number', 30)->nullable()->change();
            $table->string('job_name')->nullable()->change();
            $table->decimal('rap_value', 18, 2)->nullable()->change();
            $table->decimal('job_value', 18, 2)->nullable()->change();
            $table->decimal('tax_ppn', 18, 2)->nullable()->change();
            $table->decimal('job_value_include_ppn', 18, 2)->nullable()->change();
            $table->decimal('price_after_year', 18, 2)->nullable()->change();
            $table->decimal('price_total', 18, 2)->nullable()->change();
            $table->decimal('profit_and_loss', 18, 2)->nullable()->change();
            $table->string('reimburse_type', 50)->nullable()->change();
            // $table->foreign('client_id')->references('id')->on('clients');
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
