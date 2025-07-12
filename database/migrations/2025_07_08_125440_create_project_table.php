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
        Schema::create('project_profit_lost', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_po_id');
            $table->decimal('contract_value', 18, 2);
            $table->decimal('total_project', 18, 2);
            $table->decimal('price_material', 18, 2)->nullable();
            $table->decimal('price_subkon', 18, 2)->nullable();
            $table->decimal('price_btkl', 18, 2)->nullable();
            $table->decimal('price_transport_project', 18, 2)->nullable();
            $table->decimal('price_worker_consumption', 18, 2)->nullable();
            $table->decimal('price_project_equipment', 18, 2)->nullable();
            $table->decimal('price_other', 18, 2)->nullable();
            $table->decimal('price_profit_lost_project', 18, 2)->nullable();
            $table->timestamps();
            $table->foreign('client_po_id')->references('id')->on('client_po')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_profit_lost');
    }
};
