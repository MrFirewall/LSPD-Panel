<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use App\Events\PotentiallyNotifiableActionOccurred;
use Illuminate\Validation\Rule; // Rule für exists/in Validierung hinzugefügt

// --- ANGEPASSTE USE-STATEMENTS ---
use App\Models\Role; // Dein eigenes Role-Modell
use App\Models\Rank;
use App\Models\Department;
// ------------------------------------

class RoleController extends Controller
{
    // NEU: Definiert die versteckte Super-Admin Rolle.
    private $superAdminRole = 'Super-Admin';

    public function __construct()
    {
        // Middleware für Rollen bleibt
        $this->middleware('can:roles.view')->only('index');
        $this->middleware('can:roles.create')->only('store');
        $this->middleware('can:roles.edit')->only(['update', 'updateRankOrder']);
        $this->middleware('can:roles.delete')->only('destroy');

        // Middleware für Department-Aktionen (überprüft Rollen-Berechtigungen)
        $this->middleware('can:roles.create')->only('storeDepartment');
        $this->middleware('can:roles.edit')->only('updateDepartment');
        $this->middleware('can:roles.delete')->only('destroyDepartment');
    }

    /**
     * Zeigt die Rollenliste (kategorisiert) und die Bearbeitungsansicht an.
     * Übergibt alle Departments, Rollennamen, Ränge und den Typ der aktuellen Rolle
     */
public function index(Request $request)
    {
        // 1. Rollen laden
        $allRolesCollection = Role::where('name', '!=', $this->superAdminRole)
                                 ->withCount('users')
                                 ->get(); 
                                 
        $allRoles = $allRolesCollection->keyBy(fn($role) => strtolower($role->name));
        $ranks = Rank::orderBy('level', 'desc')->get();
        $allDepartments = Department::orderBy('name')->get();

        $allRoleNames = $allRolesCollection->pluck('name'); 
        $allRanks = Rank::orderBy('level', 'desc')->pluck('level', 'name');

        $categorizedRoles = ['Ranks' => [], 'Departments' => [], 'Other' => []];

        // 2. Ränge zuordnen & LABEL AUS RANKS TABELLE HOLEN
        foreach ($ranks as $rank) {
            $rankNameLower = strtolower($rank->name);
            if ($allRoles->has($rankNameLower)) {
                $roleModel = $allRoles->pull($rankNameLower);
                $roleModel->rank_id = $rank->id;
                
                // WICHTIG: Überschreibe das Role-Label mit dem Rank-Label für die Anzeige
                $roleModel->label = $rank->label ?? ucfirst($roleModel->name); 
                
                $categorizedRoles['Ranks'][] = $roleModel;
            }
        }

        // 3. Abteilungen zuordnen (Label kommt hier aus roles Tabelle, passiert automatisch)
        foreach ($allDepartments as $dept) {
            $dept->loadMissing('roles');
            $categorizedRoles['Departments'][$dept->name] = [];
            foreach ($dept->roles as $role) {
                $roleNameLower = strtolower($role->name);
                if ($allRoles->has($roleNameLower)) {
                    $roleModel = $allRoles->pull($roleNameLower);
                    $roleModel->department_id = $dept->id;
                    $categorizedRoles['Departments'][$dept->name][] = $roleModel;
                }
            }
            if (empty($categorizedRoles['Departments'][$dept->name])) {
                unset($categorizedRoles['Departments'][$dept->name]);
            }
        }

        // 4. Andere Rollen
        $categorizedRoles['Other'] = $allRoles->values();

        // 5. Edit-Logik vorbereiten
        $permissions = Permission::all()->sortBy('name')->groupBy(fn($item) => explode('.', $item->name, 2)[0]);
        $currentRole = null;
        $currentRolePermissions = [];
        $currentRoleType = 'other';
        $currentDepartmentId = null;

        if ($request->has('role')) {
            $currentRole = Role::findById($request->query('role'));
            
            if ($currentRole && $currentRole->name === $this->superAdminRole) {
                return redirect()->route('admin.roles.index')->with('error', 'Diese Rolle kann nicht angezeigt werden.');
            }

            if ($currentRole) {
                $currentRolePermissions = $currentRole->permissions->pluck('name')->toArray();
                $currentRoleNameLower = strtolower($currentRole->name);
                
                // Prüfen ob es ein Rank ist
                $rankEntry = Rank::whereRaw('LOWER(name) = ?', [$currentRoleNameLower])->first();

                if ($rankEntry) {
                    $currentRoleType = 'rank';
                    // WICHTIG: Für das Edit-Formular das Label aus der Rank-Tabelle setzen
                    $currentRole->label = $rankEntry->label; 
                } elseif ($deptRole = DB::table('department_role')->where('role_id', $currentRole->id)->first()) {
                    $currentRoleType = 'department';
                    $currentDepartmentId = $deptRole->department_id;
                    // Bei Department kommt das Label bereits aus der roles Tabelle ($currentRole->label)
                }
            }
        }

        return view('admin.roles.index', compact(
            'categorizedRoles', 'permissions', 'currentRole', 'currentRolePermissions',
            'allDepartments', 'allRoleNames', 'allRanks', 'currentRoleType', 'currentDepartmentId'
        ));
    }

