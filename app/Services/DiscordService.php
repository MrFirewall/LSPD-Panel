<?php

namespace App\Services;

use App\Models\DiscordSetting;
use Illuminate\Support\Facades\Http;

class DiscordService
{
    /**
     * Sendet eine Nachricht an den konfigurierten Webhook basierend auf der Aktion.
     */
    public function send(string $action, string $content, array $embeds = [])
    {
        // 1. Einstellung aus der DB laden
        $setting = DiscordSetting::where('action', $action)->first();

        // 2. Prüfen ob Einstellung existiert, eine URL hat und aktiv ist
        if (!$setting || empty($setting->webhook_url) || !$setting->active) {
            return;
        }

        // 3. Payload vorbereiten
        $payload = [
            'content' => $content,
        ];

        if (!empty($embeds)) {
            $payload['embeds'] = $embeds;
        }

        // 4. Senden (Fire & Forget, wir warten nicht auf Antwort, um den User nicht zu blockieren)
        try {
            Http::post($setting->webhook_url, $payload);
        } catch (\Exception $e) {
            // Optional: Loggen, falls Discord down ist
            \Log::error("Discord Webhook Error: " . $e->getMessage());
        }
    }
}