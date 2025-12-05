@extends('layouts.public')

@section('title', 'Gesetzbuch')

@section('content')
<!-- Hero Section - Akzentfarbe Warning/Gefahr, wie der Bußgeldkatalog (Danger/Warning) -->
<div class="hero-header bg-dark pt-5 pb-4" style="background: linear-gradient(135deg, #a81c2d 0%, #dc3545 100%);">
    <div class="container text-center text-white">
        <h1 class="hero-title display-4"><i class="fas fa-balance-scale mr-3 text-warning"></i>Gesetzbuch</h1>
        <p class="hero-subtitle mt-2">Die geltenden Rechtsvorschriften der Hansestadt Hamburg</p>
    </div>
</div>

<!-- Main content -->
<div class="content bg-gray-dark pt-5 pb-5">
    <div class="container">
        
        <!-- Search Widget - Platziert, um die Hero Section zu überlappen -->
        <div class="row justify-content-center mb-5" style="margin-top: -3rem;">
            <div class="col-md-10">
                <!-- FIX: Dark Mode Card Style des Katalogs übernommen -->
                <div class="card shadow-lg card-dark card-outline-danger">
                    <div class="card-body p-2 bg-dark">
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0 bg-dark"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <!-- FIX: ID des Suchfelds an die JS Logik anpassen -->
                            <input type="text" id="law-search-field" class="form-control border-0 bg-dark text-white" placeholder="Suche nach Paragraf (§ 211), Titel oder Inhalt..." autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Haupttabelle für alle Gesetze (Keine Tabs/Akkordeons) -->
                <div class="card card-dark card-outline card-danger">
                    <div class="card-header bg-dark">
                        <h3 class="card-title text-white">Gesetzestexte: {{ $laws->count() }} Kategorien</h3>
                    </div>
                    
                    <div class="card-body p-0 table-responsive">
                        <!-- FIX: table-dark und table-hover für Dark Mode -->
                        <table class="table table-dark table-hover mb-0 text-white" id="laws-table"> 
                            <thead class="bg-gray-dark">
                                <tr>
                                    <th style="width: 15%">Gesetzbuch</th>
                                    <th style="width: 10%">Paragraf</th>
                                    <th>Titel</th>
                                    <th style="width: 60%">Inhalt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($laws as $book => $entries)
                                    @foreach($entries as $law)
                                        <tr class="law-row" data-search-term="{{ $law->book_label }} {{ $law->paragraph }} {{ $law->title }} {{ $law->content }}">
                                            <td>
                                                <!-- Anzeige des Kürzels und des vollen Labels -->
                                                <span class="badge badge-danger">{{ $book }}</span>
                                                <small class="d-block text-muted">{{$law->book_label}}</small>
                                            </td>
                                            <td class="font-weight-bold text-warning">{{ $law->paragraph }}</td>
                                            <td class="font-weight-bold">{{ $law->title }}</td>
                                            <td class="small text-muted">{{ Str::limit(strip_tags($law->content), 120) }}</td>
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
                    <h5 class="text-muted">Keine Gesetze für Ihre Suche gefunden.</h5>
                </div>

                <div class="text-center mt-5 mb-5 text-muted">
                    <small>Stand der Gesetzgebung: {{ date('d.m.Y') }} | Änderungen vorbehalten.</small>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function(){
        const $searchField = $("#law-search-field"); // FIX: ID des Suchfeldes
        const $noResults = $("#no-results");
        const $lawRows = $(".law-row");
        
        // Fügt einen Klick-Handler hinzu, um die Details anzuzeigen (kann später Modal sein)
        $lawRows.on('click', function() {
            // Vereinfachte Ausgabe für Klick-Funktion
            const title = $(this).find('td').eq(2).text();
            const paragraph = $(this).find('td').eq(1).text();
            const fullContent = $(this).attr('data-search-term'); // Hier könnte der komplette Text stehen

            // Dies sollte durch ein Modal ersetzt werden
            alert(`§ ${paragraph} - ${title}\n\nVOLLE INHALTSVORSCHAU:\n\n${fullContent}`);
        });

        $searchField.on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var hasGlobalMatches = false;

            $lawRows.each(function() {
                var row = $(this);
                // Suche im data-search-term Attribut, das alle relevanten Infos enthält
                var text = row.attr('data-search-term').toLowerCase(); 
                var match = text.includes(value);
                
                row.toggle(match);
                if(match) hasGlobalMatches = true;
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