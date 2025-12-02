<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            
            // Identitas Batch
            $table->string('batch_code')->unique();
            $table->string('lot_number')->nullable()->unique(); // Ditambahkan sesuai seeder
            $table->string('container_code')->nullable(); // Di seeder 'container_number', di model 'container_code' (kita samakan ke code)
            
            // Relasi Produk (Integer ID)
            $table->unsignedBigInteger('product_code_id'); 
            
            // Relasi Parent/Child
            $table->unsignedBigInteger('parent_batch_id')->nullable();
            
            // Data Berat
            $table->decimal('initial_weight', 12, 2);
            $table->decimal('current_weight', 12, 2);
            $table->string('weight_unit')->default('kg');
            
            // Data Spesifik LTJ (Ditambahkan sesuai seeder)
            $table->decimal('tonase', 10, 3)->nullable();
            $table->decimal('konsentrat_persen', 5, 2)->nullable();
            $table->decimal('massa_ltj_kg', 12, 2)->nullable();
            $table->text('keterangan')->nullable();
            
            // Lokasi
            $table->string('origin_location')->nullable();
            $table->string('current_location')->nullable();
            
            // Status & Tracking
            $table->string('status')->default('created');
            $table->string('rfid_tag_uid')->nullable()->unique();
            $table->boolean('is_ready')->default(false);
            
            // Ownership (created_by bukan created_by_user_id)
            $table->unsignedBigInteger('created_by'); 
            $table->unsignedBigInteger('current_owner_partner_id')->nullable();
            
            // Metadata
            $table->json('quality_data')->nullable();
            $table->json('history_log')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('product_code_id')->references('id')->on('product_codes');
            $table->foreign('parent_batch_id')->references('id')->on('batches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('current_owner_partner_id')->references('id')->on('partners');
        });
    }

    public function down(): void 
    { 
        Schema::dropIfExists('batches'); 
    }
};