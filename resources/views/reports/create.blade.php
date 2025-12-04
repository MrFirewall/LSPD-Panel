@extends('layouts.app')

@section('title', 'Neuen Einsatzbericht erstellen')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-file-signature text-primary mr-2"></i>Neuer Einsatzbericht</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Berichte</a></li>
                    <li class="breadcrumb-item active">Erstellen</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="{{ route('reports.store') }}" method="POST">
            @csrf
            
            <!-- Schnellvorlagen (Callout Style) -->
            <div class="callout callout-info elevation-1">
                <h5><i class="fas fa-magic text-info"></i> Schnellvorlagen</h5>
                <p>Wähle eine Vorlage, um Standard-Szenarien automatisch auszufüllen.</p>
                <div class="form-group mb-0">
                    <select class="form-control select2" id="template-selector" style="width: 100%;">
                        <option value="">-- Keine Vorlage wählen --</option>
                        @foreach($templates as $key => $template)
                            <option value="{{ $key }}">{{ $template['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <!-- Linke Spalte: Basisdaten -->
                <div class="col-lg-8">
                    <div class="card card-primary card-outline elevation-2">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Sachverhalt & Beteiligte</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><i class="fas fa-heading text-muted mr-1"></i> Titel / Einsatzstichwort</label>
                                        <input type="text" name="title" class="form-control" placeholder="z.B. Verkehrskontrolle..." required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-user text-muted mr-1"></i> Bürger (Patient/TV)</label>
                                        <select name="patient_name" class="form-control select2-citizen" required>
                                            <option value="">Suchen...</option>
                                            @foreach($citizens as $c)
                                                <option value="{{ $c->name }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-map-marker-alt text-muted mr-1"></i> Einsatzort</label>
                                        <input type="text" name="location" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-align-left text-muted mr-1"></i> Vorfallbeschreibung</label>
                                <textarea name="incident_description" id="incident_description" class="form-control" rows="6" placeholder="Detaillierte Schilderung des Vorfalls..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-hand-holding-medical text-muted mr-1"></i> Getroffene Maßnahmen</label>
                                <textarea name="actions_taken" id="actions_taken" class="form-control" rows="4" placeholder="Erste Hilfe, Festnahme, Belehrung..." required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rechte Spalte: Justiz & Personal -->
                <div class="col-lg-4">
                    <!-- Beamte Box -->
                    <div class="card card-secondary card-outline elevation-2">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users-cog mr-1"></i> Einheiten</h3>
                        </div>
                        <div class="card-body">
                             <div class="form-group">
                                <label>Beteiligte Beamte</label>
                                <select name="attending_staff[]" class="form-control select2" multiple="multiple" style="width: 100%;" data-placeholder="Beamte auswählen...">
                                    @foreach($allStaff as $staff)
                                        <option value="{{ $staff->id }}" {{ Auth::id() == $staff->id ? 'selected' : '' }}>
                                            {{ optional($staff->rankRelation)->label ?? '??' }} {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Strafregister Box -->
                    <div class="card card-danger card-outline elevation-2">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-gavel mr-1"></i> Strafregister</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3 bg-light border-bottom">
                                <label>Bußgeld hinzufügen</label>
                                <div class="input-group">
                                    <select class="form-control select2-fines" id="fine-selector">
                                        <option value=""></option>
                                        @php $currentSection = ''; @endphp
                                        @foreach($fines as $fine)
                                            @if($fine->catalog_section != $currentSection)
                                                @if($currentSection != '') </optgroup> @endif
                                                <optgroup label="{{ $fine->catalog_section }}">
                                                @php $currentSection = $fine->catalog_section; @endphp
                                            @endif
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
                                    <span class="input-group-append">
                                        <button type="button" class="btn btn-success" id="add-fine-btn"><i class="fas fa-plus"></i></button>
                                    </span>
                                </div>
                            </div>

                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0" id="selected-fines-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="pl-3">Tatbestand</th>
                                            <th>Bemerkung</th>
                                            <th style="width: 40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- JS fügt hier Zeilen ein -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                             <small class="text-muted"><i class="fas fa-info-circle"></i> Beträge werden automatisch summiert.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success float-right elevation-2">
                        <i class="fas fa-save mr-1"></i> Bericht speichern
                    </button>
                    <a href="{{ route('reports.index') }}" class="btn btn-default">Abbrechen</a>
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
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            $('.select2-citizen').select2({ theme: 'bootstrap4', width: '100%', tags: true, placeholder: "Name eingeben..." });
            $('.select2-fines').select2({ theme: 'bootstrap4', width: '100%', placeholder: "Bußgeldkatalog durchsuchen..." });

            $('#template-selector').on('change', function() {
                const selectedKey = $(this).val();
                if (selectedKey && templates[selectedKey]) {
                    const template = templates[selectedKey];
                    $('input[name="title"]').val(template.title);
                    $('#incident_description').val(template.incident_description);
                    $('#actions_taken').val(template.actions_taken);
                    toastr.success('Vorlage wurde angewendet.');
                }
            });

            $('#add-fine-btn').click(function() {
                const selector = $('#fine-selector');
                const id = selector.val();
                if (!id) return;

                const option = selector.find(':selected');
                const offense = option.data('offense');
                const remark = option.data('remark') || ''; 

                if ($(`#row-fine-${id}`).length > 0) {
                    toastr.warning('Dieses Bußgeld wurde bereits hinzugefügt.');
                    return;
                }

                const html = `
                    <tr id="row-fine-${id}">
                        <td class="pl-3">
                            <strong>${offense}</strong>
                            <input type="hidden" name="fines[${id}][id]" value="${id}">
                        </td>
                        <td>
                            <input type="text" name="fines[${id}][remark]" class="form-control form-control-sm border-0" style="background-color: transparent;" value="${remark}" placeholder="-">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-outline-danger remove-fine-btn" data-id="${id}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#selected-fines-table tbody').append(html);
                selector.val('').trigger('change');
            });

            $(document).on('click', '.remove-fine-btn', function() {
                const id = $(this).data('id');
                $(`#row-fine-${id}`).remove();
            });
        });
    </script>
@endpush