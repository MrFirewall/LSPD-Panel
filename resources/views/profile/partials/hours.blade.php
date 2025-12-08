{{-- Hilfsfunktion (falls nicht global definiert) --}}
@php
if (!function_exists('formatSeconds')) {
    function formatSeconds($seconds) {
        // Sekunden können als Float reinkommen (obwohl hier unwahrscheinlich), 
        // daher ist floor() wichtig.
        $seconds = (int) round($seconds);         
        if ($seconds < 1) return '00:00 h'; // Hinzufügen des 'h' für 0 Sekunden        
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);        
        // Führende Nullen und das 'h' am Ende hinzufügen
        return sprintf('%02d:%02d h', $h, $m); 
    }
}

// Ränge dynamisch aus der Datenbank laden
// Wir holen 'label' als Wert und 'name' (slug) als Key
// Optional: Caching für bessere Performance (z.B. 60 Minuten), falls viele User gleichzeitig zugreifen
$rankNames = \Illuminate\Support\Facades\Cache::remember('ranks_list', 60, function () {
    return \App\Models\Rank::pluck('label', 'name')->toArray();
});
@endphp

<div class="row">
    {{-- 1. Wochenstunden Tabelle --}}
    <div class="col-md-4">
        <div class="card card-outline card-info h-100">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="far fa-calendar-alt mr-2"></i> Wochenstunden</h3>
            </div>
            <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th class="pl-3">KW</th>
                            <th>Stunden</th>
                            <th>Leitstelle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($weeklyHours as $kw => $hours)
                            <tr>
                                <td class="pl-3 text-muted">
                                    {{ substr($kw, 5) }} {{-- Zeigt nur KWXX --}}
                                    <small class="text-xs text-info ml-1">({{ substr($kw, 0, 4) }})</small> {{-- Fügt das Jahr (2025) hinzu --}}
                                </td>
                                <td class="font-weight-bold">{{ formatSeconds($hours['normal_seconds']) }}</td>
                                <td class="text-muted">{{ formatSeconds($hours['leitstelle_seconds']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Keine Daten vorhanden.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 2. Gesamtzeit (Aktiver Zeitraum) --}}
    <div class="col-md-4">
        <div class="card card-outline card-success h-100 text-center">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <div class="mb-2">
                    <i class="fas fa-stopwatch fa-3x text-success opacity-50"></i>
                </div>
                <h5 class="text-muted text-uppercase small font-weight-bold ls-1">Dienstzeit Gesamt</h5>
                <h2 class="font-weight-bold text-white display-4 mb-0">
                    {{ formatSeconds($hourData['active_total_seconds']) }}
                </h2>
                <small class="text-muted">(Aktiver Zeitraum)</small>
            </div>
        </div>
    </div>

    {{-- 3. Archiv nach Rang --}}
    <div class="col-md-4">
        <div class="card card-outline card-secondary h-100">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i> Archiv (nach Rang)</h3>
            </div>
            <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                <ul class="list-group list-group-flush bg-transparent">
                    @forelse ($hourData['archive_by_rank'] as $rankSlug => $seconds)
                        <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                            {{-- Hier wird der Slug mit dem Label aus der DB abgeglichen --}}
                            <span class="text-muted">{{ $rankNames[$rankSlug] ?? ucfirst($rankSlug) }}</span>
                            <span class="font-weight-bold">{{ formatSeconds($seconds) }} h</span>
                        </li>
                    @empty
                        <li class="list-group-item bg-transparent text-center text-muted py-3">
                            Keine archivierten Stunden.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>