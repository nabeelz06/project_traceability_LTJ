<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabAnalysis extends Model
{
    use HasFactory;

    // FIXED: Plural table name
    protected $table = 'lab_analyses';

    protected $fillable = [
        'batch_id',
        'analyst_user_id',
        'nd_content',
        'la_content',
        'ce_content',
        'y_content',
        'pr_content',
        'total_recovery',
        'analyzed_at',
        'notes',
    ];

    protected $casts = [
        'nd_content' => 'decimal:2',
        'la_content' => 'decimal:2',
        'ce_content' => 'decimal:2',
        'y_content' => 'decimal:2',
        'pr_content' => 'decimal:2',
        'total_recovery' => 'decimal:2',
        'analyzed_at' => 'datetime',
    ];

    // Relationships
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_user_id');
    }

    // Helper: Get total LTJ content
    public function getTotalLtjContentAttribute()
    {
        return ($this->nd_content ?? 0) + 
               ($this->la_content ?? 0) + 
               ($this->ce_content ?? 0) + 
               ($this->y_content ?? 0) + 
               ($this->pr_content ?? 0);
    }

    // Helper: Get LTJ composition array for charts
    public function getLtjCompositionAttribute()
    {
        return [
            ['element' => 'Nd (Neodymium)', 'percentage' => $this->nd_content ?? 0, 'color' => '#e74c3c'],
            ['element' => 'La (Lanthanum)', 'percentage' => $this->la_content ?? 0, 'color' => '#3498db'],
            ['element' => 'Ce (Cerium)', 'percentage' => $this->ce_content ?? 0, 'color' => '#f39c12'],
            ['element' => 'Y (Yttrium)', 'percentage' => $this->y_content ?? 0, 'color' => '#2ecc71'],
            ['element' => 'Pr (Praseodymium)', 'percentage' => $this->pr_content ?? 0, 'color' => '#9b59b6'],
        ];
    }

    // Helper: Get dominant element
    public function getDominantElementAttribute()
    {
        $elements = [
            'Nd' => $this->nd_content ?? 0,
            'La' => $this->la_content ?? 0,
            'Ce' => $this->ce_content ?? 0,
            'Y' => $this->y_content ?? 0,
            'Pr' => $this->pr_content ?? 0,
        ];

        arsort($elements);
        return array_key_first($elements);
    }

    // Helper: Get formatted analysis date
    public function getFormattedDateAttribute()
    {
        return $this->analyzed_at ? $this->analyzed_at->format('d M Y H:i') : '-';
    }
}