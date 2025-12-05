@extends('layouts.public')

@section('title', 'Gesetzbuch')

@section('content')
<!-- Hero Section - Akzentfarbe Warning/Gefahr, wie der Bußgeldkatalog (Danger/Warning) -->
<div class="hero-header">
    <div class="container text-center">
        <h1 class="hero-title display-4"><i class="fas fa-balance-scale mr-3"></i>Gesetzbuch</h1>
        <p class="hero-subtitle mt-2">Die geltenden Rechtsvorschriften der Hansestadt Hamburg</p>
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
                                                <span>{{ $law->book_label }}</span>
                                                <small class="d-block text-muted">{{$book}}</small>
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