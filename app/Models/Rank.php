<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    /**
     * Die Attribute, die per Massen-Zuweisung befüllt werden dürfen.
     * WICHTIG für den RankSeeder!
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'label', // <--- NEU
        'guard_name',
        'updated_at',
        'created_at'
    ];

    /**
     * Ruft alle Benutzer ab, die diesen Rang haben.
     */
    public function users()
    {
        // Dies ist die Umkehr-Beziehung zu User::belongsTo(Rank::class)
        return $this->hasMany(User::class);
    }
    public function getLabelAttribute()
    {
        // Wenn in der DB ein Label steht, nimm das. Sonst nimm den Namen.
        return $this->attributes['label'] ?? $this->name;
    }
}