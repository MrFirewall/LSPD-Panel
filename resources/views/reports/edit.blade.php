@extends('layouts.app')

@section('title', 'Einsatzbericht bearbeiten')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@endpush

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Einsatzbericht #{{ $report->id }} bearbeiten</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="POST" action="{{ route('reports.update', $report) }}">
                @csrf
                @method('PUT')

                <div class="form-group row align-items-center bg-dark p-2 rounded">
                    <label for="template-selector" class="col-sm-3 col-form-label mb-0">Vorlage anwenden (überschreibt Inhalt)</label>
                    <div class="col-sm-9">
                        <select class="form-control" id="template-selector">
                            <option value="">-- Keine Vorlage --</option>
                            @foreach($templates as $key => $template)
                                <option value="{{ $key }}">{{ $template['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <!-- LINKE SPALTE -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Titel / Einsatzstichwort</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $report->title) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="patient_name">Name des Patienten</label>
                            <select class="form-control select2-citizen" id="patient_name" name="patient_name" required>
                                @foreach($citizens as $citizen)
                                    <option value="{{ $citizen->name }}" {{ old('patient_name', $report->patient_name) == $citizen->name ? 'selected' : '' }}>
                                        {{ $citizen->name }}
                                    </option>
                                @endforeach
                                @if (!in_array($report->patient_name, $citizens->pluck('name')->toArray()))
                                     <option value="{{ $report->patient_name }}" selected>{{ $report->patient_name }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="location">Einsatzort</label>
                            <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $report->location) }}" required>
                        </div>
                    </div>

                    <!-- RECHTE SPALTE -->
                    <div class="col-md-6">
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
                                        <option value="{{ $fine->id }}" 
                                                data-offense="{{ $fine->offense }}" 
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

                        <!-- Dynamische Liste mit vorbefüllten Werten -->
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
                                    @foreach($report->fines as $assignedFine)
                                        <tr id="row-fine-{{ $assignedFine->id }}">
                                            <td>
                                                {{ $assignedFine->offense }}
                                                <input type="hidden" name="fines[{{ $assignedFine->id }}][id]" value="{{ $assignedFine->id }}">
                                            </td>
                                            <td>
                                                <!-- Hier nutzen wir den Pivot-Wert -->
                                                <input type="text" name="fines[{{ $assignedFine->id }}][remark]" class="form-control form-control-sm" value="{{ $assignedFine->pivot->remark }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-xs btn-danger remove-fine-btn" data-id="{{ $assignedFine->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="attending_staff">Beteiligte Mitarbeiter</label>
                            <select class="form-control select2" id="attending_staff" name="attending_staff[]" multiple="multiple" data-placeholder="Beamte wählen...">
                                @php
                                    $selectedStaffIds = $report->attendingStaff->pluck('id')->toArray();
                                @endphp
                                @foreach($allStaff as $staff)
                                    <option value="{{ $staff->id }}" {{ in_array($staff->id, $selectedStaffIds) ? 'selected' : '' }}>
                                        {{ optional($staff->rank)->label ?? '??' }} {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="incident_description">Einsatzhergang</label>
                    <textarea class="form-control" id="incident_description" name="incident_description" rows="10" required>{{ old('incident_description', $report->incident_description) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="actions_taken">Durchgeführte Maßnahmen</label>
                    <textarea class="form-control" id="actions_taken" name="actions_taken" rows="10" required>{{ old('actions_taken', $report->actions_taken) }}</textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-flat">
                        <i class="fas fa-save me-1"></i> Änderungen speichern
                    </button>
                    <a href="{{ route('reports.index') }}" class="btn btn-default btn-flat">Abbrechen</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const templates = @json($templates);

        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            $('.select2-citizen').select2({ theme: 'bootstrap4', placeholder: 'Bürger suchen oder Namen eingeben', tags: true, width: '100%' });
            $('.select2-fines').select2({ theme: 'bootstrap4', width: '100%', placeholder: "Bußgeld auswählen..." });
            
            $('#template-selector').on('change', function() {
                const selectedKey = $(this).val();
                if (selectedKey && templates[selectedKey]) {
                    if(confirm('Möchtest du wirklich die Vorlage anwenden? Der aktuelle Text wird überschrieben.')) {
                        const template = templates[selectedKey];
                        $('#title').val(template.title);
                        $('#incident_description').val(template.incident_description);
                        $('#actions_taken').val(template.actions_taken);
                    } else {
                        $(this).val('');
                    }
                }
            });

            // Gleiche JS Logik wie im Create View
            $('#add-fine-btn').click(function() {
                const selector = $('#fine-selector');
                const id = selector.val();
                if (!id) return;

                const option = selector.find(':selected');
                const offense = option.data('offense');
                const remark = option.data('remark') || '';

                if ($(`#row-fine-${id}`).length > 0) {
                    alert('Dieses Bußgeld wurde bereits hinzugefügt.');
                    return;
                }

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
                selector.val('').trigger('change');
            });

            $(document).on('click', '.remove-fine-btn', function() {
                const id = $(this).data('id');
                $(`#row-fine-${id}`).remove();
            });
        });
    </script>
@endpush