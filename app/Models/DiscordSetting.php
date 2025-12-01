<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordSetting extends Model
{
    protected $fillable = ['action', 'friendly_name', 'webhook_url', 'active', 'description'];

    // Füge das hier hinzu:
    protected $casts = [
        'active' => 'boolean',
    ];
}
