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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type'); // middlestream, downstream
            $table->string('pic_name');
            $table->string('pic_phone');
            $table->text('address');
            $table->json('allowed_product_codes'); // Menyimpan array
            $table->string('status')->default('pending'); // approved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};