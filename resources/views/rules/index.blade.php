@extends('layouts.app')

@section('title', 'Regelwerk Übersicht')

@section('content')
@php
    // --- BERECHTIGUNG ---
    $canManage = Auth::check() && (Auth::user()->rank >= 10); 
@endphp

<style>
    /* --- Allgemeine Text-Styles (Dark Mode) --- */
    .rule-content {
        color: #e2e8f0;
        font-size: 1.05rem;
        line-height: 1.7;
    }

    /* CKEditor Elemente Anpassung */
    .rule-content table {
        width: 100%;
        margin-bottom: 1rem;
        color: #fff;
        border-collapse: collapse;
        border: 1px solid #4a5568;
        background-color: #1a202c;
        border-radius: 8px;
        overflow: hidden;
    }
    .rule-content table td, .rule-content table th {
        border: 1px solid #4a5568;
        padding: 12px 15px;
    }
    .rule-content h2, .rule-content h3, .rule-content h4 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #fff;
        border-bottom: 1px solid #4a5568;
        padding-bottom: 8px;
        font-weight: 600;
    }
    .rule-content ul, .rule-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .rule-content a {
        color: #63b3ed;
        text-decoration: none;
    }
    .rule-content a:hover {
        text-decoration: underline;
    }

    /* --- TIMELINE DESIGN DASHBOARD STYLE --- */
    .timeline {
        position: relative;
        padding: 30px 0;
    }

    /* Die vertikale Linie */
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 24px; /* Position der Linie */
        width: 4px;
        background: rgba(255,255,255,0.1);
        border-radius: 2px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 60px;
        padding-left: 80px; /* Mehr Platz für den größeren Marker */
    }

    /* Der Punkt auf der Linie */
    .timeline-marker {
        position: absolute;
        left: 10px; /* Zentriert auf der 4px Linie bei left:24px (24+2=26 Center) -> 32px Breite/2 = 16. 26-16 = 10px */
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%); /* Hero Gradient */
        border: 4px solid #1a202c; /* Rand in Hintergrundfarbe für "Cutout"-Effekt */
        box-shadow: 0 0 0 2px rgba(75, 108, 183, 0.5); /* Leuchterand */
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 10px;
    }

    /* Karte für den Inhalt - Dashboard Style */
    .timeline-card {
        background-color: #2d3748; /* Dashboard Card Color */
        border: none;
        border-radius: 12px;
        position: relative;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden; /* Für Header Gradient */
    }

    /* Kleiner Pfeil zur Timeline hin */
    .timeline-card::before {
        content: '';
        position: absolute;
        top: 15px;
        left: -10px;
        width: 0; 
        height: 0; 
        border-top: 10px solid transparent;
        border-bottom: 10px solid transparent; 
        border-right: 10px solid #2d3748;
    }

    .timeline-header {
        background: rgba(0,0,0,0.2);
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .timeline-title {
        margin: 0;
        color: #fff;
        font-weight: 700;
        font-size: 1.5rem;
        letter-spacing: -0.5px;
    }

    .timeline-body {
        padding: 2rem;
    }
</style>

<!-- 1. Hero Section -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold mb-0">
                    <i class="fas fa-book mr-2" style="opacity: 0.6;"></i> Regelwerk
                </h1>
                <p class="lead mb-0 mt-2" style="opacity: 0.9;">
                    Offizielle Richtlinien &bull; <span class="text-white-50">Stand: {{ date('Y') }}</span>
                </p>
            </div>
            <div class="col-md-4 text-right">
                @if($canManage)
                    <a href="{{ route('rules.create') }}" class="btn btn-success btn-lg rounded-pill px-4 font-weight-bold shadow-lg" style="background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%); border: none;">
                        <i class="fas fa-plus mr-2"></i> Neuer Abschnitt
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 2. Main Content -->
<section class="content pb-5">
    <div class="container-fluid">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-5 shadow border-0" role="alert" style="background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%); color: white;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x mr-3"></i>
                    <div>
                        <h5 class="mb-0 font-weight-bold">Erfolg!</h5>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="timeline">
            @forelse($rules as $rule)
                <div class="timeline-item">
                    <!-- Marker -->
                    <div class="timeline-marker"></div>

                    <!-- Inhalt -->
                    <div class="timeline-card">
                        <div class="timeline-header">
                            <h2 class="timeline-title">
                                <i class="fas fa-paragraph text-muted mr-2" style="font-size: 1rem; opacity: 0.5;"></i>
                                {{ $rule->title }}
                            </h2>
                            
                            @if($canManage)
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('rules.edit', $rule->id) }}" class="btn btn-sm btn-dark border-secondary" title="Bearbeiten">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>
                                    <form action="{{ route('rules.destroy', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Möchten Sie den Abschnitt \'{{ $rule->title }}\' wirklich unwiderruflich löschen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-dark border-secondary" title="Löschen" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="timeline-body rule-content">
                            {!! $rule->content !!}
                        </div>

                        <div class="px-4 py-3 bg-black-10 text-right border-top border-secondary" style="border-color: rgba(255,255,255,0.05) !important;">
                            <small class="text-muted font-italic">
                                <i class="far fa-clock mr-1"></i>
                                Zuletzt aktualisiert: {{ $rule->updated_at->format('d.m.Y H:i') }}
                                @if($rule->editor) 
                                    von <strong class="text-white-50">{{ $rule->editor->name }}</strong>
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="timeline-item">
                    <div class="timeline-marker" style="background: #4a5568; box-shadow: none;"></div>
                    <div class="timeline-card text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-book-reader fa-3x text-muted" style="opacity: 0.3;"></i>
                        </div>
                        <h3 class="text-white font-weight-bold">Keine Einträge</h3>
                        <p class="text-muted">Das Regelwerk ist aktuell noch leer.</p>
                        @if($canManage)
                            <a href="{{ route('rules.create') }}" class="btn btn-outline-light rounded-pill mt-3 px-4">
                                Ersten Abschnitt erstellen
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
        
        <div class="text-center text-muted mt-5 mb-5 opacity-50">
            <small>&copy; {{ date('Y') }} Los Santos Police Department - Interne Verwaltung</small>
        </div>
    </div>
</section>
@endsection