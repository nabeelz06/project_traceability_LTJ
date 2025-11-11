<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchLog;
use Illuminate\Support\Facades\DB;

class TraceabilityService
{
    public function buildFullTree(Batch $batch)
    {
        $root = $this->findRootBatch($batch);
        return $this->buildTreeRecursive($root);
    }

    private function findRootBatch(Batch $batch)
    {
        while ($batch->parent_batch_id) {
            $batch = $batch->parentBatch;
        }
        return $batch;
    }

    private function buildTreeRecursive(Batch $batch, $depth = 0)
    {
        $tree = [
            'batch' => $batch->load(['productCode', 'creator', 'currentPartner']),
            'depth' => $depth,
            'children' => []
        ];

        foreach ($batch->childBatches as $child) {
            $tree['children'][] = $this->buildTreeRecursive($child, $depth + 1);
        }

        return $tree;
    }

    public function canCreateChild(Batch $parentBatch)
    {
        if ($parentBatch->status !== 'received') {
            return [
                'can' => false,
                'reason' => 'Batch harus dalam status "received" untuk membuat batch turunan'
            ];
        }
        return ['can' => true];
    }

    public function createChildBatch($parentBatch, $data, $userId)
    {
        DB::beginTransaction();
        try {
            $existingChildrenWeight = $parentBatch->childBatches()
                ->sum('estimated_weight'); // Ganti nama kolom jika salah
            
            $totalWeight = $existingChildrenWeight + $data['estimated_weight'];
            
            if ($totalWeight > $parentBatch->estimated_weight) {
                throw new \Exception('Total berat child batch melebihi parent batch');
            }

            // Asumsi fungsi ini ada di Model Batch
            $batchNumber = Batch::generateBatchNumber(); 
            $lotNumber = Batch::generateLotNumber($batchNumber);

            $childBatch = Batch::create([
                'batch_number' => $batchNumber,
                'lot_number' => $lotNumber,
                'product_code_id' => $data['product_code_id'],
                'parent_batch_id' => $parentBatch->id,
                'container_number' => $data['container_number'],
                'estimated_weight' => $data['estimated_weight'],
                'weight_unit' => $data['weight_unit'] ?? 'kg',
                'origin_location' => $parentBatch->currentPartner->name,
                'created_by_user_id' => $userId,
                'current_partner_id' => $parentBatch->current_partner_id,
                'status' => 'created',
                'notes' => $data['notes'] ?? null,
            ]);

            BatchLog::create([
                'batch_id' => $childBatch->id,
                'action' => 'created',
                'new_status' => 'created',
                'actor_user_id' => $userId,
                'notes' => 'Batch turunan dibuat dari parent: ' . $parentBatch->batch_number,
            ]);

            BatchLog::create([
                'batch_id' => $parentBatch->id,
                'action' => 'child_created',
                'actor_user_id' => $userId,
                'notes' => 'Batch turunan dibuat: ' . $childBatch->batch_number,
            ]);

            $newTotalWeight = $parentBatch->childBatches()->sum('estimated_weight');
            if ($newTotalWeight >= $parentBatch->estimated_weight) {
                $parentBatch->update(['status' => 'processed']);
                
                BatchLog::create([
                    'batch_id' => $parentBatch->id,
                    'action' => 'status_updated',
                    'previous_status' => 'received',
                    'new_status' => 'processed',
                    'actor_user_id' => $userId,
                    'notes' => 'Parent batch selesai diproses, semua child batch sudah dibuat',
                ]);
            }

            DB::commit();
            return [
                'success' => true,
                'batch' => $childBatch
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function processCheckout(Batch $batch, $data, $userId)
    {
        DB::beginTransaction();
        try {
            $oldStatus = $batch->status;
            
            $batch->update([
                'status' => 'shipped',
                'shipping_date' => now(), // Ganti nama kolom jika salah
            ]);

            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'checked_out',
                'previous_status' => $oldStatus,
                'new_status' => 'shipped',
                'actor_user_id' => $userId,
                'device_id' => $data['device_id'] ?? null,
                'gps_location' => $data['gps_location'] ?? null,
                'photo_evidence' => $data['photo_path'] ?? null,
                'notes' => $data['notes'] ?? 'Batch dikirim',
            ]);

            DB::commit();
            return ['success' => true];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function processCheckin(Batch $batch, $partnerId, $data, $userId)
    {
        DB::beginTransaction();
        try {
            $oldStatus = $batch->status;
            
            $batch->update([
                'status' => 'received',
                'current_partner_id' => $partnerId,
            ]);

            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'checked_in',
                'previous_status' => $oldStatus,
                'new_status' => 'received',
                'actor_user_id' => $userId,
                'device_id' => $data['device_id'] ?? null,
                'notes' => $data['notes'] ?? 'Batch diterima',
            ]);

            DB::commit();
            return ['success' => true];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}