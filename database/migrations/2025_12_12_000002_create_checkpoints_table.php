<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('checkpoint_code', 10); // CP1, CP2, CP3, CP4.1, CP4.2, CP4.3, CP5
            $table->string('checkpoint_name', 100);
            $table->unsignedBigInteger('operator_user_id');
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->string('evidence_photo', 255)->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('operator_user_id')->references('id')->on('users');
            
            // Indexes
            $table->index('checkpoint_code');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkpoints');
    }
};