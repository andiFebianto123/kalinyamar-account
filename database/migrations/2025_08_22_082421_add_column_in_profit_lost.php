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
        Schema::table('project_profit_lost', function (Blueprint $table) {
            $table->decimal('price_after_year', 18, 2)->nullable()->after('client_po_id');
            $table->decimal('price_voucher', 18, 2)->nullable()->after('price_after_year');
            $table->decimal('price_small_cash', 18, 2)->nullable()->after('price_voucher');
            $table->decimal('price_total', 18, 2)->nullable()->after('price_small_cash');
            $table->decimal('price_profit_lost_po', 18, 2)->nullable()->after('price_total');
            $table->decimal('price_general', 18, 2)->nullable()->after('price_profit_lost_po');
            $table->decimal('price_prift_lost_final', 18, 2)->nullable()->after('price_general');
            $table->string('category', 20)->nullable()->after('price_prift_lost_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_profit_lost', function (Blueprint $table) {
            //
        });
    }
};
