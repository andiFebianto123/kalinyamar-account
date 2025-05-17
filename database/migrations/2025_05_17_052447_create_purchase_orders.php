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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subkon_id'); // from 'id_subkon'
            $table->string('po_number');             // from 'no_po'
            $table->string('job_name');              // from 'nama_pekerjaan'
            $table->text('job_description')->nullable(); // from 'deskripsi_pekerjaan'
            $table->decimal('job_value', 18, 2)->nullable(); // from 'nilai_pekerjaan'
            $table->decimal('tax_ppn', 18, 2)->nullable();   // from 'ppn'
            $table->decimal('total_value_with_tax', 18, 2)->nullable(); // from 'nilai_pekerjaan_include_ppn'
            $table->string('document_path')->nullable(); // from 'upload_dokumen'
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('subkon_id')->references('id')->on('subkons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
