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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cast_account_id');
            $table->unsignedBigInteger('cast_account_destination_id')->nullable();
            $table->string('no_transaction', 100)->nullable();
            $table->string('no_invoice', 100)->nullable();
            $table->decimal('nominal_transaction', 18, 2);
            $table->decimal('total_saldo_before', 18, 2);
            $table->decimal('total_saldo_after', 18, 2);
            $table->enum('status', ['enter', 'out'])->default('enter');
            $table->timestamps();

            $table->foreign('cast_account_id')->references('id')->on('cast_accounts')->onDelete('cascade');
            $table->foreign('cast_account_destination_id')->references('id')->on('cast_accounts')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
