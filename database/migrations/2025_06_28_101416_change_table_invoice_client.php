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
        if (Schema::hasColumn('invoice_clients', 'client_id')) {
            Schema::table('invoice_clients', function (Blueprint $table) {
                //  $table->dropForeign(['client_id']);
                $table->unsignedBigInteger('client_id')->nullable()->change();
                $table->text('description')->nullable()->after('invoice_date');
                $table->decimal('price_dpp', 18, 2)->nullable()->after('client_id');
                $table->string('kdp', 100)->nullable()->after('price_dpp');
                $table->date('send_invoice_normal_date')->nullable()->after('kdp');
                $table->date('send_invoice_revision_date')->nullable()->after('send_invoice_normal_date');
                // $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                // $table->foreign('client_id')->references('id')->on('clients');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
