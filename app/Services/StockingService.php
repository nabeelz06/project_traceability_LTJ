<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\StockingLog;
use App\Models\BatchLog;
use Illuminate\Support\Facades\DB;
use Exception;

class StockingService
{
    // Stock batch (simpan ke stockpile)
    public function stockBatch(Batch $batch, string $location, int $userId, ?string $notes = null)
    {
        try {
            DB::beginTransaction();

            // Update batch status
            $batch->update([
                'stocking_status' => 'stocked',
                'stockpile_location' => $location,
                'stocked_at' => now(),
                'status' => 'stocked',
            ]);

            // Create stocking log
            $stockingLog = StockingLog::create([
                'batch_id' => $batch->id,
                'action' => 'stocked',
                'operator_user_id' => $userId,
                'stockpile_location' => $location,
                'notes' => $notes,
            ]);

            // Create batch log
            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'BATCH_STOCKED',
                'actor_user_id' => $userId,
                'notes' => "Batch disimpan di {$location}. {$notes}",
            ]);

            DB::commit();

            return $stockingLog;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Retrieve batch (ambil dari stockpile)
    public function retrieveBatch(Batch $batch, int $operatorUserId, ?string $notes = null)
    {
        DB::beginTransaction();
        try {
            // Validate batch is stocked
            if ($batch->stocking_status !== 'stocked') {
                throw new \Exception("Batch ini tidak dalam status stocked");
            }

            // Create stocking log
            StockingLog::create([
                'batch_id' => $batch->id,
                'action' => 'retrieve',
                'location' => $batch->stockpile_location,
                'operator_user_id' => $operatorUserId,
                'notes' => $notes, // Allow null
            ]);

            // Update batch
            $batch->update([
                'stocking_status' => null,
                'stockpile_location' => null,
                'retrieved_at' => now(),
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Get stock summary by location
    public function getStockSummary(?string $location = null)
    {
        $query = Batch::where('stocking_status', 'stocked');

        if ($location) {
            $query->where('stockpile_location', $location);
        }

        return $query->with('productCode')
            ->orderBy('stocked_at', 'desc')
            ->get();
    }

    // Get stock count by location
    public function getStockCount(?string $location = null)
    {
        $query = Batch::where('stocking_status', 'stocked');

        if ($location) {
            $query->where('stockpile_location', $location);
        }

        return $query->count();
    }

    // Get total weight in stock
    public function getTotalStockWeight(?string $location = null)
    {
        $query = Batch::where('stocking_status', 'stocked');

        if ($location) {
            $query->where('stockpile_location', $location);
        }

        return $query->sum('current_weight');
    }
}