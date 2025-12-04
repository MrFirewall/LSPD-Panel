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

        $startDate = $this->hire_date->copy()->startOfDay();
        $logTypes = ['DUTY_START', 'DUTY_END', 'LEITSTELLE_START', 'LEITSTELLE_END'];
        $logs = $this->activityLogs()
            ->whereIn('log_type', $logTypes)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $weeklyData = [];
        $lastDutyStart = null;
        $lastLeitstelleStart = null;

        foreach ($logs as $log) {
            $kw = "KW" . $log->created_at->format('W');
            if (!isset($weeklyData[$kw])) {
                $weeklyData[$kw] = ['normal_seconds' => 0, 'leitstelle_seconds' => 0];
            }
            switch ($log->log_type) {
                case 'DUTY_START': $lastDutyStart = $log; break;
                case 'DUTY_END':
                    if ($lastDutyStart) {
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
