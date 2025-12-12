<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'checkpoint_code',
        'checkpoint_name',
        'operator_user_id',
        'gps_latitude',
        'gps_longitude',
        'notes',
        'evidence_photo',
    ];

    protected $casts = [
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_user_id');
    }

    // Helper: Get checkpoint label
    public function getCheckpointLabel()
    {
        $labels = [
            'CP1' => 'Wet Process → Dry Process',
            'CP2' => 'Diterima di Dry Process',
            'CP3' => 'Dry Process → Warehouse',
            'CP4.1' => 'Warehouse - Zircon',
            'CP4.2' => 'Warehouse - Ilmenite',
            'CP4.3' => 'Warehouse - Monasit',
            'CP5' => 'Lab/Project Plan',
        ];

        return $labels[$this->checkpoint_code] ?? $this->checkpoint_code;
    }

    // Helper: Check if has GPS
    public function hasGps()
    {
        return !is_null($this->gps_latitude) && !is_null($this->gps_longitude);
    }

    // Helper: Get Google Maps URL
    public function getGoogleMapsUrl()
    {
        if ($this->hasGps()) {
            return "https://www.google.com/maps?q={$this->gps_latitude},{$this->gps_longitude}";
        }
        return null;
    }
}