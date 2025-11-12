<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat table product_codes sesuai format [STAGE]-[MATERIAL]-[SPEC]
     */
    public function up(): void
    {
        Schema::create('product_codes', function (Blueprint $table) {
            $table->string('code', 50)->primary(); // Format: TIM-MON-RAW
            $table->string('stage', 10); // TIM, MID, FINAL
            $table->string('material', 10); // MON, ND, PR, CE, Y, LE, MX
            $table->string('spec', 20); // RAW, CON, OXI99, OXI999, MET, REO
            $table->string('description');
            $table->string('category')->nullable();
            $table->text('specifications')->nullable(); // Detail teknis produk
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