<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Models\Exam;
use App\Models\Evaluation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\PotentiallyNotifiableActionOccurred;
use App\Services\ExamAttemptService;
use App\Http\Requests\Admin\GenerateExamAttemptRequest;
use App\Http\Requests\Admin\FinalizeExamRequest;

class ExamAttemptController extends Controller
{
    protected $attemptService;

    public function __construct(ExamAttemptService $attemptService)
    {
        $this->attemptService = $attemptService;
        $this->middleware('can:viewAny,' . ExamAttempt::class)->only('index');
    }

    public function index()
    {
        $attempts = ExamAttempt::with(['exam', 'user', 'evaluator']) 
                            ->orderBy('id', 'desc')
                            ->paginate(25);
        return view('admin.exams.attempts-index', compact('attempts'));
    }

    public function store(GenerateExamAttemptRequest $request)
    {
        $validated = $request->validated();
        $exam = Exam::findOrFail($validated['exam_id']);
        $user = User::findOrFail($validated['user_id']);
        $evaluation = isset($validated['evaluation_id']) ? Evaluation::find($validated['evaluation_id']) : null;

        $attempt = $this->attemptService->generateAttempt($user, $exam);

        // NEU: Den aktuellen Admin direkt als Prüfer (Evaluator) eintragen
        $attempt->update(['evaluator_id' => Auth::id()]);
        $attempt->update(['started_at' => now()]);
        if ($evaluation) {
             $evaluation->update(['status' => 'processed']);
        }

        $secureUrl = route('exams.take', $attempt);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'log_type' => 'EXAM',
            'action' => 'LINK_GENERATED',
            'target_id' => $attempt->id,
            'description' => "Prüfungslink für '{$exam->title}' wurde für {$user->name} generiert."
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            'Admin\ExamAttemptController@store',
            $user,
            $attempt,
            Auth::user()
        );

        return back()->with('success', 'Prüfungslink generiert und Sie wurden als Prüfer eingetragen!')
                     ->with('secure_url', $secureUrl);
    }

    public function show(ExamAttempt $attempt)
    {
        $this->authorize('viewResult', $attempt);
        
        $attempt->load([
            'exam.questions.options',
            'user', 
            'answers.question', 
            'answers.option',
            'evaluator'
        ]);
        
        return view('exams.result', compact('attempt'));
    }

    public function update(FinalizeExamRequest $request, ExamAttempt $attempt)
    {
        $this->authorize('setEvaluated', $attempt);
        $validated = $request->validated();

        $isPassed = $validated['final_score'] >= $attempt->exam->pass_mark;
        $status_result_for_log = $isPassed ? 'bestanden' : 'nicht_bestanden';
        
        $validated['evaluator_id'] = Auth::id(); // Hier wird der Prüfer beim Speichern des Ergebnisses auch nochmal gesetzt/aktualisiert

        $attempt = $this->attemptService->finalizeAttempt($attempt, $validated); 

        $actionDesc = "Prüfung '{$attempt->exam->title}' von {$attempt->user->name} wurde als '{$status_result_for_log}' bewertet. Score: {$validated['final_score']}%";
        ActivityLog::create(['user_id' => Auth::id(), 'log_type' => 'EXAM', 'action' => 'EVALUATED', 'target_id' => $attempt->id, 'description' => $actionDesc]);

        PotentiallyNotifiableActionOccurred::dispatch(
            'Admin\ExamAttemptController@update',
            $attempt->user,
            $attempt,
            Auth::user(),
            ['status_result' => $status_result_for_log, 'final_score' => $validated['final_score']]
        );

        return redirect()->route('admin.exams.attempts.index')->with('success', "Prüfung finalisiert: Score für {$attempt->user->name} auf {$validated['final_score']}% gesetzt.");
    }

    public function resetAttempt(ExamAttempt $attempt)
    {
        $this->authorize('resetAttempt', $attempt);
        $this->attemptService->resetAttempt($attempt);

        // Beim Resetten könnte man überlegen, ob man den Evaluator löscht oder beibehält.
        // Meistens behält man ihn oder setzt ihn neu, wenn jemand resettet.
        // Hier lassen wir es erstmal wie es ist (Evaluator bleibt oder wird durch resetAttempt Service evtl. genullt, je nach Service-Logik).

        ActivityLog::create([
            'user_id' => Auth::id(),
            'log_type' => 'EXAM',
            'action' => 'RESET',
            'target_id' => $attempt->id,
            'description' => "Prüfungsversuch #{$attempt->id} von {$attempt->user->name} wurde zurückgesetzt."
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            'Admin\ExamAttemptController@resetAttempt',
            $attempt->user,
            $attempt,
            Auth::user()
        );

        return back()->with('success', 'Prüfungsversuch wurde zurückgesetzt.');
    }

    public function sendLink(ExamAttempt $attempt) 
    {
        $this->authorize('sendLink', $attempt);
        
        // NEU: Wenn der Link erneut gesendet wird, trägt sich der aktuelle User als Prüfer ein
        $attempt->update(['evaluator_id' => Auth::id()]);
        $attempt->update(['started_at' => now()]);        
        $attempt->update(['status' => 'in_progress']);
        $secureUrl = route('exams.take', $attempt);

        PotentiallyNotifiableActionOccurred::dispatch(
            'Admin\ExamAttemptController@sendLink',
            $attempt->user,
            $attempt,
            Auth::user()
        );

        return back()->with('success', 'Prüfungslink erneut generiert und Sie wurden als Prüfer eingetragen!')
                     ->with('secure_url', $secureUrl);
    }

    public function setEvaluated(Request $request, ExamAttempt $attempt)
    {
           $this->authorize('setEvaluated', $attempt);
           $validated = $request->validate(['score' => 'required|integer|min:0|max:100']);

           $isPassed = $validated['score'] >= $attempt->exam->pass_mark;
           $resultText = $isPassed ? 'Bestanden' : 'Nicht bestanden';

           $attempt->update([
               'status' => 'evaluated',
               'score' => $validated['score'],
               'evaluator_id' => Auth::id(), // Hier wird der Prüfer explizit gesetzt
           ]);

           $message = "Prüfungsversuch #{$attempt->id} von {$attempt->user->name} wurde manuell bewertet: Score {$validated['score']}% ({$resultText}).";
           ActivityLog::create([
               'user_id' => Auth::id(),
               'log_type' => 'EXAM',
               'action' => 'EVALUATED_MANUAL',
               'target_id' => $attempt->id,
               'description' => $message
           ]);

           PotentiallyNotifiableActionOccurred::dispatch(
               'Admin\ExamAttemptController@setEvaluated',
               $attempt->user,
               $attempt,
               Auth::user(),
               ['isPassed' => $isPassed]
           );

           return back()->with('success', 'Score manuell gesetzt.');
    }

    public function destroy(ExamAttempt $attempt)
    {
        $this->authorize('delete', $attempt);

        $attempt->load(['exam', 'user']);
        $attemptTitle = $attempt->exam->title ?? 'Unbekannte Prüfung';
        $attemptUser = $attempt->user->name ?? 'Unbekannter User';
        $attemptId = $attempt->id;
        $deletedAttemptData = $attempt->toArray();

        $this->attemptService->deleteAttempt($attempt);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'log_type' => 'EXAM',
            'action' => 'DELETED',
            'target_id' => $attemptId,
            'description' => "Prüfungsversuch #{$attemptId} ('{$attemptTitle}') von {$attemptUser} wurde endgültig gelöscht."
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            'Admin\ExamAttemptController@destroy',
             Auth::user(),
             (object) $deletedAttemptData,
             Auth::user(),
             ['id' => $attemptId, 'exam_title' => $attemptTitle, 'user_name' => $attemptUser]
        );

        return redirect()->route('admin.exams.attempts.index')->with('success', 'Prüfungsversuch wurde endgültig gelöscht.');
    }
}