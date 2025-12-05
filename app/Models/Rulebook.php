<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rulebook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'order_index',
        'updated_by'
    ];

    // Relation zum User, der das Update gemacht hat
    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}