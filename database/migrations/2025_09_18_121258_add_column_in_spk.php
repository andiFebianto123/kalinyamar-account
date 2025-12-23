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
        Schema::table('spk', function (Blueprint $table) {
            $table->text('additional_info')->nullable()
            ->after('document_path');
            $table->string('work_code', 30)->nullable()->after('no_spk');
            $table->date('due_date')->nullable()->after('total_value_with_tax');
            $table->enum('status', ['open', 'close'])->default('open')->after('document_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk', function (Blueprint $table) {
            //
        });
    }
};
