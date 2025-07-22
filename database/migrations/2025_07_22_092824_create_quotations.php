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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('no_rfq', 40);
            $table->string('name_project', 150);
            $table->decimal('rab', 18, 2);
            $table->decimal('rap', 18, 2);
            $table->unsignedBigInteger('client_id');
            $table->string('pic', 100);
            $table->string('user', 150)->nullable();
            $table->date('closing_date');
            $table->string('status', 50);
            $table->text('information')->nullable();
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('setup_clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
