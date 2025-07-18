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
        if (!Schema::hasColumn('purchase_orders', 'date_po')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->date('date_po')->nullable()->after('po_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchase_orders', 'date_po')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('date_po');
            });
        }
    }
};
