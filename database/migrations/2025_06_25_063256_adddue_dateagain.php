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
        if (!Schema::hasColumn('purchase_orders', 'due_date')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->date('due_date')->nullable()->after('total_value_with_tax');
                $table->enum('status', ['open', 'close'])->default('open')->after('document_path');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchase_orders', 'due_date')){
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('due_date');
                $table->dropColumn('status');
            });
        }
    }
};
