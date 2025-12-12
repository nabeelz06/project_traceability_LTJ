<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat table product_codes
     */
    public function up(): void
    {
        Schema::create('product_codes', function (Blueprint $table) {
            $table->id();
            
            // Kolom 'code' unik
            $table->string('code', 50)->unique(); // Format: TIM-MON-RAW
            
            // PERBAIKAN: Memperbesar limit karakter dari 10 menjadi 50
            // Agar muat menampung 'wet_process', 'dry_process', 'MINERAL_IKUTAN', dll.
            $table->string('stage', 50); 
            $table->string('material', 50); 
            $table->string('spec', 50); 
            
            $table->string('description');
            $table->string('category')->nullable();
            $table->text('specifications')->nullable();
            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index('stage');
            $table->index('material');
            $table->index('spec');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_codes');
    }
};