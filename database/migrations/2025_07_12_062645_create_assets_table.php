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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('description', 150);
            $table->string('year_acquisition', 10);
            $table->decimal('price_acquisition', 18, 2);
            $table->integer('economic_age');
            $table->decimal('tarif', 18, 2);
            $table->decimal('price_rate_per_year', 18, 2);
            $table->decimal('price_rate_year_ago', 18, 2)->nullable();
            $table->decimal('accumulated_until_december_last_year', 18, 2)->nullable();
            $table->decimal('book_value_last_december', 18, 2)->nullable();
            $table->decimal('this_year_depreciation_rate', 18, 2);
            $table->decimal('accumulated_until_december_this_year', 18, 2);
            $table->decimal('book_value_this_december', 18, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
