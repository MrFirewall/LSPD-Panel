<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_section', // z.B. StVO, StGB
        'offense',         // Tatbestand
        'amount',          // Preis
        'jail_time',       // Haftzeit in Minuten (HE)
        'points',          // Punkte
        'remark',          // Bemerkung
    ];

    public function reports()
    {
        return $this->belongsToMany(Report::class, 'fine_report')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}