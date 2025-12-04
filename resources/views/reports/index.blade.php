@extends('layouts.app')

@section('title', 'Einsatzberichte')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Einsatzberichte</h1>
            </div>
            <div class="col-sm-6">
                @can('create', App\Models\Report::class)
                    <a href="{{ route('reports.create') }}" class="btn btn-primary float-sm-right elevation-2">
                        <i class="fas fa-plus me-1"></i> Neuen Bericht erstellen
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Suchleiste als Info-Box integriert für besseres Styling -->
        <div class="card card-outline card-info collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search mr-1"></i> Filter & Suche</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="card-body" style="display: none;"> <!-- Default collapsed via CSS class but JS helps -->
                <form method="GET" action="{{ route('reports.index') }}">
                    <div class="input-group input-group-lg">
                        <input type="text" name="search" class="form-control" placeholder="Stichwort, Akten-ID, Bürger oder Beamter..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-info" type="submit">
                                <i class="fas fa-search"></i> Suchen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Haupttabelle -->
        <div class="card card-outline card-primary elevation-2">
            <div class="card-header border-0">
                <h3 class="card-title">Aktenverzeichnis</h3>
                <div class="card-tools">
                    {{ $reports->links('pagination::simple-bootstrap-4') }}
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped text-nowrap align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Datum</th>
                            <th>Titel / Einsatzstichwort</th>
                            <th>Betroffener</th>
                            <th>Ersteller</th>
                            <th class="text-right">Optionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    <span class="text-muted"><i class="far fa-clock mr-1"></i> {{ $report->created_at->format('d.m.Y H:i') }}</span>
                                </td>
                                <td>
                                    <strong>{{ $report->title }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-light" style="font-size: 0.9rem;">
                                        <i class="fas fa-user mr-1 text-secondary"></i> {{ $report->patient_name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="user-block">
                                        <span class="username ml-0" style="font-size: 1rem;">
                                            <a href="#">{{ $report->user->name }}</a>
                                        </span>
                                        <span class="description ml-0">
                                            {{ optional($report->user->rankRelation)->label ?? 'Officer' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right project-actions">
                                    @can('view', $report)
                                        <a class="btn btn-default btn-sm" href="{{ route('reports.show', $report) }}">
                                            <i class="fas fa-folder-open"></i>
                                        </a>
                                    @endcan
                                    
                                    @can('update', $report)
                                        <a class="btn btn-info btn-sm" href="{{ route('reports.edit', $report) }}">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endcan

                                    @can('delete', $report)
                                        <form action="{{ route('reports.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirm('Sicher löschen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i><br>
                                    Keine Akten gefunden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</section>
@endsection