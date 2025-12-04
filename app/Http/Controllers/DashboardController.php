<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Report;
use App\Models\User;
use App\Models\Rank;
use App\Models\Citizen; // Wichtig: Citizen Model importieren
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // --- 1. DATEN FÜR DIE TOP-STATS CARDS ---

        // A) Meine Berichte (Anzahl der Berichte des aktuellen Users)
        $myReportCount = Report::where('user_id', $user->id)->count();

        // B) Offene Akten
        // Hinweis: Da wir aktuell keine 'status'-Spalte haben, zählen wir hier alle Berichte.
        // Wenn du später einen Status einbaust, ändere dies zu: Report::where('status', 'open')->count();
        $openCasesCount = Report::count();

        // C) Bußgelder (Heute)
        // Wir holen alle Berichte von heute und summieren die verknüpften Bußgelder (über die fines Relation)
        $dailyFinesAmount = Report::whereDate('created_at', Carbon::today())
            ->with('fines') // Eager Loading der Fines Relation
            ->get()
            ->flatMap(function ($report) {
                return $report->fines;
            })
            ->sum('amount');

        // D) Gesuchte Personen
        // Wir zählen Einträge in der citizens Tabelle, wo 'is_wanted' true ist.
        // Falls die Spalte bei dir anders heißt (z.B. 'wanted'), bitte anpassen.
        // Falls die Spalte noch nicht existiert, gibt dies 0 zurück oder einen Fehler (je nach DB-Modus).
        // Um Fehler zu vermeiden, prüfen wir hier einfachhalber auf Existenz oder nehmen 0 an, 
        // aber für echte Funktion brauchst du: $table->boolean('is_wanted')->default(false); in der citizens Migration.
        try {
            $wantedCount = Citizen::where('is_wanted', true)->count();
        } catch (\Exception $e) {
            $wantedCount = 0; // Fallback, falls Spalte nicht existiert
        }


        // --- 2. BESTEHENDE LOGIK (ANKÜNDIGUNGEN, RÄNGE, STUNDEN) ---

        // Ankündigungen
        $announcements = Announcement::where('is_active', true)->with('user')->latest()->take(5)->get();

        // Rangverteilung
        $ranks = Rank::orderBy('level', 'desc')->get();
        $allUsers = User::with('roles')->get();
        $rankCounts = [];

        foreach ($allUsers as $u) {
            $userRankSlug = $u->rank;
            
            if (empty($userRankSlug)) {
                $userRankSlug = $u->getRoleNames()->first(function ($roleName) use ($ranks) {
                    return $ranks->contains('name', $roleName);
                });
            }

            if ($userRankSlug) {
                $rankObj = $ranks->firstWhere('name', $userRankSlug);
                if ($rankObj) {
                    $label = $rankObj->label;
                    if (!isset($rankCounts[$label])) {
                        $rankCounts[$label] = 0;
                    }
                    $rankCounts[$label]++;
                }
            }
        }

        $sortedRankDistribution = [];
        foreach ($ranks as $rank) {
            if (isset($rankCounts[$rank->label])) {
                $sortedRankDistribution[$rank->label] = $rankCounts[$rank->label];
            }
        }

        $totalUsers = array_sum($sortedRankDistribution);
        
        // Letzte Berichte (Persönliche Übersicht)
        $lastReports = Report::where('user_id', $user->id)->latest()->take(3)->get();
            
        // Wochenstunden Berechnung
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $totalSeconds = 0;

        $dutyLogs = ActivityLog::where('user_id', $user->id)
            ->whereIn('log_type', ['DUTY_START', 'DUTY_END'])
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->orderBy('created_at', 'asc')
            ->get();

        $lastStartLog = null;

        foreach ($dutyLogs as $log) {
            if ($log->log_type === 'DUTY_START') {
                $lastStartLog = $log;
            } elseif ($log->log_type === 'DUTY_END' && $lastStartLog) {
                $totalSeconds += $lastStartLog->created_at->diffInSeconds($log->created_at);
                $lastStartLog = null; 
            }
        }

        if ($lastStartLog) {
            $totalSeconds += $lastStartLog->created_at->diffInSeconds(Carbon::now());
        }

        $weeklyHours = gmdate("H:i:s", $totalSeconds);

        // --- 3. VIEW RETURN ---
        return view('dashboard', [
            // Neue Variablen
            'myReportCount' => $myReportCount,
            'openCasesCount' => $openCasesCount,
            'dailyFinesAmount' => $dailyFinesAmount,
            'wantedCount' => $wantedCount,
            
            // Bestehende Variablen
            'announcements' => $announcements,
            'rankDistribution' => $sortedRankDistribution,
            'totalUsers' => $totalUsers,
            'lastReports' => $lastReports,
            'weeklyHours' => $weeklyHours,
        ]);
    }
}