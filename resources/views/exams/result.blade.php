@extends('layouts.app')

@section('title', 'Prüfungsergebnis: ' . $attempt->exam->title)

@section('content')

{{-- 1. HERO HEADER --}}
<div class="content-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem 1.5rem; margin-bottom: 1.5rem; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="text-uppercase font-weight-bold mb-1" style="opacity: 0.8; letter-spacing: 1px;">Prüfungsergebnis</h5>
                <h1 class="display-4 font-weight-bold mb-0" style="font-size: 2.5rem;">{{ $attempt->exam->title }}</h1>
                <p class="lead mb-0 mt-2" style="opacity: 0.9;">
                    Prüfling: <strong>{{ $attempt->user->name ?? 'Unbekannt' }}</strong> | Datum: {{ $attempt->created_at->format('d.m.Y H:i') }}
                </p>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('admin.exams.attempts.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-2"></i> Zurück zur Liste
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<section class="content">
    <div class="container-fluid">
        <div class="row">
            
            {{-- LINKE SPALTE: Status & Bewertung --}}
            <div class="col-lg-4">
                
                {{-- STATUS KARTE --}}
                @php
                    $finalScore = $attempt->score;
                    $passMark = optional($attempt->exam)->pass_mark ?? 0;
                    $isPassed = $finalScore !== null && $finalScore >= $passMark;
                    
                    $statusColor = $finalScore !== null ? ($isPassed ? 'success' : 'danger') : 'secondary';
                    $statusText = $finalScore !== null ? ($isPassed ? 'BESTANDEN' : 'NICHT BESTANDEN') : 'AUSSTEHEND';
                    $icon = $finalScore !== null ? ($isPassed ? 'fa-check-circle' : 'fa-times-circle') : 'fa-hourglass-half';
                    
                    // Gradient für die Status-Karte
                    $bgGradient = match($statusColor) {
                        'success' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
                        'danger' => 'linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%)',
                        default => 'linear-gradient(135deg, #606c88 0%, #3f4c6b 100%)'
                    };
                @endphp

                <div class="card shadow-lg border-0 mb-4" style="background: {{ $bgGradient }}; color: white;">
                    <div class="card-body text-center py-5">
                        <i class="fas {{ $icon }} fa-4x mb-3" style="opacity: 0.9;"></i>
                        <h2 class="font-weight-bold mb-0">{{ $statusText }}</h2>
                        <p class="mb-3" style="opacity: 0.9;">Benötigt: {{ $passMark }}%</p>
                        
                        @if($finalScore !== null)
                            <div class="display-4 font-weight-bold">{{ round($finalScore) }}%</div>
                        @else
                            <div class="h3 font-weight-light mt-2">Wartet auf Bewertung</div>
                        @endif
                    </div>
                </div>

                {{-- BEWERTUNGS FORMULAR (Sticky) --}}
                @can('setEvaluated', $attempt)
                    <div class="card card-outline card-warning shadow-sm sticky-top" style="top: 20px; z-index: 10;">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-gavel mr-2 text-warning"></i> Bewertung abschließen</h3>
                        </div>
                        
                        @if ($attempt->status === 'evaluated')
                            <div class="card-body text-center">
                                <div class="alert alert-light border-0" style="background-color: rgba(255,255,255,0.05);">
                                    <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                    <p class="mb-0 font-weight-bold">Bewertung abgeschlossen</p>
                                    <small class="text-muted">Status kann nicht mehr geändert werden.</small>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('admin.exams.attempts.update', $attempt) }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <p class="small text-muted mb-4">
                                        Bitte überprüfen Sie alle Antworten (insbesondere Freitextfelder) rechts und legen Sie den finalen Score fest.
                                    </p>

                                    <div class="form-group">
                                        <label for="final_score" class="text-uppercase text-xs font-weight-bold text-muted">Finaler Score (%)</label>
                                        <div class="input-group">
                                            <input type="number" name="final_score" id="final_score" 
                                                   class="form-control form-control-lg font-weight-bold text-center" 
                                                   min="0" max="100" 
                                                   value="{{ old('final_score', round($attempt->score ?? 0)) }}" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text font-weight-bold">%</span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            <i class="fas fa-calculator mr-1"></i> Auto-Kalkulation: <strong>{{ round($attempt->score ?? 0) }}%</strong>
                                        </small>
                                    </div>
                                    
                                    <div class="alert alert-info bg-transparent border border-info text-info small mt-4">
                                        <i class="fas fa-info-circle mr-1"></i> Speichern setzt den Status final auf "Bewertet".
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top border-light">
                                    <button type="submit" class="btn btn-success btn-block shadow-sm font-weight-bold">
                                        <i class="fas fa-save mr-2"></i> Ergebnis speichern
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endcan

            </div>

            {{-- RECHTE SPALTE: Fragen & Antworten --}}
            <div class="col-lg-8">
                
                <div class="d-flex align-items-center mb-3">
                    <h4 class="font-weight-bold mb-0"><i class="fas fa-list-ol mr-2 text-primary"></i> Antwortprotokoll</h4>
                    <span class="ml-auto badge badge-secondary">{{ $attempt->exam->questions->count() }} Fragen</span>
                </div>

                @if(optional($attempt->exam)->questions->count() > 0)
                    @foreach($attempt->exam->questions as $index => $question)
                        @php
                            $userAnswersForQuestion = $attempt->answers->where('question_id', $question->id);
                            $isCorrect = false;
                            $cardBorderClass = 'border-left-secondary'; // Standard (Grau)
                            $iconClass = 'fa-pen-alt text-muted';
                            
                            // Logik zur Bestimmung von Korrekt/Falsch
                            if ($question->type === 'single_choice') {
                                $correctOption = optional($question->options)->where('is_correct', true)->first();
                                $userAnswer = $userAnswersForQuestion->first();
                                $isCorrect = $correctOption && $userAnswer && $correctOption->id == $userAnswer->option_id;
                                $cardBorderClass = $isCorrect ? 'border-left-success' : 'border-left-danger';
                                $iconClass = $isCorrect ? 'fa-check text-success' : 'fa-times text-danger';
                            } elseif ($question->type === 'multiple_choice') {
                                $correctOptionIds = optional($question->options)->where('is_correct', true)->pluck('id')->sort()->values();
                                $userOptionIds = $userAnswersForQuestion->pluck('option_id')->sort()->values();
                                // Prüfung: Korrekte Optionen vorhanden UND exakte Übereinstimmung
                                $isCorrect = $correctOptionIds->isNotEmpty() && $correctOptionIds->all() == $userOptionIds->all();
                                $cardBorderClass = $isCorrect ? 'border-left-success' : 'border-left-danger';
                                $iconClass = $isCorrect ? 'fa-check text-success' : 'fa-times text-danger';
                            } elseif ($question->type === 'text_field') {
                                $cardBorderClass = 'border-left-warning'; // Gelb für manuelle Prüfung
                                $iconClass = 'fa-eye text-warning';
                            }
                        @endphp

                        {{-- FRAGE CARD --}}
                        <div class="card card-outline {{ $index > 0 ? 'mt-3' : '' }} mb-0 shadow-sm" 
                             style="border-left: 4px solid !important; {{ $cardBorderClass == 'border-left-success' ? 'border-left-color: #28a745;' : ($cardBorderClass == 'border-left-danger' ? 'border-left-color: #dc3545;' : ($cardBorderClass == 'border-left-warning' ? 'border-left-color: #ffc107;' : 'border-left-color: #6c757d;')) }}">
                            
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="mr-3 mt-1">
                                        <i class="fas {{ $iconClass }} fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="font-weight-bold mb-2">
                                            <span class="text-muted text-sm text-uppercase mr-2">Frage {{ $index + 1 }}</span>
                                            {{ $question->question_text }}
                                        </h5>
                                        
                                        {{-- ANTWORT BEREICH --}}
                                        <div class="mt-3 pl-3 border-left" style="border-color: rgba(255,255,255,0.1) !important;">
                                            
                                            @switch($question->type)
                                                @case('single_choice')
                                                    @php $userOptionId = $userAnswersForQuestion->first()->option_id ?? null; @endphp
                                                    <ul class="list-unstyled mb-0">
                                                        @forelse($question->options as $option) 
                                                            <li class="mb-1">
                                                                @if($option->is_correct)
                                                                    <i class="fas fa-check text-success mr-2" title="Richtige Antwort"></i>
                                                                @elseif($userOptionId == $option->id && !$option->is_correct)
                                                                    <i class="fas fa-times text-danger mr-2" title="Ihre falsche Antwort"></i>
                                                                @else
                                                                    <i class="far fa-circle text-muted mr-2" style="opacity: 0.3;"></i>
                                                                @endif
                                                                
                                                                <span class="{{ $userOptionId == $option->id ? 'font-weight-bold text-white' : 'text-muted' }}">
                                                                    {{ $option->option_text }}
                                                                </span>
                                                            </li>
                                                        @empty
                                                            <li class="text-muted text-xs">Keine Optionen vorhanden.</li>
                                                        @endforelse
                                                    </ul>
                                                    @break

                                                @case('multiple_choice')
                                                    @php $userOptionIds = $userAnswersForQuestion->pluck('option_id'); @endphp
                                                    <ul class="list-unstyled mb-0">
                                                        @forelse($question->options as $option)
                                                            @php
                                                                $isUserSelected = $userOptionIds->contains($option->id);
                                                                $isCorrectOption = $option->is_correct;
                                                            @endphp
                                                            <li class="mb-1">
                                                                @if($isCorrectOption)
                                                                    <i class="fas fa-check text-success mr-2" title="Richtige Option"></i>
                                                                @elseif($isUserSelected && !$isCorrectOption)
                                                                    <i class="fas fa-times text-danger mr-2" title="Fälschlicherweise gewählt"></i>
                                                                @else
                                                                    <i class="far fa-square text-muted mr-2" style="opacity: 0.3;"></i>
                                                                @endif

                                                                <span class="{{ $isUserSelected ? 'font-weight-bold text-white' : 'text-muted' }}">
                                                                    {{ $option->option_text }}
                                                                </span>
                                                                
                                                                @if($isUserSelected)
                                                                    <span class="badge badge-dark ml-2 text-xs">Gewählt</span>
                                                                @endif
                                                            </li>
                                                        @empty
                                                            <li class="text-muted text-xs">Keine Optionen vorhanden.</li>
                                                        @endforelse
                                                    </ul>
                                                    @break

                                                @case('text_field')
                                                    <div class="bg-dark p-3 rounded border border-light">
                                                        <span class="text-xs text-muted text-uppercase d-block mb-1">Gegebene Antwort:</span>
                                                        <p class="mb-0 font-italic text-white">
                                                            "{{ $userAnswersForQuestion->first()->text_answer ?? '-' }}"
                                                        </p>
                                                    </div>
                                                    <div class="mt-2 text-warning small">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i> Muss manuell bewertet werden.
                                                    </div>
                                                    @break

                                            @endswitch
                                        </div>
                                    </div>
                                    {{-- Type Badge oben rechts --}}
                                    <div class="ml-3">
                                        <span class="badge badge-secondary">{{ Str::ucfirst(str_replace('_', ' ', $question->type)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="card">
                        <div class="card-body text-center py-5 text-muted">
                            <i class="fas fa-file-excel fa-3x mb-3"></i>
                            <h5>Keine Fragen in dieser Prüfung gefunden.</h5>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</section>
@endsection