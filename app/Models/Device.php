<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTypeLabel()
    {
        $labels = [
            'rfid_reader' => 'RFID Reader',
            'rfid_writer' => 'RFID Writer',
            'scanner' => 'Scanner',
            'handheld' => 'Handheld Device',
        ];
        return $labels[$this->type] ?? $this->type;
    }
}