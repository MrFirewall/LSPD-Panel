<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceRecord;
use App\Models\Evaluation;
use App\Models\ExamAttempt;
use App\Models\TrainingModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;
use App\Events\PotentiallyNotifiableActionOccurred;
use App\Services\DiscordService;

// --- ANGEPASSTE USE-STATEMENTS ---
use App\Models\Role; // Benutzt dein eigenes Role-Modell
use Spatie\Permission\Models\Permission;
use App\Models\Department; // NEU: Department-Modell
use App\Models\Rank;       // NEU: Rank-Modell
// ------------------------------------

use App\Models\Pivots\TrainingModuleUser;
use Carbon\Carbon; // NEU: Für Datumsvergleiche

class UserController extends Controller
{
    /**
     * Definiert die unsichtbare Super-Admin Rolle.
     * @var string
     */
    private $superAdminRole = 'Super-Admin';


    public function __construct()
    {
        $this->middleware('can:users.view')->only('index', 'show');
        $this->middleware('can:users.create')->only(['create', 'store']);
        $this->middleware('can:users.edit')->only(['edit', 'update']);
        $this->middleware('can:users.manage.record')->only('addRecord');
        $this->middleware('can:users.manage.modules')->only(['update']);
    }

    /**
     * Gibt eine gefilterte Liste der Rollen zurück, die der aktuelle Admin verwalten darf.
     * (Angepasst an die Datenbank)
     */
    private function getManagableRoles()
    {
        $admin = Auth::user();

        // 1. Lade Konfigurationen aus der DB
        $ranks = Rank::pluck('level', 'name');
        $departments = Department::with('roles')->get(); 

        // Ausnahme: 'chief' (oder Super-Admin) dürfen immer alle Rollen verwalten.
        if ($admin->hasAnyRole('chief', $this->superAdminRole)) {
            return Role::where('name', '!=', $this->superAdminRole)->get();
        }

        // Ermittle Admin Level
        $adminRankLevel = $ranks->get($admin->rank, 0);
        $adminRoleNames = $admin->getRoleNames();

        $allRoles = Role::where('name', '!=', $this->superAdminRole)->get(); 
        $managableRoles = collect();

        foreach ($allRoles as $role) {
            // 1. RANG-ROLLEN PRÜFEN
            if ($ranks->has($role->name)) {
                // WICHTIG: Nur Ränge, die ECHT KLEINER sind als das eigene Level
                // Wenn Admin Level 14 ist, darf er Level 13 und tiefer sehen/vergeben.
                if ($ranks[$role->name] < $adminRankLevel) {
                    $managableRoles->push($role);
                }
                continue; 
            }

            // 2. ABTEILUNGS-ROLLEN PRÜFEN
            foreach ($departments as $department) {
                if ($department->roles->contains('name', $role->name)) {
                    if ($role->name === $department->leitung_role_name) {
                        // Leitungsrollen nur, wenn Admin hoch genug im Rang ist
                        if ($adminRankLevel >= $department->min_rank_level_to_assign_leitung) {
                            $managableRoles->push($role);
                        }
                    } else {
                        // Normale Rollen, wenn Admin Teil der Abteilungsleitung ist
                        if ($adminRoleNames->contains($department->leitung_role_name)) {
                            $managableRoles->push($role);
                        }
                    }
                    break; 
                }
            }
        }

        return $managableRoles->unique('id');
    }

    /**
     * KORRIGIERT: Hilfsfunktion, die die Super-Admin Rolle aus der Anzeige entfernt.
     * Klont den User, um das Original (z.B. Auth::user()) nicht zu verändern.
     */
    private function filterSuperAdminFromRoles(User $user): User
    {
        // KORREKTUR: Klonen, um das Originalobjekt nicht zu verändern (wichtig für Auth::user())
        $viewUser = clone $user; 
        
        if ($viewUser->relationLoaded('roles')) {
            $filteredRoles = $viewUser->roles->reject(function ($role) {
                return $role->name === $this->superAdminRole;
            });
            // Modifiziere nur den Klon
            $viewUser->setRelation('roles', $filteredRoles);
        }
        // Gib den modifizierten Klon zurück
        return $viewUser;
    }

