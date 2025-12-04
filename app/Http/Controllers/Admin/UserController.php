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
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Department;
use App\Models\Rank;
use App\Models\Pivots\TrainingModuleUser;
use Carbon\Carbon;

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
     * HILFSFUNKTION: Ermittelt das Level eines Rangs anhand des Namens oder Labels.
     * Das löst das Problem, falls in der User-DB "Polizeipräsident/in" statt "polizeipraesident" steht.
     */
    private function getRankLevel(?string $rankNameOrLabel)
    {
        if (empty($rankNameOrLabel)) {
            return 0;
        }

        // Wir suchen flexibel nach Name ODER Label in der ranks Tabelle
        $rank = Rank::where('name', $rankNameOrLabel)
                    ->orWhere('label', $rankNameOrLabel)
                    ->first();

        return $rank ? $rank->level : 0;
    }

    /**
     * Gibt eine gefilterte Liste der Rollen zurück, die der aktuelle Admin verwalten darf.
     */
    private function getManagableRoles()
    {
        $admin = Auth::user();

        // Admin-Level sicher ermitteln
        $adminRankLevel = $this->getRankLevel($admin->rank);

        // Ausnahme: 'chief' (oder Super-Admin) dürfen immer alle Rollen verwalten (außer Super-Admin selbst).
        if ($admin->hasAnyRole('chief', $this->superAdminRole)) {
            return Role::where('name', '!=', $this->superAdminRole)->get();
        }

        $adminRoleNames = $admin->getRoleNames();
        $ranks = Rank::all(); // Alle Ränge laden
        $departments = Department::with('roles')->get();

        $managableRoles = collect();
        $allRoles = Role::where('name', '!=', $this->superAdminRole)->get();

        foreach ($allRoles as $role) {
            // 1. RANG-ROLLEN PRÜFEN
            // Prüfen, ob diese Rolle ein Rang ist (Vergleich mit Ranks-Collection)
            $rankEntry = $ranks->firstWhere('name', $role->name);

            if ($rankEntry) {
                // WICHTIG: Nur Ränge anzeigen, die ECHT KLEINER sind als das eigene Level.
                if ($rankEntry->level < $adminRankLevel) {
                    $managableRoles->push($role);
                }
                continue;
            }

            // 2. ABTEILUNGS-ROLLEN PRÜFEN
            foreach ($departments as $department) {
                if ($department->roles->contains('name', $role->name)) {
                    // Ist es die Leitungsrolle?
                    if ($role->name === $department->leitung_role_name) {
                        // Leitungsrollen nur, wenn Admin hoch genug im Rang ist (Hier nutzen wir eine Spalte im Dept, falls vorhanden, sonst Fallback)
                        $minLevel = $department->min_rank_level_to_assign_leitung ?? 0;
                        if ($adminRankLevel >= $minLevel) {
                            $managableRoles->push($role);
                        }
                    } else {
                        // Es ist eine "normale" Abteilungsrolle
                        // Prüfen ob Admin eine der Leitungsrollen der Abteilung hat (Array-Support)
                        $leitungRoles = is_array($department->leitung_role_name) ? $department->leitung_role_name : [$department->leitung_role_name];
                        
                        // Prüfung: Hat der Admin eine der Leitungsrollen ODER ist er im Rang >= 15?
                        // (Hier kannst du anpassen, ab welchem Rang man generell alles verwalten darf)
                        if ($admin->hasAnyRole($leitungRoles) || $adminRankLevel >= 15) { 
                            $managableRoles->push($role);
                        }
                    }
                    break;
                }
            }
            
            // 3. SONSTIGE ROLLEN (die weder Rang noch Abteilung sind)
             if (!$managableRoles->contains('id', $role->id) && !$rankEntry) {
                 // Prüfen ob sie wirklich nirgends zugehört
                 $isDeptRole = false;
                 foreach($departments as $d) { if($d->roles->contains('id', $role->id)) $isDeptRole = true; }
                 
                 if(!$isDeptRole) {
                     $managableRoles->push($role);
                 }
             }
        }

        return $managableRoles->unique('id');
    }

    /**
     * Hilfsfunktion, die die Super-Admin Rolle aus der Anzeige entfernt.
     */
    private function filterSuperAdminFromRoles(User $user): User
    {
        $viewUser = clone $user;
        
        if ($viewUser->relationLoaded('roles')) {
            $filteredRoles = $viewUser->roles->reject(function ($role) {
                return $role->name === $this->superAdminRole;
            });
            $viewUser->setRelation('roles', $filteredRoles);
        }
        return $viewUser;
    }

    public function index()
    {
        $users = User::with('roles')->orderBy('personal_number')->get();

        $filteredUsers = $users->map(function ($user) {
            return $this->filterSuperAdminFromRoles($user);
        });

        return view('admin.users.index', ['users' => $filteredUsers]);
    }

    public function create()
    {
        $managableRoles = $this->getManagableRoles();
        
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

            // 3. Andere
            if (!$found) {
                $categorizedRoles['Other'][] = $role;
            }
        }

        $statuses = [
            'Aktiv', 'Probezeit', 'Beobachtung', 'Beurlaubt', 'Krankgeschrieben',
            'Suspendiert', 'Ausgetreten', 'Bewerbungsphase',
        ];
        
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
        
        // --- RANG-LOGIK ---
        $highestRankName = 'praktikant'; // Standardwert
        $highestLevel = 0;
        
        $rankLevels = Rank::whereIn('name', $selectedRoles)->pluck('level', 'name');

        foreach ($selectedRoles as $roleName) {
            if ($rankLevels->has($roleName) && $rankLevels[$roleName] > $highestLevel) {
                $highestLevel = $rankLevels[$roleName];
                $highestRankName = $roleName;
            }
        }
        $validatedData['rank'] = $highestRankName; 

        $validatedData['second_faction'] = $request->has('second_faction') ? 'Ja' : 'Nein';

        do {
            $newEmployeeId = rand(10000, 99999);
        } while (User::where('employee_id', $newEmployeeId)->exists());
        $validatedData['employee_id'] = $newEmployeeId;

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

    public function show(User $user)
    {
        $user->load([
            'vacations',
            'receivedEvaluations' => fn($q) => $q->with('evaluator')->latest(),
            'roles'
        ]);

        $user->load('trainingModules');

        if ($user->trainingModules->isNotEmpty()) {
            $pivots = $user->trainingModules->pluck('pivot'); 
            (new \Illuminate\Database\Eloquent\Collection($pivots))->load('assigner');
        }

        $examAttempts = ExamAttempt::where('user_id', $user->id)
                                    ->with(['exam', 'evaluator'])
                                    ->latest('completed_at') 
                                    ->get();

        $serviceRecords = $user->serviceRecords()->with('author')->latest()->get();
        $evaluationCounts = $this->calculateEvaluationCounts($user);
        $hourData = $user->calculateDutyHours();
        $weeklyHours = $user->calculateWeeklyHoursSinceEntry();

        $viewUser = $this->filterSuperAdminFromRoles($user);

        return view('profile.show', [
            'user' => $viewUser,
            'serviceRecords' => $serviceRecords,
            'examAttempts' => $examAttempts,
            'evaluationCounts' => $evaluationCounts,
            'hourData' => $hourData,
            'weeklyHours' => $weeklyHours
        ]);
    }

    private function calculateEvaluationCounts(User $user): array
    {
        $typeLabels = ['azubi', 'praktikant', 'mitarbeiter', 'leitstelle'];
        $counts = ['verfasst' => [], 'erhalten' => []];

        $receivedCounts = Evaluation::selectRaw('evaluation_type, count(*) as count')
                                    ->where('user_id', $user->id)
                                    ->whereIn('evaluation_type', $typeLabels)
                                    ->groupBy('evaluation_type')
                                    ->pluck('count', 'evaluation_type');

        $authoredCounts = Evaluation::selectRaw('evaluation_type, count(*) as count')
                                    ->where('evaluator_id', Auth::id())
                                    ->whereIn('evaluation_type', $typeLabels)
                                    ->groupBy('evaluation_type')
                                    ->pluck('count', 'evaluation_type');

        foreach ($typeLabels as $type) {
            $counts['erhalten'][$type] = $receivedCounts->get($type, 0);
            $counts['verfasst'][$type] = $authoredCounts->get($type, 0);
        }

        return $counts;
    }

    public function edit(User $user)
    {
        // KEIN HIERARCHIE-CHECK HIER: Zugriff auf die Seite wird erlaubt, 
        // Logik was geändert werden darf passiert in der View und im Update.

        $statuses = [
            'Aktiv', 'Beurlaubt', 'Beobachtung', 'Krankgeschrieben',
            'Suspendiert', 'Ausgetreten', 'Bewerbungsphase', 'Probezeit',
        ];
        
        $managableRoles = $this->getManagableRoles();
        $allRanks = Rank::pluck('name');
        $allDepartments = Department::with('roles')->get();
        
        $categorizedRoles = [
            'Ranks' => [],
            'Departments' => [],
            'Other' => []
        ];

        foreach ($managableRoles as $role) {
            if ($allRanks->contains($role->name)) {
                $categorizedRoles['Ranks'][] = $role;
                continue;
            }
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
            if (!$found) {
                $categorizedRoles['Other'][] = $role;
            }
        }

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
            'categorizedRoles',
            'permissions',
            'userDirectPermissions',
            'availablePersonalNumbers',
            'statuses',
            'allModules',
            'userModules'
        ));
    }

    /**
     * UPDATE-METHODE MIT PARTIELLER SPERRE
     */
    public function update(Request $request, User $user)
    {
        $adminUser = Auth::user();
        $canManageRanks = true; // Standard: Darf Ränge ändern

        // --- SCHRITT 0: HIERARCHIE-CHECK ---
        // Darf ich Ränge dieses Users anfassen?
        if (!$adminUser->hasRole($this->superAdminRole) && $adminUser->rank !== 'chief') {
            $adminLevel = $this->getRankLevel($adminUser->rank);
            $targetUserLevel = $this->getRankLevel($user->rank);

            // Wenn Ziel-User >= Admin Level -> Sperre Rang-Änderung!
            if ($targetUserLevel >= $adminLevel) {
                $canManageRanks = false;
            }
        }

        // --- VALIDIERUNG ---
        $rules = [
            'name' => 'required|string|max:255',
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
        ];

        // Rollen nur validieren, wenn wir sie auch speichern wollen
        if ($canManageRanks) {
            $rules['roles'] = 'sometimes|array';
        }

        $validatedData = $request->validate($rules);

        // --- UPDATE START ---
        $userBeforeUpdate = clone $user;
        $userBeforeUpdate->load('trainingModules', 'permissions');
        $originalRoleNames = $user->getRoleNames()->toArray();
        $oldPermissionNames = $userBeforeUpdate->getPermissionNames()->toArray();
        $oldModuleIds = $userBeforeUpdate->trainingModules->pluck('id')->toArray();

        // Logging-Setup
        $oldValues = [
            'name' => $userBeforeUpdate->name,
            'status' => $userBeforeUpdate->status,
            'personal_number' => $userBeforeUpdate->personal_number,
            'rank' => $userBeforeUpdate->rank,
            'hire_date' => $userBeforeUpdate->hire_date ? Carbon::parse($userBeforeUpdate->hire_date)->format('Y-m-d') : null,
            // ... (restliche Felder für Log können hier bei Bedarf ergänzt werden)
        ];

        // RANG / ROLLEN LOGIK
        $addedRoles = [];
        $removedRoles = [];
        $newRank = $oldValues['rank']; // Standard: Rang bleibt gleich

        if ($canManageRanks) {
            // Wenn erlaubt: Führe Rollen-Update durch
            $submittedRoleNames = $request->input('roles', []);
            
            // Manageable Check (damit man sich nicht selbst zum Chief macht)
            $managableRoleNames = $this->getManagableRoles()->pluck('name')->toArray();
            $unmanagableRolesToKeep = array_diff($originalRoleNames, $managableRoleNames);
            $finalRolesToSync = array_unique(array_merge($submittedRoleNames, $unmanagableRolesToKeep));

            // Rang berechnen
            $rankLevels = Rank::whereIn('name', $finalRolesToSync)->pluck('level', 'name');
            $highestLevel = 0;
            $newRank = 'praktikant'; // Fallback
            foreach ($finalRolesToSync as $roleName) {
                if ($rankLevels->has($roleName) && $rankLevels[$roleName] > $highestLevel) {
                    $highestLevel = $rankLevels[$roleName];
                    $newRank = $roleName;
                }
            }
            $validatedData['rank'] = $newRank;

            // Sync
            $user->syncRoles($finalRolesToSync);
            
            // Fürs Log
            $addedRoles = array_diff($finalRolesToSync, $originalRoleNames);
            $removedRoles = array_diff($originalRoleNames, $finalRolesToSync);

        } else {
            // Wenn NICHT erlaubt: Ignoriere 'roles' input und 'rank' update
            unset($validatedData['rank']); // Entferne rank aus Update-Daten
            // syncRoles wird NICHT aufgerufen -> Rollen bleiben unverändert
        }

        // Andere Daten normalisieren
        if (isset($validatedData['birthday'])) $validatedData['birthday'] = Carbon::parse($validatedData['birthday'])->format('Y-m-d');
        if (isset($validatedData['hire_date'])) $validatedData['hire_date'] = Carbon::parse($validatedData['hire_date'])->format('Y-m-d');
        
        $validatedData['second_faction'] = $request->has('second_faction') ? 'Ja' : 'Nein';
        $validatedData['last_edited_at'] = now();
        $validatedData['last_edited_by'] = $adminUser->name;

        // Stammdaten Update
        $user->update($validatedData);

        // Berechtigungen Sync
        $user->syncPermissions($request->permissions ?? []);

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

        // --- DISCORD & LOGGING ---
        // Nur feuern, wenn sich der Rang TATSÄCHLICH geändert hat (also wenn canManageRanks true war UND eine Änderung vorliegt)
        if ($oldValues['rank'] !== $newRank) {
            $rankInfo = Rank::whereIn('name', [$oldValues['rank'], $newRank])
                            ->orWhereIn('label', [$oldValues['rank'], $newRank])->get();
            
            // Helper zum Finden
            $findRankData = function($search) use ($rankInfo) {
                return $rankInfo->first(fn($r) => $r->name === $search || $r->label === $search);
            };

            $currentRankData = $findRankData($newRank);
            $oldRankData = $findRankData($oldValues['rank']);
            
            $newRankLabel = $currentRankData ? $currentRankData->label : ucfirst($newRank);
            $oldRankLabel = $oldRankData ? $oldRankData->label : ucfirst($oldValues['rank']);
            $currentLevel = $currentRankData?->level ?? 0;
            $oldLevel = $oldRankData?->level ?? 0;

            $recordType = $currentLevel > $oldLevel ? 'Beförderung' : ($currentLevel < $oldLevel ? 'Degradierung' : 'Rangänderung');
            ServiceRecord::create(['user_id' => $user->id, 'author_id' => Auth::id(), 'type' => $recordType, 'content' => "Rang geändert von '{$oldRankLabel}' zu '{$newRankLabel}'."]);
            
            // Discord
            $discordActionMap = ['Beförderung' => 'rank.promotion', 'Degradierung' => 'rank.demotion'];
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
                try { (new \App\Services\DiscordService())->send($actionKey, "", $embeds); } catch (\Exception $e) { \Log::error("Discord Error: " . $e->getMessage()); }
            }
        }

        // Activity Log erstellen (mit Hinweis, falls Rang übersprungen wurde)
        $description = "Benutzerprofil aktualisiert.";
        if (!$canManageRanks) $description .= " (Rang-Änderung übersprungen aufgrund Hierarchie).";

        ActivityLog::create([
             'user_id' => Auth::id(), 'log_type' => 'USER', 'action' => 'UPDATED',
             'target_id' => $user->id, 'description' => $description
        ]);

        PotentiallyNotifiableActionOccurred::dispatch('Admin\UserController@update', $user, $user, Auth::user());

        return redirect()->route('admin.users.index')->with('success', 'Mitarbeiter erfolgreich aktualisiert.');
    }

    public function addRecord(Request $request, User $user)
    {
        $request->validate(['type' => 'required|string', 'content' => 'required|string']);
        $record = ServiceRecord::create([
            'user_id' => $user->id, 'author_id' => Auth::id(),
            'type' => $request->type, 'content' => $request->content
        ]);
        $user->update(['last_edited_at' => now(), 'last_edited_by' => Auth::user()->name]);
        ActivityLog::create([
            'user_id' => Auth::id(), 'log_type' => 'USER_RECORD', 'action' => 'ADDED',
            'target_id' => $user->id,
            'description' => "Eintrag (Typ: {$request->type}) zur Personalakte von '{$user->name}' hinzugefügt.",
        ]);
        PotentiallyNotifiableActionOccurred::dispatch('Admin\UserController@addRecord', $user, $record, Auth::user());
        return redirect()->route('admin.users.show', $user);
    }
}