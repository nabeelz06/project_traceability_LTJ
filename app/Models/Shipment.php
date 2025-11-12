<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $casts = [
        'scheduled_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function assignedOperator()
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }

    public function destinationPartner()
    {
        return $this->belongsTo(Partner::class, 'destination_partner_id');
    }

    public function getStatusLabel()
    {
        $labels = [
            'scheduled' => 'Dijadwalkan',
            'in_transit' => 'Dalam Perjalanan',
            'delivered' => 'Terkirim',
            'cancelled' => 'Dibatalkan',
        ];
        return $labels[$this->status] ?? $this->status;
    }
}