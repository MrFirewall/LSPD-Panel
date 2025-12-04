@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Akte <span class="text-muted">#{{ $report->id }}</span></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Übersicht</a></li>
                    <li class="breadcrumb-item active">Akte #{{ $report->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <!-- Main Card: Simuliert Aktenoptik -->
                <div class="card card-outline card-navy elevation-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-folder mr-2"></i> LSPD Einsatzbericht
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ $report->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Invoice Header Info -->
                        <div class="row invoice-info mb-4">
                            <div class="col-sm-4 invoice-col">
                                <strong class="text-uppercase text-secondary">Erstellt von</strong>
                                <address>
                                    <h5 class="mt-1 text-primary">
                                        {{ optional($report->user->rankRelation)->label ?? 'Officer' }} {{ $report->user->name }}
                                    </h5>
                                    Dienstnummer: {{ $report->user->id }}<br>
                                    LSPD Department
                                </address>
                            </div>
                            <div class="col-sm-4 invoice-col">
                                <strong class="text-uppercase text-secondary">Betroffene Person</strong>
                                <address>
                                    <h5 class="mt-1">{{ $report->patient_name }}</h5>
                                    @if($report->citizen)
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Registriert</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-question-circle"></i> Unbekannt</span>
                                    @endif
                                </address>
                            </div>
                            <div class="col-sm-4 invoice-col">
                                <strong class="text-uppercase text-secondary">Vorfallsdetails</strong><br>
                                <b>Akten ID:</b> {{ $report->id }}<br>
                                <b>Ort:</b> {{ $report->location }}<br>
                                <b>Titel:</b> {{ $report->title }}
                            </div>
                        </div>

                        <hr>

                        <!-- Content Sections using Tabs or clean Headers -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card card-primary card-outline shadow-none">
                                    <div class="card-header">
                                        <h3 class="card-title">Einsatzhergang</h3>
                                    </div>
                                    <div class="card-body bg-light text-dark rounded">
                                        {!! nl2br(e($report->incident_description)) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-success card-outline shadow-none">
                                    <div class="card-header">
                                        <h3 class="card-title">Getroffene Maßnahmen</h3>
                                    </div>
                                    <div class="card-body bg-light text-dark rounded">
                                        {!! nl2br(e($report->actions_taken)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Beteiligte Einheiten -->
                        <div class="row mt-3 mb-4">
                            <div class="col-12">
                                <label class="text-muted">Beteiligte Einheiten:</label><br>
                                @foreach($report->attendingStaff as $staff)
                                    <span class="badge badge-info p-2 mr-1 mb-1" style="font-size: 0.9rem;">
                                        <i class="fas fa-user-shield"></i> {{ optional($staff->rankRelation)->label }} {{ $staff->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Fines Table -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-danger card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-gavel"></i> Strafregister & Sanktionen</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-striped table-valign-middle">
                                            <thead>
                                                <tr>
                                                    <th>Tatbestand</th>
                                                    <th>Individuelle Bemerkung</th>
                                                    <th class="text-center">Haftzeit (HE)</th>
                                                    <th class="text-right">Betrag</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($report->fines as $fine)
                                                    <tr>
                                                        <td>{{ $fine->offense }}</td>
                                                        <td class="small text-muted font-italic">
                                                            {{ $fine->pivot->remark ?: '-' }}
                                                        </td>
                                                        <td class="text-center">
                                                            @if($fine->jail_time > 0)
                                                                <span class="badge badge-warning text-dark">{{ $fine->jail_time }} Min</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-right">{{ number_format($fine->amount, 0, ',', '.') }} €</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">Keine Sanktionen erfasst.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                                    Haftzeiten sind unverzüglich im Bundesgefängnis anzutreten. Bei Nichtzahlung der Bußgelder erfolgt eine staatliche Zwangsvollstreckung.
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th style="width:50%">Gesamthaftzeit:</th>
                                            <td>
                                                <h4 class="text-danger mb-0">{{ $report->fines->sum('jail_time') }} <small>Minuten</small></h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Gesamtbußgeld:</th>
                                            <td>
                                                <h4 class="text-success mb-0">{{ number_format($report->fines->sum('amount'), 0, ',', '.') }} €</h4>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="row no-print mt-4">
                            <div class="col-12">
                                <a href="javascript:window.print()" class="btn btn-default"><i class="fas fa-print"></i> Drucken</a>
                                
                                <div class="float-right">
                                    @can('update', $report)
                                        <a href="{{ route('reports.edit', $report) }}" class="btn btn-info">
                                            <i class="fas fa-edit"></i> Bearbeiten
                                        </a>
                                    @endcan

                                    @can('delete', $report)
                                        <form action="{{ route('reports.destroy', $report) }}" method="POST" class="d-inline ml-2" onsubmit="return confirm('Wirklich löschen?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Löschen
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection