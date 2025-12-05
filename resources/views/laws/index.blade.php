@extends('layouts.public')

@section('title', 'Gesetzbuch')

@section('content')
<!-- Hero Section -->
<div class="hero-header bg-dark pt-5 pb-4">
    <div class="container text-center text-white">
        <h1 class="hero-title display-4"><i class="fas fa-balance-scale mr-3 text-warning"></i>Gesetzbuch</h1>
        <p class="hero-subtitle mt-2 text-muted">Die geltenden Rechtsvorschriften der Hansestadt Hamburg</p>
    </div>
</div>

<!-- Main content -->
<div class="content bg-gray-dark pt-5 pb-5">
    <div class="container">
        
        <!-- Search Widget -->
        <div class="row justify-content-center mb-5" style="margin-top: -3rem;">
            <div class="col-md-10">
                <div class="card shadow-lg card-dark card-outline-primary">
                    <div class="card-body p-2 bg-dark">
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0 bg-dark"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="law-search" class="form-control border-0 bg-dark text-white" placeholder="Suche nach Paragraf (§ 211), Titel oder Inhalt..." autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- FIX: Eine einzige Tabelle für alle Gesetze (keine Tabs/Accordions) -->
                <div class="card card-dark card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Alle Gesetzestexte</h3>
                    </div>
                    
                    <div class="card-body p-0 table-responsive">
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
                                        <tr class="law-row" data-book="{{ $book }}" data-title="{{ Str::slug($law->title) }}" data-paragraph="{{ $law->paragraph }}">
                                            <td>
                                                <span class="badge badge-primary">{{ $book }}</span>
                                                <small class="d-block text-muted">{{$law->book_label}}</small>
                                            </td>
                                            <td class="font-weight-bold text-info">{{ $law->paragraph }}</td>
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
        const $searchField = $("#law-search");
        const $noResults = $("#no-results");
        const $lawRows = $(".law-row");
        
        // Fügt einen Klick-Handler hinzu, um die Details anzuzeigen (kann später Modal sein)
        $lawRows.on('click', function() {
            // In einer echten Anwendung würden Sie hier ein Modal öffnen, 
            // um den vollen Inhalt von $law->content anzuzeigen.
            const content = $(this).find('td').eq(3).text();
            const title = $(this).find('td').eq(2).text();
            alert(`§ ${$(this).find('td').eq(1).text()} - ${title}\n\n${content}`);
        });

        $searchField.on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var hasGlobalMatches = false;

            $lawRows.each(function() {
                var row = $(this);
                // Suche in Paragraf (2. Spalte), Titel (3. Spalte) und Inhalt (4. Spalte)
                var text = row.text().toLowerCase();
                var match = text.indexOf(value) > -1;
                
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