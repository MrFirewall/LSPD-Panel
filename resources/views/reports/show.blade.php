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
                <div class="invoice p-3 mb-3">
                    <!-- title row -->
                    <div class="row">
                        <div class="col-12">
                            <h4>
                                <i class="fas fa-gavel"></i> LSPD Bericht: {{ $report->title }}
                                <small class="float-right">Datum: {{ $report->created_at->format('d.m.Y H:i') }}</small>
                            </h4>
                        </div>
                    </div>
                    
                    <!-- info row -->
                    <div class="row invoice-info mt-4">
                        <div class="col-sm-4 invoice-col">
                            Verantwortlicher Beamter
                            <address>
                                <strong>{{ $report->user->name }}</strong><br>
                                Rang: Officer<br> <!-- Beispiel -->
                                Dienstnummer: {{ $report->user->id }}
                            </address>
                        </div>
                        <div class="col-sm-4 invoice-col">
                            Betroffene Person / TV
                            <address>
                                <strong>{{ $report->patient_name }}</strong><br>
                                @if($report->citizen)
                                    Status: Registrierter Bürger<br>
                                @else
                                    Status: Unbekannt / Nicht registriert<br>
                                @endif
                            </address>
                        </div>
                        <div class="col-sm-4 invoice-col">
                            <b>Einsatzort:</b> {{ $report->location }}<br>
                            <b>Beteiligte Einheiten:</b><br>
                            @foreach($report->attendingStaff as $staff)
                                <span class="badge badge-info">{{ $staff->name }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Description Row -->
                    <div class="row mt-4">
                        <div class="col-6">
                            <p class="lead">Vorfallhergang:</p>
                            <div class="text-muted well well-sm shadow-none" style="margin-top: 10px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                {!! nl2br(e($report->incident_description)) !!}
                            </div>
                        </div>
                        <div class="col-6">
                            <p class="lead">Maßnahmen:</p>
                            <div class="text-muted well well-sm shadow-none" style="margin-top: 10px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                {!! nl2br(e($report->actions_taken)) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Fines Table -->
                    <div class="row mt-4">
                        <div class="col-12 table-responsive">
                            <p class="lead">Strafbestand</p>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tatbestand</th>
                                        <th>Haftzeit (HE)</th>
                                        <th>Bemerkung</th>
                                        <th>Betrag</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($report->fines as $fine)
                                        <tr>
                                            <td>{{ $fine->offense }}</td>
                                            <td>{{ $fine->jail_time }} HE</td>
                                            <td>{{ $fine->remark }}</td>
                                            <td>{{ number_format($fine->amount, 0, ',', '.') }} €</td>
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
                        <!-- accepted payments column -->
                        <div class="col-6">
                            <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                                Haftzeit-Hinweis: 1 HE entspricht 1 Minute im Bundesgefängnis.
                            </p>
                        </div>
                        <!-- totals -->
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

                    <!-- buttons -->
                    <div class="row no-print mt-4">
                        <div class="col-12">
                            <form action="{{ route('reports.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirm('Wirklich löschen?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger float-right" style="margin-right: 5px;">
                                    <i class="fas fa-trash"></i> Löschen
                                </button>
                            </form>
                            <a href="{{ route('reports.edit', $report) }}" class="btn btn-warning float-right" style="margin-right: 5px;">
                                <i class="fas fa-edit"></i> Bearbeiten
                            </a>
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