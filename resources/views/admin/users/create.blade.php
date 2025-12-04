@extends('layouts.app')

@section('title', 'Neuen Mitarbeiter anlegen')

@section('content')

{{-- 1. HERO HEADER --}}
<div class="content-header" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #1a202c; padding: 2rem 1.5rem; margin-bottom: 1.5rem; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h5 class="text-uppercase font-weight-bold mb-1" style="opacity: 0.7; letter-spacing: 1px;">Personalmanagement</h5>
                <h1 class="display-4 font-weight-bold mb-0">
                    <i class="fas fa-user-plus mr-3"></i>Neuer Mitarbeiter
                </h1>
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-dark rounded-pill px-4 font-weight-bold">
                    <i class="fas fa-arrow-left mr-2"></i> Zurück zur Liste
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<section class="content">
    <div class="container-fluid">
        
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            {{-- STAMMDATEN --}}
            <div class="card card-outline card-success shadow-lg border-0 mb-4">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-file-signature mr-2 text-success"></i> Stammdaten</h3>
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
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name') }}" placeholder="Max Mustermann" required>
                                </div>
                                @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- CFX.re ID --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cfx_id" class="text-muted small text-uppercase">CFX.re ID</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('cfx_id') is-invalid @enderror" name="cfx_id" id="cfx_id" value="{{ old('cfx_id') }}" placeholder="1234567" required>
                                </div>
                                @error('cfx_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status" class="text-muted small text-uppercase">Status</label>
                                <select name="status" id="status" class="form-control select2 @error('status') is-invalid @enderror" required style="width: 100%;">
                                    <option value="" disabled selected>Bitte auswählen</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ old('status', 'Bewerbungsphase') == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="w-100 d-none d-md-block my-2"></div>

                        {{-- Mitarbeiter ID (Disabled) --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="employee_id" class="text-muted small text-uppercase">Mitarbeiter ID</label>
                                <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" disabled placeholder="Wird automatisch generiert" style="background-color: rgba(0,0,0,0.2); opacity: 0.7;">
                                <small class="text-muted font-italic">Die ID wird automatisch nach dem Anlegen generiert.</small>
                            </div>
                        </div>

                        {{-- E-Mail --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email" class="text-muted small text-uppercase">E-Mail</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email') }}" placeholder="Optional">
                                </div>
                                @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Geburtstag --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="birthday" class="text-muted small text-uppercase">Geburtstag</label>
                                <input type="date" class="form-control @error('birthday') is-invalid @enderror" name="birthday" id="birthday" value="{{ old('birthday') }}">
                                @error('birthday')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
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
                                    <input type="text" class="form-control @error('discord_name') is-invalid @enderror" name="discord_name" id="discord_name" value="{{ old('discord_name') }}" placeholder="Username#1234">
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
                                    <input type="text" class="form-control @error('forum_name') is-invalid @enderror" name="forum_name" id="forum_name" value="{{ old('forum_name') }}">
                                </div>
                                @error('forum_name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Einstellungsdatum --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="hire_date" class="text-muted small text-uppercase">Einstellungsdatum</label>
                                <input type="date" class="form-control @error('hire_date') is-invalid @enderror" name="hire_date" id="hire_date" value="{{ old('hire_date') ?? \Carbon\Carbon::now()->format('Y-m-d') }}">
                                @error('hire_date')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="w-100 d-none d-md-block my-2"></div>

                        {{-- Zweitfraktion --}}
                        <div class="col-md-12">
                            <div class="form-group clearfix mt-2 p-3 rounded" style="background-color: rgba(255,255,255,0.03);">
                                <div class="icheck-success d-inline">
                                    <input type="checkbox" id="second_faction" name="second_faction" value="1" {{ old('second_faction') ? 'checked' : '' }}>
                                    <label for="second_faction" class="font-weight-normal">
                                        <i class="fas fa-users-slash mr-2 text-muted"></i> Hat eine Zweitfraktion
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ZWEITE ZEILE: Rollen & Module --}}
            <div class="row">
                
                {{-- GRUPPEN / RANG ZUWEISUNG --}}
                <div class="col-md-6">
                    <div class="card card-outline card-info shadow-lg border-0 h-100">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-user-tag mr-2 text-info"></i> Rang & Gruppen (Initial)</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4 bg-light p-2 rounded" style="background-color: rgba(255,255,255,0.05) !important;">
                                <i class="fas fa-info-circle mr-1"></i> Wähle den Start-Rang und optionale Zusatzrollen.
                            </p>
                            
                            @error('roles')<div class="alert alert-danger">{{ $message }}</div>@enderror
                            @error('roles.*')<div class="alert alert-danger">{{ $message }}</div>@enderror

                            {{-- 1. HAUPT-RANG --}}
                            @if (!empty($categorizedRoles['Ranks']))
                                <h6 class="text-info font-weight-bold mt-2 mb-3 text-uppercase small ls-1">Haupt-Rang</h6>
                                <div class="form-group pl-2">
                                    @foreach($categorizedRoles['Ranks'] as $role)
                                        <div class="icheck-info mb-2">
                                            <input type="radio" 
                                                   name="roles[]" 
                                                   value="{{ $role->name }}" 
                                                   id="rank_{{ $role->id }}" 
                                                   {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                                            <label for="rank_{{ $role->id }}" class="font-weight-normal">
                                                {{ $role->label }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning shadow-sm border-0">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Keine Ränge verfügbar.
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
                                                               {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
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
                                                           {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
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
                            <h3 class="card-title font-weight-bold"><i class="fas fa-graduation-cap mr-2 text-success"></i> Module (Initial)</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border border-secondary text-muted small mb-4" style="background-color: rgba(255,255,255,0.05) !important;">
                                <i class="fas fa-info-circle mr-1"></i> Wähle Module, die der Mitarbeiter bereits bestanden hat (z.B. bei Wiedereinstellung).
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
                                                   @if(in_array($module->id, old('modules', []))) checked @endif>
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

            </div>

            {{-- FOOTER ACTION BAR --}}
            <div class="card mt-4 mb-5 border-0 shadow-lg" style="background: rgba(45, 55, 72, 0.95); position: sticky; bottom: 20px; z-index: 10;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default rounded-pill px-4">Abbrechen</a>
                    <button type="submit" class="btn btn-success rounded-pill px-5 font-weight-bold shadow-sm">
                        <i class="fas fa-user-plus mr-2"></i> Mitarbeiter anlegen
                    </button>
                </div>
            </div>

        </form>
    </div>
</section>
@endsection