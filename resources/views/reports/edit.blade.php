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
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-edit text-warning mr-2"></i>Bericht #{{ $report->id }} bearbeiten
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Berichte</a></li>
                    <li class="breadcrumb-item active">#{{ $report->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="{{ route('reports.update', $report) }}">
            @csrf
            @method('PUT')

            <!-- Vorlagen (Optional, standardmäßig eingeklappt beim Bearbeiten) -->
            <div class="card card-secondary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">Schnellvorlagen anwenden (Überschreiben)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <select class="form-control select2" id="template-selector" style="width: 100%;">
                             <option value="">-- Vorlage wählen --</option>
                            @foreach($templates as $key => $template)
                                <option value="{{ $key }}">{{ $template['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Linke Spalte -->
                <div class="col-lg-8">
                    <div class="card card-primary card-outline elevation-2">
                         <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Sachverhalt & Beteiligte</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Titel / Einsatzstichwort</label>
                                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $report->title) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name des Patienten</label>
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
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Einsatzort</label>
                                        <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $report->location) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Einsatzhergang</label>
                                <textarea class="form-control" id="incident_description" name="incident_description" rows="8" required>{{ old('incident_description', $report->incident_description) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Durchgeführte Maßnahmen</label>
                                <textarea class="form-control" id="actions_taken" name="actions_taken" rows="6" required>{{ old('actions_taken', $report->actions_taken) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rechte Spalte -->
                <div class="col-lg-4">
                     <div class="card card-secondary card-outline elevation-2">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users-cog mr-1"></i> Einheiten</h3>
                        </div>
                        <div class="card-body">
                             <div class="form-group">
                                <label>Beteiligte Mitarbeiter</label>
                                <select class="form-control select2" id="attending_staff" name="attending_staff[]" multiple="multiple">
                                    @php
                                        $selectedStaffIds = $report->attendingStaff->pluck('id')->toArray();
                                    @endphp
                                    @foreach($allStaff as $staff)
                                        <option value="{{ $staff->id }}" {{ in_array($staff->id, $selectedStaffIds) ? 'selected' : '' }}>
                                            {{ optional($staff->rankRelation)->label ?? '??' }} {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card card-danger card-outline elevation-2">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-gavel mr-1"></i> Strafregister</h3>
                        </div>
                        <div class="card-body p-0">
                             <div class="p-3 bg-light border-bottom">
                                <label>Bußgeld hinzufügen</label>
                                <div class="input-group">
                                    <select class="form-control select2-fines" id="fine-selector" data-placeholder="Suche...">
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
                                        @foreach($report->fines as $assignedFine)
                                            <tr id="row-fine-{{ $assignedFine->id }}">
                                                <td class="pl-3">
                                                    <strong>{{ $assignedFine->offense }}</strong>
                                                    <input type="hidden" name="fines[{{ $assignedFine->id }}][id]" value="{{ $assignedFine->id }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="fines[{{ $assignedFine->id }}][remark]" class="form-control form-control-sm border-0" style="background-color: transparent;" value="{{ $assignedFine->pivot->remark }}">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-outline-danger remove-fine-btn" data-id="{{ $assignedFine->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary float-right elevation-2">
                        <i class="fas fa-save me-1"></i> Änderungen speichern
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
                        <td class="pl-3">
                            <strong>${offense}</strong>
                            <input type="hidden" name="fines[${id}][id]" value="${id}">
                        </td>
                        <td>
                            <input type="text" name="fines[${id}][remark]" class="form-control form-control-sm border-0" style="background-color: transparent;" value="${remark}">
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