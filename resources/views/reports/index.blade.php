@extends('layouts.app')

@section('title', 'Einsatzberichte')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Einsatzberichte</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('create', App\Models\Report::class)
                        <a href="{{ route('reports.create') }}" class="btn btn-primary btn-flat">
                            <i class="fas fa-plus me-1"></i> Neuen Bericht erstellen
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Suchformular -->
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search"></i> Berichtsarchiv durchsuchen</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Suche nach Titel, Patient oder Ersteller..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-info" type="submit">Suchen</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Titel</th>
                            <th>Patient</th>
                            <th>Ersteller</th>
                            <th class="text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $report->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $report->title }}</td>
                                <td>{{ $report->patient_name }}</td>
                                <td>
                                    <!-- FIX: Zugriff über rankRelation -->
                                    <span class="badge badge-secondary">{{ optional($report->user->rankRelation)->label ?? 'Officer' }}</span> 
                                    {{ $report->user->name }}
                                </td>
                                <td class="text-right">
                                    @can('view', $report)
                                        <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-default btn-flat" title="Ansehen">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    
                                    @can('update', $report)
                                        <a href="{{ route('reports.edit', $report) }}" class="btn btn-sm btn-primary btn-flat" title="Bearbeiten">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('delete', $report)
                                        <form action="{{ route('reports.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirm('Bist du sicher, dass du diesen Bericht löschen möchtest?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-flat" title="Löschen">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted p-4">Keine Berichte gefunden.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $reports->links() }}
        </div>
    </div>
@endsection