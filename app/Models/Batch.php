<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Model Batch - Representasi batch LTJ dalam sistem traceability
 * Optimized dengan eager loading dan indexed queries
 */
class Batch extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $casts = [
        'is_ready' => 'boolean',
        'quality_data' => 'array',
        'initial_weight' => 'decimal:2',
        'current_weight' => 'decimal:2',
    ];

    // ============================================
    // RELATIONSHIPS (Optimized dengan indexes)
    // ============================================

    /**
     * Batch dibuat oleh user mana
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Batch saat ini dimiliki oleh partner mana
     */
    public function currentPartner()
    {
        return $this->belongsTo(Partner::class, 'current_owner_partner_id');
    }

    /**
     * Product code dari batch ini
     */
    public function productCode()
    {
        return $this->belongsTo(ProductCode::class, 'product_code', 'code');
    }

    /**
     * Parent batch (untuk child batch)
     */
    public function parentBatch()
    {
        return $this->belongsTo(Batch::class, 'parent_batch_id');
    }

    /**
     * Child batches (untuk parent batch)
     */
    public function childBatches()
    {
        return $this->hasMany(Batch::class, 'parent_batch_id');
    }

    /**
     * Logs/history dari batch ini
     */
    public function logs()
    {
        return $this->hasMany(BatchLog::class, 'batch_id')->orderBy('created_at', 'desc');
    }

    /**
     * Documents terkait batch
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'batch_id');
    }

    /**
     * RFID writes untuk batch ini
     */
    public function rfidWrites()
    {
        return $this->hasMany(RfidWrite::class, 'batch_id');
    }

    /**
     * Shipments terkait batch
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'batch_id');
    }

    // ============================================
    // SCOPES (Query optimization)
    // ============================================

    /**
     * Scope: Hanya batch induk (tidak punya parent)
     */
    public function scopeParentOnly(Builder $query)
    {
        return $query->whereNull('parent_batch_id');
    }

    /**
     * Scope: Hanya child batch (punya parent)
     */
    public function scopeChildOnly(Builder $query)
    {
        return $query->whereNotNull('parent_batch_id');
    }

    /**
     * Scope: Batch dengan status tertentu
     */
    public function scopeByStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Batch aktif (belum delivered)
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereNotIn('status', ['delivered', 'quarantine']);
    }

    /**
     * Scope: Batch yang siap dikirim
     */
    public function scopeReadyToShip(Builder $query)
    {
        return $query->where('status', 'ready_to_ship')
            ->where('is_ready', true);
    }

    /**
     * Scope: Batch milik partner tertentu
     */
    public function scopeOwnedBy(Builder $query, int $partnerId)
    {
        return $query->where('current_owner_partner_id', $partnerId);
    }

    /**
     * Scope: Search batch (optimized)
     */
    public function scopeSearch(Builder $query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function($q) use ($term) {
            $q->where('batch_code', 'like', "%{$term}%")
              ->orWhere('lot_number', 'like', "%{$term}%")
              ->orWhere('container_code', 'like', "%{$term}%")
              ->orWhere('rfid_tag_uid', 'like', "%{$term}%");
        });
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Generate batch code unik dengan format: B-YYYYMMDD-XXX
     */
    public static function generateBatchCode()
    {
        $date = now()->format('Ymd');
        $prefix = 'B-' . $date . '-';
        
        // Gunakan lock untuk mencegah race condition
        $lastBatch = static::where('batch_code', 'like', $prefix . '%')
            ->lockForUpdate()
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
     * Generate lot number dari batch code
     */
    public static function generateLotNumber(string $batchCode)
    {
        return 'LOT-' . $batchCode;
    }

    /**
     * Get status label dalam bahasa Indonesia
     */
    public function getStatusLabel()
    {
        $labels = [
            'created' => 'Dibuat',
            'ready_to_ship' => 'Siap Kirim',
            'shipped' => 'Dalam Pengiriman',
            'received' => 'Diterima',
            'processed' => 'Diproses',
            'delivered' => 'Terkirim',
            'quarantine' => 'Karantina',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status badge class untuk UI
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            'created' => 'badge-secondary',
            'ready_to_ship' => 'badge-primary',
            'shipped' => 'badge-info',
            'received' => 'badge-success',
            'processed' => 'badge-warning',
            'delivered' => 'badge-success',
            'quarantine' => 'badge-danger',
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Check apakah batch adalah parent (induk)
     */
    public function isParent()
    {
        return is_null($this->parent_batch_id);
    }

    /**
     * Check apakah batch adalah child (turunan)
     */
    public function isChild()
    {
        return !is_null($this->parent_batch_id);
    }

    /**
     * Check apakah batch punya RFID tag
     */
    public function hasRfidTag()
    {
        return !is_null($this->rfid_tag_uid);
    }

    /**
     * Check apakah batch bisa diedit
     */
    public function canBeEdited()
    {
        return in_array($this->status, ['created', 'ready_to_ship']);
    }

    /**
     * Check apakah batch bisa dihapus
     */
    public function canBeDeleted()
    {
        return $this->status === 'created' && $this->childBatches()->count() === 0;
    }

    /**
     * Check apakah batch bisa di-checkout
     */
    public function canBeCheckedOut()
    {
        return in_array($this->status, ['ready_to_ship', 'created', 'received']) 
            && $this->is_ready;
    }

    /**
     * Check apakah batch bisa di-checkin
     */
    public function canBeCheckedIn()
    {
        return $this->status === 'shipped';
    }

    /**
     * Get total child batches
     */
    public function getTotalChildrenAttribute()
    {
        return $this->childBatches()->count();
    }

    /**
     * Get sisa berat yang belum diproses (untuk parent batch)
     */
    public function getRemainingWeightAttribute()
    {
        if ($this->isChild()) {
            return 0;
        }

        $totalChildWeight = $this->childBatches()->sum('initial_weight');
        return max(0, $this->initial_weight - $totalChildWeight);
    }

    /**
     * Get percentage berat yang sudah diproses
     */
    public function getProcessedPercentageAttribute()
    {
        if ($this->isChild() || $this->initial_weight == 0) {
            return 0;
        }

        $totalChildWeight = $this->childBatches()->sum('initial_weight');
        return min(100, ($totalChildWeight / $this->initial_weight) * 100);
    }

    /**
     * Get latest log entry
     */
    public function getLatestLogAttribute()
    {
        return $this->logs()->first();
    }

    /**
     * Get formatted weight dengan satuan
     */
    public function getFormattedWeightAttribute()
    {
        return number_format($this->current_weight, 2) . ' ' . $this->weight_unit;
    }

    /**
     * Get age dalam hari
     */
    public function getAgeInDaysAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    // ============================================
    // BOOT METHOD (Auto-generate codes)
    // ============================================

    protected static function boot()
    {
        parent::boot();

        // Auto-generate batch code saat creating
        static::creating(function ($batch) {
            if (empty($batch->batch_code)) {
                $batch->batch_code = static::generateBatchCode();
            }
            if (empty($batch->lot_number)) {
                $batch->lot_number = static::generateLotNumber($batch->batch_code);
            }
        });
    }
}