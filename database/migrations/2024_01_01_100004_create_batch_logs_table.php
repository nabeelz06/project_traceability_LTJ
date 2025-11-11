<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('action'); // e.g., CREATED, SHIPPED, RECEIVED
            
            // === KOLOM YANG DIPERBARUI/DITAMBAHKAN ===
            $table->string('previous_status')->nullable(); // Dari TraceabilityService
            $table->string('new_status')->nullable();      // Dari TraceabilityService
            $table->unsignedBigInteger('actor_user_id');  // Dari TraceabilityService (menggantikan user_id)
            $table->unsignedBigInteger('device_id')->nullable(); // Dari TraceabilityService
            $table->string('gps_location')->nullable();       // Dari TraceabilityService
            $table->string('photo_evidence')->nullable();    // Dari TraceabilityService
            $table->text('notes')->nullable();              // Dari TraceabilityService
            // === AKHIR PERUBAHAN ===
            
            $table->timestamps();
            
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('actor_user_id')->references('id')->on('users');
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
        });
    }

    public function down(): void 
    { 
        Schema::dropIfExists('batch_logs'); 
    }
};