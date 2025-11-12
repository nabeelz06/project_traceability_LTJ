<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfid_writes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('tag_uid');
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_success');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('batches');
            $table->foreign('device_id')->references('id')->on('devices');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('rfid_writes'); }
};