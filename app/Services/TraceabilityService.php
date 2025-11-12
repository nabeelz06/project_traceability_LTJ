<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TraceabilityService - Service utama untuk mengelola logika bisnis traceability
 * Menangani: Batch lifecycle, Check-in/out, Child batch creation, Traceability tree
 */
class TraceabilityService
{
    /**
     * Build full traceability tree dari root hingga semua child
     */
    public function buildFullTree(Batch $batch)
    {
        $root = $this->findRootBatch($batch);
        return $this->buildTreeRecursive($root);
    }

    /**
     * Cari root batch (parent paling atas)
     */
    private function findRootBatch(Batch $batch)
    {
        while ($batch->parent_batch_id) {
            $batch = $batch->parentBatch;
        }
        return $batch;
    }

    /**
     * Build tree secara recursive dengan semua child-nya
     */
    private function buildTreeRecursive(Batch $batch, $depth = 0)
    {
        $tree = [
            'batch' => $batch->load(['productCode', 'creator', 'currentPartner']),
            'depth' => $depth,
            'children' => [],
            'logs' => $batch->logs()->orderBy('created_at', 'desc')->get(),
        ];

        foreach ($batch->childBatches as $child) {
            $tree['children'][] = $this->buildTreeRecursive($child, $depth + 1);
        }

        return $tree;
    }

    /**
     * Validasi apakah batch bisa membuat child
     */
    public function canCreateChild(Batch $parentBatch)
    {
        // Hanya batch yang sudah diterima yang bisa diproses
        if ($parentBatch->status !== 'received') {
            return [
                'can' => false,
                'reason' => 'Batch harus dalam status "received" untuk membuat batch turunan. Status saat ini: ' . $parentBatch->getStatusLabel()
            ];
        }

        // Pastikan parent belum selesai diproses
        if ($parentBatch->status === 'processed') {
            return [
                'can' => false,
                'reason' => 'Batch sudah selesai diproses dan tidak bisa membuat child baru'
            ];
        }

        return ['can' => true];
    }

