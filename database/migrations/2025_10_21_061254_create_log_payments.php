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
        Schema::create('log_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_type', 70);
            $table->unsignedBigInteger('reference_id');
            $table->string('name', 120);
            $table->json('snapshot');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_payments');
    }
};
