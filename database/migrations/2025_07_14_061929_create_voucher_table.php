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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('no_payment', 150);
            $table->unsignedBigInteger('account_id');
            $table->string('reference_type', 70);
            $table->unsignedBigInteger('reference_id');
            $table->string('no_voucher', 120);
            $table->string('work_code', 30);
            $table->string('job_name');
            $table->string('for_voucher', 20);
            $table->date('date_voucher');
            $table->string('bussines_entity_code', 30);
            $table->string('bussines_entity_type', 30);
            $table->string('bussines_entity_name', 150);
            $table->string('bill_number', 50);
            $table->date('bill_date');
            $table->date('date_receipt_bill');
            $table->text('payment_description');
            $table->string('no_po_spk');
            $table->date('date_po_spk');
            $table->decimal('bill_value', 18 ,2);
            $table->decimal('tax_ppn', 18, 2);
            $table->decimal('total', 18, 2);
            $table->decimal('pph_23', 18, 2);
            $table->decimal('discount_pph_23', 18, 2);
            $table->decimal('pph_4', 18, 2);
            $table->decimal('discount_pph_4', 18, 2);
            $table->decimal('pph_21', 18, 2);
            $table->decimal('discount_pph_21', 18, 2);
            $table->decimal('payment_transfer', 18, 2);
            $table->date('due_date');
            $table->string('factur_status', 30);
            $table->string('no_factur', 30)->nullable();
            $table->date('date_factur')->nullable();
            $table->string('bank_name', 100);
            $table->string('no_account', 100);
            $table->string('payment_type', 50);
            $table->string('payment_status', 50);
            $table->string('priority', 50);
            $table->string('information', 150);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