    public function index()
    {
        $users = User::with('roles')->orderBy('personal_number')->get();

        // KORREKTUR: Filtere die Super-Admin-Rolle für die Anzeige heraus.
        // Verwende map(), um eine neue Collection mit den gefilterten Klonen zu erstellen.
        $filteredUsers = $users->map(function ($user) {
            return $this->filterSuperAdminFromRoles($user);
        });

        // KORREKTUR: 'compact' kann keine assoziativen Zuweisungen ('=>') annehmen.
        // Wir übergeben das Array direkt.
        return view('admin.users.index', ['users' => $filteredUsers]);
    }

    public function create()
    {
        $managableRoles = $this->getManagableRoles(); // Die flache Liste aller Rollen
        
        // --- NEUE KATEGORISIERUNG ---
        $allRanks = Rank::pluck('name'); // Alle Rang-Namen aus der DB
        $allDepartments = Department::with('roles')->get(); // Alle Abteilungen & ihre Rollen
        
        $categorizedRoles = [
            'Ranks' => [],
            'Departments' => [],
            'Other' => []
        ];

        foreach ($managableRoles as $role) {
            // 1. Ist es ein Rang?
            if ($allRanks->contains($role->name)) {
                $categorizedRoles['Ranks'][] = $role;
                continue;
            }
            
            // 2. Ist es eine Abteilungsrolle?
            $found = false;
            foreach ($allDepartments as $dept) {
                if ($dept->roles->contains('id', $role->id)) {
                    // Erstelle die Abteilungskategorie, falls sie noch nicht existiert
                    if (!isset($categorizedRoles['Departments'][$dept->name])) {
                        $categorizedRoles['Departments'][$dept->name] = [];
                    }
                    $categorizedRoles['Departments'][$dept->name][] = $role;
                    $found = true;
                    break; // Rolle gefunden, nächste Rolle prüfen
                }
            }

            // 3. Wenn nirgends zugeordnet -> "Andere"
            if (!$found) {
                $categorizedRoles['Other'][] = $role;
            }
        }
        // --- ENDE KATEGORISIERUNG ---

        $statuses = [
            'Aktiv', 'Probezeit', 'Beobachtung', 'Beurlaubt', 'Krankgeschrieben',
            'Suspendiert', 'Ausgetreten', 'Bewerbungsphase',
        ];
        
        // WICHTIG: Wir übergeben $categorizedRoles statt $roles
        return view('admin.users.create', compact('categorizedRoles', 'statuses'));
    }

