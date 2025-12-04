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
                <div id="accordion">
                    @foreach($categories as $section => $fines)
                        <!-- card-purple zu card-danger geändert -->
                        <div class="card card-outline card-danger mb-3 category-card">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    <a class="d-block w-100  font-weight-bold" data-toggle="collapse" href="#collapse{{ Str::slug($section) }}">
                                        <i class="fas fa-folder-open text-danger mr-2"></i> {{ $section }}
                                        <!-- badge-purple zu badge-danger geändert -->
                                        <span class="float-right badge badge-danger badge-pill">{{ count($fines) }} Einträge</span>
                                    </a>
                                </h4>
                            </div>
                            
                            <div id="collapse{{ Str::slug($section) }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-parent="#accordion">
                                <div class="card-body p-0 table-responsive">
                                    <table class="table table-hover table-striped fine-table mb-0">
                                        <thead>
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
                                                    <!-- text-danger zu text-warning für bessere Sichtbarkeit von Geldbeträgen im Dark Mode -->
                                                    <td class="text-warning font-weight-bold">{{ number_format($fine->amount, 0, ',', '.') }} €</td>
                                                    <td class="text-center">
                                                        @if($fine->jail_time > 0)
                                                            <span class="badge badge-danger ">{{ $fine->jail_time }} HE</span>
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
                                                    <!-- text-light, um sicherzustellen, dass die Hinweise gut sichtbar sind -->
                                                    <td class="text-light small">
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
        $("#catalog-search").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var hasGlobalMatches = false;

            // Iteriere durch alle Karten (Kategorien)
            $(".category-card").each(function() {
                var card = $(this);
                var hasVisibleRows = false;
                
                // Suche innerhalb der Tabelle in dieser Karte
                card.find(".fine-row").filter(function() {
                    // Suche in Tatbestand (Index 0) und Hinweisen (Index 4)
                    var text = $(this).text().toLowerCase();
                    var match = text.indexOf(value) > -1;
                    $(this).toggle(match);
                    if(match) hasVisibleRows = true;
                });

                // Wenn Suche leer ist, zeige alles wie Standard
                if(value === "") {
                    card.show();
                    // Optional: Akkordeon Logik zurücksetzen (z.B. nur erstes offen)
                    // Hier lassen wir den aktuellen Status
                } else {
                    // Wenn Treffer in der Tabelle, öffne das Akkordeon und zeige Karte
                    if (hasVisibleRows) {
                        card.show();
                        card.find('.collapse').collapse('show');
                        hasGlobalMatches = true;
                    } else {
                        card.hide();
                    }
                }
            });

            // "Keine Ergebnisse" Nachricht
            if(!hasGlobalMatches && value !== "") {
                $("#no-results").show();
            } else {
                $("#no-results").hide();
            }
        });
    });
</script>
@endpush