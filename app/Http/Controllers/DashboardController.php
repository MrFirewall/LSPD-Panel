<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Report;
use App\Models\User;
use App\Models\Rank; // Importiere das Rank Model
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. ANKÜNDIGUNGEN
        $announcements = Announcement::where('is_active', true)->with('user')->latest()->take(5)->get();

        // 2. RANGVERTEILUNG
        // Wir holen alle Ränge, sortiert nach Level (höchster zuerst: 19 -> 1)
        $ranks = Rank::orderBy('level', 'desc')->get();
        
        $allUsers = User::with('roles')->get();
        
        $rankCounts = [];

        foreach ($allUsers as $u) {
            // Schritt A: "Den wirklichen Rank in der User Tabelle nehmen"
            // Wir greifen auf die Spalte 'rank' im User-Model zu.
            // Dort sollte der 'slug' (z.B. 'polizeimeister') stehen.
            $userRankSlug = $u->rank;

            // Schritt B: Fallback (Falls die Spalte 'rank' leer ist)
            // Wir suchen in den Rollen nach einem gültigen Polizeirang.
            // 'super-admin' wird hier automatisch ignoriert, da er nicht in der $ranks Liste ist.
            if (empty($userRankSlug)) {
                $userRankSlug = $u->getRoleNames()->first(function ($roleName) use ($ranks) {
                    return $ranks->contains('name', $roleName);
                });
            }

            // Schritt C: Wenn wir einen gültigen Slug gefunden haben (der in der ranks Tabelle existiert)
            if ($userRankSlug) {
                // Wir suchen das passende Rank-Objekt aus unserer geladenen Liste
                $rankObj = $ranks->firstWhere('name', $userRankSlug);
                
                if ($rankObj) {
                    // Wir nutzen das LABEL für die Zählung/Anzeige
                    $label = $rankObj->label;
                    
                    if (!isset($rankCounts[$label])) {
                        $rankCounts[$label] = 0;
                    }
                    $rankCounts[$label]++;
                }
            }
        }

        // Schritt D: Das Array für die View bauen, basierend auf der korrekten Hierarchie-Reihenfolge
        $sortedRankDistribution = [];
        foreach ($ranks as $rank) {
            // Nur Ränge aufnehmen, die auch mindestens einen User haben
            if (isset($rankCounts[$rank->label])) {
                $sortedRankDistribution[$rank->label] = $rankCounts[$rank->label];
            }
        }

        // Berechne Total Users basierend auf den gezählten Rängen (ohne reine Admins)
        $totalUsers = array_sum($sortedRankDistribution);
        
        // 3. PERSÖNLICHE ÜBERSICHT
        $lastReports = Report::where('user_id', $user->id)->latest()->take(3)->get();
            
        // 4. NEU: BERECHNUNG DER WOCHENSTUNDEN
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $totalSeconds = 0;

        // Hole alle relevanten Logs für den User in der aktuellen Woche
        $dutyLogs = ActivityLog::where('user_id', $user->id)
            ->whereIn('log_type', ['DUTY_START', 'DUTY_END'])
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->orderBy('created_at', 'asc')
            ->get();

        $lastStartLog = null;

        foreach ($dutyLogs as $log) {
            if ($log->log_type === 'DUTY_START') {
                // Speichere den Start-Log
                $lastStartLog = $log;
            } elseif ($log->log_type === 'DUTY_END' && $lastStartLog) {
                // Wenn ein End-Log gefunden wird und ein Start-Log existiert, berechne die Differenz
                $totalSeconds += $lastStartLog->created_at->diffInSeconds($log->created_at);
                // Setze den Start-Log zurück, um Paare zu bilden
                $lastStartLog = null; 
            }
        }

        // Sonderfall: User ist aktuell im Dienst (letzter Log war DUTY_START)
        if ($lastStartLog) {
            $totalSeconds += $lastStartLog->created_at->diffInSeconds(Carbon::now());
        }

        // Formatiere die Gesamtsekunden in ein lesbares Format (HH:MM:SS)
        $weeklyHours = gmdate("H:i:s", $totalSeconds);

        // 5. DATEN AN DIE VIEW ÜBERGEBEN
        return view('dashboard', [
            'announcements' => $announcements,
            'rankDistribution' => $sortedRankDistribution,
            'totalUsers' => $totalUsers,
            'lastReports' => $lastReports,
            'weeklyHours' => $weeklyHours,
        ]);
    }
}