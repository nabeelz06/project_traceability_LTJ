<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCode extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    public function batches()
    {
        return $this->hasMany(Batch::class, 'product_code', 'code');
    }

    public function getStageLabel()
    {
        $labels = [
            'RAW' => 'Bahan Mentah',
            'MID' => 'Hasil Pengolahan',
            'FINAL' => 'Produk Akhir',
        ];
        return $labels[$this->stage] ?? $this->stage;
    }
}