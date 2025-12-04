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
        Schema::create('project_profit_lost_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_profit_lost_id');
            $table->decimal('price_after_year', 18, 2)->nullable();
            $table->decimal('price_general', 18, 2)->nullable();
            $table->decimal('price_small_cash', 18, 2)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('project_profit_lost_id')->references('id')->on('project_profit_lost')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_profit_lost_log');
    }
};
