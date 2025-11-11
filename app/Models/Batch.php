<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'is_ready' => 'boolean',
        'quality_data' => 'array',
        'history_log' => 'array',
    ];

    // === RELASI YANG DIBUTUHKAN DASHBOARD & SERVICE ===

    /**
     * Relasi: Batch ini dibuat oleh User mana.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Relasi: Batch ini dimiliki oleh Partner mana.
     */
    public function currentPartner()
    {
        return $this->belongsTo(Partner::class, 'current_owner_partner_id');
    }

    /**
     * Relasi: Apa Product Code dari batch ini.
     */
    public function productCode()
    {
        return $this->belongsTo(ProductCode::class, 'product_code', 'code');
    }

    /**
     * Relasi: Batch ini adalah child dari batch mana.
     */
    public function parentBatch()
    {
        return $this->belongsTo(Batch::class, 'parent_batch_id');
    }

    /**
     * Relasi: Batch ini memiliki child batch apa saja.
     */
    public function childBatches()
    {
        return $this->hasMany(Batch::class, 'parent_batch_id');
    }
}