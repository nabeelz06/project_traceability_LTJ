<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $casts = [
        'allowed_product_codes' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'partner_id');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class, 'current_owner_partner_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function getTypeLabel()
    {
        $labels = [
            'middlestream' => 'Pengolahan (Middlestream)',
            'downstream' => 'Industri Pengguna (Downstream)',
        ];
        return $labels[$this->type] ?? $this->type;
    }
}