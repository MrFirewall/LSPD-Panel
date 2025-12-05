@extends('layouts.public')

@section('title', 'Bußgeldkatalog')

@section('content')
<!-- Hero Section - Akzentfarbe Rot/Gefahr (Danger) -->
<div class="hero-header" style="background: linear-gradient(135deg, #dc3545 0%, #a81c2d 100%);">
    <div class="container text-center">
        <h1 class="hero-title display-4"><i class="fas fa-clipboard-list mr-3"></i>Bußgeldkatalog</h1>
        <p class="hero-subtitle mt-2">Übersicht aller Verwarnungs- und Bußgelder (BBuG)</p>
    </div>
</div>

<div class="content">
    <div class="container">
        
        <!-- Search Widget - Hell abgesetzt für Sichtbarkeit -->
        <div class="row justify-content-center mb-5" style="margin-top: -3rem;">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-body p-2">
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0 bg-transparent"><i class="fas fa-search text-info"></i></span>
                            </div>
                            <!-- Input-Feld passt sich automatisch an Dark Mode an -->
                            <input type="text" id="catalog-search" class="form-control border-0" placeholder="Was suchen Sie? (z.B. 'Fahren', 'Körperverletzung', 'Drogen')..." autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalog Categories -->
<div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Haupttabelle für alle Bußgelder (KEINE Akkordeons) -->
                <div class="card card-dark card-outline card-danger">
                    <div class="card-header bg-dark">
                        <h3 class="card-title text-white">Alle Tatbestände</h3>
                    </div>
                    
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-dark table-hover mb-0 text-white" id="fines-table"> 
                            <thead class="bg-gray-dark">
                                <tr>
                                    <th style="width: 15%">Kategorie</th>
                                    <th>Tatbestand</th>
                                    <th style="width: 150px">Bußgeld</th>
                                    <th style="width: 100px" class="text-center">Haft (HE)</th>
                                    <th style="width: 80px" class="text-center">Punkte</th>
                                    <th>Hinweise</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $currentSection = null; @endphp
                                @foreach($categories as $section => $fines)
                                    <!-- Optische Trennung durch eine Header-Zeile -->
                                    <tr class="law-section-header">
                                        <td colspan="6" class="font-weight-bold text-center p-2">
                                            <i class="fas fa-angle-double-down mr-2"></i> {{ $section }} <i class="fas fa-angle-double-down ml-2"></i>
                                        </td>
                                    </tr>
                                    
                                    @foreach($fines as $fine)
                                        <tr class="fine-row" data-search-term="{{ $fine->catalog_section }} {{ $fine->offense }} {{ $fine->amount }} {{ $fine->remark }}">
                                            <td>
                                                <span class="badge badge-danger">{{ Str::limit($fine->catalog_section, 12, '') }}</span>
                                            </td>
                                            <td class="font-weight-bold">{{ $fine->offense }}</td>
                                            <td class="text-warning font-weight-bold">{{ number_format($fine->amount, 0, ',', '.') }} €</td>
                                            <td class="text-center">
                                                @if($fine->jail_time > 0)
                                                    <span>{{ $fine->jail_time }} HE</span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($fine->points > 0)
                                                    <span>{{ $fine->points }}</span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $fine->remark ?: '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- No Results Message -->
                <div id="no-results" class="text-center mt-5" style="display: none;">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Keine Einträge für Ihre Suche gefunden.</h5>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function(){
        const $searchField = $("#catalog-search-field");
        const $noResults = $("#no-results");
        const $fineRows = $(".fine-row");
        const $sectionHeaders = $(".law-section-header"); // Elemente, die Kategorien trennen
        
        $searchField.on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var hasGlobalMatches = false;

            if (value.length === 0) {
                // Keine Suche: Alles anzeigen
                $fineRows.show();
                $sectionHeaders.show();
                $noResults.hide();
                return;
            }

            let currentSectionMatch = false;

            // Iteriere rückwärts, um die Sichtbarkeit der Header einfacher zu steuern
            $($fineRows.get().reverse()).each(function() {
                var row = $(this);
                // Sucht im data-search-term
                var text = row.attr('data-search-term').toLowerCase(); 
                var match = text.includes(value);
                
                row.toggle(match);

                if (match) {
                    hasGlobalMatches = true;
                    currentSectionMatch = true;
                }
            });

            // Wir müssen die Section Headers separat behandeln
            $sectionHeaders.each(function() {
                // Suche, ob irgendeine Zeile nach diesem Header sichtbar ist
                let header = $(this);
                let nextVisibleRow = header.nextAll('.fine-row:visible').first();
                
                if (nextVisibleRow.length > 0) {
                    header.show();
                } else {
                    header.hide();
                }
            });


            // "Keine Ergebnisse" Nachricht anzeigen
            if(!hasGlobalMatches && value.length > 0) {
                $noResults.show();
            } else {
                $noResults.hide();
            }
        });
    });
</script>
@endpush