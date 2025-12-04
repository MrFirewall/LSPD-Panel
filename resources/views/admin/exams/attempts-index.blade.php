@extends('layouts.app')
@section('title', 'Prüfungsversuche verwalten')

@section('content')

{{-- 1. HERO HEADER --}}
<div class="content-header" style="background: linear-gradient(135deg, #3a1c71 0%, #d76d77 100%, #ffaf7b 100%); color: white; padding: 2rem 1.5rem; margin-bottom: 1.5rem; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h5 class="text-uppercase font-weight-bold mb-1" style="opacity: 0.8; letter-spacing: 1px;">Verwaltung</h5>
                <h1 class="display-4 font-weight-bold mb-0"><i class="fas fa-tasks mr-3"></i>Prüfungsversuche</h1>
            </div>
            <div class="col-sm-6 text-right">
                <span class="badge badge-light badge-pill px-3 py-2 text-dark font-weight-bold shadow-sm">
                    {{ $attempts->total() }} Einträge gesamt
                </span>
            </div>
        </div>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                {{-- Alerts --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible shadow-sm border-0" style="background-color: rgba(40, 167, 69, 0.9); color: white;">
                        <button type="button" class="close text-white" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Erfolg!</h5>
                        {{ session('success') }}
                        @if (session('secure_url'))
                            <div class="mt-3 p-2 rounded" style="background-color: rgba(0,0,0,0.2);">
                                <small class="d-block mb-1 text-uppercase font-weight-bold" style="opacity: 0.8;">Manueller Link:</small>
                                <code id="secure-link" class="text-white user-select-all">{{ session('secure_url') }}</code>
                                {{-- Kopier-Button mit 'this' Parameter für Feedback --}}
                                <button type="button" class="btn btn-xs btn-light text-success ml-2 font-weight-bold rounded-pill px-2" onclick="copyLink(this)">
                                    <i class="fas fa-copy mr-1"></i> Kopieren
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible shadow-sm border-0" style="background-color: rgba(220, 53, 69, 0.9); color: white;">
                        <button type="button" class="close text-white" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Fehler!</h5>
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Hauptkarte --}}
                <div class="card card-outline card-primary shadow-lg border-0">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-list-alt mr-2 text-primary"></i> Übersicht aller Versuche
                        </h3>
                        <div class="card-tools">
                            {{-- Hier könnte eine Suche hin --}}
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap table-striped mb-0">
                                <thead style="background-color: rgba(0,0,0,0.1);">
                                    <tr>
                                        <th class="pl-4">ID</th>
                                        <th>Prüfling</th>
                                        <th>Ausbilder</th>
                                        <th>Prüfung</th>
                                        <th>Status</th>
                                        <th class="text-center">Score</th>
                                        <th>Gestartet</th>
                                        <th>Abgeschlossen</th>
                                        <th class="text-right pr-4">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($attempts as $attempt)
                                        <tr class="align-middle">
                                            <td class="pl-4 text-muted">#{{ $attempt->id }}</td>
                                            <td class="font-weight-bold">
                                                @if($attempt->user)
                                                    <a href="{{ route('admin.users.show', $attempt->user) }}" class="text-reset">
                                                        <i class="fas fa-user-graduate mr-1 text-info opacity-50"></i> {{ $attempt->user->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted font-italic">Unbekannt</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attempt->evaluator)
                                                    <a href="{{ route('admin.users.show', $attempt->evaluator) }}" class="text-reset">
                                                        <i class="fas fa-user-tie mr-1 text-secondary opacity-50"></i> {{ $attempt->evaluator->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted text-xs">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-white">{{ $attempt->exam->title ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if ($attempt->status === 'in_progress')
                                                    <span class="badge badge-info px-2 py-1"><i class="fas fa-spinner fa-spin mr-1"></i> In Arbeit</span>
                                                @elseif ($attempt->status === 'submitted')
                                                    <span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i> Eingereicht</span>
                                                @elseif ($attempt->status === 'evaluated')
                                                    @php $passed = optional($attempt->exam)->pass_mark !== null && $attempt->score >= $attempt->exam->pass_mark; @endphp
                                                    @if($passed)
                                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Bestanden</span>
                                                    @else
                                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i> Durchgefallen</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center font-weight-bold">
                                                @if($attempt->score !== null)
                                                    {{ round($attempt->score) }}%
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-xs text-muted">{{ $attempt->started_at ? $attempt->started_at->format('d.m.Y H:i') : '-' }}</td>
                                            <td class="text-xs text-muted">{{ $attempt->completed_at ? $attempt->completed_at->format('d.m.Y H:i') : '-' }}</td>
                                            <td class="text-right pr-4">
                                                <div class="btn-group btn-group-sm">
                                                    {{-- 1. Ansehen --}}
                                                    {{-- d-flex justify-content-center align-items-center sorgt für mittige Icons --}}
                                                    <a href="{{ route('admin.exams.attempts.show', $attempt) }}" class="btn btn-default d-flex justify-content-center align-items-center" style="width: 32px; height: 30px;" title="Details / Bewerten">
                                                        <i class="fas fa-eye text-primary"></i>
                                                    </a>

                                                    {{-- 2. Link senden (Nur wenn in Progress/Submitted) --}}
                                                    @can('sendLink', $attempt)
                                                        <form action="{{ route('admin.exams.attempts.sendLink', $attempt) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-default d-flex justify-content-center align-items-center" style="width: 32px; height: 30px;" title="Link kopieren/senden">
                                                                <i class="fas fa-link text-secondary"></i>
                                                            </button>
                                                        </form>
                                                    @endcan

                                                    {{-- 3. Reset --}}
                                                    @can('resetAttempt', $attempt)
                                                        <form action="{{ route('admin.exams.attempts.reset', $attempt) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-default d-flex justify-content-center align-items-center" style="width: 32px; height: 30px;" title="Zurücksetzen" onclick="return confirm('ACHTUNG: Alle Antworten werden gelöscht. Fortfahren?');">
                                                                <i class="fas fa-undo text-warning"></i>
                                                            </button>
                                                        </form>
                                                    @endcan

                                                    {{-- 4. Löschen --}}
                                                    @can('delete', $attempt)
                                                        <form action="{{ route('admin.exams.attempts.destroy', $attempt) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-default d-flex justify-content-center align-items-center" style="width: 32px; height: 30px;" title="Löschen" onclick="return confirm('ACHTUNG: Endgültig löschen?');">
                                                                <i class="fas fa-trash-alt text-danger"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i><br>
                                                Keine Prüfungsversuche gefunden.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if ($attempts->hasPages())
                        <div class="card-footer bg-transparent border-top border-light">
                            {{ $attempts->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
// Funktion zum Kopieren des Links mit visuellem Feedback
function copyLink(btnElement) {
    const linkElement = document.getElementById('secure-link');
    if (!linkElement) return;

    const textToCopy = linkElement.textContent.trim();
    // Originalen Button-Inhalt speichern (für Reset)
    const originalHtml = btnElement.innerHTML;

    // Hilfsfunktion für Erfolgs-Feedback
    const showSuccess = () => {
        btnElement.innerHTML = '<i class="fas fa-check mr-1"></i> Kopiert!';
        btnElement.classList.remove('btn-light', 'text-success');
        btnElement.classList.add('btn-success', 'text-white');
        
        // Nach 2 Sekunden zurücksetzen
        setTimeout(() => {
            btnElement.innerHTML = originalHtml;
            btnElement.classList.remove('btn-success', 'text-white');
            btnElement.classList.add('btn-light', 'text-success');
        }, 2000);
        
        // Toastr Nachricht (optional, falls toastr vorhanden)
        if(typeof toastr !== 'undefined') toastr.success('Link kopiert!');
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textToCopy).then(showSuccess).catch(err => {
            fallbackCopyLink(textToCopy, btnElement, originalHtml);
        });
    } else {
        fallbackCopyLink(textToCopy, btnElement, originalHtml);
    }
}

// Fallback für ältere Browser
function fallbackCopyLink(text, btnElement, originalHtml) {
    const tempInput = document.createElement('textarea');
    tempInput.value = text;
    tempInput.style.position = 'absolute';
    tempInput.style.left = '-9999px';
    document.body.appendChild(tempInput);
    tempInput.select();
    try {
        document.execCommand('copy');
        // Auch hier visuelles Feedback geben
        btnElement.innerHTML = '<i class="fas fa-check mr-1"></i> Kopiert!';
        btnElement.classList.remove('btn-light', 'text-success');
        btnElement.classList.add('btn-success', 'text-white');
        setTimeout(() => {
            btnElement.innerHTML = originalHtml;
            btnElement.classList.remove('btn-success', 'text-white');
            btnElement.classList.add('btn-light', 'text-success');
        }, 2000);
        if(typeof toastr !== 'undefined') toastr.success('Link kopiert!');
    } catch (err) {
        if(typeof toastr !== 'undefined') toastr.error('Kopieren fehlgeschlagen.');
    }
    document.body.removeChild(tempInput);
}

// Fehler-Handling für Modal Wiedereröffnung
@if ($errors->any() && session('error_attempt_id'))
    $(document).ready(function() {
        $('#evaluateModal{{ session('error_attempt_id') }}').modal('show');
    });
@endif
</script>
@endpush