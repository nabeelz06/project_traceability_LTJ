<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // Checkpoint tracking
            $table->string('checkpoint_status', 20)->default('pending')->after('status');
            $table->string('process_stage', 50)->nullable()->after('checkpoint_status');
            $table->string('current_checkpoint', 10)->nullable()->after('process_stage');
            
            // Stocking management (Dry Process)
            $table->string('stocking_status', 20)->nullable()->after('current_checkpoint');
            $table->timestamp('stocked_at')->nullable()->after('stocking_status');
            $table->timestamp('retrieved_at')->nullable()->after('stocked_at');
            $table->string('stockpile_location', 100)->nullable()->after('retrieved_at');
            
            // Batch splitting (Warehouse → Lab)
            $table->boolean('is_split')->default(false)->after('stockpile_location');
            $table->unsignedBigInteger('split_from_batch_id')->nullable()->after('is_split');
            $table->decimal('split_ratio', 5, 2)->nullable()->after('split_from_batch_id');
            
            // Export tracking (Zircon & Ilmenite)
            $table->string('export_status', 20)->nullable()->after('split_ratio');
            $table->timestamp('exported_at')->nullable()->after('export_status');
            $table->string('export_destination', 255)->nullable()->after('exported_at');
            $table->string('export_manifest_number', 100)->nullable()->after('export_destination');
            
            // Foreign key untuk split batch
            $table->foreign('split_from_batch_id')->references('id')->on('batches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['split_from_batch_id']);
            $table->dropColumn([
                'checkpoint_status',
                'process_stage',
                'current_checkpoint',
                'stocking_status',
                'stocked_at',
                'retrieved_at',
                'stockpile_location',
                'is_split',
                'split_from_batch_id',
                'split_ratio',
                'export_status',
                'exported_at',
                'export_destination',
                'export_manifest_number',
            ]);
        });
    }
};