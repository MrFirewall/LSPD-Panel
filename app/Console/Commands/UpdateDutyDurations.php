<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DutyRecord;
use App\Models\User;
use Illuminate\Support\Carbon;

class UpdateDutyDurations extends Command
{
    protected $signature = 'duty:update-durations';
    protected $description = 'Aktualisiert Dienstzeiten (Debug Version)';

    public function handle()
    {
        $this->info('--------------------------------------');
        $this->info('1. Start: Command wurde gestartet.');
        
        $now = Carbon::now();
        $this->info('2. Aktuelle Serverzeit: ' . $now->toDateTimeString());

        // Prüfen, wie viele offene Einträge es gibt
        try {
            $openCount = DutyRecord::whereNull('end_time')->count();
            $this->info("3. Datenbank-Check: Es gibt aktuell {$openCount} offene Dienste (end_time ist NULL).");
        } catch (\Exception $e) {
            $this->error('!!! DATENBANK FEHLER !!!');
            $this->error($e->getMessage());
            return;
        }

        if ($openCount === 0) {
            $this->warn('   -> Da 0 Dienste offen sind, gibt es nichts zu tun.');
            $this->warn('   -> Bitte gehe im Panel erst "In den Dienst", damit wir etwas zum Updaten haben!');
        } else {
            $this->info('4. Starte Update-Schleife...');
            
            DutyRecord::whereNull('end_time')->chunk(100, function ($records) use ($now) {
                foreach ($records as $record) {
                    $oldDuration = $record->duration_seconds;
                    $newDuration = $record->start_time->diffInSeconds($now);
                    
                    $record->update(['duration_seconds' => $newDuration]);
                    
                    $this->line("   -> Record ID {$record->id} (User {$record->user_id}): Dauer von {$oldDuration}s auf {$newDuration}s aktualisiert.");
                }
            });
        }

        $this->info('5. Ende: Skript erfolgreich durchgelaufen.');
        $this->info('--------------------------------------');
    }
}