<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DutyRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\PotentiallyNotifiableActionOccurred; 
use App\Models\User; 
use Illuminate\Support\Carbon; 

class DutyStatusController extends Controller
{
    /**
     * Neuer Heartbeat: Aktualisiert die Dauer laufender Dienste.
     */
    public function heartbeat(Request $request)
    {
        $user = Auth::user();

        if (!$user->on_duty) {
            return response()->json(['status' => 'ignored', 'message' => 'User not on duty']);
        }

        // Suche den offenen Record
        $openRecord = DutyRecord::where('user_id', $user->id)
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        if ($openRecord) {
            $currentTime = Carbon::now();
            $duration = $openRecord->start_time->diffInSeconds($currentTime);
            $gracePeriod = config('app.duty_grace_period', env('DUTY_GRACE_PERIOD', 0));

            // Nur in die DB schreiben, wenn die Toleranzzeit überschritten ist
            if ($duration > $gracePeriod) {
                // Wir aktualisieren NUR die duration_seconds, end_time bleibt NULL
                $openRecord->update([
                    'duration_seconds' => $duration
                ]);
                return response()->json(['status' => 'updated', 'duration' => $duration]);
            }
            
            return response()->json(['status' => 'in_grace_period', 'duration' => 0]);
        }

        return response()->json(['status' => 'no_record_found']);
    }

    public function toggle(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Status im User-Objekt umschalten
        $user->on_duty = !$user->on_duty;
        $user->save();

        // Config laden
        $gracePeriod = (int) env('DUTY_GRACE_PERIOD', 0);

        if ($user->on_duty) {
            // === DIENST START ===
            
            DutyRecord::create([
                'user_id' => $user->id,
                'start_time' => Carbon::now(),
                'type' => 'DUTY', 
                'rank' => $user->rank, // Aktuellen Rang sichern
                'end_time' => null, 
                'duration_seconds' => 0 
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'log_type' => 'DUTY_START',
                'action' => 'TOGGLED',
                'description' => 'Benutzer hat den Dienst angetreten.',
            ]);
            
            $status_text = 'Im Dienst';
            $actionIdentifier = 'DutyStatusController@toggle.on_duty'; 
            
        } else {
            // === DIENST ENDE ===
            
            $currentTime = Carbon::now();
            
            $openRecord = DutyRecord::where('user_id', $user->id)
                ->whereNull('end_time')
                ->latest('start_time')
                ->first();

            if ($openRecord) {
                // Endgültige Zeit berechnen
                $duration = $openRecord->start_time->diffInSeconds($currentTime);

                // Grace Period Check (z.B. 120 Sekunden)
                $gracePeriod = (int) env('DUTY_GRACE_PERIOD', 0);

                if ($duration < $gracePeriod) {
                    // Zu kurz! Löschen.
                    $openRecord->forceDelete();
                    $status_text = 'Dienstzeit verworfen (zu kurz).';
                } else {
                    // Gültig! Abschließen.
                    $openRecord->update([
                        'end_time' => $currentTime,
                        'duration_seconds' => $duration
                    ]);
                }
            }

            ActivityLog::create([
                'user_id' => $user->id,
                'log_type' => 'DUTY_END',
                'action' => 'TOGGLED',
                'description' => 'Benutzer hat den Dienst beendet.',
            ]);
            
            $status_text = 'Außer Dienst';
            $actionIdentifier = 'DutyStatusController@toggle.off_duty'; 
        }

        PotentiallyNotifiableActionOccurred::dispatch($actionIdentifier, $user, $user, $user);

        return response()->json([
            'success' => true,
            'new_status' => $user->on_duty,
            'status_text' => $status_text
        ]);
    }
}