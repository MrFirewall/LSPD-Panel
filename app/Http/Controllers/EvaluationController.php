<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\TrainingModule;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\PotentiallyNotifiableActionOccurred;
use App\Services\ExamAttemptService;

class EvaluationController extends Controller
{
    // =========================================================================
    // KONFIGURATION & STATICS
    // =========================================================================
    
    public static array $grades = ['Sehr Gut', 'Gut', 'Befriedigend', 'Ausreichend', 'Mangelhaft', 'Ungenügend', 'Nicht feststellbar'];
    public static array $periods = ['00 - 06 Uhr', '06 - 12 Uhr', '12 - 18 Uhr', '18 - 00 Uhr'];
    public static array $applicationTypes = ['modul_anmeldung', 'pruefung_anmeldung'];
    public static array $evaluationTypes = ['azubi', 'praktikant', 'mitarbeiter', 'leitstelle', 'gutachten', 'anmeldung'];
    
    // Alle erlaubten Typen für die Validierung
    public static array $allTypeLabels = [
        'azubi', 'praktikant', 'mitarbeiter', 'leitstelle', 'gutachten',
        'anmeldung', 'modul_anmeldung', 'pruefung_anmeldung'
    ];

    protected $attemptService;

    public function __construct(ExamAttemptService $attemptService)
    {
        // Service injizieren für direkte Exam-Erstellung
        $this->attemptService = $attemptService;
        
        // Policy-basierte Autorisierung
        $this->authorizeResource(Evaluation::class, 'evaluation');
    }

    // =========================================================================
    // ÜBERSICHT (INDEX)
    // =========================================================================

