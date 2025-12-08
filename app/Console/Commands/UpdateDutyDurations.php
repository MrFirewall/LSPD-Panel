<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DutyRecord;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateDutyDurations extends Command
{
    /**
     * Der Name und die Signatur des Befehls.
     */
    protected $signature = 'duty:update-durations';

    /**
     * Die Beschreibung des Befehls.
     */
    protected $description = 'Aktualisiert die Dauer aller laufenden Dienste und prüft auf Timeouts';

    /**
     * Führt den Befehl aus.
     */
    public function handle()
    {
        // Lade Intervall aus .env (z.B. 10 Sekunden)
        $interval = (int) env('DUTY_HEARTBEAT_INTERVAL', 60);
        
        // Sicherheitsnetz: Nicht unter 5 Sekunden gehen, um CPU zu schonen
        if ($interval < 5) $interval = 5;

        // Startzeitpunkt des Commands
        $startTime = time();
        
        // Wir lassen den Command maximal 55 Sekunden laufen (damit der nächste Cronjob übernimmt)
        // Das simuliert einen dauerhaften Prozess.
        while (time() - $startTime < 55) {
            
            $now = Carbon::now();
            
            // --- DEINE LOGIK ---
            DutyRecord::whereNull('end_time')->chunk(100, function ($records) use ($now) {
                foreach ($records as $record) {
                    $record->update([
                        'duration_seconds' => $record->start_time->diffInSeconds($now)
                    ]);
                }
            });
            // -------------------

            // Warte die eingestellten Sekunden (z.B. 10s)
            sleep($interval);
        }
    }

    /**
     * Hilfsfunktion: Zwangsweises Beenden
     */
    private function forceStopDuty(DutyRecord $record, Carbon $now)
    {
        // Record schließen
        $record->update([
            'end_time' => $now,
            'duration_seconds' => $record->start_time->diffInSeconds($now),
        ]);

        // User Status zurücksetzen
        $user = User::find($record->user_id);
        if ($user) {
            $user->on_duty = false;
            $user->save();
            
            // Log schreiben
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'log_type' => 'DUTY_END',
                'action' => 'SYSTEM',
                'description' => 'Dienst automatisch beendet (Zeitüberschreitung).',
            ]);
            
            Log::info("User {$user->id} wurde zwangsweise ausgeloggt (Maximalzeit).");
        }
    }
}