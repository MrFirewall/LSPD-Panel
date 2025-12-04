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

    // =========================================================================
    // HILFSFUNKTIONEN
    // =========================================================================

    /**
     * Ermittelt das Level eines Rangs anhand des Namens oder Labels.
     */
    private function getRankLevel(?string $rankNameOrLabel)
    {
        if (empty($rankNameOrLabel)) {
            return 0;
        }
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
        $adminRankLevel = $this->getRankLevel($admin->rank);

        if ($admin->hasAnyRole('chief', $this->superAdminRole)) {
            return Role::where('name', '!=', $this->superAdminRole)->get();
        }

        $ranks = Rank::all(); 
        $departments = Department::with('roles')->get();
        $managableRoles = collect();
        $allRoles = Role::where('name', '!=', $this->superAdminRole)->get();

        foreach ($allRoles as $role) {
            // 1. RANG-ROLLEN PRÜFEN
            $rankEntry = $ranks->firstWhere('name', $role->name);
            if ($rankEntry) {
                if ($rankEntry->level < $adminRankLevel) {
                    $managableRoles->push($role);
                }
                continue;
            }

            // 2. ABTEILUNGS-ROLLEN PRÜFEN
            foreach ($departments as $department) {
                if ($department->roles->contains('name', $role->name)) {
                    if ($role->name === $department->leitung_role_name) {
                        $minLevel = $department->min_rank_level_to_assign_leitung ?? 0;
                        if ($adminRankLevel >= $minLevel) {
                            $managableRoles->push($role);
                        }
                    } else {
                        $leitungRoles = is_array($department->leitung_role_name) ? $department->leitung_role_name : [$department->leitung_role_name];
                        if ($admin->hasAnyRole($leitungRoles) || $adminRankLevel >= 15) { 
                            $managableRoles->push($role);
                        }
                    }
                    break;
                }
            }
            
            // 3. SONSTIGE ROLLEN
             if (!$managableRoles->contains('id', $role->id) && !$rankEntry) {
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
     * Entfernt die Super-Admin Rolle aus der Anzeige.
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

    /**
     * Erstellt einen Eintrag in der Personalakte (Private Helper).
     */
    private function createSystemRecord(User $targetUser, string $type, string $content)
    {
        ServiceRecord::create([
            'user_id' => $targetUser->id,
            'author_id' => Auth::id(),
            'type' => $type,
            'content' => $content
        ]);
    }

    /**
     * Berechnet Statistiken für Bewertungen.
     */
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

    // =========================================================================
    // HAUPTMETHODEN (CRUD)
    // =========================================================================

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
        
        // Rang automatisch aus Rollen bestimmen
        $highestRankName = 'praktikant'; 
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

        PotentiallyNotifiableActionOccurred::dispatch('Admin\UserController@store', $user, $user, Auth::user());

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

    public function edit(User $user)
    {
        $statuses = [
            'Aktiv', 'Beurlaubt', 'Beobachtung', 'Krankgeschrieben',
            'Suspendiert', 'Ausgetreten', 'Bewerbungsphase', 'Probezeit',
        ];
        
        $managableRoles = $this->getManagableRoles();
        $allRanks = Rank::pluck('name');
        $allDepartments = Department::with('roles')->get();
        
        $categorizedRoles = ['Ranks' => [], 'Departments' => [], 'Other' => []];

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
     * UPDATE MIT GRANULARER PROTOKOLLIERUNG
     */
    public function update(Request $request, User $user)
    {
        $adminUser = Auth::user();
        $canManageRanks = true;

        // 1. Hierarchie-Check
        if (!$adminUser->hasRole($this->superAdminRole) && $adminUser->rank !== 'chief') {
            $adminLevel = $this->getRankLevel($adminUser->rank);
            $targetUserLevel = $this->getRankLevel($user->rank);
            if ($targetUserLevel >= $adminLevel) {
                $canManageRanks = false;
            }
        }

        // 2. Validierung
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
        if ($canManageRanks) {
            $rules['roles'] = 'sometimes|array';
        }
        $validatedData = $request->validate($rules);

        // 3. Status VOR dem Update sichern (Deep Clone / Fresh Load)
        $userBeforeUpdate = User::with(['roles', 'permissions', 'trainingModules'])->find($user->id);
        
        $originalRoleNames = $userBeforeUpdate->getRoleNames()->toArray();
        $originalPermissionNames = $userBeforeUpdate->getPermissionNames()->toArray();
        $originalModuleIds = $userBeforeUpdate->trainingModules->pluck('id')->toArray();
        
        $oldHireDate = $userBeforeUpdate->hire_date ? Carbon::parse($userBeforeUpdate->hire_date)->format('Y-m-d') : null;
        $oldBirthday = $userBeforeUpdate->birthday ? Carbon::parse($userBeforeUpdate->birthday)->format('Y-m-d') : null;

        // 4. Rollen & Rang Logik vorbereiten
        $addedRoles = [];
        $removedRoles = [];
        $newRank = $userBeforeUpdate->rank;

        if ($canManageRanks) {
            $submittedRoleNames = $request->input('roles', []);
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

            // Änderungen merken
            $addedRoles = array_diff($finalRolesToSync, $originalRoleNames);
            $removedRoles = array_diff($originalRoleNames, $finalRolesToSync);

            // Sync später durchführen, nachdem $user->update() durch ist
        } else {
            unset($validatedData['rank']);
        }

        // 5. Daten normalisieren
        if (isset($validatedData['birthday'])) $validatedData['birthday'] = Carbon::parse($validatedData['birthday'])->format('Y-m-d');
        if (isset($validatedData['hire_date'])) $validatedData['hire_date'] = Carbon::parse($validatedData['hire_date'])->format('Y-m-d');
        
        $validatedData['second_faction'] = $request->has('second_faction') ? 'Ja' : 'Nein';
        $validatedData['last_edited_at'] = now();
        $validatedData['last_edited_by'] = $adminUser->name;

        // 6. Update durchführen
        $user->update($validatedData);

        if ($canManageRanks && isset($finalRolesToSync)) {
            $user->syncRoles($finalRolesToSync);
        }

        $user->syncPermissions($request->permissions ?? []);
        $newPermissionNames = $user->getPermissionNames()->toArray();

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
                    'notes' => "Zugewiesen von {$adminUser->name} am " . $timestamp->format('d.m.Y H:i')
                ];
            }
        }
        $user->trainingModules()->sync($modulesToSync);

        // =====================================================================
        // GRANULARE PROTOKOLLIERUNG
        // =====================================================================

        // A. Stammdaten
        $fieldsToCheck = [
            'name' => 'Name',
            'status' => 'Status',
            'personal_number' => 'Dienstnummer',
            'email' => 'E-Mail',
            'discord_name' => 'Discord Name',
            'forum_name' => 'Forum Name',
            'second_faction' => 'Zweitfraktion',
            'special_functions' => 'Sonderfunktionen',
            'employee_id' => 'Employee ID',
        ];

        foreach ($fieldsToCheck as $field => $label) {
            $oldVal = $userBeforeUpdate->$field;
            $newVal = $user->$field;
            if ($oldVal != $newVal) {
                $this->createSystemRecord($user, 'Stammdatenänderung', "{$label} geändert von '{$oldVal}' zu '{$newVal}'.");
            }
        }

        // Datum spezial
        if ($oldHireDate != $validatedData['hire_date']) {
            $this->createSystemRecord($user, 'Stammdatenänderung', "Einstellungsdatum geändert von '{$oldHireDate}' zu '{$validatedData['hire_date']}'.");
        }
        if ($oldBirthday != $validatedData['birthday']) {
            $this->createSystemRecord($user, 'Stammdatenänderung', "Geburtstag geändert von '{$oldBirthday}' zu '{$validatedData['birthday']}'.");
        }

        // B. Rollen
        foreach ($addedRoles as $role) {
            $this->createSystemRecord($user, 'Rollenänderung', "Rolle '{$role}' wurde zugewiesen.");
        }
        foreach ($removedRoles as $role) {
            $this->createSystemRecord($user, 'Rollenänderung', "Rolle '{$role}' wurde entfernt.");
        }

        // C. Rang mit Discord
        if ($userBeforeUpdate->rank !== $newRank) {
            $this->handleRankChange($userBeforeUpdate->rank, $newRank, $user);
        }

        // D. Berechtigungen
        $addedPerms = array_diff($newPermissionNames, $originalPermissionNames);
        $removedPerms = array_diff($originalPermissionNames, $newPermissionNames);
        foreach ($addedPerms as $perm) {
            $this->createSystemRecord($user, 'Berechtigung', "Berechtigung '{$perm}' hinzugefügt.");
        }
        foreach ($removedPerms as $perm) {
            $this->createSystemRecord($user, 'Berechtigung', "Berechtigung '{$perm}' entfernt.");
        }

        // E. Module
        $addedModules = array_diff($submittedModuleIds, $originalModuleIds);
        $removedModules = array_diff($originalModuleIds, $submittedModuleIds);
        
        if (!empty($addedModules)) {
            $moduleNames = TrainingModule::whereIn('id', $addedModules)->pluck('name')->toArray();
            foreach ($moduleNames as $mName) {
                $this->createSystemRecord($user, 'Ausbildung', "Modul '{$mName}' zugewiesen.");
            }
        }
        if (!empty($removedModules)) {
            $moduleNames = TrainingModule::whereIn('id', $removedModules)->pluck('name')->toArray();
            foreach ($moduleNames as $mName) {
                $this->createSystemRecord($user, 'Ausbildung', "Modul '{$mName}' entfernt.");
            }
        }

        // Summary Activity Log
        $description = "Benutzerprofil aktualisiert.";
        if (!$canManageRanks) $description .= " (Rang-Änderung übersprungen aufgrund Hierarchie).";

        ActivityLog::create([
             'user_id' => Auth::id(), 
             'log_type' => 'USER', 
             'action' => 'UPDATED',
             'target_id' => $user->id, 
             'description' => $description
        ]);

        PotentiallyNotifiableActionOccurred::dispatch('Admin\UserController@update', $user, $user, Auth::user());

        return redirect()->route('admin.users.index')->with('success', 'Mitarbeiter erfolgreich aktualisiert.');
    }

    /**
     * Lagert die Rang-Logik (Discord) aus.
     */
    private function handleRankChange($oldRankName, $newRankName, $user)
    {
        $rankInfo = Rank::whereIn('name', [$oldRankName, $newRankName])
                        ->orWhereIn('label', [$oldRankName, $newRankName])->get();
        
        $findRankData = fn($search) => $rankInfo->first(fn($r) => $r->name === $search || $r->label === $search);

        $currentRankData = $findRankData($newRankName);
        $oldRankData = $findRankData($oldRankName);
        
        $newRankLabel = $currentRankData ? $currentRankData->label : ucfirst($newRankName);
        $oldRankLabel = $oldRankData ? $oldRankData->label : ucfirst($oldRankName);
        $currentLevel = $currentRankData?->level ?? 0;
        $oldLevel = $oldRankData?->level ?? 0;

        $recordType = $currentLevel > $oldLevel ? 'Beförderung' : ($currentLevel < $oldLevel ? 'Degradierung' : 'Rangänderung');
        
        // Record erstellen
        $this->createSystemRecord($user, $recordType, "Rang geändert von '{$oldRankLabel}' zu '{$newRankLabel}'.");
        
        // Discord senden
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