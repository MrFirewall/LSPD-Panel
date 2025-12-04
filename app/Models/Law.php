<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Law extends Model
{
    use HasFactory;

    protected $fillable = [
        'book',      // z.B. StGB, Verfassung
        'paragraph', // z.B. § 211
        'title',     // z.B. Mord
        'content',   // Der Text
    ];
}