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

        if (!Schema::hasColumn('invoice_clients', 'price_total')) {
            Schema::table('invoice_clients', function (Blueprint $table) {
                $table->decimal('price_total', 18, 2);
            });
        }

        Schema::create('invoice_client_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_client_id');
            $table->string('name', 120);
            $table->decimal('price', 18, 2);
            $table->timestamps();
            $table->foreign('invoice_client_id')->references('id')->on('invoice_clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_client_details');
    }
};
