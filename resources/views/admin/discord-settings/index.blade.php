@extends('layouts.app')

@section('title', 'Discord Einstellungen')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        
        {{-- Kopfzeile --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0 text-dark">
                <i class="fab fa-discord" style="color: #5865F2;"></i> Discord Webhooks
            </h1>
            <a href="https://support.discord.com/hc/de/articles/228383668-Webhooks-verwenden" target="_blank" class="btn btn-sm btn-default">
                <i class="fas fa-question-circle"></i> Hilfe
            </a>
        </div>

        {{-- ======================================================= --}}
        {{-- HAUPTFORMULAR: Zum Speichern der Einstellungen --}}
        {{-- ======================================================= --}}
        <form action="{{ route('admin.discord.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- AdminLTE Card Style --}}
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Konfiguration der Ereignisse</h3>
                </div>
                
                <div class="card-body">
                    
                    @foreach($settings as $setting)
                        <div class="setting-item p-3 mb-3 rounded">
                            <div class="row align-items-center">
                                
                                {{-- Linke Spalte: Infos & Switch --}}
                                <div class="col-md-5">
                                    <div class="custom-control custom-switch">
                                        {{-- Hidden Input sorgt dafür, dass '0' gesendet wird, wenn unchecked --}}
                                        <input type="hidden" name="settings[{{ $setting->id }}][active]" value="0">
                                        
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="switch_{{ $setting->id }}" 
                                               name="settings[{{ $setting->id }}][active]" 
                                               value="1" 
                                               {{ $setting->active ? 'checked' : '' }}
                                               onchange="toggleInput({{ $setting->id }})">
                                        
                                        <label class="custom-control-label font-weight-bold" for="switch_{{ $setting->id }}">
                                            {{ $setting->friendly_name }}
                                        </label>
                                    </div>
                                    
                                    <small class="d-block mt-1 ml-5 text-muted">
                                        {{ $setting->description }}
                                    </small>
                                    
                                    {{-- Kleiner technischer Key zur Orientierung --}}
                                    <div class="ml-5 mt-1 badge badge-secondary" style="font-size: 0.65em; opacity: 0.7;">
                                        Key: {{ $setting->action }}
                                    </div>
                                </div>

                                {{-- Rechte Spalte: URL Input & Test Button --}}
                                <div class="col-md-7 mt-2 mt-md-0">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                                        </div>
                                        
                                        <input type="url" 
                                               class="form-control" 
                                               id="input_{{ $setting->id }}"
                                               name="settings[{{ $setting->id }}][webhook_url]" 
                                               value="{{ old("settings.{$setting->id}.webhook_url", $setting->webhook_url) }}"
                                               placeholder="https://discord.com/api/webhooks/..."
                                               {{ !$setting->active ? 'disabled' : '' }}>

                                        <span class="input-group-append">
                                            {{-- Der Test-Button wird nur angezeigt, wenn eine URL existiert --}}
                                            @if(!empty($setting->webhook_url))
                                                <button type="button" 
                                                        class="btn btn-default" 
                                                        onclick="submitTest({{ $setting->id }})"
                                                        title="Testnachricht senden"
                                                        data-toggle="tooltip">
                                                    <i class="fas fa-paper-plane text-primary"></i> Test
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-default" disabled>
                                                    <i class="fas fa-paper-plane text-muted"></i>
                                                </button>
                                            @endif
                                        </span>
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

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save mr-1"></i> Einstellungen speichern
                    </button>
                </div>
            </div>
        </form>
        {{-- ENDE HAUPTFORMULAR --}}

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

@endsection

{{-- ======================================================= --}}
{{-- JAVASCRIPT LOGIK (In den Stack pushen) --}}
{{-- ======================================================= --}}
@push('scripts')
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
            // Optional: Zeige Lade-Indikator via SweetAlert (da du es im Layout hast)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Sende Test...',
                    text: 'Bitte warten',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            form.submit();
        } else {
            // Fallback Alert
            alert('Bitte speichere die URL zuerst ab, bevor du testen kannst.');
        }
    }

    // Tooltips initialisieren (Standard AdminLTE/Bootstrap)
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
@endpush