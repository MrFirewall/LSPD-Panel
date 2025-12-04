<?php

namespace App\Http\Controllers;

use App\Events\PotentiallyNotifiableActionOccurred;
use App\Models\ActivityLog;
use App\Models\Citizen;
use App\Models\Report;
use App\Models\User;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Report::class, 'report');
    }

    public function index(Request $request)
    {
        // Wir laden 'user.rank' mit, um das Label anzuzeigen
        $query = Report::with(['user.rank'])->latest();

        if (Auth::user()->cannot('viewAny', Report::class)) {
             $query->where('user_id', Auth::id());
        }

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

    public function create()
    {
        $templates = config('report_templates', []);
        $citizens = Citizen::orderBy('name')->get();
        // Hier auch Rang laden für die Anzeige im Dropdown
        $allStaff = User::with('rank')->orderBy('name')->get();
        $fines = Fine::orderBy('catalog_section')->orderBy('offense')->get();

        return view('reports.create', compact('templates', 'citizens', 'allStaff', 'fines'));
    }

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
            // Wir erwarten nun ein Array von Bußgeldern mit ID und Bemerkung
            'fines' => 'nullable|array', 
            'fines.*.id' => 'exists:fines,id',
            'fines.*.remark' => 'nullable|string',
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

        // Fines verknüpfen mit individueller Bemerkung
        if ($request->has('fines')) {
            $syncData = [];
            foreach ($request->input('fines') as $fineData) {
                // Wir nutzen die ID als Key für sync, und übergeben die Pivot-Daten
                // Da man theoretisch dasselbe Bußgeld 2x haben könnte, wäre attach() besser, 
                // aber sync() ist sauberer beim Update. 
                // Für multiple gleiche Einträge müsste man $report->fines()->attach(...) in Loop nutzen.
                // Hier gehen wir erstmal von Unique pro Bericht aus oder nutzen die ID als Key.
                
                $syncData[$fineData['id']] = ['remark' => $fineData['remark'] ?? ''];
            }
            $report->fines()->sync($syncData);
        }

        ActivityLog::create([
            'user_id' => $creator->id,
            'log_type' => 'REPORT',
            'action' => 'CREATED',
            'target_id' => $report->id,
            'description' => "Einsatzbericht '{$report->title}' erstellt.",
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            triggeringUser: $citizen ?? (object)['name' => $report->patient_name],
            relatedModel: $report,
            actorUser: $creator,
            additionalData: ['action' => 'ReportController@store']
        );

        return redirect()->route('reports.index');
    }

    public function show(Report $report)
    {
        // User.rank laden
        $report->load(['user.rank', 'citizen', 'attendingStaff.rank', 'fines']);
        return view('reports.show', compact('report'));
    }

    public function edit(Report $report)
    {
        $templates = config('report_templates', []);
        $citizens = Citizen::orderBy('name')->get();
        $allStaff = User::with('rank')->orderBy('name')->get();
        $fines = Fine::orderBy('catalog_section')->orderBy('offense')->get();
        
        $report->load(['attendingStaff', 'fines']); 

        return view('reports.edit', compact('report', 'templates', 'citizens', 'allStaff', 'fines'));
    }

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
            'fines.*.id' => 'exists:fines,id',
            'fines.*.remark' => 'nullable|string',
        ]);

        /** @var User $editor */
        $editor = Auth::user();

        $citizen = Citizen::where('name', $validatedData['patient_name'])->first();
        $validatedData['citizen_id'] = $citizen ? $citizen->id : null;

        $report->update($validatedData);
        $report->attendingStaff()->sync($request->input('attending_staff', []));
        
        // Fines synchronisieren mit Bemerkung
        if ($request->has('fines')) {
            $syncData = [];
            foreach ($request->input('fines') as $fineData) {
                $syncData[$fineData['id']] = ['remark' => $fineData['remark'] ?? ''];
            }
            $report->fines()->sync($syncData);
        } else {
            $report->fines()->detach();
        }

        ActivityLog::create([
            'user_id' => $editor->id,
            'log_type' => 'REPORT',
            'action' => 'UPDATED',
            'target_id' => $report->id,
            'description' => "Einsatzbericht '{$report->title}' aktualisiert.",
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            triggeringUser: $citizen ?? (object)['name' => $report->patient_name],
            relatedModel: $report,
            actorUser: $editor,
            additionalData: ['action' => 'ReportController@update']
        );

        return redirect()->route('reports.index');
    }

    public function destroy(Report $report)
    {
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
            'description' => "Einsatzbericht '{$reportTitle}' gelöscht.",
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