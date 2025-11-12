<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('type'); // BAST, CoA
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->timestamps();
            
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('uploaded_by_user_id')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('documents'); }
};