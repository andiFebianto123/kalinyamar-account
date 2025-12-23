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
        Schema::create('consolidate_income_account_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_id')->constrained('consolidate_income_headers')->onDelete('cascade');
            $table->unsignedBigInteger('account_id');
            $table->timestamps();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consolidate_income_account_items');
    }
};
