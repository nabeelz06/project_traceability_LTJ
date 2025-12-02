<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Batch extends Model
{
    use HasFactory;
    
    // Izinkan mass assignment untuk semua kolom
    protected $guarded = [];
    
    // Casting tipe data
    protected $casts = [
        'is_ready' => 'boolean',
        'quality_data' => 'array',
        'history_log' => 'array',
        'initial_weight' => 'decimal:2',
        'current_weight' => 'decimal:2',
        'tonase' => 'decimal:3',
        'konsentrat_persen' => 'decimal:2',
        'massa_ltj_kg' => 'decimal:2',
        'nd_content' => 'decimal:2',
        'y_content' => 'decimal:2',
        'ce_content' => 'decimal:2',
        'la_content' => 'decimal:2',
        'pr_content' => 'decimal:2',
        // Traceability fields
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'evidence_photos' => 'array',
        'evidence_videos' => 'array',
        'evidence_documents' => 'array',
        'energy_input_kwh' => 'decimal:2',
        'water_consumption_liter' => 'decimal:2',
        'process_temperature_celsius' => 'decimal:2',
        'process_ph' => 'decimal:2',
        'efficiency_percent' => 'decimal:2',
        'last_gps_update' => 'datetime',
        'qr_generated_at' => 'datetime',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentPartner()
    {
        return $this->belongsTo(Partner::class, 'current_owner_partner_id');
    }

    public function productCode()
    {
        return $this->belongsTo(ProductCode::class, 'product_code_id');
    }

    public function parentBatch()
    {
        return $this->belongsTo(Batch::class, 'parent_batch_id');
    }

    public function childBatches()
    {
        return $this->hasMany(Batch::class, 'parent_batch_id');
    }

    public function logs()
    {
        return $this->hasMany(BatchLog::class, 'batch_id')->orderBy('created_at', 'desc');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'batch_id');
    }

    public function rfidWrites()
    {
        return $this->hasMany(RfidWrite::class, 'batch_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'batch_id');
    }

    // ============================================
    // SCOPES (Query Filters)
    // ============================================

    public function scopeParentOnly(Builder $query)
    {
        return $query->whereNull('parent_batch_id');
    }

    public function scopeChildOnly(Builder $query)
    {
        return $query->whereNotNull('parent_batch_id');
    }

    public function scopeByStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNotIn('status', ['delivered', 'quarantine']);
    }

    public function scopeReadyToShip(Builder $query)
    {
        return $query->where('status', 'ready_to_ship')
            ->where('is_ready', true);
    }

    public function scopeOwnedBy(Builder $query, int $partnerId)
    {
        return $query->where('current_owner_partner_id', $partnerId);
    }

    public function scopeSearch(Builder $query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function($q) use ($term) {
            $q->where('batch_code', 'like', "%{$term}%")
              ->orWhere('lot_number', 'like', "%{$term}%")
              ->orWhere('container_code', 'like', "%{$term}%")
              ->orWhere('rfid_tag_uid', 'like', "%{$term}%")
              ->orWhere('rfid_tag_full', 'like', "%{$term}%");
        });
    }

    // ============================================
    // BATCH CODE & LOT NUMBER GENERATION
    // ============================================

    /**
     * Generate kode batch otomatis: B-YYYYMMDD-XXX
     * Format sesuai dengan standar traceability PT Timah
     */
    public static function generateBatchCode()
    {
        $date = now()->format('Ymd');
        $prefix = 'B-' . $date . '-';
        
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
     * Format: L-YYYYMMDD-XXX-A
     * Suffix A bisa berubah jika 1 batch dipecah jadi multiple lots
     */
    public static function generateLotNumber(string $batchCode, string $suffix = 'A')
    {
        // Extract date dan sequence dari batch code
        // B-20251202-001 → L-20251202-001-A
        $parts = explode('-', $batchCode);
        
        if (count($parts) >= 3) {
            $date = $parts[1];      // 20251202
            $sequence = $parts[2];  // 001
            return "L-{$date}-{$sequence}-{$suffix}";
        }
        
        // Fallback jika format tidak sesuai
        return 'L-' . $batchCode . '-A';
    }

    // ============================================
    // RFID TAG GENERATION
    // ============================================

    /**
     * Generate RFID Tag lengkap sesuai standar nasional
     * Format: [STAGE]-[MATERIAL]-[SPEC]-[BATCH]-[LOT]-[CONTAINER]
     * Contoh: TIM-MON-RAW-B20251202001-L20251202001A-KTMH1234
     */
    public static function generateRfidTag($productCode, $batchCode, $lotNumber, $containerCode)
    {
        // Extract stage, material, spec dari product code
        $parts = explode('-', $productCode);
        $stage = $parts[0] ?? 'TIM';      // TIM/MID/DSW
        $material = $parts[1] ?? 'MON';   // MON/ND/Y/CE/LA/PR/MX
        $spec = $parts[2] ?? 'RAW';       // RAW/CON/OXI99/OXI999/MET
        
        // Remove hyphens dari batch, lot, container untuk RFID tag
        $batchPart = str_replace('-', '', $batchCode);        // B20251202001
        $lotPart = str_replace('-', '', $lotNumber);           // L20251202001A
        $containerPart = str_replace('-', '', $containerCode); // KTMH1234
        
        return "{$stage}-{$material}-{$spec}-{$batchPart}-{$lotPart}-{$containerPart}";
    }

    /**
     * Accessor untuk mendapatkan RFID tag full
     * Digunakan saat display di view atau API response
     */
    public function getRfidTagFullGeneratedAttribute()
    {
        if ($this->productCode && $this->batch_code && $this->lot_number && $this->container_code) {
            return self::generateRfidTag(
                $this->productCode->code, 
                $this->batch_code, 
                $this->lot_number, 
                $this->container_code
            );
        }
        
        return $this->rfid_tag_full;
    }

    // ============================================
    // GPS & LOCATION HELPERS
    // ============================================

    /**
     * Check apakah batch memiliki koordinat GPS
     */
    public function hasGpsCoordinates()
    {
        return !is_null($this->current_latitude) && !is_null($this->current_longitude);
    }

    /**
     * Get Google Maps URL untuk koordinat saat ini
     */
    public function getGoogleMapsUrlAttribute()
    {
        if ($this->hasGpsCoordinates()) {
            return "https://www.google.com/maps?q={$this->current_latitude},{$this->current_longitude}";
        }
        return null;
    }

    /**
     * Get formatted GPS coordinates
     */
    public function getFormattedGpsAttribute()
    {
        if ($this->hasGpsCoordinates()) {
            return "{$this->current_latitude}, {$this->current_longitude}";
        }
        return 'GPS belum tercatat';
    }

    // ============================================
    // EVIDENCE HELPERS
    // ============================================

    /**
     * Get total jumlah evidence yang diupload
     */
    public function getTotalEvidenceCountAttribute()
    {
        $photos = is_array($this->evidence_photos) ? count($this->evidence_photos) : 0;
        $videos = is_array($this->evidence_videos) ? count($this->evidence_videos) : 0;
        $documents = is_array($this->evidence_documents) ? count($this->evidence_documents) : 0;
        
        return $photos + $videos + $documents;
    }

    /**
     * Check apakah batch memiliki evidence
     */
    public function hasEvidence()
    {
        return $this->total_evidence_count > 0;
    }

    // ============================================
    // CALCULATION HELPERS
    // ============================================

    /**
     * Auto-calculate Massa LTJ jika belum diisi manual
     * Formula: massa_ltj_kg = (tonase × 1000) × (konsentrat_persen / 100)
     */
    public static function calculateMassaLtj($tonase, $konsentratPersen)
    {
        if (empty($tonase) || empty($konsentratPersen)) {
            return 0;
        }
        
        // Konversi tonase ke kg
        $weightInKg = $tonase * 1000;
        
        // Hitung massa LTJ
        return round($weightInKg * ($konsentratPersen / 100), 2);
    }

    /**
     * Mendapatkan total kandungan 5 unsur LTJ
     */
    public function getTotalLtjContentAttribute()
    {
        return ($this->nd_content ?? 0) + 
               ($this->y_content ?? 0) + 
               ($this->ce_content ?? 0) + 
               ($this->la_content ?? 0) + 
               ($this->pr_content ?? 0);
    }

    /**
     * Format tampilan kandungan LTJ untuk dashboard
     */
    public function getLtjContentSummaryAttribute()
    {
        $contents = [];
        
        if ($this->nd_content > 0) $contents[] = "Nd: {$this->nd_content}%";
        if ($this->y_content > 0) $contents[] = "Y: {$this->y_content}%";
        if ($this->ce_content > 0) $contents[] = "Ce: {$this->ce_content}%";
        if ($this->la_content > 0) $contents[] = "La: {$this->la_content}%";
        if ($this->pr_content > 0) $contents[] = "Pr: {$this->pr_content}%";
        
        return implode(', ', $contents) ?: '-';
    }

    // ============================================
    // STATUS & LABEL HELPERS
    // ============================================

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

    // ============================================
    // PERMISSION CHECKERS
    // ============================================

    public function isParent()
    {
        return is_null($this->parent_batch_id);
    }

    public function isChild()
    {
        return !is_null($this->parent_batch_id);
    }

    public function hasRfidTag()
    {
        return !is_null($this->rfid_tag_uid) || !is_null($this->rfid_tag_full);
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['created', 'ready_to_ship']);
    }

    public function canBeDeleted()
    {
        return $this->status === 'created' && $this->childBatches()->count() === 0;
    }

    public function canBeCheckedOut()
    {
        return in_array($this->status, ['ready_to_ship', 'created', 'received']) 
            && $this->is_ready;
    }

    public function canBeCheckedIn()
    {
        return $this->status === 'shipped';
    }

    // ============================================
    // AGGREGATE ATTRIBUTES
    // ============================================

    public function getTotalChildrenAttribute()
    {
        return $this->childBatches()->count();
    }

    public function getRemainingWeightAttribute()
    {
        if ($this->isChild()) {
            return 0;
        }

        $totalChildWeight = $this->childBatches()->sum('initial_weight');
        return max(0, $this->initial_weight - $totalChildWeight);
    }

    public function getProcessedPercentageAttribute()
    {
        if ($this->isChild() || $this->initial_weight == 0) {
            return 0;
        }

        $totalChildWeight = $this->childBatches()->sum('initial_weight');
        return min(100, ($totalChildWeight / $this->initial_weight) * 100);
    }

    public function getLatestLogAttribute()
    {
        return $this->logs()->first();
    }

    public function getFormattedWeightAttribute()
    {
        return number_format($this->current_weight, 2) . ' ' . $this->weight_unit;
    }

    public function getAgeInDaysAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    // ============================================
    // BOOT METHOD - AUTO GENERATION
    // ============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            // Generate batch code jika belum ada
            if (empty($batch->batch_code)) {
                $batch->batch_code = static::generateBatchCode();
            }
            
            // Generate lot number
            if (empty($batch->lot_number)) {
                $batch->lot_number = static::generateLotNumber($batch->batch_code);
            }
            
            // Auto-calculate massa_ltj_kg jika belum diisi manual
            if (empty($batch->massa_ltj_kg) && !empty($batch->tonase) && !empty($batch->konsentrat_persen)) {
                $batch->massa_ltj_kg = static::calculateMassaLtj($batch->tonase, $batch->konsentrat_persen);
            }
            
            // Set current_owner_partner_id dari user yang login jika belum ada
            if (empty($batch->current_owner_partner_id) && auth()->check()) {
                $batch->current_owner_partner_id = auth()->user()->partner_id;
            }
        });
        
        static::created(function ($batch) {
            // Auto-generate RFID tag setelah batch dibuat (saat sudah punya productCode relationship)
            if (empty($batch->rfid_tag_full) && $batch->productCode && $batch->container_code) {
                $batch->rfid_tag_full = static::generateRfidTag(
                    $batch->productCode->code,
                    $batch->batch_code,
                    $batch->lot_number,
                    $batch->container_code
                );
                $batch->saveQuietly(); // Save tanpa trigger event lagi
            }
        });
    }
}