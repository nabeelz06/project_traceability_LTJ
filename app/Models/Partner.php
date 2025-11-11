<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;
    protected $guarded = []; // Mengizinkan mass assignment dari seeder
    
    // Mengubah JSON string dari DB menjadi array
    protected $casts = [
        'allowed_product_codes' => 'array',
    ];

    /**
     * Relasi: Partner ini dimiliki oleh User mana saja.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'partner_id');
    }

    /**
     * Relasi: Batch mana saja yang saat ini dimiliki oleh partner ini.
     */
    public function batches()
    {
        return $this->hasMany(Batch::class, 'current_owner_partner_id');
    }

    /**
     * PERBAIKAN: Scope untuk filter partner yang sudah 'approved'.
     * Ini akan memperbaiki error Anda. 
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}