<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'action',
        'operator_user_id',
        'stockpile_location',
        'notes',
    ];

    protected $casts = [
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

    // Helper: Get action label
    public function getActionLabel()
    {
        $labels = [
            'stocked' => 'Disimpan ke Stockpile',
            'retrieved' => 'Diambil dari Stockpile',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    // Helper: Get action badge class
    public function getActionBadgeClass()
    {
        $classes = [
            'stocked' => 'badge-warning',
            'retrieved' => 'badge-success',
        ];

        return $classes[$this->action] ?? 'badge-secondary';
    }
}