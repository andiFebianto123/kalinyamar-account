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
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_key')->index(); // Untuk membedakan setiap proses (misal: normalize-xxxx)
            $table->string('loggable_type');      // Nama Model/Tabel (misal: App\Models\JournalEntry)
            $table->unsignedBigInteger('loggable_id'); // ID record yang diinsert
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
