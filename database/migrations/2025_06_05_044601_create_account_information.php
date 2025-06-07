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
        Schema::create('account_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cast_account_id');
            $table->unsignedBigInteger('account_information_id');
            $table->timestamps();
            $table->foreign('cast_account_id')->references('id')->on('cast_accounts')->onDelete('cascade');
            $table->foreign('account_information_id')->references('id')->on('additional_informations')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_information');
    }
};
