<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'export_type',
        'destination',
        'manifest_number',
        'weight_kg',
        'operator_user_id',
        'exported_at',
        'notes',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'exported_at' => 'datetime',
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

    // Helper: Get export type label
    public function getExportTypeLabel()
    {
        $labels = [
            'export' => 'Ekspor',
            'domestic' => 'Penjualan Domestik',
        ];

        return $labels[$this->export_type] ?? $this->export_type;
    }

    // Helper: Get export type badge
    public function getExportTypeBadge()
    {
        $classes = [
            'export' => 'badge-primary',
            'domestic' => 'badge-success',
        ];

        return $classes[$this->export_type] ?? 'badge-secondary';
    }

    // Helper: Format weight
    public function getFormattedWeight()
    {
        return number_format($this->weight_kg, 2) . ' kg';
    }
}