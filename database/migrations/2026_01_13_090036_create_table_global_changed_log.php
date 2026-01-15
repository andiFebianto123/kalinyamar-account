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
        Schema::create('global_changed_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('log_payment_id')->nullable();
            $table->string('reference_type', 100);
            $table->string('reference_id', 50);
            $table->enum('action', ['INSERT', 'UPDATE', 'DELETE']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['reference_type', 'reference_id'], 'idx_ref_type_id');
            $table->index('changed_by', 'idx_changed_by');
            $table->index('created_at', 'idx_created_at');
        });

        Schema::table('log_payments', function (Blueprint $table) {
            $table->index(['reference_type', 'reference_id'], 'idx_ref_type_id');
            $table->index('name', 'idx_name');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_changed_logs');
    }
};
