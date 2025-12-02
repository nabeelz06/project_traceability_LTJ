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
            $table->string('type'); // upstream, middlestream, downstream, end_user
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            
            // PERBAIKAN 1: Tambahkan kolom pic_email
            $table->string('pic_email')->nullable(); 
            
            $table->text('address')->nullable();
            
            // PERBAIKAN 2: Jadikan nullable karena di seeder ada partner yg tidak punya kode ini
            $table->json('allowed_product_codes')->nullable(); 
            
            $table->string('status')->default('pending'); // approved, pending
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