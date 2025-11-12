<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('from_partner_id')->nullable();
            $table->unsignedBigInteger('to_partner_id');
            $table->string('status'); // scheduled, in_transit, delivered
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedBigInteger('operator_user_id')->nullable();
            $table->timestamps();
            
            $table->foreign('batch_id')->references('id')->on('batches');
            $table->foreign('to_partner_id')->references('id')->on('partners');
        });
    }
    public function down(): void { Schema::dropIfExists('shipments'); }
};