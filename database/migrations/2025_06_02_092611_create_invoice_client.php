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
        Schema::create('invoice_clients', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->string('name', 100);
            $table->date('invoice_date');
            $table->unsignedBigInteger('client_po_id');
            $table->date('po_date');
            $table->unsignedBigInteger('client_id');
            $table->decimal('price_total_exclude_ppn', 18, 2);
            $table->decimal('price_total_include_ppn', 18, 2);
            $table->string('status', 10);
            $table->timestamps();

            $table->foreign('client_po_id')->references('id')->on('client_po')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->index(['name', 'invoice_date'], 'invoice_name_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_clients');
    }
};
