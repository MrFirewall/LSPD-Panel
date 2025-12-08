<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;
use Illuminate\Database\Eloquent\Casts\Attribute; 
use Illuminate\Support\Carbon; 
use NotificationChannels\WebPush\HasPushSubscriptions;
use App\Models\Rank;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Impersonate, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'cfx_name',
        'cfx_id',
        'avatar',
        'status',        
        'rank', // Das ist der String (Slug) des Haupt-Rangs (z.B. 'captain')
        'personal_number',
        'employee_id',
        'email',
        'birthday',
        'discord_name',
        'forum_name',
        'special_functions',
        'second_faction',
        'hire_date',
        'last_edited_at',
        'last_edited_by',
    ];

    protected $casts = [
        'hire_date' => 'datetime',
    ];

    /**
     * NEU: Dynamischer Abruf des Levels basierend auf dem Haupt-Rang ($user->rank).
     * Zugriff in Blade via: $user->level
     */
    public function getLevelAttribute(): int
    {
        if (empty($this->rank)) {
            return 0;
        }

        // Suche den Rang in der DB (Cache empfohlen für Produktion, hier direkt)
        $rankModel = Rank::where('name', $this->rank)
                         ->orWhere('label', $this->rank)
                         ->first();

        return $rankModel ? $rankModel->level : 0;
    }

    /**
     * Ermittelt den Namen der höchsten Rolle, die der Benutzer hat (basierend auf DB-Level).
     */
    public function getHighestRank(): string
    {
        // Wir holen alle Rollennamen des Users
        $roleNames = $this->getRoleNames()->toArray();

        if (empty($roleNames)) {
            return 'praktikant';
        }

        // Wir suchen in der Rank-Tabelle nach diesen Namen und holen den mit dem höchsten Level
        $highestRank = Rank::whereIn('name', $roleNames)
                           ->orderBy('level', 'desc')
                           ->first();

        return $highestRank ? $highestRank->name : 'praktikant';
    }

    /**
     * Gibt die "Stufe" des höchsten Ranges zurück (basierend auf zugewiesenen Rollen).
     */
    public function getHighestRankLevel(): int
    {
        $roleNames = $this->getRoleNames()->toArray();

        if (empty($roleNames)) {
            return 0;
        }

        // Höchstes Level direkt aus der DB aggregieren
        return (int) Rank::whereIn('name', $roleNames)->max('level');
    }

    /**
     * Accessor, der "System" zurückgibt, wenn kein Bearbeiter gesetzt ist.
     */
    protected function lastEditor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->last_edited_by ?? 'System',
        );
    }

    /**
     * Berechnet die Dienststunden basierend auf den ActivityLogs.
     */
    public function calculateDutyHours(): array
    {
        $logs = $this->activityLogs()
                     ->whereIn('log_type', ['DUTY_START', 'DUTY_END', 'UPDATED'])
                     ->orderBy('created_at', 'asc')
                     ->get();

        $archiveByRank = [];
        $activeTotalSeconds = 0;
        $lastStartLog = null;
        $currentRank = $this->rank; 

        foreach ($logs as $log) {
            if ($log->log_type === 'UPDATED' && !empty($log->details)) {
                if (str_contains($log->details, 'Status geändert:') && str_contains($log->details, '-> inaktiv')) {
                    $activeTotalSeconds = 0; 
                }
                if (preg_match('/Rang geändert:.*?-> ([\w-]+)/', $log->details, $matches)) {
                    $currentRank = $matches[1];
                }
            }

            if ($log->log_type === 'DUTY_START') {
                $lastStartLog = $log;
            } 
            elseif ($log->log_type === 'DUTY_END' && $lastStartLog) {
                $duration = $lastStartLog->created_at->diffInSeconds($log->created_at);
                if (!isset($archiveByRank[$currentRank])) {
                    $archiveByRank[$currentRank] = 0;
                }
                $archiveByRank[$currentRank] += $duration;
                $activeTotalSeconds += $duration;
                $lastStartLog = null;
            }
        }

        if ($lastStartLog) {
            $duration = $lastStartLog->created_at->diffInSeconds(Carbon::now());
            if (!isset($archiveByRank[$currentRank])) {
                $archiveByRank[$currentRank] = 0;
            }
            $archiveByRank[$currentRank] += $duration;
            $activeTotalSeconds += $duration;
        }

        return [
            'active_total_seconds' => $activeTotalSeconds,
            'archive_by_rank' => $archiveByRank,
        ];
    }

    public function calculateWeeklyHoursSinceEntry(): array
    {
        if (!$this->hire_date) {
            return [];
        }

        $startDate = $this->hire_date->copy()->startOfWeek(); // Start der ersten Woche
        $endDate = Carbon::now()->endOfWeek(); // Ende der aktuellen Woche
        $weeklyData = [];

        // 1. Initialisiere alle Wochen von hire_date bis heute mit 00:00 h
        $currentWeek = $startDate->copy();
        while ($currentWeek->lessThanOrEqualTo($endDate)) {
            $kw = $currentWeek->format('Y') . "_" . "KW" . $currentWeek->format('W');
            // Sicherstellen, dass die KW nicht schon existiert, falls die Schleife mehrmals durchlaufen wird (z.B. bei Jahreswechsel)
            if (!isset($weeklyData[$kw])) {
                $weeklyData[$kw] = ['normal_seconds' => 0, 'leitstelle_seconds' => 0];
            }
            $currentWeek->addWeek();
        }

        $logTypes = ['DUTY_START', 'DUTY_END', 'LEITSTELLE_START', 'LEITSTELLE_END'];
        // Hole alle relevanten Logs seit dem hire_date
        $logs = $this->activityLogs()
            ->whereIn('log_type', $logTypes)
            ->where('created_at', '>=', $this->hire_date->copy()->startOfDay()) // Wir nutzen das genaue hire_date für die Logs-Abfrage
            ->orderBy('created_at', 'asc')
            ->get();

        $lastDutyStart = null;
        $lastLeitstelleStart = null;

        // 2. Verarbeite die Logs und addiere die Zeiten zur korrekten Kalenderwoche
        foreach ($logs as $log) {
            // HINWEIS: Carbon's `format('W')` funktioniert gut für europäische KWs.
            $kw = $log->created_at->format('Y') . "_" . "KW" . $log->created_at->format('W');
            
            // Sollte immer existieren wegen der Initialisierung, aber zur Sicherheit:
            if (!isset($weeklyData[$kw])) {
                $weeklyData[$kw] = ['normal_seconds' => 0, 'leitstelle_seconds' => 0];
            }

            switch ($log->log_type) {
                case 'DUTY_START': 
                    // Prüfe auf Überlappung (falls vergessen wurde auszuloggen, aber hier wird einfach überschrieben)
                    $lastDutyStart = $log; 
                    break;
                case 'DUTY_END':
                    if ($lastDutyStart) {
                        // Wichtig: Prüfen, ob der Start-Log in der gleichen KW liegt
                        // (Wenn Logs zwischen Wochen liegen, ist die Logik komplexer,
                        // aber für eine einfache Addition pro KW ist das in Ordnung,
                        // solange Start und Ende in der gleichen KW liegen oder nur kurze Übergänge existieren).
                        // In deinem Fall gehen wir davon aus, dass Start und Ende in derselben Woche stattfinden
                        // oder die Methode ist für Übergänge wie in deinem Code geplant (Gesamtzeit zwischen Start und Endlog).
                        // Da die Schleife nach dem Ende des Dienstes fortfährt, wird die volle Dauer der End-KW gutgeschrieben.
                        $weeklyData[$kw]['normal_seconds'] += $lastDutyStart->created_at->diffInSeconds($log->created_at);
                        $lastDutyStart = null;
                    }
                    break;
                case 'LEITSTELLE_START': $lastLeitstelleStart = $log; break;
                case 'LEITSTELLE_END':
                    if ($lastLeitstelleStart) {
                        $weeklyData[$kw]['leitstelle_seconds'] += $lastLeitstelleStart->created_at->diffInSeconds($log->created_at);
                        $lastLeitstelleStart = null;
                    }
                    break;
            }
        }

        // 3. Optional: Offene Dienste bis zur aktuellen Zeit addieren (für die aktuelle KW)
        $currentKw = "KW" . Carbon::now()->format('W');
        if ($lastDutyStart && $lastDutyStart->created_at->isSameWeek(Carbon::now())) {
            $weeklyData[$currentKw]['normal_seconds'] += $lastDutyStart->created_at->diffInSeconds(Carbon::now());
        }
        if ($lastLeitstelleStart && $lastLeitstelleStart->created_at->isSameWeek(Carbon::now())) {
            $weeklyData[$currentKw]['leitstelle_seconds'] += $lastLeitstelleStart->created_at->diffInSeconds(Carbon::now());
        }

        // 4. Sortieren (KW absteigend)
        krsort($weeklyData);
        return $weeklyData;
    }

    // --- Relationen ---
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
    public function receivedEvaluations() { return $this->hasMany(Evaluation::class, 'user_id'); }
    public function serviceRecords() { return $this->hasMany(ServiceRecord::class); }
    public function reports() { return $this->hasMany(Report::class); }
    public function vacations() { return $this->hasMany(Vacation::class); }
    public function attendedReports() { return $this->belongsToMany(Report::class, 'report_user'); }
    public function canImpersonate(): bool{ return $this->hasAnyRole('chief', 'Super-Admin'); }
    public function canBeImpersonated(): bool{ return !$this->hasAnyRole('chief', 'Super-Admin'); }
    public function prescriptions(){ return $this->hasMany(Prescription::class); }
    public function trainingModules(){ 
        return $this->belongsToMany(TrainingModule::class, 'training_module_user')
        ->using(\App\Models\Pivots\TrainingModuleUser::class)
        ->withPivot('assigned_by_user_id', 'completed_at', 'notes')
        ->withTimestamps();
    }

    public function qualifications(){ return $this->trainingModules()->wherePivot('status', 'bestanden'); }
    public function examAttempts(){ return $this->hasMany(ExamAttempt::class); }
    public function pushSubscriptions() { return $this->hasMany(\App\Models\PushSubscription::class); }
    
    // Falls du die Relation nutzen willst (optional, da wir oben oft den String nutzen)
    public function rankRelation(){ return $this->belongsTo(Rank::class, 'rank', 'name'); }
}
