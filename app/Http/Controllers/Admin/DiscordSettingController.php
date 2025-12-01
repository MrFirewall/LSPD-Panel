<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordSetting;
use Illuminate\Http\Request;

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
            'settings.*.active' => 'nullable|boolean',
        ]);

        foreach ($data['settings'] as $id => $values) {
            $setting = DiscordSetting::findOrFail($id);
            $setting->update([
                'webhook_url' => $values['webhook_url'] ?? null,
                'active' => isset($values['active']), // Checkbox-Logik
            ]);
        }

        return back()->with('success', 'Discord Einstellungen gespeichert.');
    }
}