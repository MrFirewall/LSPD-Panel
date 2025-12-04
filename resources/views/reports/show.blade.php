@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Akte #{{ $report->id }}</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('reports.index') }}" class="btn btn-secondary float-sm-right">Zurück zur Übersicht</a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <!-- Main Report Card -->
                <div class="invoice p-3 mb-3" style="background-color: transparent;"> <!-- BG transparent für Dark Mode Adaption wenn Card verwendet wird -->
                    <!-- In AdminLTE ist .invoice meist weiß, im Dark Mode dunkel. Wir nutzen hier die Standardklassen -->
                    
                    <div class="row">
                        <div class="col-12">
                            <h4>
                                <i class="fas fa-gavel"></i> LSPD Bericht: {{ $report->title }}
                                <small class="float-right">Datum: {{ $report->created_at->format('d.m.Y H:i') }}</small>
                            </h4>
                        </div>
                    </div>
                    
                    <div class="row invoice-info mt-4">
                        <div class="col-sm-4 invoice-col">
                            Verantwortlicher Beamter
                            <address>
                                <strong>{{ optional($report->user->rank)->label ?? 'Officer' }} {{ $report->user->name }}</strong><br>
                                Dienstnummer: {{ $report->user->id }}
                            </address>
                        </div>
                        <div class="col-sm-4 invoice-col">
                            Betroffene Person / TV
                            <address>
                                <strong>{{ $report->patient_name }}</strong><br>
                                @if($report->citizen)
                                    Status: <span class="badge badge-success">Registrierter Bürger</span><br>
                                @else
                                    Status: <span class="badge badge-warning">Unbekannt / Nicht registriert</span><br>
                                @endif
                            </address>
                        </div>
                        <div class="col-sm-4 invoice-col">
                            <b>Einsatzort:</b> {{ $report->location }}<br>
                            <b>Beteiligte Einheiten:</b><br>
                            @foreach($report->attendingStaff as $staff)
                                <span class="badge badge-info">{{ optional($staff->rank)->label }} {{ $staff->name }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p class="lead">Vorfallhergang:</p>
                            <!-- Hintergrund entfernen oder an Dark Mode anpassen -->
                            <div class="border rounded p-3" style="min-height: 150px;">
                                {!! nl2br(e($report->incident_description)) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p class="lead">Maßnahmen:</p>
                            <div class="border rounded p-3" style="min-height: 150px;">
                                {!! nl2br(e($report->actions_taken)) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Fines Table -->
                    <div class="row mt-4">
                        <div class="col-12 table-responsive">
                            <p class="lead text-danger"><i class="fas fa-exclamation-triangle"></i> Strafbestand & Bußgelder</p>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tatbestand</th>
                                        <th>Haftzeit (HE)</th>
                                        <th>Individuelle Bemerkung</th>
                                        <th class="text-right">Betrag</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($report->fines as $fine)
                                        <tr>
                                            <td>{{ $fine->offense }}</td>
                                            <td>
                                                @if($fine->jail_time > 0)
                                                    <span class="badge badge-warning">{{ $fine->jail_time }} HE</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <!-- Hier holen wir uns die Bemerkung aus der Pivot Tabelle -->
                                                @if(!empty($fine->pivot->remark))
                                                    {{ $fine->pivot->remark }}
                                                @else
                                                    <span class="text-muted font-italic">- keine -</span>
                                                @endif
                                            </td>
                                            <td class="text-right text-bold">{{ number_format($fine->amount, 0, ',', '.') }} €</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Keine Bußgelder verhängt.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted small mt-3">
                                <i class="fas fa-info-circle"></i> Haftzeit-Hinweis: 1 HE entspricht 1 Minute im Bundesgefängnis.
                            </p>
                        </div>
                        <div class="col-6">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th style="width:50%">Gesamthaftzeit:</th>
                                        <td class="text-danger"><strong>{{ $report->fines->sum('jail_time') }} HE</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Gesamtbußgeld:</th>
                                        <td class="text-success"><strong>{{ number_format($report->fines->sum('amount'), 0, ',', '.') }} €</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row no-print mt-4">
                        <div class="col-12">
                             @can('delete', $report)
                                <form action="{{ route('reports.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirm('Wirklich löschen?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger float-right" style="margin-right: 5px;">
                                        <i class="fas fa-trash"></i> Löschen
                                    </button>
                                </form>
                            @endcan
                            
                            @can('update', $report)
                                <a href="{{ route('reports.edit', $report) }}" class="btn btn-warning float-right" style="margin-right: 5px;">
                                    <i class="fas fa-edit"></i> Bearbeiten
                                </a>
                            @endcan
                            
                            <button type="button" class="btn btn-default float-right" style="margin-right: 5px;" onclick="window.print()">
                                <i class="fas fa-print"></i> Drucken
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection