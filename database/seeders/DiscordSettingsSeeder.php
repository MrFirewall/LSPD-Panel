<?php

namespace Database\Seeders;

use App\Models\DiscordSetting;
use Illuminate\Database\Seeder;

class DiscordSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'action' => 'rank.promotion',
                'friendly_name' => 'Beförderung',
                'description' => 'Wird gesendet, wenn ein User einen höheren Rang erhält.',
            ],
            [
                'action' => 'rank.demotion',
                'friendly_name' => 'Degradierung',
                'description' => 'Wird gesendet, wenn ein User herabgestuft wird.',
            ],
            [
                'action' => 'user.registered',
                'friendly_name' => 'Neuer Benutzer',
                'description' => 'Wird gesendet, wenn sich jemand neu registriert.',
            ],
            // Hier kannst du beliebig viele weitere Hooks definieren
        ];

        foreach ($settings as $setting) {
            DiscordSetting::updateOrCreate(
                ['action' => $setting['action']], // Prüfen ob Key existiert
                $setting // Daten setzen/updaten
            );
        }
    }
}