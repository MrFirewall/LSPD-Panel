@extends('layouts.app')

@section('title', 'Prüfung ablegen: ' . $attempt->exam->title)

@section('content')

{{-- 1. HERO HEADER (Ohne Overlap) --}}
<div class="content-header" style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: white; padding: 2rem 1.5rem; margin-bottom: 1.5rem; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="text-uppercase font-weight-bold mb-1" style="opacity: 0.8; letter-spacing: 1px;">Prüfung</h5>
                <h1 class="display-4 font-weight-bold mb-0">{{ $attempt->exam->title }}</h1>
                <p class="lead mb-0 mt-2" style="opacity: 0.9;">
                    <i class="fas fa-info-circle mr-1"></i> 
                    @if($attempt->exam->description)
                        {{ $attempt->exam->description }}
                    @else
                        Bitte beantworten Sie alle Fragen gewissenhaft.
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-right d-none d-md-block">
                <i class="fas fa-pencil-alt fa-4x" style="opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<div class="content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <form action="{{ route('exams.submit', $attempt) }}" method="POST" id="exam-form">
                    @csrf
                    
                    {{-- Karte ohne negativen Margin --}}
                    <div class="card card-outline card-primary shadow-lg border-0">
                        <div class="card-header bg-transparent border-bottom-0 pt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title font-weight-bold text-white mb-0">
                                    <i class="fas fa-list-ol mr-2 text-primary"></i> Fragebogen
                                </h3>
                                <span class="badge badge-primary px-3 py-2" style="font-size: 0.9rem;">
                                    {{ $attempt->exam->questions->count() }} Fragen
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            
                            @if($attempt->exam->questions->isEmpty())
                                <div class="text-center py-5">
                                    <i class="fas fa-exclamation-circle text-warning fa-3x mb-3"></i>
                                    <h5>Diese Prüfung enthält noch keine Fragen.</h5>
                                    <p class="text-muted">Bitte wenden Sie sich an die Ausbildungsleitung.</p>
                                    <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-3">Zurück zum Dashboard</a>
                                </div>
                            @else
                                @foreach($attempt->exam->questions as $index => $question)
                                    
                                    {{-- Frage Container --}}
                                    <div class="question-block mb-4 p-4 rounded" style="background-color: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                                        
                                        <div class="d-flex align-items-start mb-3">
                                            <span class="badge badge-primary mr-3 mt-1" style="font-size: 1rem;">{{ $index + 1 }}</span>
                                            <div>
                                                <h5 class="font-weight-bold mb-1">{{ $question->question_text }}</h5>
                                                <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                                    {{ Str::ucfirst(str_replace('_', ' ', $question->type)) }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="ml-1 pl-4 border-left border-secondary" style="border-width: 2px !important; border-color: rgba(255,255,255,0.1) !important;">
                                            @switch($question->type)

                                                @case('single_choice')
                                                    @forelse($question->options as $option)
                                                        <div class="custom-control custom-radio mb-2">
                                                            <input type="radio" 
                                                                   id="option_{{ $option->id }}" 
                                                                   name="answers[{{ $question->id }}]" 
                                                                   value="{{ $option->id }}" 
                                                                   class="custom-control-input" 
                                                                   required>
                                                            <label class="custom-control-label font-weight-normal text-white" for="option_{{ $option->id }}" style="cursor: pointer; opacity: 0.9;">
                                                                {{ $option->option_text }}
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <p class="text-danger small"><i class="fas fa-exclamation-triangle"></i> Fehler: Keine Optionen vorhanden.</p>
                                                    @endforelse
                                                    @break

                                                @case('multiple_choice')
                                                    <div class="alert alert-light bg-transparent border border-secondary text-muted p-2 mb-3 small">
                                                        <i class="fas fa-check-double mr-1"></i> Mehrere Antworten möglich
                                                    </div>
                                                    @forelse($question->options as $option)
                                                        <div class="custom-control custom-checkbox mb-2">
                                                            <input type="checkbox" 
                                                                   id="option_{{ $option->id }}" 
                                                                   name="answers[{{ $question->id }}][]" 
                                                                   value="{{ $option->id }}" 
                                                                   class="custom-control-input">
                                                            <label class="custom-control-label font-weight-normal text-white" for="option_{{ $option->id }}" style="cursor: pointer; opacity: 0.9;">
                                                                {{ $option->option_text }}
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <p class="text-danger small"><i class="fas fa-exclamation-triangle"></i> Fehler: Keine Optionen vorhanden.</p>
                                                    @endforelse
                                                    @break

                                                @case('text_field')
                                                    <div class="form-group mb-0">
                                                        <textarea name="answers[{{ $question->id }}]" 
                                                                  class="form-control" 
                                                                  rows="4" 
                                                                  placeholder="Geben Sie hier Ihre Antwort ein..." 
                                                                  required 
                                                                  style="background-color: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);"></textarea>
                                                    </div>
                                                    @break

                                                @default
                                                    <p class="text-danger">Unbekannter Fragetyp.</p>
                                            @endswitch
                                        </div>
                                    </div>

                                @endforeach
                            @endif

                        </div>

                        {{-- Footer Actions --}}
                        @if($attempt->exam->questions->isNotEmpty())
                            <div class="card-footer bg-transparent text-center py-4 border-top border-secondary">
                                <button type="submit" 
                                        class="btn btn-success btn-lg px-5 rounded-pill font-weight-bold shadow-sm" 
                                        onclick="return confirm('Sind Sie sicher, dass Sie die Prüfung jetzt einreichen möchten? Änderungen sind danach nicht mehr möglich.');">
                                    <i class="fas fa-paper-plane mr-2"></i> Prüfung einreichen
                                </button>
                                <div class="mt-3">
                                    <small class="text-muted">Überprüfen Sie Ihre Antworten sorgfältig, bevor Sie absenden.</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection