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
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * HINWEIS: Diese Methode bleibt unverändert, da sie die Rangwechsel-Logik 
     * für das Archiv benötigt und weiterhin ActivityLogs auswertet.
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
    
    /**
     * Berechnet die wöchentlichen Dienststunden seit dem Beitritt, 
     * basierend auf der NEUEN DutyRecord-Tabelle. 
     */
    public function calculateWeeklyHoursSinceEntry(): array
    {
        if (!$this->hire_date) {
            return [];
        }

        $startDate = $this->hire_date->copy()->startOfDay();
        $weeklyData = [];

        // 1. Hole alle ABGESCHLOSSENEN DutyRecords (end_time IS NOT NULL)
        $records = $this->dutyRecords()
            ->select('start_time', 'duration_seconds', 'type')
            ->where('start_time', '>=', $startDate)
            ->whereNotNull('end_time') // Nur abgeschlossene Dienste
            ->get();

        // 2. Initialisiere alle Wochen (von hire_date bis heute) mit 00:00 h
        $currentWeek = $this->hire_date->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        while ($currentWeek->lessThanOrEqualTo($endDate)) {
            // Verwende das korrekte, sortierbare Y_KW-Format
            $kwKey = $currentWeek->format('Y') . "_KW" . $currentWeek->format('W');
            $weeklyData[$kwKey] = ['normal_seconds' => 0, 'leitstelle_seconds' => 0];
            $currentWeek->addWeek();
        }

        // 3. Füge die Zeiten aus den abgeschlossenen DutyRecords hinzu.
        foreach ($records as $record) {
            $kwKey = $record->start_time->format('Y') . "_KW" . $record->start_time->format('W');
            
            $typeKey = ($record->type === 'LEITSTELLE') ? 'leitstelle_seconds' : 'normal_seconds';
            
            if (isset($weeklyData[$kwKey])) {
                $weeklyData[$kwKey][$typeKey] += $record->duration_seconds;
            }
        }
        
        // 4. LAUFENDER DIENST (end_time IS NULL) hinzufügen
        /** @var DutyRecord $openRecord */
        $openRecord = $this->dutyRecords()
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        if ($openRecord && $openRecord->start_time->isSameWeek(Carbon::now())) {
            $currentKwKey = Carbon::now()->format('Y') . "_KW" . Carbon::now()->format('W');
            $duration = $openRecord->start_time->diffInSeconds(Carbon::now());
            
            if (isset($weeklyData[$currentKwKey])) {
                $typeKey = ($openRecord->type === 'LEITSTELLE') ? 'leitstelle_seconds' : 'normal_seconds';
                $weeklyData[$currentKwKey][$typeKey] += $duration;
            }
        }

        // 5. Sortieren (Y_KW absteigend)
        krsort($weeklyData);
        return $weeklyData;
    }

    // --- Relationen ---
    public function activityLogs(): HasMany { return $this->hasMany(ActivityLog::class); }
    public function dutyRecords(): HasMany { return $this->hasMany(DutyRecord::class); } // NEUE RELATION
    public function receivedEvaluations(): HasMany { return $this->hasMany(Evaluation::class, 'user_id'); }
    public function serviceRecords(): HasMany { return $this->hasMany(ServiceRecord::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
    public function vacations(): HasMany { return $this->hasMany(Vacation::class); }
    public function attendedReports() { return $this->belongsToMany(Report::class, 'report_user'); }
    public function canImpersonate(): bool{ return $this->hasAnyRole('chief', 'Super-Admin'); }
    public function canBeImpersonated(): bool{ return !$this->hasAnyRole('chief', 'Super-Admin'); }
    public function prescriptions(): HasMany{ return $this->hasMany(Prescription::class); }
    public function trainingModules(){ 
        return $this->belongsToMany(TrainingModule::class, 'training_module_user')
        ->using(\App\Models\Pivots\TrainingModuleUser::class)
        ->withPivot('assigned_by_user_id', 'completed_at', 'notes')
        ->withTimestamps();
    }

    public function qualifications(){ return $this->trainingModules()->wherePivot('status', 'bestanden'); }
    public function examAttempts(): HasMany{ return $this->hasMany(ExamAttempt::class); }
    public function pushSubscriptions(): HasMany { return $this->hasMany(\App\Models\PushSubscription::class); }
    
    // Falls du die Relation nutzen willst (optional, da wir oben oft den String nutzen)
    public function rankRelation(){ return $this->belongsTo(Rank::class, 'rank', 'name'); }
}