    public function index()
    {
        $canViewAll = Auth::user()->can('evaluations.view.all');
        $userId = Auth::id();

        // 1. Anträge laden (Modul-Anmeldungen etc.)
        $applicationsQuery = Evaluation::whereIn('evaluation_type', self::$applicationTypes)
                                        ->latest('created_at');

        if (!$canViewAll) {
            $applicationsQuery->where('user_id', $userId);
        }
        $applications = $applicationsQuery->with('user')->paginate(20, ['*'], 'applicationsPage');

        // 2. Bewertungen laden
        $evaluationsQuery = Evaluation::whereIn('evaluation_type', self::$evaluationTypes)
                                       ->latest('created_at');
         if (!$canViewAll) {
            $evaluationsQuery->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('evaluator_id', $userId);
            });
        }
        $evaluations = $evaluationsQuery->with(['user', 'evaluator'])->paginate(15, ['*'], 'evaluationsPage');

        // 3. User für Modal laden (falls benötigt)
        $usersForModal = collect();
         if ($canViewAll && Auth::user()->can('generateExamLink', ExamAttempt::class)) {
             $usersForModal = User::leftJoin('ranks', 'users.rank', '=', 'ranks.name')
                 ->orderByDesc('ranks.level')
                 ->orderBy('users.name')
                 ->select('users.id', 'users.name')
                 ->get();
         }

        $counts = $this->getEvaluationCounts();

        return view('forms.evaluations.index', compact(
            'applications',
            'evaluations',
            'counts',
            'canViewAll',
            'usersForModal'
        ));
    }

    private function getEvaluationCounts()
    {
        $currentUserId = Auth::id();
        $relevantTypes = array_merge(self::$applicationTypes, self::$evaluationTypes);
        $counts = ['verfasst' => [], 'erhalten' => [], 'gesamt' => []];

        foreach ($relevantTypes as $type) {
            $counts['verfasst'][$type] = 0;
            $counts['erhalten'][$type] = 0;
            $counts['gesamt'][$type] = 0;
        }

        $allCounts = Evaluation::selectRaw('evaluation_type, user_id, evaluator_id, count(*) as count')
                                ->whereIn('evaluation_type', $relevantTypes)
                                ->groupBy('evaluation_type', 'user_id', 'evaluator_id')
                                ->get();

        foreach ($allCounts as $countData) {
            $type = $countData->evaluation_type;
            if (!isset($counts['gesamt'][$type])) continue;

            $counts['gesamt'][$type] += $countData->count;
            if ($countData->evaluator_id === $currentUserId) $counts['verfasst'][$type] += $countData->count;
            if ($countData->user_id === $currentUserId) $counts['erhalten'][$type] += $countData->count;
        }
        return $counts;
    }

    // =========================================================================
    // VIEW METHODEN FÜR FORMULARE
    // =========================================================================

    public function azubi()
    {
        $traineeRankNames = Rank::where('name', 'LIKE', '%anwaerter%')
                                ->orWhere('name', 'LIKE', '%schueler%')
                                ->pluck('name');

        $users = User::whereIn('rank', $traineeRankNames)
            ->leftJoin('ranks', 'users.rank', '=', 'ranks.name')
            ->orderByDesc('ranks.level')
            ->orderBy('users.name')
            ->select('users.id', 'users.name')
            ->get();

        return view('forms.evaluations.azubi', ['users' => $users, 'evaluationType' => 'azubi']);
    }

    public function praktikant()
    {
        return view('forms.evaluations.praktikant', ['evaluationType' => 'praktikant']);
    }

    public function leitstelle()
    {
        $users = User::leftJoin('ranks', 'users.rank', '=', 'ranks.name')
            ->orderByDesc('ranks.level')
            ->orderBy('users.name')
            ->select('users.id', 'users.name')
            ->get();

        return view('forms.evaluations.leitstelle', ['users' => $users, 'evaluationType' => 'leitstelle']);
    }

    public function mitarbeiter()
    {
        $traineeRankNames = Rank::where('name', 'LIKE', '%anwaerter%')
                                ->orWhere('name', 'LIKE', '%schueler%')
                                ->pluck('name');
        
        $users = User::whereNotIn('rank', $traineeRankNames)
            ->leftJoin('ranks', 'users.rank', '=', 'ranks.name')
            ->orderByDesc('ranks.level')
            ->orderBy('users.name')
            ->select('users.id', 'users.name')
            ->get();

        return view('forms.evaluations.mitarbeiter', ['users' => $users, 'evaluationType' => 'mitarbeiter']);
    }

    public function modulAnmeldung()
    {
        $existingModuleIds = Auth::user()->trainingModules()->pluck('training_module_id');
        $availableModules = TrainingModule::whereNotIn('id', $existingModuleIds)->orderBy('name')->get(['id', 'name']);

        return view('forms.evaluations.modul_anmeldung', [
            'evaluationType' => 'modul_anmeldung',
            'modules' => $availableModules
        ]);
    }

    public function pruefungsAnmeldung()
    {
        $availableExams = Exam::orderBy('title')->get(['id', 'title']);
        return view('forms.evaluations.pruefung_anmeldung', [
            'evaluationType' => 'pruefung_anmeldung',
            'exams' => $availableExams
        ]);
    }

    // =========================================================================
    // STORE (KERNLOGIK)
    // =========================================================================

    public function store(Request $request)
    {
        $evaluationType = $request->input('evaluation_type');

        // 1. Validierungsregeln definieren
        $rules = [
            'evaluation_type' => 'required|in:' . implode(',', self::$allTypeLabels),
            'description' => 'nullable|string|max:5000',
            // Datum/Period nur Pflicht, wenn es KEINE Prüfungsanmeldung ist
            'evaluation_date' => $evaluationType === 'pruefung_anmeldung' ? 'nullable|date' : 'required|date',
            'period' => $evaluationType === 'pruefung_anmeldung' ? 'nullable|string' : 'required|string',
            'data' => 'nullable|array',
        ];

        // Typspezifische Regeln
        if ($evaluationType === 'modul_anmeldung') {
            $rules['target_module_id'] = 'required|exists:training_modules,id';
        } elseif ($evaluationType === 'pruefung_anmeldung') {
            $rules['target_exam_id'] = 'required|exists:exams,id';
        } elseif ($evaluationType === 'praktikant') {
            $rules['target_name'] = 'required|string|max:255';
        } elseif (in_array($evaluationType, self::$evaluationTypes)) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        // =====================================================================
        // PFAD A: PRÜFUNGSANMELDUNG (Direkt in Exams, keine Evaluation)
        // =====================================================================
        if ($evaluationType === 'pruefung_anmeldung') {
            $exam = Exam::find($validated['target_exam_id']);
            
            // 1. Exam Attempt erstellen (Nutzt Auth User)
            $attempt = $this->attemptService->generateAttempt(Auth::user(), $exam);

            // 2. Activity Log (Typ EXAM)
            ActivityLog::create([
                'user_id' => Auth::id(),
                'log_type' => 'EXAM',
                'action' => 'REQUESTED',
                'target_id' => $attempt->id,
                'description' => "Prüfung '{$exam->title}' wurde beantragt und angelegt."
            ]);

            // 3. Benachrichtigung an Admins/Ausbilder
            // Wir übergeben $attempt als Zielobjekt -> Link führt zu Exam
            // Wir übergeben 'subject_name' -> Notification zeigt Titel an statt '?'
            PotentiallyNotifiableActionOccurred::dispatch(
                'EvaluationController@store_exam_request', 
                Auth::user(),   // Auslöser (User)
                $attempt,       // Ziel (Der Prüfungsversuch)
                Auth::user(),   // Akteur
                [
                    'subject_name' => $exam->title,
                    'message_type' => 'exam_request' 
                ]
            );

            // Früher Abbruch und Redirect
            return redirect()->route('forms.evaluations.index')
                             ->with('success', 'Prüfung wurde angelegt. Dein Prüfer wird dir den Link zusenden.');
        }

        // =====================================================================
        // PFAD B: NORMALE FORMULARE (Evaluations Eintrag erstellen)
        // =====================================================================

        $data = [
            'evaluator_id' => Auth::id(),
            'evaluation_type' => $validated['evaluation_type'],
            'evaluation_date' => $validated['evaluation_date'],
            'period' => $validated['period'],
            'json_data' => $validated['data'] ?? [],
            'description' => $validated['description'] ?? null,
            'status' => 'pending', 
        ];

        $logDescription = '';
        $relatedModel = null;
        $notificationSubject = null;

        if ($evaluationType === 'modul_anmeldung') {
            $module = TrainingModule::find($validated['target_module_id']);
            $data['user_id'] = Auth::id();
            $data['target_name'] = Auth::user()->name;
            $data['json_data']['module_id'] = $module->id;
            $data['json_data']['module_name'] = $module->name;
            $logDescription = "Antrag auf Modulanmeldung für '{$module->name}' von {$data['target_name']} eingereicht.";
            $relatedModel = $module;
            $notificationSubject = $module->name;

        } elseif ($evaluationType === 'praktikant') {
            $data['user_id'] = null;
            $data['target_name'] = $validated['target_name'];
            $logDescription = "Neue Bewertung für Praktikant/in '{$data['target_name']}' ({$evaluationType}) erstellt.";
            $data['status'] = 'processed';
            $notificationSubject = $validated['target_name'];

        } elseif (in_array($evaluationType, self::$evaluationTypes)) {
            $data['user_id'] = $validated['user_id'];
            $targetUser = User::find($data['user_id']);
            $data['target_name'] = $targetUser->name;
            $logDescription = "Neue Bewertung für '{$data['target_name']}' ({$evaluationType}) erstellt.";
            $data['status'] = 'processed';
            // Bei Bewertungen ist der User selbst das Subject, braucht meist kein Extra-Subject
        }

        $evaluation = Evaluation::create($data);

        ActivityLog::create([
             'user_id' => Auth::id(),
             'log_type' => 'EVALUATION',
             'action' => 'CREATED',
             'target_id' => $evaluation->id,
             'description' => $logDescription,
         ]);

        // Benachrichtigung für normale Anträge
        if (in_array($evaluationType, self::$applicationTypes)) {
             PotentiallyNotifiableActionOccurred::dispatch(
                 'EvaluationController@store',
                 Auth::user(),
                 $evaluation, 
                 Auth::user(),
                 [
                    'related_model_type' => $relatedModel ? get_class($relatedModel) : null,
                    'subject_name' => $notificationSubject // Behebt das '?' auch bei Modulen
                 ]
             );
        }

        return redirect()->route('forms.evaluations.index')->with('success', 'Eintrag erfolgreich gespeichert.');
    }

    // =========================================================================
    // SHOW & DESTROY
    // =========================================================================

    public function show(Evaluation $evaluation)
    {
        $evaluation->load(['user', 'evaluator']);

        $evaluationData = is_array($evaluation->json_data)
            ? $evaluation->json_data
            : json_decode($evaluation->json_data, true) ?? [];

        $targetName = $evaluation->target_name ?? $evaluation->user?->name ?? 'Unbekannt';

        $relatedItem = null;
        if ($evaluation->evaluation_type === 'modul_anmeldung' && isset($evaluationData['module_id'])) {
            $relatedItem = TrainingModule::find($evaluationData['module_id']);
        }
        // Exam Logik hier nicht mehr nötig für neue Einträge, da diese nicht mehr existieren

        return view('forms.evaluations.show', compact('evaluation', 'evaluationData', 'targetName', 'relatedItem'));
    }

    public function destroy(Evaluation $evaluation)
    {
        // Activity Log Details vorbereiten
        $logDescription = "Eintrag #{$evaluation->id} (Typ: {$evaluation->evaluation_type})";
        $applicantName = $evaluation->target_name ?? $evaluation->user->name ?? 'Unbekannt';

        if (in_array($evaluation->evaluation_type, self::$applicationTypes)) {
            $subject = $evaluation->json_data['module_name'] ?? 'N/A';
            $logDescription = "Antrag '{$subject}' von {$applicantName} (ID: {$evaluation->id}) wurde gelöscht.";
        } else {
            $logDescription = "Bewertung (Typ: {$evaluation->evaluation_type}) für {$applicantName} (ID: {$evaluation->id}) wurde gelöscht.";
        }

        $deletedData = $evaluation->toArray();
        $triggeringUser = $evaluation->user ?? Auth::user(); 

        $evaluation->delete();

        ActivityLog::create([
             'user_id' => Auth::id(),
             'log_type' => 'EVALUATION',
             'action' => 'DELETED',
             'target_id' => $deletedData['id'],
             'description' => $logDescription,
         ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            'EvaluationController@destroy',
            $triggeringUser,    
            (object) $deletedData, 
            Auth::user(),       
            ['description' => $logDescription]
        );

        return redirect()->route('forms.evaluations.index')->with('success', 'Eintrag erfolgreich gelöscht.');
    }
}