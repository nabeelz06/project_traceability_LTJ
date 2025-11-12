<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function getTypeLabel()
    {
        $labels = [
            'lab_certificate' => 'Sertifikat Lab',
            'bast' => 'BAST',
            'surat_jalan' => 'Surat Jalan',
            'photo' => 'Foto',
            'other' => 'Lainnya',
        ];
        return $labels[$this->type] ?? $this->type;
    }
}