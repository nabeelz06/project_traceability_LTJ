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
        Schema::create('product_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // 'TIM-MON-RAW'
            $table->string('stage');        // 'TIM'
            $table->string('material');     // 'MON'
            $table->string('spec');         // 'RAW'
            $table->string('description');
            $table->string('category');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_codes');
    }
};