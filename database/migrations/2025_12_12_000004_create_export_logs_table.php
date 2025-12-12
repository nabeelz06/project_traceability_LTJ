<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('export_type', 20); // export, domestic
            $table->string('destination', 255);
            $table->string('manifest_number', 100)->nullable();
            $table->decimal('weight_kg', 12, 2);
            $table->unsignedBigInteger('operator_user_id');
            $table->timestamp('exported_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('operator_user_id')->references('id')->on('users');
            
            // Indexes
            $table->index('export_type');
            $table->index('exported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_logs');
    }
};