@extends('layouts.app')

@section('title', 'Mitarbeiter bearbeiten: ' . $user->name)

@section('content')

{{-- 1. HERO HEADER --}}
<div class="content-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2rem 1.5rem; margin-bottom: 1.5rem; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h5 class="text-uppercase font-weight-bold mb-1" style="opacity: 0.8; letter-spacing: 1px;">Personalakte editieren</h5>
                <h1 class="display-4 font-weight-bold mb-0">
                    <i class="fas fa-user-edit mr-3"></i>{{ $user->name }}
                </h1>
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light rounded-pill px-4 font-weight-bold">
                    <i class="fas fa-arrow-left mr-2"></i> Zurück zur Liste
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<section class="content">
    <div class="container-fluid">
        
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            {{-- STAMMDATEN --}}
            <div class="card card-outline card-primary shadow-lg border-0 mb-4">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-id-card mr-2 text-primary"></i> Stammdaten</h3>
                    <div class="card-tools">
                        <span class="text-muted small">ID: {{ $user->id }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Name --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name" class="text-muted small text-uppercase">Mitarbeiter Name</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                                @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Personalnummer --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="personal_number" class="text-muted small text-uppercase">Personalnummer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    </div>
                                    <select name="personal_number" id="personal_number" class="form-control select2 @error('personal_number') is-invalid @enderror" required style="width: 80%;">
                                        <option value="">Bitte wählen...</option>
                                        @if($user->personal_number)
                                            <option value="{{ $user->personal_number }}" selected>{{ $user->personal_number }} (Aktuell)</option>
                                        @endif
                                        @foreach($availablePersonalNumbers as $number)
                                            @if($number != $user->personal_number)
                                                <option value="{{ $number }}" @if(old('personal_number') == $number) selected @endif>{{ $number }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                @error('personal_number')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Mitarbeiter ID --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="employee_id" class="text-muted small text-uppercase">Mitarbeiter ID</label>
                                <input type="text" class="form-control @error('employee_id') is-invalid @enderror" name="employee_id" id="employee_id" value="{{ old('employee_id', $user->employee_id) }}">
                                @error('employee_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="w-100 d-none d-md-block my-2"></div> {{-- Zeilenumbruch --}}

                        {{-- E-Mail --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email" class="text-muted small text-uppercase">E-Mail</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $user->email) }}">
                                </div>
                                @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Geburtstag --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="birthday" class="text-muted small text-uppercase">Geburtstag</label>
                                <input type="date" class="form-control @error('birthday') is-invalid @enderror" name="birthday" id="birthday" value="{{ old('birthday', $user->birthday) }}">
                                @error('birthday')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Einstellungsdatum --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="hire_date" class="text-muted small text-uppercase">Einstellungsdatum</label>
                                <input type="date" class="form-control @error('hire_date') is-invalid @enderror" name="hire_date" id="hire_date" value="{{ old('hire_date', optional($user->hire_date)->format('Y-m-d')) }}">
                                @error('hire_date')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="w-100 d-none d-md-block my-2"></div>

                        {{-- Discord --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="discord_name" class="text-muted small text-uppercase">Discord</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fab fa-discord"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('discord_name') is-invalid @enderror" name="discord_name" id="discord_name" value="{{ old('discord_name', $user->discord_name) }}">
                                </div>
                                @error('discord_name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Forum --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="forum_name" class="text-muted small text-uppercase">Forum</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-comments"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('forum_name') is-invalid @enderror" name="forum_name" id="forum_name" value="{{ old('forum_name', $user->forum_name) }}">
                                </div>
                                @error('forum_name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status" class="text-muted small text-uppercase">Status</label>
                                <select name="status" id="status" class="form-control select2 @error('status') is-invalid @enderror" required style="width: 100%;">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ old('status', $user->status) == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="w-100 d-none d-md-block my-2"></div>

                        {{-- Sonderfunktionen --}}
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="special_functions" class="text-muted small text-uppercase">Sonderfunktionen</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-star"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('special_functions') is-invalid @enderror" name="special_functions" id="special_functions" value="{{ old('special_functions', $user->special_functions) }}" placeholder="z.B. Ausbilder, Recruiter...">
                                </div>
                                @error('special_functions')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Zweitfraktion Checkbox --}}
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-group mb-0 mt-4">
                                <div class="icheck-primary d-inline">
                                    <input type="checkbox" id="second_faction" name="second_faction" value="1" @if(old('second_faction', $user->second_faction) == 'Ja') checked @endif>
                                    <label for="second_faction">
                                        <i class="fas fa-users-slash mr-1"></i> Hat eine Zweitfraktion
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div> {{-- /.row --}}
                </div>
            </div>

            {{-- ZWEITE ZEILE: Rollen & Module --}}
            <div class="row">
                
                {{-- GRUPPEN / RANG ZUWEISUNG --}}
                <div class="col-md-6">
                    <div class="card card-outline card-info shadow-lg border-0 h-100">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-user-tag mr-2 text-info"></i> Rang & Gruppen</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4 bg-light p-2 rounded" style="background-color: rgba(255,255,255,0.05) !important;">
                                <i class="fas fa-info-circle mr-1"></i> Bitte wählen Sie <strong>einen</strong> Haupt-Rang und optional weitere Zusatzrollen.
                            </p>
                            
                            @error('roles')<div class="alert alert-danger">{{ $message }}</div>@enderror
                            @error('roles.*')<div class="alert alert-danger">{{ $message }}</div>@enderror

                            @php
                                $currentUser = auth()->user();
                                $canEditUser = $currentUser->hasAnyRole('Super-Admin', 'chief') || ($currentUser->level > $user->level);
                                $hasRanksAvailable = !empty($categorizedRoles['Ranks']);
                            @endphp

                            {{-- 1. HAUPT-RANG --}}
                            @if($canEditUser && $hasRanksAvailable)
                                <h6 class="text-info font-weight-bold mt-2 mb-3 text-uppercase small ls-1">Haupt-Rang</h6>
                                <div class="form-group pl-2">
                                    @foreach($categorizedRoles['Ranks'] as $role)
                                        <div class="icheck-info mb-2">
                                            <input type="radio" 
                                                   name="roles[]" 
                                                   value="{{ $role->name }}" 
                                                   id="rank_{{ $role->id }}" 
                                                   @if(in_array($role->name, old('roles', $user->getRoleNames()->toArray()))) checked @endif>
                                            <label for="rank_{{ $role->id }}" class="font-weight-normal">
                                                {{ $role->label }}
                                                @if(in_array($role->name, $user->getRoleNames()->toArray()))
                                                    <span class="badge badge-info ml-2">Aktuell</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif(!$canEditUser)
                                <div class="alert alert-danger shadow-sm border-0">
                                    <h5><i class="icon fas fa-ban"></i> Zugriff verweigert</h5>
                                    Sie können den Rang dieses Mitarbeiters nicht ändern (Hierarchie).
                                </div>
                            @else
                                <div class="alert alert-warning shadow-sm border-0">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Keine verfügbaren Ränge gefunden.
                                </div>
                            @endif

                            {{-- 2. ABTEILUNGEN --}}
                            @if (!empty($categorizedRoles['Departments']))
                                <div class="mt-4 pt-3 border-top border-secondary">
                                    <h6 class="text-info font-weight-bold mb-3 text-uppercase small ls-1">Abteilungen</h6>
                                    @foreach($categorizedRoles['Departments'] as $deptName => $deptRoles)
                                        <strong class="text-muted d-block mb-2 text-xs text-uppercase">{{ $deptName }}</strong>
                                        <div class="row pl-2 mb-3">
                                            @foreach($deptRoles as $role)
                                                <div class="col-md-6">
                                                    <div class="icheck-primary mb-1">
                                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" id="dept_role_{{ $role->id }}"
                                                               @if(in_array($role->name, old('roles', $user->getRoleNames()->toArray()))) checked @endif>
                                                        <label for="dept_role_{{ $role->id }}">{{ $role->label }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- 3. SONSTIGE ROLLEN --}}
                            @if (!empty($categorizedRoles['Other']))
                                <div class="mt-4 pt-3 border-top border-secondary">
                                    <h6 class="text-info font-weight-bold mb-3 text-uppercase small ls-1">Sonstige</h6>
                                    <div class="row pl-2">
                                        @foreach($categorizedRoles['Other'] as $role)
                                            <div class="col-md-6">
                                                <div class="icheck-secondary mb-1">
                                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" id="other_role_{{ $role->id }}"
                                                           @if(in_array($role->name, old('roles', $user->getRoleNames()->toArray()))) checked @endif>
                                                    <label for="other_role_{{ $role->id }}">{{ $role->label }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                {{-- MANUELLE MODULZUWEISUNG --}}
                @can('users.manage.modules')
                <div class="col-md-6">
                    <div class="card card-outline card-success shadow-lg border-0 h-100">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-graduation-cap mr-2 text-success"></i> Module (Manuell)</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border border-secondary text-muted small mb-4" style="background-color: rgba(255,255,255,0.05) !important;">
                                <i class="fas fa-info-circle mr-1"></i> Wähle Module, um sie manuell als "bestanden" zu markieren. Entfernen löscht das Modul aus der Akte.
                            </div>
                            
                            @error('modules.*')<div class="alert alert-danger">{{ $message }}</div>@enderror
                            
                            <div class="row">
                                @forelse($allModules as $module)
                                    <div class="col-md-6">
                                        <div class="icheck-success mb-2">
                                            <input type="checkbox"
                                                   name="modules[]"
                                                   value="{{ $module->id }}"
                                                   id="module_{{ $module->id }}"
                                                   @if(in_array($module->id, old('modules', $userModules ?? []))) checked @endif>
                                            <label for="module_{{ $module->id }}">
                                                {{ $module->name }} 
                                                <span class="badge badge-dark ml-1 border border-secondary text-xs" style="font-weight: normal;">{{ $module->category }}</span>
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted py-4">
                                        Keine Module vorhanden.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- BERECHTIGUNGEN (Nur für Admins) --}}
                @role('chief|Super-Admin')
                <div class="col-md-12 mt-4">
                    <div class="card card-outline card-warning shadow-lg border-0">
                        <div class="card-header border-0 bg-transparent">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-shield-alt mr-2 text-warning"></i> Erweiterte Berechtigungen</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <p class="text-warning small mb-3"><i class="fas fa-exclamation-triangle mr-1"></i> Nur für Ausnahmefälle. Überschreibt Gruppenrechte.</p>
                            @error('permissions.*')<div class="alert alert-danger">{{ $message }}</div>@enderror
                            
                            <div class="row">
                                @forelse($permissions as $module => $modulePermissions)
                                    <div class="col-md-4 mb-3">
                                        <div class="card card-body p-3 border border-secondary bg-transparent h-100"> 
                                            <h6 class="text-uppercase text-xs font-weight-bold text-muted mb-3 border-bottom pb-1 border-secondary">{{ $module }}</h6>
                                            
                                            @foreach($modulePermissions as $permission)
                                                <div class="icheck-warning mb-1">
                                                    <input type="checkbox" name="permissions[]" 
                                                           value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                           {{ in_array($permission->name, old('permissions', $userDirectPermissions)) ? 'checked' : '' }}>
                                                    
                                                    <label for="perm_{{ $permission->id }}" class="small font-weight-normal text-light" style="opacity: 0.9;">
                                                        {{ ucfirst(str_replace('-', ' ', explode('.', $permission->name)[1] ?? $permission->name)) }}
                                                        <span class="text-muted d-block text-xs font-italic">({{ $permission->name }})</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12"><p class="text-muted">Keine Permissions.</p></div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @endrole

            </div>

            {{-- FOOTER ACTION BAR (FIXED: Sticky entfernt) --}}
            <div class="card mt-4 mb-4 border-0 shadow-lg" style="background: rgba(45, 55, 72, 0.95);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default rounded-pill px-4">Abbrechen</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-2"></i> Änderungen speichern
                    </button>
                </div>
            </div>

        </form>
    </div>
</section>
@endsection