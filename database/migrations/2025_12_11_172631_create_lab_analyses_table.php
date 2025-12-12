<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->foreignId('analyst_user_id')->constrained('users')->onDelete('cascade');
            
            // 5 Unsur LTJ (Logam Tanah Jarang) - dalam persen (%)
            $table->decimal('nd_content', 5, 2)->comment('Neodymium (Nd) %');
            $table->decimal('la_content', 5, 2)->comment('Lanthanum (La) %');
            $table->decimal('ce_content', 5, 2)->comment('Cerium (Ce) %');
            $table->decimal('y_content', 5, 2)->comment('Yttrium (Y) %');
            $table->decimal('pr_content', 5, 2)->comment('Praseodymium (Pr) %');
            
            // Total recovery (total dari 5 unsur, boleh < 100%)
            $table->decimal('total_recovery', 5, 2)->comment('Total LTJ Recovery %');
            
            // Timestamps
            $table->timestamp('analyzed_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('batch_id');
            $table->index('analyst_user_id');
            $table->index('analyzed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_analyses');
    }
};