@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Neuen Einsatzbericht anlegen</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <form action="{{ route('reports.store') }}" method="POST">
            @csrf
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Allgemeine Informationen</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Titel / Einsatzstichwort</label>
                                <input type="text" name="title" class="form-control" placeholder="z.B. Verkehrskontrolle..." required>
                            </div>
                            <div class="form-group">
                                <label>Name des Bürgers (Patient/TV)</label>
                                <select name="patient_name" class="form-control select2-citizen" required>
                                    <option value="">Bitte wählen oder tippen...</option>
                                    @foreach($citizens as $c)
                                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Einsatzort</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    </div>
                                    <input type="text" name="location" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Vorfallbeschreibung</label>
                                <textarea name="incident_description" id="incident_description" class="form-control" rows="5" placeholder="Was ist passiert?" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Getroffene Maßnahmen</label>
                                <textarea name="actions_taken" id="actions_taken" class="form-control" rows="3" placeholder="Erste Hilfe, Festnahme, etc." required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Vorlagen Box (Optional) -->
                    <div class="card card-secondary collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">Schnellvorlagen</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Vorlage anwenden</label>
                                <select class="form-control" id="template-selector">
                                    <option value="">-- Keine Vorlage --</option>
                                    @foreach($templates as $key => $template)
                                        <option value="{{ $key }}">{{ $template['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title">Strafregister & Beamte</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Tatvorwürfe / Bußgelder auswählen</label>
                                <select name="fines[]" class="form-control select2" multiple="multiple" style="width: 100%;" data-placeholder="Bußgelder suchen...">
                                    @php $currentSection = ''; @endphp
                                    @foreach($fines as $fine)
                                        @if($fine->catalog_section != $currentSection)
                                            @if($currentSection != '') </optgroup> @endif
                                            <optgroup label="{{ $fine->catalog_section }}">
                                            @php $currentSection = $fine->catalog_section; @endphp
                                        @endif
                                        <option value="{{ $fine->id }}">
                                            {{ $fine->offense }} ({{ number_format($fine->amount, 0, ',', '.') }}€ @if($fine->jail_time > 0) - {{ $fine->jail_time }} HE @endif)
                                        </option>
                                    @endforeach
                                    @if($currentSection != '') </optgroup> @endif
                                </select>
                                <small class="form-text text-muted">Mehrfachauswahl möglich.</small>
                            </div>

                            <div class="form-group">
                                <label>Beteiligte Beamte</label>
                                <select name="attending_staff[]" class="form-control select2" multiple="multiple" style="width: 100%;" data-placeholder="Beamte auswählen...">
                                    @foreach($allStaff as $staff)
                                        <option value="{{ $staff->id }}" {{ Auth::id() == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->rank }} {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success float-right">
                                <i class="fas fa-save"></i> Bericht speichern
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const templates = @json($templates);

        $(document).ready(function() {
            // Standard Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Bürger Auswahl mit Tags (neue Namen erstellen)
            $('.select2-citizen').select2({
                theme: 'bootstrap4',
                width: '100%',
                tags: true,
                placeholder: "Bürger suchen oder Name eingeben"
            });
            
            // Vorlagen Logik
            $('#template-selector').on('change', function() {
                const selectedKey = $(this).val();
                
                if (selectedKey && templates[selectedKey]) {
                    const template = templates[selectedKey];
                    $('input[name="title"]').val(template.title);
                    $('#incident_description').val(template.incident_description);
                    $('#actions_taken').val(template.actions_taken);
                }
            });
        });
    </script>
@endpush