    /**
     * Buat batch turunan (child) dari batch induk
     */
    public function createChildBatch(Batch $parentBatch, array $data, int $userId)
    {
        DB::beginTransaction();
        try {
            // Validasi total berat child tidak melebihi parent
            $existingChildrenWeight = $parentBatch->childBatches()->sum('initial_weight');
            $totalWeight = $existingChildrenWeight + $data['initial_weight'];
            
            if ($totalWeight > $parentBatch->initial_weight) {
                throw new \Exception('Total berat batch turunan (' . $totalWeight . ' ' . $data['weight_unit'] . ') melebihi berat batch induk (' . $parentBatch->initial_weight . ' ' . $parentBatch->weight_unit . ')');
            }

            // Generate batch code dan lot number
            $batchCode = $this->generateBatchCode();
            $lotNumber = $this->generateLotNumber($batchCode);

            // Buat child batch
            $childBatch = Batch::create([
                'batch_code' => $batchCode,
                'lot_number' => $lotNumber,
                'product_code' => $data['product_code'],
                'parent_batch_id' => $parentBatch->id,
                'container_code' => $data['container_code'],
                'initial_weight' => $data['initial_weight'],
                'current_weight' => $data['initial_weight'],
                'weight_unit' => $data['weight_unit'] ?? 'kg',
                'current_location' => $parentBatch->current_location,
                'created_by_user_id' => $userId,
                'current_owner_partner_id' => $parentBatch->current_owner_partner_id,
                'status' => 'created',
                'quality_data' => $data['quality_data'] ?? null,
            ]);

            // Log pembuatan child batch
            BatchLog::create([
                'batch_id' => $childBatch->id,
                'action' => 'created',
                'previous_status' => null,
                'new_status' => 'created',
                'actor_user_id' => $userId,
                'notes' => 'Batch turunan dibuat dari parent: ' . $parentBatch->batch_code,
            ]);

            // Log di parent batch
            BatchLog::create([
                'batch_id' => $parentBatch->id,
                'action' => 'child_created',
                'previous_status' => $parentBatch->status,
                'new_status' => $parentBatch->status,
                'actor_user_id' => $userId,
                'notes' => 'Batch turunan dibuat: ' . $childBatch->batch_code . ' (' . $data['initial_weight'] . ' ' . $data['weight_unit'] . ')',
            ]);

            // Cek apakah semua berat parent sudah habis diproses
            $newTotalWeight = $parentBatch->childBatches()->sum('initial_weight');
            if ($newTotalWeight >= $parentBatch->initial_weight) {
                $parentBatch->update(['status' => 'processed']);
                
                BatchLog::create([
                    'batch_id' => $parentBatch->id,
                    'action' => 'status_updated',
                    'previous_status' => 'received',
                    'new_status' => 'processed',
                    'actor_user_id' => $userId,
                    'notes' => 'Parent batch selesai diproses. Total berat child: ' . $newTotalWeight . ' ' . $parentBatch->weight_unit,
                ]);
            }

            DB::commit();
            return [
                'success' => true,
                'batch' => $childBatch,
                'message' => 'Batch turunan berhasil dibuat: ' . $childBatch->batch_code
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating child batch: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Proses checkout (pengiriman batch)
     */
    public function processCheckout(Batch $batch, array $data, int $userId)
    {
        DB::beginTransaction();
        try {
            // Validasi status batch
            if (!in_array($batch->status, ['ready_to_ship', 'created', 'received'])) {
                throw new \Exception('Batch tidak dapat dikirim. Status saat ini: ' . $batch->getStatusLabel());
            }

            $oldStatus = $batch->status;
            
            // Update batch status
            $batch->update([
                'status' => 'shipped',
            ]);

            // Log checkout
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
            return [
                'success' => true,
                'message' => 'Batch ' . $batch->batch_code . ' berhasil di-checkout'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing checkout: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Proses checkin (penerimaan batch)
     */
    public function processCheckin(Batch $batch, $partnerId, array $data, int $userId)
    {
        DB::beginTransaction();
        try {
            // Validasi status batch
            if ($batch->status !== 'shipped') {
                throw new \Exception('Batch tidak dalam status pengiriman. Status saat ini: ' . $batch->getStatusLabel());
            }

            $oldStatus = $batch->status;
            
            // Update batch
            $batch->update([
                'status' => 'received',
                'current_owner_partner_id' => $partnerId,
                'current_location' => Partner::find($partnerId)->name ?? $batch->current_location,
            ]);

            // Log checkin
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
            return [
                'success' => true,
                'message' => 'Batch ' . $batch->batch_code . ' berhasil di-checkin'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing checkin: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Mark batch as delivered (final destination)
     */
    public function markAsDelivered(Batch $batch, int $userId, array $data = [])
    {
        DB::beginTransaction();
        try {
            $oldStatus = $batch->status;
            
            $batch->update([
                'status' => 'delivered',
            ]);

            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'delivered',
                'previous_status' => $oldStatus,
                'new_status' => 'delivered',
                'actor_user_id' => $userId,
                'notes' => $data['notes'] ?? 'Batch telah sampai di tujuan akhir',
            ]);

            DB::commit();
            return [
                'success' => true,
                'message' => 'Batch berhasil ditandai sebagai delivered'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate batch code dengan format: B-YYYYMMDD-XXX
     */
    private function generateBatchCode()
    {
        $date = now()->format('Ymd');
        $prefix = 'B-' . $date . '-';
        
        // Cari batch terakhir hari ini
        $lastBatch = Batch::where('batch_code', 'like', $prefix . '%')
            ->orderBy('batch_code', 'desc')
            ->first();
        
        if ($lastBatch) {
            $lastNumber = (int) substr($lastBatch->batch_code, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate lot number berdasarkan batch code
     */
    private function generateLotNumber($batchCode)
    {
        // Format: LOT-B-YYYYMMDD-XXX
        return 'LOT-' . $batchCode;
    }

    /**
     * Get full chain/history dari batch
     */
    public function getFullChain(Batch $batch)
    {
        $chain = [];
        
        // Get parent chain (backward)
        $current = $batch;
        while ($current->parent_batch_id) {
            $current = $current->parentBatch;
            array_unshift($chain, $current);
        }
        
        // Add current batch
        $chain[] = $batch;
        
        // Get child chain (forward)
        $this->addChildrenToChain($batch, $chain);
        
        return $chain;
    }

    /**
     * Recursive add children to chain
     */
    private function addChildrenToChain(Batch $batch, &$chain)
    {
        foreach ($batch->childBatches as $child) {
            $chain[] = $child;
            $this->addChildrenToChain($child, $chain);
        }
    }

    /**
     * Search batches dengan berbagai kriteria
     */
    public function searchBatches(array $criteria)
    {
        $query = Batch::with(['productCode', 'creator', 'currentPartner']);

        if (!empty($criteria['batch_code'])) {
            $query->where('batch_code', 'like', '%' . $criteria['batch_code'] . '%');
        }

        if (!empty($criteria['lot_number'])) {
            $query->where('lot_number', 'like', '%' . $criteria['lot_number'] . '%');
        }

        if (!empty($criteria['container_code'])) {
            $query->where('container_code', 'like', '%' . $criteria['container_code'] . '%');
        }

        if (!empty($criteria['rfid_tag_uid'])) {
            $query->where('rfid_tag_uid', $criteria['rfid_tag_uid']);
        }

        if (!empty($criteria['product_code'])) {
            $query->where('product_code', $criteria['product_code']);
        }

        if (!empty($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}