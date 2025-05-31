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
        Schema::create('client_po', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id'); // from 'id_subkon'
            $table->string('work_code', 30);
            $table->string('po_number', 30);
            $table->string('job_name');
            $table->decimal('job_value', 18, 2); // from 'nilai_pekerjaan'
            $table->decimal('tax_ppn', 18, 2);
            $table->decimal('total_value_with_tax', 18, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reimburse_type', 50);
            $table->decimal('price_total', 18, 2);
            $table->decimal('profit_and_loss', 18, 2);
            $table->string('document_path')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->index(['work_code', 'po_number'], 'work_and_po');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_po');
    }
};
