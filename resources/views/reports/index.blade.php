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
        
        <!-- Live Suchleiste (Immer sichtbar) -->
        <div class="card card-outline card-info mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('reports.index') }}">
                    <div class="row">
                        <div class="col-12">
                            <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <!-- ID für JavaScript Selector -->
                                <input type="text" 
                                       id="live-search-input"
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Live-Suche: Tippe ID (z.B. 3), Titel, Bürger oder Beamter..." 
                                       value="{{ request('search') }}"
                                       autocomplete="off">
                                <div class="input-group-append" id="search-loading" style="display: none;">
                                     <span class="input-group-text bg-white"><i class="fas fa-spinner fa-spin text-primary"></i></span>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-info" type="submit">
                                        Suchen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Haupttabelle -->
        <div class="card card-outline card-primary elevation-2" id="reports-card">
            <div class="card-header border-0">
                <h3 class="card-title">Aktenverzeichnis</h3>
                <div class="card-tools" id="pagination-top">
                    {{ $reports->links('pagination::simple-bootstrap-4') }}
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <!-- ID für JS-Targeting -->
                <table class="table table-hover table-striped text-nowrap align-middle" id="reports-table">
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
            <div class="card-footer clearfix" id="pagination-bottom">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let timer;
        const input = $('#live-search-input');
        const spinner = $('#search-loading');

        input.on('keyup', function() {
            clearTimeout(timer);
            
            const query = $(this).val();
            // Optional: Erst ab 1-2 Zeichen suchen, aber für ID suche ist auch 1 Zeichen ok
            
            spinner.show();

            timer = setTimeout(function() {
                $.ajax({
                    url: "{{ route('reports.index') }}",
                    type: "GET",
                    data: { search: query },
                    success: function(response) {
                        // Wir extrahieren die neuen Teile aus der kompletten HTML Antwort
                        const newTableBody = $(response).find('#reports-table tbody').html();
                        const newPaginationTop = $(response).find('#pagination-top').html();
                        const newPaginationBottom = $(response).find('#pagination-bottom').html();

                        $('#reports-table tbody').html(newTableBody);
                        $('#pagination-top').html(newPaginationTop);
                        $('#pagination-bottom').html(newPaginationBottom);
                        
                        spinner.hide();
                    },
                    error: function() {
                        console.error('Fehler bei der Live-Suche');
                        spinner.hide();
                    }
                });
            }, 300); // 300ms warten (Debounce)
        });
    });
</script>
@endpush