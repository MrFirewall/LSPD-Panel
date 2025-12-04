@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- 1. Hero Section -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold mb-0">
                    <i class="fas fa-layer-group mr-2" style="opacity: 0.6;"></i> Dashboard
                </h1>
                <p class="lead mb-0 mt-2" style="opacity: 0.9;">
                    Willkommen zurück, <strong>{{ Auth::user()->name }}</strong>.
                </p>
                <p class="mb-0" style="opacity: 0.9;">
                    Ihre aktuelle Position: <strong>{{ Auth::user()->rankRelation->label ?? 'Mitarbeiter' }}</strong>.
                </p>
            </div>
            <div class="col-md-4 text-right d-none d-md-block">
                {{-- Live-Uhrzeit (wird via JS aktualisiert) --}}
                <h4 class="mb-0 font-weight-bold">
                    <span id="live-clock">{{ \Carbon\Carbon::now()->format('H:i') }}</span> <small>Uhr</small>
                </h4>
                <span class="text-white-50" id="live-date">{{ \Carbon\Carbon::now()->format('d.m.Y') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- 2. Main Content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Stats Row -->
        <div class="row">
            <!-- Eigene Berichte -->
            <div class="col-lg-3 col-6">
                <div class="card stat-card" style="background: linear-gradient(45deg, #4b6cb7 0%, #182848 100%); border: none;">
                    <div class="card-body">
                        <div style="position: absolute; right: -10px; top: -10px; font-size: 5rem; opacity: 0.1; transform: rotate(15deg);">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="font-weight-bold">{{ $myReportCount ?? 0 }}</h3>
                        <p class="mb-0 text-uppercase font-weight-bold small" style="letter-spacing: 1px;">Meine Berichte</p>
                        <div class="mt-3">
                            <a href="{{ route('reports.index') }}" class="text-white small" style="text-decoration: underline;">
                                Alle ansehen <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Offene Akten / Gesamt -->
            <div class="col-lg-3 col-6">
                <div class="card stat-card" style="background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%); border: none;">
                    <div class="card-body">
                         <div style="position: absolute; right: -10px; top: -10px; font-size: 5rem; opacity: 0.1; transform: rotate(15deg);">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3 class="font-weight-bold">{{ $openCasesCount ?? 0 }}</h3>
                        <p class="mb-0 text-uppercase font-weight-bold small" style="letter-spacing: 1px;">Akten im System</p>
                        <div class="mt-3">
                            <a href="{{ route('reports.index') }}" class="text-white small" style="text-decoration: underline;">
                                Zum Archiv <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bußgelder -->
            <div class="col-lg-3 col-6">
                 <div class="card stat-card" style="background: linear-gradient(45deg, #FF416C 0%, #FF4B2B 100%); border: none;">
                    <div class="card-body">
                         <div style="position: absolute; right: -10px; top: -10px; font-size: 5rem; opacity: 0.1; transform: rotate(15deg);">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                        <h3 class="font-weight-bold">{{ number_format($dailyFinesAmount ?? 0, 0, ',', '.') }} €</h3>
                        <p class="mb-0 text-uppercase font-weight-bold small" style="letter-spacing: 1px;">Bußgelder (Heute)</p>
                        <div class="mt-3">
                            <small class="text-white-50">Summe aller heutigen Einträge</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gesuchte Personen -->
            <div class="col-lg-3 col-6">
                 <div class="card stat-card" style="background: linear-gradient(45deg, #8E2DE2 0%, #4A00E0 100%); border: none;">
                    <div class="card-body">
                         <div style="position: absolute; right: -10px; top: -10px; font-size: 5rem; opacity: 0.1; transform: rotate(15deg);">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <h3 class="font-weight-bold">{{ $wantedCount ?? 0 }}</h3>
                        <p class="mb-0 text-uppercase font-weight-bold small" style="letter-spacing: 1px;">Gesuchte Personen</p>
                         <div class="mt-3">
                            {{-- Link zur Bürgerliste, da Fahndung dort meist gefiltert wird --}}
                            <a href="{{ route('citizens.index') }}" class="text-white small" style="text-decoration: underline;">
                                Bürgerdatenbank <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Linke Spalte: Ankündigungen & Letzte Berichte -->
            <div class="col-lg-8">
                
                <!-- Ankündigungen -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-bullhorn mr-2 text-primary"></i> Ankündigungen
                            @if(count($announcements) > 0)
                                <span class="badge badge-primary ml-2" style="font-size: 0.9rem; vertical-align: middle;">{{ count($announcements) }} Neu</span>
                            @endif
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($announcements as $announcement)
                                <div class="list-group-item bg-transparent">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 font-weight-bold text-white">{{ $announcement->title }}</h6>
                                        <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 text-white-50 small">{{ Str::limit($announcement->content, 120) }}</p>
                                    <small class="text-muted"><i class="fas fa-user-circle mr-1"></i> {{ $announcement->user->name }}</small>
                                </div>
                            @empty
                                <div class="list-group-item bg-transparent text-center py-5">
                                    <i class="far fa-newspaper fa-3x mb-3 text-muted" style="opacity: 0.3"></i>
                                    <p class="mb-0 text-muted">Aktuell gibt es keine Ankündigungen.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                <!-- Letzte Berichte -->
                 <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-file-contract mr-2 text-info"></i> Letzte Berichte (Persönlich)</h3>
                    </div>
                    <div class="card-body p-0 table-responsive">
                         <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Titel</th>
                                    <th>Datum</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lastReports as $report)
                                    <tr>
                                        <td><span class="text-muted">#{{ $report->id }}</span></td>
                                        <td class="font-weight-bold">{{ $report->title }}</td>
                                        <td class="text-muted small">{{ $report->created_at->format('d.m.Y H:i') }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('reports.show', $report) }}" class="btn btn-xs btn-outline-light rounded-pill px-3">
                                                Öffnen
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Sie haben noch keine Berichte verfasst.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Rechte Spalte: Dienst & Personal -->
            <div class="col-lg-4">
                
                <!-- Dienst Status -->
                <div class="card text-center overflow-hidden">
                    <div class="card-body py-5">
                        <div class="mb-3">
                             <div style="width: 80px; height: 80px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(255,255,255,0.05);">
                                 <i class="fas fa-user-shield fa-2x {{ Auth::user()->on_duty ? 'text-success' : 'text-danger' }}"></i>
                             </div>
                        </div>
                        <h3 class="font-weight-bold mb-1">Dienststatus</h3>
                        <p class="text-muted mb-4">Aktueller Status im System</p>
                        
                        <h2 id="duty-status-text" class="mb-4">
                            @if(Auth::user()->on_duty)
                                <span class="text-success" style="text-shadow: 0 0 15px rgba(40, 167, 69, 0.4);">IM DIENST</span>
                            @else
                                <span class="text-danger" style="text-shadow: 0 0 15px rgba(220, 53, 69, 0.4);">AUSSER DIENST</span>
                            @endif
                        </h2>

                        <button id="toggle-duty-btn" class="btn btn-block btn-lg {{ Auth::user()->on_duty ? 'btn-outline-danger' : 'btn-outline-success' }} rounded-pill font-weight-bold">
                            {{ Auth::user()->on_duty ? 'Dienst beenden' : 'Dienst antreten' }}
                        </button>
                        
                        <div class="mt-4 pt-3 border-top border-secondary">
                            <span class="text-muted small">Ihre Dienstzeit diese Woche:</span>
                            <h5 class="font-weight-bold text-white mt-1">{{ $weeklyHours ?? '00:00:00' }}</h5>
                        </div>
                    </div>
                </div>

                <!-- Personal Übersicht -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Personal Übersicht</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                <span><i class="fas fa-users mr-2 text-muted"></i> Gesamtpersonal</span>
                                <span class="badge badge-light badge-pill text-dark font-weight-bold">{{ $totalUsers ?? 0 }}</span>
                            </li>
                            @foreach($rankDistribution as $rankName => $count)
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent" style="border-top: 1px solid rgba(255,255,255,0.05);">
                                    <span class="text-muted">{{ $rankName }}</span>
                                    <span class="badge badge-dark border border-secondary">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // --- LIVE UHRZEIT FUNKTION ---
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeString = `${hours}:${minutes}`;
        
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        const dateString = `${day}.${month}.${year}`;

        $('#live-clock').text(timeString);
        $('#live-date').text(dateString);
    }
    updateClock(); 
    setInterval(updateClock, 1000);

    // --- DIENST TOGGLE ---
    $('#toggle-duty-btn').on('click', function() {
        var button = $(this);
        button.prop('disabled', true); 
        var originalText = button.text();
        button.html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("duty.toggle") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if(response.success) {
                    var statusTextContainer = $('#duty-status-text');
                    if(response.new_status) {
                        statusTextContainer.html('<span class="text-success" style="text-shadow: 0 0 15px rgba(40, 167, 69, 0.4);">IM DIENST</span>');
                        button.text('Dienst beenden').removeClass('btn-outline-success').addClass('btn-outline-danger');
                        $('.fa-user-shield').removeClass('text-danger').addClass('text-success');
                    } else {
                        statusTextContainer.html('<span class="text-danger" style="text-shadow: 0 0 15px rgba(220, 53, 69, 0.4);">AUSSER DIENST</span>');
                        button.text('Dienst antreten').removeClass('btn-outline-danger').addClass('btn-outline-success');
                        $('.fa-user-shield').removeClass('text-success').addClass('text-danger');
                    }
                } else {
                    button.text(originalText);
                    alert('Fehler beim Statuswechsel.');
                }
            },
            error: function() {
                button.text(originalText);
                alert('Serverfehler.');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush