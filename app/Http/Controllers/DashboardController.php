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
    // Die harte Array-Definition ($rankHierarchy) wurde entfernt, da wir nun die Datenbank nutzen.

    public function index()
    {
        $user = Auth::user();

        // 1. ANKÜNDIGUNGEN
        $announcements = Announcement::where('is_active', true)->with('user')->latest()->take(5)->get();

        // 2. RANGVERTEILUNG (Dynamisch aus der DB)
        $allUsers = User::with('roles')->get();
        $totalUsers = $allUsers->count();

        // Hole alle Ränge aus der Datenbank, sortiert nach Level (höchster zuerst)
        // Level 19 (Präsident) steht oben, Level 1 unten.
        $dbRanks = Rank::orderBy('level', 'desc')->get();

        // Zähle zuerst die Benutzer pro Rolle (basierend auf dem internen Rollennamen/Slug)
        $userCountsByRole = [];
        foreach ($allUsers as $u) {
            $role = $u->getRoleNames()->first(); // Holt den ersten Rollennamen (z.B. 'polizeipraesident')
            if ($role) {
                if (!isset($userCountsByRole[$role])) {
                    $userCountsByRole[$role] = 0;
                }
                $userCountsByRole[$role]++;
            }
        }

        // Baue das Anzeige-Array basierend auf der Sortierung der Rank-Tabelle
        $sortedRankDistribution = [];

        foreach ($dbRanks as $rank) {
            // $rank->name ist der Slug (z.B. 'polizeipraesident')
            // $rank->label ist der Anzeigename (z.B. 'Polizeipräsident/in')
            
            // Wenn Benutzer mit diesem Rang existieren, füge sie zur Liste hinzu
            if (isset($userCountsByRole[$rank->name])) {
                $sortedRankDistribution[$rank->label] = $userCountsByRole[$rank->name];
                
                // Entferne diesen Rang aus den Zählungen, um zu sehen, ob Ränge übrig bleiben, die nicht in der DB sind
                unset($userCountsByRole[$rank->name]);
            }
        }

        // Optional: Falls User Rollen haben, die NICHT in der Rank-Tabelle stehen (Fallback), hängen wir diese unten an
        foreach ($userCountsByRole as $roleName => $count) {
            $formattedName = ucwords(str_replace(['-', '_'], ' ', $roleName));
            $sortedRankDistribution[$formattedName] = $count;
        }
        
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