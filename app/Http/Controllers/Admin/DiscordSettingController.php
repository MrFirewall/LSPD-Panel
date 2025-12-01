<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordSetting;
use App\Models\ActivityLog; // <--- WICHTIG
use App\Events\PotentiallyNotifiableActionOccurred; // <--- WICHTIG
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <--- WICHTIG

class DiscordSettingController extends Controller
{
    // Zeigt die Liste aller Hooks an
    public function index()
    {
        $settings = DiscordSetting::all();
        return view('admin.discord-settings.index', compact('settings'));
    }

    // Speichert alle Änderungen auf einmal
    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.webhook_url' => 'nullable|url',
            'settings.*.active' => 'nullable', // "nullable" reicht hier, da wir unten casten
        ]);

        $changesCount = 0;

        foreach ($data['settings'] as $id => $values) {
            $setting = DiscordSetting::findOrFail($id);

            // 1. Alte Werte sichern für Vergleich
            $oldUrl = $setting->webhook_url;
            $oldActive = (bool)$setting->active;
            
            // Neue Werte vorbereiten
            $newUrl = $values['webhook_url'] ?? null;
            $newActive = (bool)($values['active'] ?? 0);

            // 2. Prüfen ob sich etwas geändert hat
            $changes = [];
            if ($oldUrl !== $newUrl) {
                $changes[] = "URL geändert";
            }
            if ($oldActive !== $newActive) {
                $changes[] = "Status geändert von '" . ($oldActive ? 'Aktiv' : 'Inaktiv') . "' zu '" . ($newActive ? 'Aktiv' : 'Inaktiv') . "'";
            }

            // Nur speichern und loggen, wenn es Änderungen gab
            if (!empty($changes)) {
                $setting->update([
                    'webhook_url' => $newUrl,
                    'active' => $newActive,
                ]);

                $changesCount++;

                // --- A) LOGGING ---
                $description = "Discord-Einstellung '{$setting->friendly_name}' aktualisiert. ";
                $description .= "Änderungen: " . implode(', ', $changes) . ".";

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'log_type' => 'SYSTEM', // Oder 'SETTINGS' je nach deinem Enum
                    'action' => 'UPDATED',
                    'target_id' => $setting->id,
                    'description' => $description,
                ]);

                // --- B) EVENT AUSLÖSEN ---
                PotentiallyNotifiableActionOccurred::dispatch(
                    'Admin\DiscordSettingController@update',
                    $setting, // relatedModel
                    null,     // targetModel (kein spezifischer User als Ziel)
                    Auth::user(),
                    [
                        'description' => $description,
                        'setting_name' => $setting->friendly_name,
                        'changes' => $changes
                    ]
                );
            }
        }

        if ($changesCount > 0) {
            return back()->with('success', "{$changesCount} Discord Einstellung(en) erfolgreich gespeichert.");
        }

        return back()->with('info', 'Keine Änderungen vorgenommen.');
    }

    public function test(DiscordSetting $discordSetting)
    {
        // Sicherheitscheck: Hat dieser Eintrag überhaupt eine URL?
        if (empty($discordSetting->webhook_url)) {
            return back()->with('error', 'Bitte erst eine URL speichern, bevor du testest.');
        }

        try {
            // Service aufrufen (Pfad ggf. anpassen, falls du imports nutzt)
            (new \App\Services\DiscordService())->sendTest($discordSetting->webhook_url);
            
            return back()->with('success', 'Testnachricht wurde erfolgreich an Discord gesendet!');
        } catch (\Exception $e) {
            return back()->with('error', 'Fehler beim Senden: ' . $e->getMessage());
        }
    }
}