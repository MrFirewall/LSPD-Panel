<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DutyRecord; // WICHTIG: Model importieren
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\PotentiallyNotifiableActionOccurred; 
use App\Models\User; 
use Illuminate\Support\Carbon; 

class DutyStatusController extends Controller
{
    public function toggle(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Status im User-Objekt umschalten
        $user->on_duty = !$user->on_duty;
        $user->save();

        if ($user->on_duty) {
            // ====================================================
            // DIENST START (Schreibt in duty_records UND activity_logs)
            // ====================================================
            
            // A: Erstelle einen neuen, offenen Eintrag in der NEUEN Tabelle
            DutyRecord::create([
                'user_id' => $user->id,
                'start_time' => Carbon::now(),
                'type' => 'DUTY', 
                'end_time' => null, // Explizit null
                'duration_seconds' => null // Explizit null
            ]);

            // B: Protokoll (Log)
            ActivityLog::create([
                'user_id' => $user->id,
                'log_type' => 'DUTY_START',
                'action' => 'TOGGLED',
                'description' => 'Benutzer hat den Dienst angetreten.',
            ]);
            
            $status_text = 'Im Dienst';
            $actionIdentifier = 'DutyStatusController@toggle.on_duty'; 
            
        } else {
            // ====================================================
            // DIENST ENDE (Aktualisiert duty_records UND schreibt Log)
            // ====================================================
            
            $currentTime = Carbon::now();
            
            // A: Suche den offenen Eintrag in der NEUEN Tabelle und schließe ihn
            $openRecord = DutyRecord::where('user_id', $user->id)
                ->whereNull('end_time') // Suche nach Eintrag ohne Ende
                ->latest('start_time')
                ->first();

            if ($openRecord) {
                // Berechne Dauer
                $duration = $openRecord->start_time->diffInSeconds($currentTime);

                // Update den bestehenden Eintrag
                $openRecord->update([
                    'end_time' => $currentTime,
                    'duration_seconds' => $duration,
                ]);
            }

            // B: Protokoll (Log)
            ActivityLog::create([
                'user_id' => $user->id,
                'log_type' => 'DUTY_END',
                'action' => 'TOGGLED',
                'description' => 'Benutzer hat den Dienst beendet.',
            ]);
            
            $status_text = 'Außer Dienst';
            $actionIdentifier = 'DutyStatusController@toggle.off_duty'; 
        }

        // Event feuern (für Benachrichtigungen etc.)
        PotentiallyNotifiableActionOccurred::dispatch($actionIdentifier, $user, $user, $user);

        return response()->json([
            'success' => true,
            'new_status' => $user->on_duty,
            'status_text' => $status_text
        ]);
    }
}