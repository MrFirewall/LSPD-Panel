@extends('layouts.app')

@section('title', 'Profil von ' . $user->name)

@section('content')

{{-- 1. PROFILE HERO HEADER --}}
<div class="content-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 1.5rem; margin-bottom: 1.5rem; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold mb-0">Personalakte</h1>
                <p class="lead mb-0 mt-2" style="opacity: 0.9;">
                    Dienstakte von <strong>{{ $user->name }}</strong>
                </p>
            </div>
            <div class="col-md-4 text-right">
                <span class="badge badge-light badge-pill px-3 py-2 text-dark font-weight-bold" style="font-size: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    {{ $user->rankRelation->label ?? 'Mitarbeiter' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<section class="content">
    <div class="container-fluid">
        <div class="row">
            
            {{-- LINKE SPALTE: Profilbild & Stammdaten --}}
            <div class="col-md-3">
                
                {{-- Profilkarte --}}
                <div class="card card-outline card-primary shadow-lg">
                    <div class="card-body box-profile text-center">
                        <div class="text-center mb-3">
                            <img class="profile-user-img img-fluid img-circle elevation-2"
                                 src="{{ $user->avatar }}"
                                 alt="Profilbild"
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #2d3748;">
                        </div>

                        <h3 class="profile-username text-center font-weight-bold">{{ $user->name }}</h3>
                        <p class="text-muted text-center mb-4">
                            @forelse($user->getRoleNames() as $role)
                                <span class="badge badge-info">{{ $role }}</span>
                            @empty
                                <span class="text-muted small">Keine Rolle zugewiesen</span>
                            @endforelse
                        </p>

                        @can('users.edit')
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-block rounded-pill">
                                <i class="fas fa-pencil-alt mr-1"></i> Profil bearbeiten
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- Stammdaten Partial --}}
                @include('profile.partials.master-data', ['user' => $user])

                {{-- Metadaten Partial (Admin Only) --}}
                @can('users.manage.record')
                    @include('profile.partials.metadata', ['user' => $user])
                @endcan

            </div>

            {{-- RECHTE SPALTE: Dashboard-Style Layout (Keine Tabs mehr) --}}
            <div class="col-md-9">
                
                {{-- Sektion 1: Stunden & Statistiken --}}
                <div class="mb-4">
                    @include('profile.partials.hours', ['hourData' => $hourData])
                </div>

                {{-- Sektion 2: Details Grid --}}
                <div class="row">
                    <div class="col-md-6">
                        @include('profile.partials.examinations', ['examinations' => $user->examinations])
                        @include('profile.partials.training-modules', ['trainingModules' => $user->trainingModules])
                    </div>
                    <div class="col-md-6">
                        @include('profile.partials.evaluations', ['evaluationCounts' => $evaluationCounts])
                        @include('profile.partials.vacations', ['vacations' => $user->vacations])
                    </div>
                </div>

                {{-- Sektion 3: Personalakte (Admin Only) - Jetzt untereinander angeordnet --}}
                @can('users.manage.record')
                    <div class="mt-5">
                        {{-- Eleganter Trenner --}}
                        <div class="d-flex align-items-center mb-3">
                            <h4 class="font-weight-bold mb-0 text-white"><i class="fas fa-folder-open text-primary mr-2"></i> Interne Personalakte</h4>
                            <div class="ml-3 border-bottom flex-grow-1" style="border-color: rgba(255,255,255,0.1) !important;"></div>
                        </div>
                        
                        @include('profile.partials.service-records', ['user' => $user, 'serviceRecords' => $serviceRecords])
                    </div>
                @endcan

            </div>

        </div>
    </div>
</section>
@endsection