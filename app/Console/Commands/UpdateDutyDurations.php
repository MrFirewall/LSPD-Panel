<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DutyRecord;
use Illuminate\Support\Carbon;

class UpdateDutyDurations extends Command
{
    protected $signature = 'duty:update-durations';
    protected $description = 'Aktualisiert Dienstzeiten kontinuierlich (Worker Mode)';

    public function handle()
    {
        // 1. Intervall aus .env laden (Fallback: 10 Sekunden)
        // Wir nehmen hier bewusst (int) für ganze Sekunden
        $interval = (int) env('DUTY_HEARTBEAT_INTERVAL', 10);
        
        // SICHERHEIT: Um die CPU nicht zu töten, erzwingen wir mindestens 1 Sekunde Pause.
        // Wenn du 0 oder Quatsch in der .env hast, nehmen wir 5 Sekunden.
        if ($interval < 1) $interval = 5;

        $startTime = time();
        $maxExecutionTime = 55; // Das Skript beendet sich nach 55 Sek, damit der nächste Cronjob übernimmt.

        $this->info("Starte Worker-Loop. Intervall: {$interval}s");

        // 2. Die Schleife: Läuft solange, bis 55 Sekunden vergangen sind
        while ((time() - $startTime) < $maxExecutionTime) {
            
            $now = Carbon::now();

            // Massen-Update durchführen
            DutyRecord::whereNull('end_time')->chunk(100, function ($records) use ($now) {
                foreach ($records as $record) {
                    $record->update([
                        'duration_seconds' => $record->start_time->diffInSeconds($now)
                    ]);
                }
            });

            // 3. Warten (Schlafen) bis zum nächsten Intervall
            sleep($interval);
        }

        $this->info('Worker beendet (Zeitlimit erreicht).');
    }
}