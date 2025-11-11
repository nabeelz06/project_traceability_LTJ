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
            $table->string('batch_code')->unique();
            $table->string('product_code');
            $table->unsignedBigInteger('parent_batch_id')->nullable();
            $table->decimal('initial_weight', 10, 2);
            $table->decimal('current_weight', 10, 2);
            $table->string('weight_unit')->default('kg');
            $table->string('container_code')->nullable();
            $table->string('current_location');
            $table->string('status');
            $table->string('rfid_tag_uid')->nullable()->unique();
            $table->boolean('is_ready')->default(false);
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('current_owner_partner_id')->nullable();
            $table->json('quality_data')->nullable();
            $table->json('history_log')->nullable();
            $table->timestamps();
            
            $table->foreign('product_code')->references('code')->on('product_codes');
            $table->foreign('parent_batch_id')->references('id')->on('batches')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('current_owner_partner_id')->references('id')->on('partners');
        });
    }
    public function down(): void { Schema::dropIfExists('batches'); }
};