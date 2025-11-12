<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchLog extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function getActionLabel()
    {
        $labels = [
            'created' => 'Dibuat',
            'checked_out' => 'Check-Out',
            'checked_in' => 'Check-In',
            'status_updated' => 'Status Diperbarui',
            'child_created' => 'Batch Turunan Dibuat',
            'corrected' => 'Koreksi Manual',
            'rfid_written' => 'RFID Ditulis',
            'delivered' => 'Terkirim',
            'updated' => 'Data Diperbarui',
        ];
        return $labels[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}