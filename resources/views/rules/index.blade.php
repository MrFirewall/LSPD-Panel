@extends('layouts.app')

@section('content')
@php
    // --- BERECHTIGUNG ---
    // Hier kannst du einstellen, wer die Buttons sehen darf.
    // Beispiel: Auth::user()->rank >= 10 oder Auth::user()->can('manage_rules')
    // Aktuell: Zum Testen auf true gesetzt, wenn eingeloggt.
    $canManage = Auth::check() && (Auth::user()->rank >= 10); 
@endphp

<style>
    /* --- Styles für die Anzeige der Regel-Inhalte (Dark Mode kompatibel) --- */
    .rule-content {
        color: #e0e0e0; /* Helle Schrift */
        font-size: 1.05rem;
        line-height: 1.6;
    }

    /* Tabellen aus dem CKEditor hübsch machen */
    .rule-content table {
        width: 100%;
        margin-bottom: 1rem;
        color: #fff;
        border-collapse: collapse;
        border: 1px solid #495057;
    }
    
    .rule-content table td, 
    .rule-content table th {
        border: 1px solid #495057;
        padding: 8px 12px;
    }

    /* Listen sauber einrücken */
    .rule-content ul, .rule-content ol {
        padding-left: 1.5rem;
    }

    /* Überschriften im Text */
    .rule-content h2, .rule-content h3, .rule-content h4 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        color: #fff;
        border-bottom: 1px solid #495057;
        padding-bottom: 5px;
    }

    /* Karten-Design Anpassung */
    .card-rule {
        border: 1px solid #495057;
        background-color: #212529; /* Dunkler Hintergrund */
    }
    
    .card-rule .card-header {
        background-color: #2b3035;
        border-bottom: 1px solid #495057;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .card-rule .card-header:hover {
        background-color: #343a40;
    }

    /* Link/Button Style im Header */
    .rule-toggle-btn {
        color: #fff;
        text-decoration: none;
        font-weight: bold;
        display: block;
        width: 100%;
        text-align: left;
    }
    .rule-toggle-btn:hover {
        color: #17a2b8; /* Hover Farbe */
        text-decoration: none;
    }
    
    /* Icon Rotation wenn geöffnet (Optional, braucht JS, hier einfach statisch) */
    .collapse-icon {
        float: right;
        font-size: 0.8em;
        color: #adb5bd;
    }
</style>

<div class="container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h1 class="mb-0">Internes LSPD Regelwerk</h1>
            <small class="text-muted">Stand: {{ date('Y') }} | Offizielles Dokument</small>
        </div>
        
        @if($canManage)
            <a href="{{ route('rules.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus"></i> Neuen Abschnitt
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="accordion" id="rulesAccordion">
        @forelse($rules as $rule)
            <div class="card card-rule mb-3 shadow-sm">
                <!-- Header: Klickbar zum Öffnen/Schließen -->
                <div class="card-header d-flex justify-content-between align-items-center" id="heading{{ $rule->id }}">
                    <div class="flex-grow-1">
                        <h5 class="mb-0">
                            <button class="btn btn-link rule-toggle-btn p-0" type="button" data-toggle="collapse" data-target="#collapse{{ $rule->id }}" aria-expanded="true" aria-controls="collapse{{ $rule->id }}">
                                {{ $rule->title }}
                            </button>
                        </h5>
                    </div>
                    
                    <!-- Admin Aktionen: Nur sichtbar wenn berechtigt -->
                    @if($canManage)
                        <div class="ml-3 d-flex align-items-center">
                            <a href="{{ route('rules.edit', $rule->id) }}" class="btn btn-sm btn-outline-warning mr-2" title="Bearbeiten">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <form action="{{ route('rules.destroy', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Möchten Sie den Abschnitt \'{{ $rule->title }}\' wirklich unwiderruflich löschen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Löschen">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Inhalt: Standardmäßig geöffnet (show) -->
                <div id="collapse{{ $rule->id }}" class="collapse show" aria-labelledby="heading{{ $rule->id }}">
                    <div class="card-body rule-content">
                        <!-- Hier wird das HTML aus der Datenbank gerendert -->
                        {!! $rule->content !!}
                        
                        <div class="mt-4 pt-3 border-top border-secondary">
                            <small class="text-muted">
                                <i class="fas fa-history"></i> 
                                Zuletzt aktualisiert: {{ $rule->updated_at->format('d.m.Y H:i') }}
                                @if($rule->editor) 
                                    von <span class="text-info">{{ $rule->editor->name }}</span>
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info text-center p-5">
                <h4>Noch keine Regeln vorhanden</h4>
                @if($canManage)
                    <p>Beginnen Sie mit dem Erstellen des ersten Abschnitts.</p>
                    <a href="{{ route('rules.create') }}" class="btn btn-primary">Erstellen</a>
                @else
                    <p>Das Regelwerk wird derzeit überarbeitet.</p>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection