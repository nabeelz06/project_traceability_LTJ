<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchLog;
use Illuminate\Support\Facades\DB;
use Exception;

class BatchSplitService
{
    /**
     * Split batch untuk Lab (1 ton → multiple 50kg samples)
     * 
     * @param Batch $parentBatch - Parent batch yang akan di-split
     * @param int $productCodeId - Product code untuk child batches
     * @param float $weightKg - Berat per child batch (biasanya 50kg)
     * @param int $userId - User ID yang melakukan split
     * @param int $splitCount - Jumlah child batches yang akan dibuat
     * @return array - Array of created child batches
     */
    public function splitBatch(Batch $parentBatch, int $productCodeId, float $weightKg, int $userId, int $splitCount = 1)
    {
        try {
            DB::beginTransaction();

            // Validate total split weight
            $totalSplitWeight = $weightKg * $splitCount;
            if (!$this->validateSplitWeight($parentBatch, $totalSplitWeight)) {
                throw new Exception("Total berat split ({$totalSplitWeight} kg) melebihi berat tersedia ({$parentBatch->current_weight} kg)");
            }

            // Validate split count
            if ($splitCount < 1 || $splitCount > 20) {
                throw new Exception("Split count harus antara 1-20 batches");
            }

            // Get last split sequence number untuk parent batch ini
            $lastSequence = $this->getLastSplitSequence($parentBatch->batch_code);

            $childBatches = [];
            $createdCount = 0;

            for ($i = 1; $i <= $splitCount; $i++) {
                // Calculate sequence number (continue from last split if any)
                $sequence = $lastSequence + $i;
                
                // Generate unique child batch code dengan retry mechanism
                $childBatchCode = $this->generateUniqueBatchCode($parentBatch->batch_code, $sequence);
                $childLotNumber = Batch::generateLotNumber($childBatchCode, 'A');

                // Create child batch
                $childBatch = Batch::create([
                    'batch_code' => $childBatchCode,
                    'lot_number' => $childLotNumber,
                    'product_code_id' => $productCodeId,
                    'parent_batch_id' => $parentBatch->id,
                    'is_split' => true,
                    'split_from_batch_id' => $parentBatch->id,
                    'split_ratio' => ($weightKg / $parentBatch->initial_weight),
                    'initial_weight' => $weightKg,
                    'current_weight' => $weightKg,
                    'weight_unit' => 'kg',
                    'status' => 'created',
                    'origin_location' => $parentBatch->current_location,
                    'current_location' => $parentBatch->current_location,
                    'process_stage' => 'warehouse', // Stay in warehouse until dispatched
                    'created_by' => $userId,
                    'current_owner_partner_id' => $parentBatch->current_owner_partner_id,
                ]);

                $childBatches[] = $childBatch;
                $createdCount++;

                // Create batch log for child
                BatchLog::create([
                    'batch_id' => $childBatch->id,
                    'action' => 'BATCH_CREATED_FROM_SPLIT',
                    'actor_user_id' => $userId,
                    'notes' => "Batch split dari {$parentBatch->batch_code} - Sample {$sequence} ({$weightKg} kg)",
                ]);
            }

            // Update parent batch weight
            $parentBatch->update([
                'current_weight' => $parentBatch->current_weight - $totalSplitWeight,
            ]);

            // Create batch log for parent
            BatchLog::create([
                'batch_id' => $parentBatch->id,
                'action' => 'BATCH_SPLIT',
                'actor_user_id' => $userId,
                'notes' => "Batch di-split menjadi {$createdCount} samples @ {$weightKg} kg (Total: {$totalSplitWeight} kg). Sisa: {$parentBatch->current_weight} kg",
            ]);

            DB::commit();

            return $childBatches;

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            // Check for duplicate entry error
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new Exception("Batch code sudah ada. Mohon refresh halaman dan coba lagi.");
            }
            
            throw new Exception("Database error: " . $e->getMessage());
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get last split sequence number untuk parent batch tertentu
     * Untuk mencegah collision jika user split multiple times
     */
    private function getLastSplitSequence(string $parentBatchCode): int
    {
        $lastSplit = Batch::where('batch_code', 'like', $parentBatchCode . '-S%')
            ->orderBy('batch_code', 'desc')
            ->first();

        if (!$lastSplit) {
            return 0;
        }

        // Extract sequence number from batch code
        // Example: B-20251211-002-MON-S05 → 5
        $matches = [];
        if (preg_match('/-S(\d+)$/', $lastSplit->batch_code, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * Generate unique batch code dengan retry mechanism
     */
    private function generateUniqueBatchCode(string $parentCode, int $sequence, int $maxRetries = 10): string
    {
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            $suffix = 'S' . str_pad($sequence + $attempt, 2, '0', STR_PAD_LEFT);
            $batchCode = $parentCode . '-' . $suffix;
            
            // Check if batch code already exists
            $exists = Batch::where('batch_code', $batchCode)->exists();
            
            if (!$exists) {
                return $batchCode;
            }
            
            $attempt++;
        }
        
        // Fallback: use timestamp untuk ensure uniqueness
        $timestamp = now()->format('His'); // HourMinuteSecond
        return $parentCode . '-S' . $timestamp;
    }

    /**
     * Validate split weight
     */
    private function validateSplitWeight(Batch $parentBatch, float $totalSplitWeight): bool
    {
        return $totalSplitWeight <= $parentBatch->current_weight;
    }

    /**
     * Get split history untuk parent batch
     */
    public function getSplitHistory(Batch $parentBatch)
    {
        return Batch::where('split_from_batch_id', $parentBatch->id)
            ->with('productCode')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get total weight yang sudah di-split dari parent batch
     */
    public function getTotalSplitWeight(Batch $parentBatch): float
    {
        return Batch::where('split_from_batch_id', $parentBatch->id)
            ->sum('current_weight');
    }

    /**
     * Check if batch bisa di-split lagi
     */
    public function canSplit(Batch $batch, float $requiredWeight): bool
    {
        // Minimum weight untuk split adalah 50kg
        if ($batch->current_weight < 50) {
            return false;
        }

        // Check if required weight available
        return $batch->current_weight >= $requiredWeight;
    }
}