    public function store(Request $request)
    {
        $managableRoleNames = $this->getManagableRoles()->pluck('name')->toArray();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'cfx_id' => 'required|string|unique:users,cfx_id',
            'status' => 'required|string',
            'roles' => 'sometimes|array',
            'roles.*' => [Rule::in($managableRoleNames)],
            'email' => 'nullable|email|max:255',
            'birthday' => 'nullable|date',
            'discord_name' => 'nullable|string|max:255',
            'forum_name' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
        ]);

        $selectedRoles = $request->roles ?? [];
        
        // --- ANGEPASSTE RANG-LOGIK (DB-ABFRAGE) ---
        $highestRankName = 'praktikant'; // Dein Standardwert
        $highestLevel = 0;
        
        // Hole die Level der Ränge, die auch ausgewählt wurden, aus der DB
        $rankLevels = Rank::whereIn('name', $selectedRoles)->pluck('level', 'name');

        foreach ($selectedRoles as $roleName) {
            if ($rankLevels->has($roleName) && $rankLevels[$roleName] > $highestLevel) {
                $highestLevel = $rankLevels[$roleName];
                $highestRankName = $roleName;
            }
        }
        $validatedData['rank'] = $highestRankName;
        // --- ENDE ANGEPASSTE RANG-LOGIK ---

        $validatedData['second_faction'] = $request->has('second_faction') ? 'Ja' : 'Nein';

        do {
            $newEmployeeId = rand(10000, 99999);
        } while (User::where('employee_id', $newEmployeeId)->exists());
        $validatedData['employee_id'] = $newEmployeeId;

        // Einstellungsdatum nur setzen, wenn es nicht explizit übergeben wurde
        if (empty($validatedData['hire_date'])) {
            $validatedData['hire_date'] = now();
        }
        $validatedData['last_edited_by'] = Auth::user()->name;
        $validatedData['last_edited_at'] = now();

        $user = User::create($validatedData);
        $user->syncRoles($selectedRoles);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'log_type' => 'USER',
            'action' => 'CREATED',
            'target_id' => $user->id,
            'description' => "Neuer Mitarbeiter '{$user->name}' ({$user->id}) angelegt.",
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            'Admin\UserController@store',
            $user,
            $user,
            Auth::user()
        );

        return redirect()->route('admin.users.index');
    }

    /**
     * Zeigt das Profil eines spezifischen Benutzers (Admin-Ansicht).
     * Der View 'profile.show' wird hier wiederverwendet.
     */
    public function show(User $user)
    {
       // KORREKTUR: Laden der Relationen, 'roles' hinzugefügt
        $user->load([
            'vacations',
            'receivedEvaluations' => fn($q) => $q->with('evaluator')->latest(),
            'roles' // KORREKTUR: Rollen müssen geladen werden!
        ]);

        // 1. Lade die Module
        $user->load('trainingModules');

        // 2. Lade die 'assigner'-Beziehung AUF die Pivot-Objekte (verhindert N+1 Queries)
        if ($user->trainingModules->isNotEmpty()) {
            $pivots = $user->trainingModules->pluck('pivot'); 
            (new \Illuminate\Database\Eloquent\Collection($pivots))->load('assigner');
        }

        // 1. Prüfungsversuche laden
        // KORREKTUR: 'evaluator' wird jetzt mitgeladen (und 'exam' statt exam.trainingModule)
        $examAttempts = ExamAttempt::where('user_id', $user->id)
                                    ->with(['exam', 'evaluator'])
                                    ->latest('completed_at') 
                                    ->get();

        // 2. Weitere Variablen laden
        $serviceRecords = $user->serviceRecords()->with('author')->latest()->get();
        $evaluationCounts = $this->calculateEvaluationCounts($user);
        $hourData = $user->calculateDutyHours();
        $weeklyHours = $user->calculateWeeklyHoursSinceEntry();

        // KORREKTUR: Wende den "sicheren" Filter an und übergib den Klon an die View
        $viewUser = $this->filterSuperAdminFromRoles($user);

        // KORREKTUR: 'compact' kann keine assoziativen Zuweisungen ('=>') annehmen.
        // Wir übergeben das Array direkt, damit die View die Variable $user erhält.
        return view('profile.show', [
            'user' => $viewUser, // KORREKTUR: Übergib den gefilterten Klon
            'serviceRecords' => $serviceRecords,
            'examAttempts' => $examAttempts,
            'evaluationCounts' => $evaluationCounts,
            'hourData' => $hourData,
            'weeklyHours' => $weeklyHours
        ]);
    }

    private function calculateEvaluationCounts(User $user): array
    {
        $typeLabels = ['azubi', 'praktikant', 'mitarbeiter', 'leitstelle']; // Nur relevante Typen
        $counts = ['verfasst' => [], 'erhalten' => []];

        // Zählungen des Profilbesitzers ($user) - ERHALTEN
        $receivedCounts = Evaluation::selectRaw('evaluation_type, count(*) as count')
                                    ->where('user_id', $user->id)
                                    ->whereIn('evaluation_type', $typeLabels)
                                    ->groupBy('evaluation_type')
                                    ->pluck('count', 'evaluation_type');

        // Zählungen des angemeldeten Benutzers (Auth::user()) - VERFASST
        $authoredCounts = Evaluation::selectRaw('evaluation_type, count(*) as count')
                                    ->where('evaluator_id', Auth::id())
                                    ->whereIn('evaluation_type', $typeLabels)
                                    ->groupBy('evaluation_type')
                                    ->pluck('count', 'evaluation_type');

        // Initialisiere mit 0 und fülle die Ergebnisse auf
        foreach ($typeLabels as $type) {
            $counts['erhalten'][$type] = $receivedCounts->get($type, 0);
            $counts['verfasst'][$type] = $authoredCounts->get($type, 0);
        }

        return $counts;
    }

    public function edit(User $user)
    {
        $statuses = [
            'Aktiv', 'Beurlaubt', 'Beobachtung', 'Krankgeschrieben',
            'Suspendiert', 'Ausgetreten', 'Bewerbungsphase', 'Probezeit',
        ];
        
        // --- NEUE KATEGORISIERUNG ---
        $managableRoles = $this->getManagableRoles(); // Die flache Liste
        $allRanks = Rank::pluck('name');
        $allDepartments = Department::with('roles')->get();
        
        $categorizedRoles = [
            'Ranks' => [],
            'Departments' => [],
            'Other' => []
        ];

        foreach ($managableRoles as $role) {
            // 1. Ist es ein Rang?
            if ($allRanks->contains($role->name)) {
                $categorizedRoles['Ranks'][] = $role;
                continue;
            }
            // 2. Ist es eine Abteilungsrolle?
            $found = false;
            foreach ($allDepartments as $dept) {
                if ($dept->roles->contains('id', $role->id)) {
                    if (!isset($categorizedRoles['Departments'][$dept->name])) {
                        $categorizedRoles['Departments'][$dept->name] = [];
                    }
                    $categorizedRoles['Departments'][$dept->name][] = $role;
                    $found = true;
                    break; 
                }
            }
            // 3. "Andere"
            if (!$found) {
                $categorizedRoles['Other'][] = $role;
            }
        }
        // --- ENDE KATEGORISIERUNG ---

        $permissions = Permission::all()->sortBy('name')->groupBy(function ($item) {
            $parts = explode('.', $item->name, 2);
            return $parts[0];
        });
        $userDirectPermissions = $user->getPermissionNames()->toArray();

        $allPossibleNumbers = range(1, 150);
        $takenNumbers = User::where('status', 'Aktiv')->where('id', '!=', $user->id)->pluck('personal_number')->toArray();
        $availablePersonalNumbers = array_diff($allPossibleNumbers, $takenNumbers);

        $allModules = TrainingModule::orderBy('category')->orderBy('name')->get();
        $userModules = $user->trainingModules()->pluck('training_module_id')->toArray();

        return view('admin.users.edit', compact(
            'user',
            'categorizedRoles', // NEUE Variable
            'permissions',
            'userDirectPermissions',
            'availablePersonalNumbers',
            'statuses',
            'allModules',
            'userModules'
        ));
    }

    /**
     * KORRIGIERT: update-Methode mit detailliertem Logging
     */
    public function update(Request $request, User $user)
    {
        $adminUser = Auth::user();

        // --- SCHRITT 0: HIERARCHIE-SCHUTZ (NEU) ---
        // Prüfen, ob der Admin überhaupt berechtigt ist, DIESEN User anzufassen.
        // Ein Level 14 darf keinen Level 14 oder Level 15 bearbeiten.
        
        if (!$adminUser->hasRole($this->superAdminRole) && $adminUser->rank !== 'chief') {
            $ranks = Rank::pluck('level', 'name');
            
            // Level ermitteln
            $adminLevel = $ranks->get($adminUser->rank, 0);
            $targetUserLevel = $ranks->get($user->rank, 0);

            // Wenn Ziel-User >= Admin Level -> Abbruch!
            if ($targetUserLevel >= $adminLevel) {
                return redirect()->back()
                    ->with('error', 'Zugriff verweigert: Du kannst keine Mitarbeiter bearbeiten, die im Rang gleich oder höher stehen als du.');
            }
        }
        // --- ENDE SCHUTZ ---

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'roles' => 'sometimes|array',
            'permissions' => 'sometimes|array',
            'status' => 'required|string',
            'personal_number' => ['required', 'integer', 'min:1', 'max:150', Rule::unique('users')->ignore($user->id)],
            'employee_id' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'birthday' => 'nullable|date',
            'discord_name' => 'nullable|string|max:255',
            'forum_name' => 'nullable|string|max:255',
            'special_functions' => 'nullable|string',
            'hire_date' => 'nullable|date', 
            'modules' => 'sometimes|array',
            'modules.*' => 'exists:training_modules,id',
        ]);

        // --- START: ROLLEN-LOGIK ---
        $managableRoleNames = $this->getManagableRoles()->pluck('name')->toArray();
        $originalRoleNames = $user->getRoleNames()->toArray();
        $submittedRoleNames = $request->input('roles', []);

        // Prüfen, ob der Admin versucht, Rollen zu vergeben, die er nicht darf
        $newlyAddedRoles = array_diff($submittedRoleNames, $originalRoleNames);
        foreach ($newlyAddedRoles as $addedRole) {
            if (!in_array($addedRole, $managableRoleNames)) {
                return redirect()->back()
                    ->withErrors(['roles' => 'Du hast nicht die Berechtigung, die Rolle "' . $addedRole . '" zuzuweisen.'])
                    ->withInput();
            }
        }
        
        // Rollen behalten, die der Admin nicht verwalten darf (z.B. wenn ein Admin eine Abteilung nicht leitet,
        // darf er die Abteilungsrolle des Users nicht entfernen)
        $unmanagableRolesToKeep = array_diff($originalRoleNames, $managableRoleNames);
        $finalRolesToSync = array_unique(array_merge($submittedRoleNames, $unmanagableRolesToKeep));
        // --- ENDE: ROLLEN-LOGIK ---


        // --- START: LOGGING SETUP ---
        $userBeforeUpdate = clone $user;
        $userBeforeUpdate->load('trainingModules', 'permissions');
        
        $oldValues = [
            'name' => $userBeforeUpdate->name,
            'status' => $userBeforeUpdate->status,
            'personal_number' => $userBeforeUpdate->personal_number,
            'employee_id' => $userBeforeUpdate->employee_id,
            'email' => $userBeforeUpdate->email,
            'birthday' => $userBeforeUpdate->birthday ? Carbon::parse($userBeforeUpdate->birthday)->format('Y-m-d') : null,
            'discord_name' => $userBeforeUpdate->discord_name,
            'forum_name' => $userBeforeUpdate->forum_name,
            'special_functions' => $userBeforeUpdate->special_functions,
            'hire_date' => $userBeforeUpdate->hire_date ? Carbon::parse($userBeforeUpdate->hire_date)->format('Y-m-d') : null,
            'second_faction' => $userBeforeUpdate->second_faction,
            'rank' => $userBeforeUpdate->rank,
        ];
        
        $oldModuleIds = $userBeforeUpdate->trainingModules->pluck('id')->toArray();
        $oldRoleNames = $originalRoleNames;
        $oldPermissionNames = $userBeforeUpdate->getPermissionNames()->toArray();
        // --- ENDE: LOGGING SETUP ---


        // --- UPDATE DURCHFÜHREN ---
        $validatedData['second_faction'] = $request->has('second_faction') ? 'Ja' : 'Nein';
        $newStatus = $validatedData['status']; 

        $inactiveStatuses = ['Ausgetreten', 'inaktiv', 'Suspendiert']; 
        $activeStatuses = ['Aktiv', 'Probezeit', 'Bewerbungsphase']; 
        if (in_array($oldValues['status'], $inactiveStatuses) && in_array($newStatus, $activeStatuses)) {
            if (empty($validatedData['hire_date'])) {
                 $validatedData['hire_date'] = now();
            }
        }

        // Rang berechnen
        $newRank = 'praktikant'; 
        $highestLevel = 0;
        $rankLevels = Rank::whereIn('name', $finalRolesToSync)->pluck('level', 'name');

        foreach ($finalRolesToSync as $roleName) {
            if ($rankLevels->has($roleName) && $rankLevels[$roleName] > $highestLevel) {
                $highestLevel = $rankLevels[$roleName];
                $newRank = $roleName;
            }
        }
        $validatedData['rank'] = $newRank;

        // Daten normalisieren
        $newValues = $validatedData;
        if (isset($newValues['birthday'])) $newValues['birthday'] = $newValues['birthday'] ? Carbon::parse($newValues['birthday'])->format('Y-m-d') : null;
        if (isset($newValues['hire_date'])) $newValues['hire_date'] = $newValues['hire_date'] ? Carbon::parse($newValues['hire_date'])->format('Y-m-d') : null;

        $submittedPermissionNames = $request->permissions ?? [];
        $user->syncRoles($finalRolesToSync); 
        $user->syncPermissions($submittedPermissionNames);

        $validatedData['last_edited_at'] = now();
        $validatedData['last_edited_by'] = $adminUser->name;
        $user->update($validatedData);

        // Module Sync
        $submittedModuleIds = $request->input('modules', []);
        $modulesToSync = [];
        $timestamp = now();

        foreach ($submittedModuleIds as $moduleId) {
            $existingPivot = $userBeforeUpdate->trainingModules->firstWhere('id', $moduleId)?->pivot;
            if ($existingPivot) {
                $modulesToSync[$moduleId] = [
                    'assigned_by_user_id' => $existingPivot->assigned_by_user_id ?? $adminUser->id,
                    'completed_at' => $existingPivot->completed_at, 
                    'notes' => $existingPivot->notes, 
                    'updated_at' => $timestamp,
                ];
            } else {
                $modulesToSync[$moduleId] = [
                    'assigned_by_user_id' => $adminUser->id,
                    'completed_at' => $timestamp->toDateString(),
                    'notes' => "Manuell zugewiesen von {$adminUser->name} am " . $timestamp->format('d.m.Y H:i')
                ];
            }
        }
        $user->trainingModules()->sync($modulesToSync);


        // --- LOGGING GENERIEREN ---
        $user->load(['roles', 'trainingModules']);
        $changes = [];

        $fieldsToCompare = [
            'name' => 'Name', 'status' => 'Status', 'personal_number' => 'Dienstnummer',
            'employee_id' => 'Mitarbeiter-ID', 'email' => 'E-Mail', 'birthday' => 'Geburtstag',
            'discord_name' => 'Discord', 'forum_name' => 'Forum', 'special_functions' => 'Sonderfunktionen',
            'hire_date' => 'Einstelldatum', 'second_faction' => 'Zweitfraktion', 'rank' => 'Rang'
        ];

        foreach ($fieldsToCompare as $key => $label) {
            $newValue = $newValues[$key] ?? null; 
            $oldValue = $oldValues[$key] ?? null;
            
            // Fix für Hire Date Vergleich bei Reaktivierung
            if ($key === 'hire_date' && in_array($oldValues['status'], $inactiveStatuses) && in_array($newStatus, $activeStatuses) && empty($request->hire_date)) {
                 $newValue = $validatedData['hire_date']->format('Y-m-d');
            }

            if ($newValue != $oldValue) {
                $changes[] = "{$label} geändert: '{$oldValue}' -> '{$newValue}'";
            }
        }

        $addedRoles = array_diff($finalRolesToSync, $oldRoleNames);
        $removedRoles = array_diff($oldRoleNames, $finalRolesToSync);
        if (!empty($addedRoles)) $changes[] = "Rollen hinzugefügt: " . implode(', ', $addedRoles);
        if (!empty($removedRoles)) {
            $removedRoles = array_filter($removedRoles, fn($role) => $role !== $this->superAdminRole);
            if(!empty($removedRoles)) $changes[] = "Rollen entfernt: " . implode(', ', $removedRoles);
        }

        $addedPermissions = array_diff($submittedPermissionNames, $oldPermissionNames);
        $removedPermissions = array_diff($oldPermissionNames, $submittedPermissionNames);
        if (!empty($addedPermissions)) $changes[] = "Berechtigungen hinzugefügt: " . implode(', ', $addedPermissions);
        if (!empty($removedPermissions)) $changes[] = "Berechtigungen entfernt: " . implode(', ', $removedPermissions);

        $addedModules = array_diff($submittedModuleIds, $oldModuleIds); 
        $removedModules = array_diff($oldModuleIds, $submittedModuleIds);
        if (!empty($addedModules)) {
            $names = TrainingModule::whereIn('id', $addedModules)->pluck('name')->implode(', ');
            $changes[] = "Module hinzugefügt: {$names}";
        }
        if (!empty($removedModules)) {
            $names = TrainingModule::whereIn('id', $removedModules)->pluck('name')->implode(', ');
            $changes[] = "Module entfernt: {$names}";
        }

        $description = "Benutzerprofil von '{$user->name}' ({$user->id}) aktualisiert. ";
        $description .= empty($changes) ? "Keine Änderungen." : "Änderungen: " . implode('. ', $changes) . ".";

        ActivityLog::create([
             'user_id' => Auth::id(),
             'log_type' => 'USER',
             'action' => 'UPDATED',
             'target_id' => $user->id,
             'description' => $description,
        ]);
        
        // --- HIERARCHIE-LOGIK UND DISCORD (NUR WENN RANG SICH ÄNDERT) ---
        if ($oldValues['rank'] !== $newRank) {
            $rankInfo = Rank::whereIn('name', [$oldValues['rank'], $newRank])->get()->keyBy('name');

            $currentRankData = $rankInfo->get($newRank);
            $oldRankData = $rankInfo->get($oldValues['rank']);

            $currentRankLevel = $currentRankData ? $currentRankData->level : 0;
            $oldRankLevel = $oldRankData ? $oldRankData->level : 0;

            $newRankLabel = $currentRankData ? $currentRankData->label : ucfirst($newRank);
            $oldRankLabel = $oldRankData ? $oldRankData->label : ucfirst($oldValues['rank']);

            $recordType = $currentRankLevel > $oldRankLevel ? 'Beförderung' : ($currentRankLevel < $oldRankLevel ? 'Degradierung' : 'Rangänderung');
            
            ServiceRecord::create([
                'user_id' => $user->id,
                'author_id' => Auth::id(),
                'type' => $recordType,
                'content' => "Rang geändert von '{$oldRankLabel}' zu '{$newRankLabel}'."
            ]);

            // Discord
            $discordActionMap = [
                'Beförderung'  => 'rank.promotion',
                'Degradierung' => 'rank.demotion',
            ];

            if (array_key_exists($recordType, $discordActionMap)) {
                $actionKey = $discordActionMap[$recordType];
                $color = ($recordType === 'Beförderung') ? 5763719 : 15548997; 

                $embeds = [[
                    'title' => "📢 Neue " . $recordType,
                    'description' => "Der Benutzer **{$user->name}** hat einen neuen Rang erhalten.",
                    'color' => $color,
                    'fields' => [
                        ['name' => 'Alte Position', 'value' => $oldRankLabel, 'inline' => true],
                        ['name' => 'Neue Position', 'value' => $newRankLabel, 'inline' => true],
                        ['name' => 'Ausgeführt von', 'value' => Auth::user()->name, 'inline' => false],
                    ],
                    'footer' => ['text' => config('app.name') . ' System Log'],
                    'timestamp' => now()->toIso8601String()
                ]];

                try {
                    (new \App\Services\DiscordService())->send($actionKey, "", $embeds);
                } catch (\Exception $e) {
                    \Log::error("Discord Webhook Fehler: " . $e->getMessage());
                }
            }
        }

        PotentiallyNotifiableActionOccurred::dispatch(
            'Admin\UserController@update', $user, $user, Auth::user(),
            [
                'description' => $description, 
                'old_values' => $oldValues, 'added_roles' => $addedRoles, 'removed_roles' => $removedRoles,
                'added_modules' => $addedModules, 'removed_modules' => $removedModules,
                'added_permissions' => $addedPermissions, 'removed_permissions' => $removedPermissions
            ]
        );

        return redirect()->route('admin.users.index')->with('success', 'Mitarbeiter erfolgreich aktualisiert.');
    }

    public function addRecord(Request $request, User $user)
    {
        $request->validate(['type' => 'required|string', 'content' => 'required|string']);

        $record = ServiceRecord::create([
            'user_id' => $user->id, 'author_id' => Auth::id(),
            'type' => $request->type, 'content' => $request->content
        ]);

        // Update last edited info
        $user->update(['last_edited_at' => now(), 'last_edited_by' => Auth::user()->name]);

        ActivityLog::create([
            'user_id' => Auth::id(), 'log_type' => 'USER_RECORD', 'action' => 'ADDED',
            'target_id' => $user->id,
            'description' => "Eintrag (Typ: {$request->type}) zur Personalakte von '{$user->name}' hinzugefügt.",
        ]);

        PotentiallyNotifiableActionOccurred::dispatch(
            'Admin\UserController@addRecord',
            $user,
            $record,
            Auth::user()
        );

        return redirect()->route('admin.users.show', $user); // Ohne success
    }
}



