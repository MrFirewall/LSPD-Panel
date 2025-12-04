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
                    <div class="card card-primary card-outline">
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

                    <!-- Vorlagen Box -->
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
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Strafregister & Beamte</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Bußgeld hinzufügen</label>
                                <div class="input-group">
                                    <select class="form-control select2-fines" id="fine-selector" data-placeholder="Bußgeld suchen...">
                                        <option value=""></option>
                                        @php $currentSection = ''; @endphp
                                        @foreach($fines as $fine)
                                            @if($fine->catalog_section != $currentSection)
                                                @if($currentSection != '') </optgroup> @endif
                                                <optgroup label="{{ $fine->catalog_section }}">
                                                @php $currentSection = $fine->catalog_section; @endphp
                                            @endif
                                            <!-- Wir speichern die Standard-Bemerkung im data-Attribut -->
                                            <option value="{{ $fine->id }}" 
                                                    data-offense="{{ $fine->offense }}" 
                                                    data-amount="{{ $fine->amount }}" 
                                                    data-jail="{{ $fine->jail_time }}"
                                                    data-remark="{{ $fine->remark }}">
                                                {{ $fine->offense }} ({{ number_format($fine->amount, 0, ',', '.') }}€)
                                            </option>
                                        @endforeach
                                        @if($currentSection != '') </optgroup> @endif
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success" id="add-fine-btn"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamische Liste -->
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-striped" id="selected-fines-table">
                                    <thead>
                                        <tr>
                                            <th>Tatbestand</th>
                                            <th>Bemerkung (Editierbar)</th>
                                            <th style="width: 40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Hier werden die Rows per JS eingefügt -->
                                    </tbody>
                                </table>
                            </div>

                            <hr>

                            <div class="form-group">
                                <label>Beteiligte Beamte</label>
                                <select name="attending_staff[]" class="form-control select2" multiple="multiple" style="width: 100%;" data-placeholder="Beamte auswählen...">
                                    @foreach($allStaff as $staff)
                                        <option value="{{ $staff->id }}" {{ Auth::id() == $staff->id ? 'selected' : '' }}>
                                            {{ optional($staff->rank)->label ?? '??' }} {{ $staff->name }}
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const templates = @json($templates);

        $(document).ready(function() {
            // Init Select2
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            $('.select2-citizen').select2({ theme: 'bootstrap4', width: '100%', tags: true, placeholder: "Bürger suchen oder Name eingeben" });
            $('.select2-fines').select2({ theme: 'bootstrap4', width: '100%', placeholder: "Bußgeld auswählen..." });

            // Vorlagen
            $('#template-selector').on('change', function() {
                const selectedKey = $(this).val();
                if (selectedKey && templates[selectedKey]) {
                    const template = templates[selectedKey];
                    $('input[name="title"]').val(template.title);
                    $('#incident_description').val(template.incident_description);
                    $('#actions_taken').val(template.actions_taken);
                }
            });

            // --- Bußgeld Logik ---
            $('#add-fine-btn').click(function() {
                const selector = $('#fine-selector');
                const id = selector.val();
                
                if (!id) return;

                // Daten aus dem Option-Tag holen
                const option = selector.find(':selected');
                const offense = option.data('offense');
                const remark = option.data('remark') || ''; // Standard Bemerkung

                // Prüfen ob schon vorhanden
                if ($(`#row-fine-${id}`).length > 0) {
                    alert('Dieses Bußgeld wurde bereits hinzugefügt.');
                    return;
                }

                // Neue Zeile einfügen
                // Wichtig: name="fines[index][id]" und name="fines[index][remark]"
                // Wir nutzen die ID als Index, um es einfach zu halten
                const html = `
                    <tr id="row-fine-${id}">
                        <td>
                            ${offense}
                            <input type="hidden" name="fines[${id}][id]" value="${id}">
                        </td>
                        <td>
                            <input type="text" name="fines[${id}][remark]" class="form-control form-control-sm" value="${remark}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-xs btn-danger remove-fine-btn" data-id="${id}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#selected-fines-table tbody').append(html);
                
                // Reset Selection
                selector.val('').trigger('change');
            });

            // Entfernen Button
            $(document).on('click', '.remove-fine-btn', function() {
                const id = $(this).data('id');
                $(`#row-fine-${id}`).remove();
            });
        });
    </script>
@endpush