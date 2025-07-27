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
        Schema::table('client_po', function (Blueprint $table) {
            $table->dropColumn('total_value_with_tax');
            $table->decimal('rap_value', 18, 2)->after('job_value');
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
            $table->decimal('job_value_include_ppn', 18, 2)->after('tax_ppn');
            $table->decimal('price_after_year', 18, 2)->after('job_value_include_ppn');
            $table->decimal('load_general_value', 18, 2)->nullable()->after('price_after_year');
            $table->decimal('profit_and_lost_final', 18, 2)->nullable()->after('load_general_value');
            $table->string('category', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
