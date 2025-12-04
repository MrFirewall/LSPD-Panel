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
                                <input type="text" name="patient_name" list="citizens_list" class="form-control" placeholder="Name eingeben..." required autocomplete="off">
                                <datalist id="citizens_list">
                                    @foreach($citizens as $c)
                                        <option value="{{ $c->name }}">
                                    @endforeach
                                </datalist>
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
                                <textarea name="incident_description" class="form-control" rows="5" placeholder="Was ist passiert?" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Getroffene Maßnahmen</label>
                                <textarea name="actions_taken" class="form-control" rows="3" placeholder="Erste Hilfe, Festnahme, etc." required></textarea>
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
                                <select name="fines[]" class="form-control select2" multiple="multiple" style="width: 100%; height: 300px;">
                                    @php $currentSection = ''; @endphp
                                    @foreach($fines as $fine)
                                        @if($fine->catalog_section != $currentSection)
                                            @if($currentSection != '') </optgroup> @endif
                                            <optgroup label="{{ $fine->catalog_section }}">
                                            @php $currentSection = $fine->catalog_section; @endphp
                                        @endif
                                        <option value="{{ $fine->id }}">
                                            {{ $fine->offense }} ({{ $fine->amount }}€ - {{ $fine->jail_time }} HE)
                                        </option>
                                    @endforeach
                                    </optgroup>
                                </select>
                                <small class="form-text text-muted">Mehrfachauswahl mit STRG + Klick möglich.</small>
                            </div>

                            <div class="form-group">
                                <label>Beteiligte Beamte</label>
                                <select name="attending_staff[]" class="form-control" multiple>
                                    @foreach($allStaff as $staff)
                                        <option value="{{ $staff->id }}" {{ Auth::id() == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->name }}
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