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
        Schema::table('invoice_clients', function (Blueprint $table) {
            $table->decimal('pph_23', 5, 2)->default(0)->after('tax_ppn');
            $table->decimal('discount_pph_23', 18, 2)->default(0)->after('pph_23');
            $table->decimal('pph_4', 5, 2)->default(0)->after('discount_pph_23');
            $table->decimal('discount_pph_4', 18, 2)->default(0)->after('pph_4');
            $table->decimal('pph_21', 5, 2)->default(0)->after('discount_pph_4');
            $table->decimal('discount_pph_21', 18, 2)->default(0)->after('pph_21');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_clients', function (Blueprint $table) {
            $table->dropColumn(['pph_23', 'discount_pph_23', 'pph_4', 'discount_pph_4', 'pph_21', 'discount_pph_21']);
        });
    }
};