    /**
     * Aktualisiert die Sortierung (level) der Ränge.
     */
    public function updateRankOrder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        $rankIds = $request->input('order');
        $maxLevel = count($rankIds);

        try {
            DB::transaction(function () use ($rankIds, $maxLevel) {
                foreach ($rankIds as $index => $rankId) {
                    $level = $maxLevel - $index;
                    Rank::where('id', $rankId)->update(['level' => $level]);
                }
            });
            cache()->forget(config('permission.cache.key'));

            // Logging
            ActivityLog::create([
                'user_id' => Auth::id(),
                'log_type' => 'RANK_ORDER',
                'action' => 'UPDATED',
                'target_id' => null,
                'description' => 'Rang-Hierarchie wurde neu sortiert.',
            ]);

            // Optional: Event für Rank Order Update
            /* PotentiallyNotifiableActionOccurred::dispatch(...) */

        } catch (\Exception $e) {
            Log::error("Fehler beim Sortieren der Ränge: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Fehler beim Speichern der Hierarchie.'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Rang-Hierarchie erfolgreich aktualisiert.']);
    }

    /**
     * Speichert eine neu erstellte Rolle (Typ: Rank, Department oder Other).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'label' => 'required|string|max:255', // Label ist Pflicht
            'role_type' => 'required|in:rank,department,other',
            'department_id' => 'required_if:role_type,department|nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'createRole')->withInput()->with('open_modal', 'createRoleModal');
        }

        $roleName = strtolower(trim($request->name));
        if ($roleName === strtolower($this->superAdminRole)) {
            return back()->withErrors(['name' => 'Name reserviert.'], 'createRole')->withInput();
        }
        
        $roleType = $request->role_type;
        $label = $request->label;

        try {
            DB::beginTransaction();

            // LOGIK: Wo speichern wir das Label?
            // Wenn Rank: Label in Rank-Tabelle, Role-Tabelle Label = null
            // Wenn Sonst: Label in Role-Tabelle
            
            $roleData = ['name' => $roleName];
            if ($roleType !== 'rank') {
                $roleData['label'] = $label;
            }

            $role = Role::create($roleData);

            $department = null;
            if ($roleType === 'rank') {
                // Hier Label in Rank speichern
                Rank::create(['name' => $roleName, 'level' => 0, 'label' => $label]);
            } elseif ($roleType === 'department') {
                $department = Department::find($request->department_id);
                $department->roles()->attach($role->id);
            }
            DB::commit();

            // Logging...
            ActivityLog::create([
                'user_id' => Auth::id(), 'log_type' => 'ROLE', 'action' => 'CREATED',
                'target_id' => $role->id, 'description' => "Rolle '{$role->name}' ({$label}) erstellt.",
            ]);
            
            return redirect()->route('admin.roles.index', ['role' => $role->id])->with('success', 'Rolle erstellt.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Fehler: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Aktualisiert Rolle, Berechtigungen UND Typ/Department-Zugehörigkeit.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === $this->superAdminRole || $role->name === 'chief') {
            return back()->with('error', 'Standardrolle kann nicht geändert werden.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'label' => 'required|string|max:255',
            'role_type' => 'required|in:rank,department,other',
            'department_id' => 'required_if:role_type,department|nullable|exists:departments,id',
            'permissions' => 'nullable|array',
        ]);

         if ($validator->fails()) {
            return redirect()->route('admin.roles.index', ['role' => $role->id])
                             ->withErrors($validator, 'updateRole')->withInput();
        }

        $oldName = $role->name;
        $newName = strtolower(trim($request->name));
        $newType = $request->role_type;
        $newLabel = $request->label;
        $permissionsToSync = $request->permissions ?? [];

        // Alten Typ bestimmen
        $oldType = 'other';
        $rankEntry = Rank::whereRaw('LOWER(name) = ?', [strtolower($oldName)])->first();
        if ($rankEntry) { $oldType = 'rank'; }
        elseif (DB::table('department_role')->where('role_id', $role->id)->exists()) { $oldType = 'department'; }

        try {
            DB::beginTransaction();

            // 1. Rolle updaten
            // Wenn der NEUE Typ 'rank' ist, soll das Label in der Roles-Tabelle NULL sein (oder leer)
            // Wenn der NEUE Typ NICHT 'rank' ist, soll das Label in der Roles-Tabelle stehen.
            
            $roleUpdateData = ['name' => $newName];
            if ($newType !== 'rank') {
                $roleUpdateData['label'] = $newLabel;
            } else {
                // Optional: Label in Role Tabelle leeren, wenn es nun ein Rank ist, um Redundanz zu vermeiden
                $roleUpdateData['label'] = null; 
            }
            $role->update($roleUpdateData);
            $role->syncPermissions($permissionsToSync);

            // 2. Typ-Wechsel und Rank/Department Logik
            // Cleanup Old stuff
            if ($oldType === 'rank' && $rankEntry) { 
                // Wenn wir immer noch Rank sind, behalten wir den Eintrag, sonst löschen
                if ($newType !== 'rank') {
                    $rankEntry->delete(); 
                }
            }
            if ($oldType === 'department') { 
                DB::table('department_role')->where('role_id', $role->id)->delete(); 
            }

            // Create/Update New stuff
            if ($newType === 'rank') {
                // Rank Eintrag aktualisieren oder erstellen
                Rank::updateOrCreate(
                    ['name' => $oldName], // Suche nach altem Namen (falls er sich geändert hat, korrigieren wir gleich)
                    [
                        'name' => $newName, 
                        'label' => $newLabel, // <--- Hier speichern wir das Label für Ranks
                        // Level beibehalten oder 0 bei neu
                        'level' => $rankEntry ? $rankEntry->level : 0 
                    ]
                );
            } elseif ($newType === 'department') {
                $department = Department::find($request->department_id);
                $department->roles()->attach($role->id);
            }

            // Namensänderung im Rank korrigieren (falls wir Rank waren und Rank geblieben sind, hat updateOrCreate das erledigt. 
            // Aber falls logic oben abweicht, hier sicherheitshalber:)
            if ($oldName !== $newName && $newType === 'rank') {
                 // Wurde durch updateOrCreate schon behandelt
            }

            DB::commit();

            ActivityLog::create([
                'user_id' => Auth::id(), 'log_type' => 'ROLE', 'action' => 'UPDATED',
                'target_id' => $role->id, 'description' => "Rolle '{$oldName}' aktualisiert zu '{$newName}'.",
            ]);

            return redirect()->route('admin.roles.index', ['role' => $role->id])->with('success', 'Rolle aktualisiert.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.roles.index', ['role' => $role->id])->with('error', 'Fehler: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Entfernt die Rolle UND ggf. den zugehörigen Rank-Eintrag.
     */
    public function destroy(Role $role)
    {
        // GEÄNDERT: Verwendet die $superAdminRole Eigenschaft
        if ($role->name === $this->superAdminRole || $role->name === 'chief' || $role->users()->count() > 0) {
             $error = match(true) {
                   $role->name === $this->superAdminRole, $role->name === 'chief' => 'Standardrollen können nicht gelöscht werden.',
                   $role->users()->count() > 0 => 'Rolle kann nicht gelöscht werden, da noch Benutzer zugewiesen sind.',
                   default => 'Diese Rolle kann nicht gelöscht werden.'
             };
            return back()->with('error', $error);
        }

        $roleName = $role->name;
        $roleId = $role->id;
        $deletedRoleData = clone $role;
        $deletedRoleData->load('permissions');

        try {
            DB::beginTransaction();
            $rankEntry = Rank::whereRaw('LOWER(name) = ?', [strtolower($roleName)])->first();
            if ($rankEntry) { $rankEntry->delete(); }
            $role->delete();
            DB::commit();

            // Logging & Event
            ActivityLog::create([
                'user_id' => Auth::id(), 'log_type' => 'ROLE', 'action' => 'DELETED',
                'target_id' => $roleId, 'description' => "Rolle '{$roleName}' gelöscht.",
            ]);
            PotentiallyNotifiableActionOccurred::dispatch('Admin\RoleController@destroy', Auth::user(), $deletedRoleData, Auth::user());

            return redirect()->route('admin.roles.index')->with('success', "Rolle '{$roleName}' erfolgreich gelöscht.");

        } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Fehler beim Löschen der Rolle {$roleId}: " . $e->getMessage());
             return back()->with('error', 'Fehler beim Löschen der Rolle.');
        }
    }

