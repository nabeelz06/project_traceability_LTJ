<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchLog extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Relasi: Log ini milik Batch mana.
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * Relasi: Log ini dibuat oleh User (Actor) mana.
     * (Dinamai 'actor' agar cocok dengan $log->actor->name di view)
     */
    public function actor()
    {
        // PENTING: Nama fungsi adalah 'actor', tapi foreign key adalah 'actor_user_id'
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}