<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'stage',
        'specifications',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship: ProductCode has many Batches
    // Foreign key di tabel batches: product_code_id
    // Owner key di tabel product_codes: id
    public function batches()
    {
        return $this->hasMany(Batch::class, 'product_code_id', 'id');
    }

    // Helper: Get stage label
    public function getStageLabel()
    {
        $stages = [
            'RAW' => 'Raw Material (Upstream)',
            'MID' => 'Mitra Pemurnian LTJ (Midstream)',
            'FINAL' => 'Produk Akhir (Downstream)',
        ];

        return $stages[$this->stage] ?? $this->stage;
    }

    // Helper: Check if stage is midstream
    public function isMidstream()
    {
        return $this->stage === 'MID';
    }

    // Accessor: Full code with description
    public function getFullNameAttribute()
    {
        return "{$this->code} - {$this->description}";
    }
}