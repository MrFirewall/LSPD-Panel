@extends('layouts.public')

@section('title', 'Bußgeldkatalog')

@section('content')
<!-- Hero Section -->
<div class="hero-header bg-dark pt-5 pb-4">
    <div class="container text-center text-white">
        <h1 class="hero-title display-4"><i class="fas fa-clipboard-list mr-3 text-warning"></i>Bußgeldkatalog</h1>
        <p class="hero-subtitle mt-2 text-muted">Übersicht aller Verwarnungs- und Bußgelder (BBuG)</p>
    </div>
</div>

<div class="content" style="background-color: #1f2937;">
    <div class="container">
        
        <!-- Search Widget -->
        <div class="row justify-content-center mb-5" style="margin-top: -3rem;">
            <div class="col-md-8">
                <!-- FIX: Dark Mode Card (Card-dark) -->
                <div class="card shadow-lg card-dark">
                    <div class="card-body p-2">
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0 bg-dark"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <!-- FIX: Dark Mode Input -->
                            <input type="text" id="catalog-search" class="form-control border-0 bg-dark text-white" placeholder="Was suchen Sie? (z.B. 'Fahren', 'Körperverletzung', 'Drogen')..." autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalog Categories -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div id="accordion">
                    @foreach($categories as $section => $fines)
                        <!-- FIX: Dark Mode Card (card-dark) -->
                        <div class="card card-dark card-outline card-primary mb-3 category-card">
                            <div class="card-header bg-dark"> <!-- Hintergrund des Headers auf Dunkel setzen -->
                                <h4 class="card-title w-100">
                                    <a class="d-block w-100 text-white font-weight-bold" data-toggle="collapse" href="#collapse{{ Str::slug($section) }}">
                                        <i class="fas fa-folder-open text-primary mr-2"></i> {{ $section }}
                                        <span class="float-right badge badge-primary badge-pill">{{ count($fines) }} Einträge</span>
                                    </a>
                                </h4>
                            </div>
                            
                            <div id="collapse{{ Str::slug($section) }}" class="collapse" data-parent="#accordion">
                                <div class="card-body p-0 table-responsive">
                                    <!-- FIX: bg-dark und text-white für Tabelleninhalt -->
                                    <table class="table table-hover table-striped fine-table mb-0 text-white" style="background-color: #1f2937;">
                                        <thead class="bg-dark">
                                            <tr>
                                                <th class="pl-4">Tatbestand</th>
                                                <th style="width: 150px">Bußgeld</th>
                                                <th style="width: 100px" class="text-center">Haft (HE)</th>
                                                <th style="width: 80px" class="text-center">Punkte</th>
                                                <th>Hinweise</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($fines as $fine)
                                                <tr class="fine-row">
                                                    <td class="pl-4 font-weight-bold">{{ $fine->offense }}</td>
                                                    <td class="text-warning font-weight-bold">{{ number_format($fine->amount, 0, ',', '.') }} €</td>
                                                    <td class="text-center">
                                                        @if($fine->jail_time > 0)
                                                            <span class="badge badge-warning text-dark">{{ $fine->jail_time }} HE</span>
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($fine->points > 0)
                                                            <span class="badge badge-danger">{{ $fine->points }}</span>
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted small">
                                                        {{ $fine->remark ?: '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
        const $searchField = $("#catalog-search");
        const $noResults = $("#no-results");
        const $categoryCards = $(".category-card");
        
        $searchField.on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var hasGlobalMatches = false;

            // Iteriere durch alle Karten (Kategorien)
            $categoryCards.each(function() {
                var card = $(this);
                var collapseTarget = card.find('.collapse');
                var hasVisibleRows = false;
                
                // Suche innerhalb der Tabelle in dieser Karte
                card.find(".fine-row").filter(function() {
                    // Suche in Tatbestand (Index 0) und Hinweisen (Index 4)
                    var text = $(this).text().toLowerCase();
                    var match = text.indexOf(value) > -1;
                    
                    // Zeile anzeigen/verbergen
                    $(this).toggle(match);
                    if(match) hasVisibleRows = true;
                });

                // Leere Suche: Zeige alle Karten, schließe alle Akkordeons
                if(value === "") {
                    card.show();
                    collapseTarget.collapse('hide');
                    if (card.is(':first-child')) {
                        // Optional: Nur die erste Kategorie standardmäßig öffnen
                        // collapseTarget.collapse('show'); 
                    }
                    hasGlobalMatches = true;
                } else {
                    // Ergebnis gefunden: Zeige Karte und öffne Akkordeon
                    if (hasVisibleRows) {
                        card.show();
                        collapseTarget.collapse('show');
                        hasGlobalMatches = true;
                    } else {
                        // Kein Ergebnis: Verstecke die Karte und schließe Akkordeon (falls offen)
                        card.hide();
                        collapseTarget.collapse('hide');
                    }
                }
            });

            // "Keine Ergebnisse" Nachricht anzeigen
            if(!hasGlobalMatches && value !== "") {
                $noResults.show();
            } else {
                $noResults.hide();
            }
        });
        
        // Beim Laden alle Akkordeons schließen (für sauberen Start)
        $categoryCards.find('.collapse').collapse('hide');
    });
</script>
@endpush