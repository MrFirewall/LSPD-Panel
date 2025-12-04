@extends('layouts.app')

@section('title', 'Prüfung eingereicht')

@section('content')

{{-- 1. HERO HEADER --}}
<div class="content-header" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 3rem 1.5rem; margin-bottom: 2rem; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center;">
    <div class="container">
        <h1 class="display-3 font-weight-bold mb-0"><i class="fas fa-check-circle mr-3"></i>Gesendet!</h1>
        <p class="lead mb-0 mt-3" style="opacity: 0.9; font-size: 1.25rem;">
            Ihre Antworten wurden sicher an das System übermittelt.
        </p>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<div class="content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card card-outline card-success shadow-lg border-0" style="margin-top: -4rem;">
                    <div class="card-body text-center py-5 px-4">
                        
                        <div class="mb-4">
                            <i class="fas fa-clipboard-check text-success" style="font-size: 5rem; opacity: 0.8;"></i>
                        </div>

                        <h2 class="font-weight-bold mb-3">Vielen Dank für Ihre Teilnahme</h2>
                        {{-- TEXT ANGEPASST: Generischer formuliert --}}
                        <p class="text-muted" style="font-size: 1.1rem;">
                            Der erste Schritt ist geschafft. Ihre Prüfung wurde erfolgreich eingereicht. 
                            Je nach Prüfungsart erfolgt die Auswertung nun automatisch oder wird durch einen Ausbilder überprüft.
                        </p>

                        <hr class="my-5" style="border-color: rgba(255,255,255,0.1); width: 50%;">

                        <h4 class="font-weight-bold mb-4">Wie geht es weiter?</h4>

                        {{-- Timeline Steps --}}
                        <div class="row justify-content-center text-left">
                            <div class="col-md-10">
                                <div class="timeline timeline-inverse">
                                    
                                    {{-- Step 1 --}}
                                    <div>
                                        <i class="fas fa-envelope bg-primary"></i>
                                        <div class="timeline-item border-0 shadow-sm" style="background-color: rgba(255,255,255,0.02);">
                                            <h3 class="timeline-header font-weight-bold text-primary">Eingang bestätigt</h3>
                                            <div class="timeline-body text-muted">
                                                Ihre Prüfung wurde im System gespeichert.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Step 2: ANGEPASST für generische Auswertung --}}
                                    <div>
                                        <i class="fas fa-search bg-warning"></i>
                                        <div class="timeline-item border-0 shadow-sm" style="background-color: rgba(255,255,255,0.02);">
                                            <h3 class="timeline-header font-weight-bold text-warning">Auswertung</h3>
                                            <div class="timeline-body text-muted">
                                                Ihre Antworten werden überprüft. Bei Freitextfragen erfolgt dies manuell durch einen Ausbilder.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 3 --}}
                                    <div>
                                        <i class="fas fa-flag-checkered bg-success"></i>
                                        <div class="timeline-item border-0 shadow-sm" style="background-color: rgba(255,255,255,0.02);">
                                            <h3 class="timeline-header font-weight-bold text-success">Ergebnis & Modulstatus</h3>
                                            <div class="timeline-body text-muted">
                                                Sobald die Bewertung abgeschlossen ist, erscheint das Ergebnis in Ihrem Profil und Sie erhalten eine Benachrichtigung.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <i class="far fa-clock bg-gray"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <a href="{{ route('dashboard') }}" class="btn btn-lg btn-outline-success rounded-pill px-5 shadow-sm font-weight-bold">
                                <i class="fas fa-tachometer-alt mr-2"></i> Zurück zum Dashboard
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection