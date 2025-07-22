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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('po_status', ['1', '0'])->default('not_exist');
            $table->string('reference_type', 70)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('no_po_spk', 100)->nullable();
            $table->date('po_date')->nullable();
            $table->date('received_po_date')->nullable();
            $table->decimal('price_total_exclude_ppn', 18, 2);
            $table->decimal('tax_ppn', 18, 2);
            $table->decimal('price_ppn', 18, 2)->nullable();
            $table->decimal('price_total_include_ppn', 18, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('status_po', 30);
            $table->unsignedBigInteger('client_id');
            $table->string('category', 30);
            $table->decimal('progress', 18, 2)->nullable();
            $table->string('pic', 100)->nullable();
            $table->string('user', 150)->nullable();
            $table->text('information');
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('setup_clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
