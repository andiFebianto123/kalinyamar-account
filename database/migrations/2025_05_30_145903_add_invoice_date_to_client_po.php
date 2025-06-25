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
        if (!Schema::hasColumn('client_po', 'date_invoice')) {
            Schema::table('client_po', function (Blueprint $table) {
                $table->date('date_invoice')->nullable()->after('document_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('client_po', 'date_invoice')){
            Schema::table('client_po', function (Blueprint $table) {
                $table->dropColumn('date_invoice');
            });
        }
    }
};
