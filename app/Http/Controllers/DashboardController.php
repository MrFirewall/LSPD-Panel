<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Modelle importieren
use App\Models\User;
use App\Models\Report;
use App\Models\Announcement;
use App\Models\Fine;      // Angenommen, du hast ein Fine Model
use App\Models\Citizen;   // Angenommen, du hast ein Citizen Model für Gesuchte
use App\Models\Rank;

class DashboardController extends Controller
{
    /**
     * Zeigt das Haupt-Dashboard an.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // ---------------------------------------------------------
        // 1. NEUE DIENSTZEIT-BERECHNUNG (aus DutyRecord Tabelle)
        // ---------------------------------------------------------
        
        // Holt das Array mit allen Wochen (nutzt die neue, performante User-Methode)
        $allWeeklyData = $user->calculateWeeklyHoursSinceEntry();
        
        // Generiere den Schlüssel für die aktuelle Woche (z.B. "2025_KW50")
        $currentKwKey = Carbon::now()->format('Y') . "_KW" . Carbon::now()->format('W');
        
        $secondsThisWeek = 0;

        // Prüfen, ob für diese Woche Daten existieren
        if (isset($allWeeklyData[$currentKwKey])) {
            // Wir nehmen die 'normal_seconds'. Falls Leitstelle separat zählt, hier anpassen.
            $secondsThisWeek = $allWeeklyData[$currentKwKey]['normal_seconds'];
            
            // Optional: Falls Leitstelle auch zur Gesamtanzeige zählen soll:
            // $secondsThisWeek += $allWeeklyData[$currentKwKey]['leitstelle_seconds'];
        }

        // Sekunden in das Format HH:MM:SS umwandeln für die Anzeige im Dashboard
        $hours = floor($secondsThisWeek / 3600);
        $minutes = floor(($secondsThisWeek % 3600) / 60);
        $seconds = $secondsThisWeek % 60;
        
        $weeklyHours = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);


        // ---------------------------------------------------------
        // 2. STATISTIKEN LADEN
        // ---------------------------------------------------------

        // Statistik: Meine Berichte
        $myReportCount = $user->reports()->count();

        // Statistik: Offene Akten (Gesamt)
        // Annahme: Es gibt eine Spalte 'status' in der reports Tabelle
        $openCasesCount = Report::count();

        // Statistik: Bußgelder Heute
        // Hinweis: Falls das Model anders heißt (z.B. Ticket), hier anpassen.
        // Wir fangen Fehler ab, falls das Model 'Fine' noch nicht existiert.
        try {
            $dailyFinesAmount = Fine::whereDate('created_at', Carbon::today())->sum('amount');
        } catch (\Exception $e) {
            $dailyFinesAmount = 0; // Fallback, falls Tabelle nicht existiert
        }

        // Statistik: Gesuchte Personen
        // Hinweis: Falls das Model 'Citizen' oder das Feld 'wanted' anders heißt, anpassen.
        try {
            $wantedCount = Citizen::where('wanted', true)->count();
        } catch (\Exception $e) {
            $wantedCount = 0; // Fallback
        }


        // ---------------------------------------------------------
        // 3. INHALTE LADEN
        // ---------------------------------------------------------

        // Ankündigungen (die neuesten 3)
        $announcements = Announcement::with('user')
                            ->latest()
                            ->take(3)
                            ->get();

        // Letzte Berichte des Users (die neuesten 5)
        $lastReports = $user->reports()
                            ->latest()
                            ->take(5)
                            ->get();


        // ---------------------------------------------------------
        // 4. PERSONAL ÜBERSICHT
        // ---------------------------------------------------------

        // Gesamtpersonal
        $totalUsers = User::count();

        // Rangverteilung (Gruppiert nach Rang-Namen)
        // Dies zählt, wie viele User welchen Rang haben
        $rawRankDistribution = User::select('rank', DB::raw('count(*) as total'))
                                ->groupBy('rank')
                                ->pluck('total', 'rank')
                                ->toArray();
        
        // Wir versuchen, die schönen Labels aus der Ranks-Tabelle zu holen
        $rankLabels = Rank::pluck('label', 'name')->toArray();
        
        $rankDistribution = [];
        foreach($rawRankDistribution as $rankSlug => $count) {
            // Wenn es ein Label gibt, nimm das, sonst den Slug (erster Buchstabe groß)
            $label = $rankLabels[$rankSlug] ?? ucfirst($rankSlug);
            $rankDistribution[$label] = $count;
        }


        // ---------------------------------------------------------
        // 5. VIEW RENDERN
        // ---------------------------------------------------------

        return view('dashboard', [
            // Die wichtigste Variable für dein Problem:
            'weeklyHours' => $weeklyHours,

            // Statistiken
            'myReportCount' => $myReportCount,
            'openCasesCount' => $openCasesCount,
            'dailyFinesAmount' => $dailyFinesAmount,
            'wantedCount' => $wantedCount,

            // Listen
            'announcements' => $announcements,
            'lastReports' => $lastReports,

            // Personal
            'totalUsers' => $totalUsers,
            'rankDistribution' => $rankDistribution,
        ]);
    }
}