<?php

namespace App\Http\Controllers;

use App\Events\PotentiallyNotifiableActionOccurred;
use App\Models\ActivityLog;
use App\Models\Citizen;
use App\Models\Report;
use App\Models\User;
use App\Models\Fine; // WICHTIG: Das Model importieren
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
        $citizens = Citizen::orderBy('name')->get();
        $allStaff = User::orderBy('name')->get();

        // FIX: Hier laden wir die Bußgelder für das Dropdown
        $fines = Fine::orderBy('catalog_section')->orderBy('offense')->get();

        return view('reports.create', compact('templates', 'citizens', 'allStaff', 'fines'));
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
            'fines' => 'nullable|array',         // Validierung für Fines
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

        // Fines verknüpfen
        if ($request->has('fines')) {
            $report->fines()->attach($request->input('fines'));
        }

        // Logging
        ActivityLog::create([
            'user_id' => $creator->id,
            'log_type' => 'REPORT',
            'action' => 'CREATED',
            'target_id' => $report->id,
            'description' => "Einsatzbericht '{$report->title}' erstellt (Patient: {$report->patient_name}).",
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            triggeringUser: $citizen ?? (object)['name' => $report->patient_name],
            relatedModel: $report,
            actorUser: $creator,
            additionalData: ['action' => 'ReportController@store']
        );

        return redirect()->route('reports.index');
    }

    /**
     * Zeigt einen einzelnen Bericht detailliert an.
     */
    public function show(Report $report)
    {
        // Fines Relation mitladen für die Ansicht
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
        
        // FIX: Auch hier Fines laden
        $fines = Fine::orderBy('catalog_section')->orderBy('offense')->get();
        
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

        /** @var User $editor */
        $editor = Auth::user();

        $citizen = Citizen::where('name', $validatedData['patient_name'])->first();
        $validatedData['citizen_id'] = $citizen ? $citizen->id : null;

        $report->update($validatedData);
        $report->attendingStaff()->sync($request->input('attending_staff', []));
        
        // Fines synchronisieren
        $report->fines()->sync($request->input('fines', []));

        ActivityLog::create([
            'user_id' => $editor->id,
            'log_type' => 'REPORT',
            'action' => 'UPDATED',
            'target_id' => $report->id,
            'description' => "Einsatzbericht '{$report->title}' ({$report->id}) aktualisiert.",
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            triggeringUser: $citizen ?? (object)['name' => $report->patient_name],
            relatedModel: $report,
            actorUser: $editor,
            additionalData: ['action' => 'ReportController@update']
        );

        return redirect()->route('reports.index');
    }

    /**
     * Löscht einen Bericht aus der Datenbank.
     */
    public function destroy(Report $report)
    {
        /** @var User $deleter */
        $deleter = Auth::user();
        $reportTitle = $report->title;
        $reportId = $report->id;
        $patientName = $report->patient_name;

        $report->delete();

        ActivityLog::create([
            'user_id' => $deleter->id,
            'log_type' => 'REPORT',
            'action' => 'DELETED',
            'target_id' => $reportId,
            'description' => "Einsatzbericht '{$reportTitle}' ({$reportId}) gelöscht.",
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            triggeringUser: (object)['name' => $patientName],
            relatedModel: null,
            actorUser: $deleter,
            additionalData: [
                'action' => 'ReportController@destroy',
                'title' => $reportTitle, 
                'patient_name' => $patientName
            ]
        );

        return redirect()->route('reports.index');
    }
}