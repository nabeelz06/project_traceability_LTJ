<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidWrite extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $casts = [
        'is_success' => 'boolean',
        'verified' => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function writer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}