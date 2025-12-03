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
        'level',
        'label',
    ];

    /**
     * Ruft alle Benutzer ab, die diesen Rang haben.
     */
    public function users()
    {
        // Dies ist die Umkehr-Beziehung zu User::belongsTo(Rank::class)
        return $this->hasMany(User::class);
    }
    // Hilfsfunktion: Gibt Label zurück, wenn vorhanden, sonst den Namen (erster Buchstabe groß)
    public function getLabelAttribute($value)
    {
        return $value ?? ucfirst($this->name);
    }
}