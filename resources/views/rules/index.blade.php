@extends('layouts.app')

@section('content')
@php
    // --- BERECHTIGUNG ---
    $canManage = Auth::check() && (Auth::user()->rank >= 10); 
@endphp

<style>
    /* --- Allgemeine Text-Styles (Dark Mode) --- */
    .rule-content {
        color: #e0e0e0;
        font-size: 1.05rem;
        line-height: 1.7;
    }

    /* CKEditor Elemente */
    .rule-content table {
        width: 100%;
        margin-bottom: 1rem;
        color: #fff;
        border-collapse: collapse;
        border: 1px solid #495057;
        background-color: #2b3035;
    }
    .rule-content table td, .rule-content table th {
        border: 1px solid #495057;
        padding: 10px 15px;
    }
    .rule-content h2, .rule-content h3, .rule-content h4 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #fff;
        border-bottom: 1px solid #495057;
        padding-bottom: 8px;
    }
    .rule-content ul, .rule-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .rule-content a {
        color: #3498db;
    }

    /* --- TIMELINE DESIGN --- */
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    /* Die vertikale Linie */
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 20px; /* Position der Linie */
        width: 3px;
        background: #495057;
        border-radius: 2px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 50px; /* Abstand zwischen den Regeln */
        padding-left: 60px; /* Platz für Linie und Punkt */
    }

    /* Der Punkt auf der Linie */
    .timeline-marker {
        position: absolute;
        left: 9px; /* Zentriert auf der 3px Linie bei left:20px */
        top: 0;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background-color: #0d6efd; /* Primary Color */
        border: 4px solid #1a1d20; /* Rand in Hintergrundfarbe für "Cutout"-Effekt */
        box-shadow: 0 0 0 1px #0d6efd; /* Leuchterand */
        z-index: 1;
    }

    /* Karte für den Inhalt */
    .timeline-card {
        background-color: #212529;
        border: 1px solid #495057;
        border-radius: 8px;
        padding: 25px;
        position: relative;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    /* Kleiner Pfeil zur Timeline hin */
    .timeline-card::before {
        content: '';
        position: absolute;
        top: 10px;
        left: -10px; /* Pfeilspitze */
        width: 0; 
        height: 0; 
        border-top: 10px solid transparent;
        border-bottom: 10px solid transparent; 
        border-right: 10px solid #495057; /* Farbe des Rands */
    }
    .timeline-card::after {
        content: '';
        position: absolute;
        top: 11px;
        left: -8px; /* Pfeilspitze Innen */
        width: 0; 
        height: 0; 
        border-top: 9px solid transparent;
        border-bottom: 9px solid transparent; 
        border-right: 9px solid #212529; /* Farbe des Hintergrunds */
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #343a40;
    }

    .timeline-title {
        margin: 0;
        color: #fff;
        font-weight: 700;
        font-size: 1.5rem;
    }
</style>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-5 mt-4">
        <div>
            <h1 class="mb-0 font-weight-bold">Internes LSPD Regelwerk</h1>
            <p class="text-muted mt-1 mb-0">Offizielle Richtlinien (Stand: {{ date('Y') }})</p>
        </div>
        
        @if($canManage)
            <a href="{{ route('rules.create') }}" class="btn btn-primary btn-lg shadow">
                <i class="fas fa-plus"></i> Neuen Abschnitt
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="timeline">
        @forelse($rules as $rule)
            <div class="timeline-item">
                <!-- Der blaue Punkt auf der Linie -->
                <div class="timeline-marker"></div>

                <!-- Der Inhalt -->
                <div class="timeline-card">
                    <div class="timeline-header">
                        <h2 class="timeline-title">{{ $rule->title }}</h2>
                        
                        @if($canManage)
                            <div class="btn-group">
                                <a href="{{ route('rules.edit', $rule->id) }}" class="btn btn-sm btn-outline-warning" title="Bearbeiten">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('rules.destroy', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Möchten Sie den Abschnitt \'{{ $rule->title }}\' wirklich unwiderruflich löschen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Löschen" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="rule-content">
                        {!! $rule->content !!}
                    </div>

                    <div class="mt-4 pt-2 border-top border-secondary text-right">
                        <small class="text-muted font-italic">
                            Zuletzt bearbeitet: {{ $rule->updated_at->format('d.m.Y') }}
                            @if($rule->editor) 
                                von {{ $rule->editor->name }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        @empty
            <div class="timeline-item">
                <div class="timeline-marker" style="background-color: #6c757d; box-shadow: none;"></div>
                <div class="timeline-card text-center py-5">
                    <h3 class="text-muted">Noch keine Einträge</h3>
                    <p class="text-muted">Das Regelwerk ist aktuell leer.</p>
                    @if($canManage)
                        <a href="{{ route('rules.create') }}" class="btn btn-outline-primary mt-3">Ersten Abschnitt erstellen</a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>
    
    <!-- Abschluss der Timeline -->
    <div class="text-center text-muted mt-5 mb-5">
        <small>&copy; {{ date('Y') }} Los Santos Police Department - Interne Verwaltung</small>
    </div>
</div>
@endsection