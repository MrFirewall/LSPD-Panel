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
     */
    public function getLevelAttribute(): int
    {
        if (empty($this->rank)) {
            return 0;
        }
        $rankModel = Rank::where('name', $this->rank)
                            ->orWhere('label', $this->rank)
                            ->first();
        return $rankModel ? $rankModel->level : 0;
    }

    public function getHighestRank(): string
    {
        $roleNames = $this->getRoleNames()->toArray();
        if (empty($roleNames)) return 'praktikant';

        $highestRank = Rank::whereIn('name', $roleNames)
                            ->orderBy('level', 'desc')
                            ->first();
        return $highestRank ? $highestRank->name : 'praktikant';
    }

    public function getHighestRankLevel(): int
    {
        $roleNames = $this->getRoleNames()->toArray();
        if (empty($roleNames)) return 0;
        return (int) Rank::whereIn('name', $roleNames)->max('level');
    }

    protected function lastEditor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->last_edited_by ?? 'System',
        );
    }

    /**
     * Berechnet die GESAMTZEIT und das ARCHIV NACH RANG
     * KOMPLETT NEU: Basiert jetzt zu 100% auf der duty_records Tabelle.
     */
    public function calculateDutyHours(): array
    {
        // 1. Hole alle abgeschlossenen Records
        $records = $this->dutyRecords()
            ->select('rank', 'duration_seconds')
            ->whereNotNull('end_time')
            ->get();

        $archiveByRank = [];
        $activeTotalSeconds = 0;

        // 2. Summieren der abgeschlossenen Dienste
        foreach ($records as $record) {
            $rankSlug = $record->rank ?? 'unbekannt'; // Fallback für alte Einträge ohne Rang
            
            if (!isset($archiveByRank[$rankSlug])) {
                $archiveByRank[$rankSlug] = 0;
            }
            
            $seconds = (int) $record->duration_seconds;
            $archiveByRank[$rankSlug] += $seconds;
            $activeTotalSeconds += $seconds;
        }

        // 3. Laufenden Dienst hinzufügen (falls vorhanden)
        /** @var DutyRecord $openRecord */
        $openRecord = $this->dutyRecords()
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        if ($openRecord) {
            $currentDuration = $openRecord->start_time->diffInSeconds(Carbon::now());
            
            // Verwende den Rang aus dem Record oder den aktuellen User-Rang als Fallback
            $currentRankSlug = $openRecord->rank ?? $this->rank;

            if (!isset($archiveByRank[$currentRankSlug])) {
                $archiveByRank[$currentRankSlug] = 0;
            }

            $archiveByRank[$currentRankSlug] += $currentDuration;
            $activeTotalSeconds += $currentDuration;
        }

        return [
            'active_total_seconds' => $activeTotalSeconds,
            'archive_by_rank' => $archiveByRank,
        ];
    }
    
    /**
     * Berechnet die WOCHENSTUNDEN seit dem Beitritt, 
     * basierend auf der NEUEN DutyRecord-Tabelle. 
     */
    public function calculateWeeklyHoursSinceEntry(): array
    {
        if (!$this->hire_date) {
            return [];
        }

        $startDate = $this->hire_date->copy()->startOfDay();
        $weeklyData = [];

        // 1. Hole alle abgeschlossenen DutyRecords seit dem Beitrittsdatum.
        $records = $this->dutyRecords()
            ->select('start_time', 'duration_seconds', 'type')
            ->where('start_time', '>=', $startDate)
            ->whereNotNull('end_time') 
            ->get();

        // 2. Initialisiere alle Wochen (von hire_date bis heute) mit 00:00 h
        $currentWeek = $this->hire_date->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        while ($currentWeek->lessThanOrEqualTo($endDate)) {
            $kwKey = $currentWeek->format('Y') . "_KW" . $currentWeek->format('W');
            $weeklyData[$kwKey] = ['normal_seconds' => 0, 'leitstelle_seconds' => 0];
            $currentWeek->addWeek();
        }

        // 3. Füge die Zeiten aus den Records hinzu
        foreach ($records as $record) {
            $kwKey = $record->start_time->format('Y') . "_KW" . $record->start_time->format('W');
            $typeKey = ($record->type === 'LEITSTELLE') ? 'leitstelle_seconds' : 'normal_seconds';
            
            if (isset($weeklyData[$kwKey])) {
                $weeklyData[$kwKey][$typeKey] += $record->duration_seconds;
            }
        }
        
        // 4. Laufenden Dienst hinzufügen
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

        // 5. Sortieren
        krsort($weeklyData);
        return $weeklyData;
    }

    // --- Relationen ---
    public function activityLogs(): HasMany { return $this->hasMany(ActivityLog::class); }
    public function dutyRecords(): HasMany { return $this->hasMany(DutyRecord::class); }
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
    public function rankRelation(){ return $this->belongsTo(Rank::class, 'rank', 'name'); }
}