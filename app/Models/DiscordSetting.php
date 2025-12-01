<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordSetting extends Model
{
    protected $fillable = ['action', 'webhook_url', 'active'];
}
