@extends('layouts.app')

@section('title', 'Discord Einstellungen')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            {{-- Kopfzeile --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0 text-gray-800">
                    <i class="fab fa-discord" style="color: #5865F2;"></i> Discord Webhooks
                </h2>
                <a href="https://support.discord.com/hc/de/articles/228383668-Webhooks-verwenden" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-question-circle"></i> Hilfe
                </a>
            </div>

            {{-- Erfolgsmeldungen --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Fehlermeldungen --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- ======================================================= --}}
            {{-- HAUPTFORMULAR: Zum Speichern der Einstellungen --}}
            {{-- ======================================================= --}}
            <form action="{{ route('admin.discord.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Konfiguration der Ereignisse</h6>
                    </div>
                    
                    <div class="card-body">
                        
                        @foreach($settings as $setting)
                            <div class="setting-item p-3 mb-3 border rounded bg-light">
                                <div class="row align-items-center">
                                    
                                    {{-- Linke Spalte: Infos & Switch --}}
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            {{-- Hidden Input sorgt dafür, dass '0' gesendet wird, wenn unchecked --}}
                                            <input type="hidden" name="settings[{{ $setting->id }}][active]" value="0">
                                            
                                            <input class="form-check-input" type="checkbox" 
                                                   id="switch_{{ $setting->id }}" 
                                                   name="settings[{{ $setting->id }}][active]" 
                                                   value="1" 
                                                   {{ $setting->active ? 'checked' : '' }}
                                                   onchange="toggleInput({{ $setting->id }})">
                                            
                                            <label class="form-check-label fw-bold" for="switch_{{ $setting->id }}">
                                                {{ $setting->friendly_name }}
                                            </label>
                                        </div>
                                        
                                        <small class="text-muted d-block mt-1 ms-4">
                                            {{ $setting->description }}
                                        </small>
                                        
                                        {{-- Kleiner technischer Key zur Orientierung --}}
                                        <div class="ms-4 mt-1 badge bg-secondary text-white" style="font-size: 0.65em; opacity: 0.7;">
                                            Key: {{ $setting->action }}
                                        </div>
                                    </div>

                                    {{-- Rechte Spalte: URL Input & Test Button --}}
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted">URL</span>
                                            
                                            <input type="url" 
                                                   class="form-control" 
                                                   id="input_{{ $setting->id }}"
                                                   name="settings[{{ $setting->id }}][webhook_url]" 
                                                   value="{{ old("settings.{$setting->id}.webhook_url", $setting->webhook_url) }}"
                                                   placeholder="https://discord.com/api/webhooks/..."
                                                   {{ !$setting->active ? 'disabled' : '' }}>

                                            {{-- Der Test-Button wird nur angezeigt, wenn eine URL existiert --}}
                                            @if(!empty($setting->webhook_url))
                                                <button type="button" 
                                                        class="btn btn-outline-secondary" 
                                                        onclick="submitTest({{ $setting->id }})"
                                                        title="Testnachricht senden">
                                                    <i class="fas fa-paper-plane"></i> Test
                                                </button>
                                            @endif
                                        </div>

                                        {{-- Validierungsfehler unter dem Feld anzeigen --}}
                                        @error("settings.{$setting->id}.webhook_url")
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        @endforeach

                    </div>

                    <div class="card-footer bg-white text-end py-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save"></i> Einstellungen speichern
                        </button>
                    </div>
                </div>
            </form>
            {{-- ENDE HAUPTFORMULAR --}}

        </div>
    </div>
</div>

{{-- ======================================================= --}}
{{-- UNSICHTBARE FORMULARE FÜR DEN TEST-BUTTON --}}
{{-- ======================================================= --}}
@foreach($settings as $setting)
    @if(!empty($setting->webhook_url))
        <form id="test-form-{{ $setting->id }}" 
              action="{{ route('admin.discord.test', $setting->id) }}" 
              method="POST" 
              style="display: none;">
            @csrf
        </form>
    @endif
@endforeach

{{-- ======================================================= --}}
{{-- JAVASCRIPT LOGIK --}}
{{-- ======================================================= --}}
<script>
    /**
     * Aktiviert/Deaktiviert das URL-Feld basierend auf dem Switch
     */
    function toggleInput(id) {
        const checkbox = document.getElementById('switch_' + id);
        const input = document.getElementById('input_' + id);
        
        if (checkbox && input) {
            input.disabled = !checkbox.checked;
        }
    }

    /**
     * Sendet das versteckte Test-Formular ab
     */
    function submitTest(id) {
        const form = document.getElementById('test-form-' + id);
        
        if (form) {
            // Optional: Button Feedback geben (z.B. Spinner), hier einfach absenden
            form.submit();
        } else {
            alert('Bitte speichere die URL zuerst ab, bevor du testen kannst.');
        }
    }
</script>
@endsection