    // ===========================================
    // DEPARTMENT CRUD METHODEN
    // ===========================================

    /**
     * Speichert eine neue Abteilung.
     */
    public function storeDepartment(Request $request)
    {
        // Hole Rollen und Ranks für die Validierung
        // GEÄNDERT: Filtert Super-Admin aus der Validierungsliste heraus.
        $allRoleNames = Role::where('name', '!=', $this->superAdminRole)->pluck('name')->toArray();
        $allRankLevels = Rank::pluck('level')->toArray(); // Nur die Level-Werte

        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:255|unique:departments,name',
             'leitung_role_name' => ['nullable','string', Rule::in($allRoleNames)], // Prüft gegen existierende Rollen
             'min_rank_level_to_assign_leitung' => ['nullable','integer', Rule::in($allRankLevels)], // Prüft gegen existierende Level
        ], [
            'department_name.required' => 'Der Abteilungsname ist erforderlich.',
            'department_name.unique' => 'Eine Abteilung mit diesem Namen existiert bereits.',
            'leitung_role_name.in' => 'Die ausgewählte Leitungsrolle ist ungültig.',
            'min_rank_level_to_assign_leitung.in' => 'Das ausgewählte minimale Rang-Level ist ungültig.',
            'min_rank_level_to_assign_leitung.integer' => 'Das minimale Rang-Level muss eine Zahl sein.',
        ]);

        if ($validator->fails()) {
             return back()->withErrors($validator, 'createDepartment')
                          ->withInput()
                          ->with('open_modal', 'createDepartmentModal');
        }

        try {
            $department = Department::create([
                'name' => $request->department_name,
                 'leitung_role_name' => $request->leitung_role_name ?? '', // Speichere leeren String wenn null
                 'min_rank_level_to_assign_leitung' => $request->min_rank_level_to_assign_leitung ?? 0, // Speichere 0 wenn null
            ]);

            // Logging & Event
            ActivityLog::create([
                'user_id' => Auth::id(), 'log_type' => 'DEPARTMENT', 'action' => 'CREATED',
                'target_id' => $department->id, 'description' => "Neue Abteilung '{$department->name}' erstellt.",
            ]);
            PotentiallyNotifiableActionOccurred::dispatch('Admin\RoleController@storeDepartment', Auth::user(), $department, Auth::user());

            return redirect()->route('admin.roles.index')->with('success', 'Abteilung erfolgreich erstellt.');

        } catch (\Exception $e) {
             Log::error("Fehler beim Erstellen der Abteilung: " . $e->getMessage());
             return back()->with('error', 'Fehler beim Erstellen der Abteilung.')->withInput();
        }
    }

    /**
     * Aktualisiert eine Abteilung.
     */
    public function updateDepartment(Request $request, Department $department)
    {
         // Hole Rollen und Ranks für die Validierung
         // GEÄNDERT: Filtert Super-Admin aus der Validierungsliste heraus.
        $allRoleNames = Role::where('name', '!=', $this->superAdminRole)->pluck('name')->toArray();
        $allRankLevels = Rank::pluck('level')->toArray();

         $validator = Validator::make($request->all(), [
             'edit_department_name' => 'required|string|max:255|unique:departments,name,' . $department->id,
             'edit_leitung_role_name' => ['nullable','string', Rule::in($allRoleNames)],
             'edit_min_rank_level_to_assign_leitung' => ['nullable','integer', Rule::in($allRankLevels)],
         ], [
             'edit_department_name.required' => 'Der Abteilungsname darf nicht leer sein.',
             'edit_department_name.unique' => 'Eine Abteilung mit diesem Namen existiert bereits.',
             'edit_leitung_role_name.in' => 'Die ausgewählte Leitungsrolle ist ungültig.',
             'edit_min_rank_level_to_assign_leitung.in' => 'Das ausgewählte minimale Rang-Level ist ungültig.',
             'edit_min_rank_level_to_assign_leitung.integer' => 'Das minimale Rang-Level muss eine Zahl sein.',
             // 'edit_min_rank_level_to_assign_leitung.min' war redundant durch Rule::in, kann aber optional bleiben
         ]);

         if ($validator->fails()) {
             return back()->withErrors($validator, 'editDepartment_' . $department->id)
                          ->withInput()
                          ->with('open_modal', 'editDepartmentModal_' . $department->id);
         }

        try {
            $oldName = $department->name;
            $oldLeitungRole = $department->leitung_role_name;
            $oldMinRankLevel = $department->min_rank_level_to_assign_leitung;
            $oldData = $department->toArray();

            $department->update([
                'name' => $request->edit_department_name,
                'leitung_role_name' => $request->edit_leitung_role_name ?? '', // Setze auf leer wenn nicht vorhanden
                'min_rank_level_to_assign_leitung' => $request->edit_min_rank_level_to_assign_leitung ?? 0, // Setze auf 0 wenn nicht vorhanden
            ]);

            // Logging
            $logDescription = "Abteilung '{$oldName}' aktualisiert.";
            if ($oldName !== $department->name) $logDescription .= " Neuer Name: '{$department->name}'.";
            if ($oldLeitungRole !== $department->leitung_role_name) $logDescription .= " Leitungsrolle geändert: '{$oldLeitungRole}' -> '{$department->leitung_role_name}'.";
            if ($oldMinRankLevel != $department->min_rank_level_to_assign_leitung) $logDescription .= " Min. Rang-Level geändert: {$oldMinRankLevel} -> {$department->min_rank_level_to_assign_leitung}.";

            ActivityLog::create([
                'user_id' => Auth::id(), 'log_type' => 'DEPARTMENT', 'action' => 'UPDATED',
                'target_id' => $department->id, 'description' => $logDescription,
            ]);

            // Event
            PotentiallyNotifiableActionOccurred::dispatch('Admin\RoleController@updateDepartment', Auth::user(), $department, Auth::user());

            return redirect()->route('admin.roles.index')->with('success', 'Abteilung erfolgreich aktualisiert.');

        } catch (\Exception $e) {
             Log::error("Fehler beim Aktualisieren der Abteilung {$department->id}: " . $e->getMessage());
             return back()->with('error', 'Fehler beim Aktualisieren der Abteilung.')->withInput();
        }
    }

    /**
     * Löscht eine Abteilung.
     */
    public function destroyDepartment(Department $department)
    {
        if ($department->roles()->count() > 0) {
             return back()->with('error', 'Abteilung kann nicht gelöscht werden, da ihr noch Rollen zugewiesen sind.');
        }

        try {
            $deptName = $department->name;
            $deptId = $department->id;
            $deletedDeptData = clone $department;

            $department->delete();

            // Logging & Event
            ActivityLog::create([
                'user_id' => Auth::id(), 'log_type' => 'DEPARTMENT', 'action' => 'DELETED',
                'target_id' => $deptId, 'description' => "Abteilung '{$deptName}' gelöscht.",
            ]);
            PotentiallyNotifiableActionOccurred::dispatch('Admin\RoleController@destroyDepartment', Auth::user(), $deletedDeptData, Auth::user());

            return redirect()->route('admin.roles.index')->with('success', 'Abteilung erfolgreich gelöscht.');

        } catch (\Exception $e) {
             Log::error("Fehler beim Löschen der Abteilung {$department->id}: " . $e->getMessage());
             return back()->with('error', 'Fehler beim Löschen der Abteilung.');
        }
    }
}