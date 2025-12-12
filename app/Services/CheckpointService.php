<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Checkpoint;
use App\Models\BatchLog;
use Illuminate\Support\Facades\DB;
use Exception;

class CheckpointService
{
    /**
     * Record checkpoint untuk batch
     */
    public function recordCheckpoint(
        Batch $batch, 
        string $checkpointCode, 
        int $userId, 
        ?string $notes = null,
        ?array $gps = null, // GPS jadi optional
        ?string $photo = null
    ) {
        // Validate checkpoint sequence
        $this->validateCheckpointSequence($batch, $checkpointCode);

        DB::beginTransaction();
        try {
            // Create checkpoint record
            $checkpoint = Checkpoint::create([
                'batch_id' => $batch->id,
                'checkpoint_code' => $checkpointCode,
                'checkpoint_name' => $this->getCheckpointName($checkpointCode),
                'operator_user_id' => $userId,
                'gps_latitude' => $gps['latitude'] ?? null, // Handle null
                'gps_longitude' => $gps['longitude'] ?? null, // Handle null
                'notes' => $notes,
                'evidence_photo' => $photo,
                'recorded_at' => now(),
            ]);

            // Update batch
            $batch->update([
                'current_checkpoint' => $checkpointCode,
                'checkpoint_status' => 'completed',
                'process_stage' => $this->getProcessStage($checkpointCode),
            ]);

            // Create batch log
            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'CHECKPOINT_' . $checkpointCode,
                'actor_user_id' => $userId,
                'notes' => $notes ?? "Checkpoint {$checkpointCode} recorded",
            ]);

            DB::commit();
            return $checkpoint;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Validate checkpoint sequence
    public function validateCheckpointSequence(Batch $batch, string $newCheckpoint)
    {
        $validSequences = [
            'CP1' => ['pending', null],
            'CP2' => ['CP1'],
            'CP3' => ['CP2'],
            'CP4.1' => ['CP3'],
            'CP4.2' => ['CP3'],
            'CP4.3' => ['CP3'],
            'CP5' => ['CP4.3'],
        ];

        if (!isset($validSequences[$newCheckpoint])) {
            return false;
        }

        $allowedPrevious = $validSequences[$newCheckpoint];
        return in_array($batch->current_checkpoint, $allowedPrevious);
    }

    // Get checkpoint history for batch
    public function getCheckpointHistory(Batch $batch)
    {
        return Checkpoint::where('batch_id', $batch->id)
            ->with('operator')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Get checkpoint name
    private function getCheckpointName(string $code)
    {
        $names = [
            'CP1' => 'Dispatch Wet Process → Dry Process',
            'CP2' => 'Receive at Dry Process',
            'CP3' => 'Dispatch Dry Process → Warehouse',
            'CP4.1' => 'Receive Zircon at Warehouse',
            'CP4.2' => 'Receive Ilmenite at Warehouse',
            'CP4.3' => 'Receive Monasit at Warehouse',
            'CP5' => 'Receive at Lab/Project Plan',
        ];

        return $names[$code] ?? $code;
    }

    // Get process stage from checkpoint
    private function getProcessStage(string $checkpointCode)
    {
        $stages = [
            'CP1' => 'wet_process',
            'CP2' => 'dry_process',
            'CP3' => 'dry_process',
            'CP4.1' => 'warehouse',
            'CP4.2' => 'warehouse',
            'CP4.3' => 'warehouse',
            'CP5' => 'lab',
        ];

        return $stages[$checkpointCode] ?? 'unknown';
    }
}