<?php

namespace App\Http\Controllers;

use App\Events\PotentiallyNotifiableActionOccurred; // Event hinzufügen
use App\Models\ActivityLog;
use App\Models\Citizen;
use App\Models\Report;
use App\Models\User; // Hinzugefügt für die Suche
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Verknüpft den Controller mit der ReportPolicy.
     */
    public function __construct()
    {
        $this->authorizeResource(Report::class, 'report');
    }

    /**
     * Zeigt eine Liste aller Einsatzberichte an, inkl. Suchfunktion.
     */
    public function index(Request $request)
    {
        $query = Report::with('user')->latest();

        // Nur Admins dürfen alle Berichte sehen, andere nur ihre eigenen
        // Annahme: 'viewAny' prüft, ob der User alle sehen darf
        if (Auth::user()->cannot('viewAny', Report::class)) {
             $query->where('user_id', Auth::id());
        }


        // Suchfunktion
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('patient_name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $reports = $query->paginate(15)->withQueryString();

        return view('reports.index', compact('reports'));
    }

    /**
     * Zeigt das Formular zum Erstellen eines neuen Berichts an.
     */
    public function create()
    {
        $templates = config('report_templates', []);
        $citizens = Citizen::orderBy('name')->get(); // Bürgerliste laden
        $allStaff = User::orderBy('name')->get(); // Alle Mitarbeiter laden

        return view('reports.create', compact('templates', 'citizens', 'allStaff'));
    }

    /**
     * Speichert einen neuen Bericht in der Datenbank.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'patient_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'incident_description' => 'required|string',
            'actions_taken' => 'required|string',
            'attending_staff' => 'nullable|array',
            'attending_staff.*' => 'exists:users,id',
            // NEU: Validierung der Bußgelder
            'fines' => 'nullable|array',
            'fines.*' => 'exists:fines,id',
        ]);

        /** @var User $creator */
        $creator = Auth::user();
        $validatedData['user_id'] = $creator->id;

        $citizen = Citizen::where('name', $validatedData['patient_name'])->first();
        if ($citizen) {
            $validatedData['citizen_id'] = $citizen->id;
        }

        $report = Report::create($validatedData);

        if ($request->has('attending_staff')) {
            $report->attendingStaff()->attach($request->input('attending_staff'));
        }

        // NEU: Bußgelder verknüpfen
        if ($request->has('fines')) {
            $report->fines()->attach($request->input('fines'));
            
            // Optional: Berechne automatisch die Gesamtsumme/Haftzeit und schreibe sie in actions_taken oder ein extra Feld
            // $totalFine = Fine::whereIn('id', $request->input('fines'))->sum('amount');
        }

        // Logging
        ActivityLog::create([
            'user_id' => $creator->id,
            'log_type' => 'REPORT',
            'action' => 'CREATED',
            'target_id' => $report->id,
            'description' => "Einsatzbericht '{$report->title}' erstellt.",
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            action: 'ReportController@store',
            triggeringUser: $citizen ?? (object)['name' => $report->patient_name],
            relatedModel: $report,
            actorUser: $creator
        );

        return redirect()->route('reports.index');
    }

    /**
     * Zeigt einen einzelnen Bericht detailliert an.
     */
    public function show(Report $report)
    {
        // NEU: 'fines' relation laden
        $report->load(['user', 'citizen', 'attendingStaff', 'fines']); 
        return view('reports.show', compact('report'));
    }


    /**
     * Zeigt das Formular zum Bearbeiten eines Berichts an.
     */
    public function edit(Report $report)
    {
        $templates = config('report_templates', []);
        $citizens = Citizen::orderBy('name')->get();
        $allStaff = User::orderBy('name')->get();
        
        // NEU: Fines laden
        $fines = Fine::orderBy('catalog_section')->orderBy('offense')->get();
        
        // Relationen laden
        $report->load(['attendingStaff', 'fines']);

        return view('reports.edit', compact('report', 'templates', 'citizens', 'allStaff', 'fines'));
    }

    /**
     * Aktualisiert einen Bericht in der Datenbank.
     */
    public function update(Request $request, Report $report)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'patient_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'incident_description' => 'required|string',
            'actions_taken' => 'required|string',
            'attending_staff' => 'nullable|array',
            'attending_staff.*' => 'exists:users,id',
            'fines' => 'nullable|array',
            'fines.*' => 'exists:fines,id',
        ]);

        $citizen = Citizen::where('name', $validatedData['patient_name'])->first();
        $validatedData['citizen_id'] = $citizen ? $citizen->id : null;

        $report->update($validatedData);
        
        // Sync Staff
        $report->attendingStaff()->sync($request->input('attending_staff', []));
        
        // NEU: Sync Fines (aktualisiert die Bußgelder, löscht nicht gewählte)
        $report->fines()->sync($request->input('fines', []));

                // Logging
        ActivityLog::create([
            'user_id' => $editor->id,
            'log_type' => 'REPORT',
            'action' => 'UPDATED',
            'target_id' => $report->id,
            'description' => "Einsatzbericht '{$report->title}' ({$report->id}) aktualisiert.",
        ]);

        // --- BENACHRICHTIGUNG VIA EVENT ---
        PotentiallyNotifiableActionOccurred::dispatch(
            action: 'ReportController@update',
            triggeringUser: $citizen ?? (object)['name' => $report->patient_name],
            relatedModel: $report,
            actorUser: $editor
        );
        return redirect()->route('reports.index');
    }
    /**
     * Löscht einen Bericht aus der Datenbank.
     */
    public function destroy(Report $report)
    {
        // Policy prüft implizit 'delete'
        /** @var User $deleter */
        $deleter = Auth::user();
        $reportTitle = $report->title;
        $reportId = $report->id;
        $patientName = $report->patient_name; // Namen für Event speichern

        $report->delete();

        // Logging
        ActivityLog::create([
            'user_id' => $deleter->id,
            'log_type' => 'REPORT',
            'action' => 'DELETED',
            'target_id' => $reportId, // Use the stored ID after deletion
            'description' => "Einsatzbericht '{$reportTitle}' ({$reportId}) gelöscht.",
        ]);

        // --- BENACHRICHTIGUNG VIA EVENT ---
        PotentiallyNotifiableActionOccurred::dispatch(
            action: 'ReportController@destroy',
            // Hier gibt es kein direktes Citizen-Objekt mehr als Trigger, wir nehmen den Namen
            triggeringUser: (object)['name' => $patientName],
            relatedModel: null, // Modell existiert nicht mehr
            actorUser: $deleter,
            additionalData: ['title' => $reportTitle, 'patient_name' => $patientName] // Zusätzliche Daten
        );
        // ---------------------------------

        // Erfolgsmeldung entfernt
        return redirect()->route('reports.index');
    }
}

