@extends('layouts.app')

@section('title', 'Benachrichtigungen')

@push('styles')
<style>
    /* Überschreibt DataTables Styles für saubereren Look */
    table.dataTable.no-footer { border-bottom: none; }
    .mailbox-messages tr td { vertical-align: middle; }
    
    /* Gelesen/Ungelesen Styling */
    .msg-unread { background-color: #fff; font-weight: 600; }
    .msg-read { background-color: #f9f9f9; opacity: 0.9; }

    /* Detail-Liste unter dem Text */
    .notification-details { font-size: 0.85em; margin-top: 4px; color: #666; }
    .notification-details ul { list-style: none; padding-left: 0; margin-bottom: 0; }
    .notification-details li::before { content: "•"; color: #007bff; display: inline-block; width: 1em; margin-left: -1em; }
</style>
@endpush

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Benachrichtigungen</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Benachrichtigungen</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        
        {{-- LINKE SPALTE (Navigation) --}}
        <div class="col-md-3">
            <a href="{{ route('notifications.index') }}" class="btn btn-primary btn-block mb-3">Aktualisieren</a>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ordner</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="#" class="nav-link active" id="filter-all">
                                <i class="fas fa-inbox"></i> Alle
                                <span class="badge bg-secondary float-right">{{ $totalCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" id="filter-unread">
                                <i class="far fa-envelope"></i> Ungelesene
                                <span class="badge bg-primary float-right">{{ $unreadCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" id="filter-read">
                                <i class="far fa-envelope-open"></i> Gelesene
                                <span class="badge bg-light text-dark float-right">{{ $readCount }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Schnellaktionen</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link text-left text-success" {{ $unreadCount == 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-check-double"></i> Alle als gelesen markieren
                                </button>
                            </form>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('notifications.clearRead') }}" method="POST" onsubmit="return confirm('Wirklich alle gelesenen löschen?');">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link text-left text-danger" {{ $readCount == 0 ? 'disabled' : '' }}>
                                    <i class="far fa-trash-alt"></i> Gelesene löschen
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- RECHTE SPALTE (Liste) --}}
        <div class="col-md-9">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Benachrichtigungen</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="customSearchInput" placeholder="Suchen...">
                            <div class="input-group-append">
                                <div class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="mailbox-controls">
                        <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm" id="bulk-destroy" title="Löschen"><i class="far fa-trash-alt"></i></button>
                            <button type="button" class="btn btn-default btn-sm" id="bulk-mark-read" title="Als gelesen markieren"><i class="far fa-envelope-open"></i></button>
                        </div>
                        <button type="button" class="btn btn-default btn-sm" onclick="window.location.reload()"><i class="fas fa-sync-alt"></i></button>
                        
                        {{-- DataTables Info Platzhalter --}}
                        <div class="float-right text-muted text-sm" id="dt-info-box"></div>
                    </div>

                    <div class="table-responsive mailbox-messages">
                        <table id="notificationsTable" class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th class="no-sort" style="width: 40px;"></th> <th class="d-none">Status</th> <th style="width: 200px;">Von</th>
                                    <th>Nachricht</th>
                                    <th>Zeit</th>
                                    <th class="no-sort text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allNotifications as $n)
                                    @php
                                        // Text Parsen
                                        $fullText = $n->data['text'] ?? '';
                                        $parts = str_contains($fullText, 'Änderungen: ') ? explode('Änderungen: ', $fullText, 2) : [$fullText, null];
                                        $mainText = $parts[0];
                                        $changes = $parts[1] ? explode('. ', $parts[1]) : [];
                                        
                                        // Klassen für Gelesen/Ungelesen
                                        $rowClass = $n->read_at ? 'msg-read' : 'msg-unread';
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td>
                                            <div class="icheck-primary">
                                                <input type="checkbox" class="row-checkbox" value="{{ $n->id }}" id="check_{{ $n->id }}">
                                                <label for="check_{{ $n->id }}"></label>
                                            </div>
                                        </td>
                                        <td class="d-none">{{ $n->read_at ? 'Gelesen' : 'Neu' }}</td>
                                        
                                        <td class="mailbox-name">
                                            <a href="{{ $n->data['url'] ?? '#' }}" class="text-dark">
                                                <i class="{{ $n->data['icon'] ?? 'fas fa-info' }} mr-1"></i> System
                                            </a>
                                        </td>
                                        
                                        <td class="mailbox-subject">
                                            <a href="{{ $n->data['url'] ?? '#' }}" style="display: block; text-decoration: none;">
                                                <b>{{ Str::limit(strip_tags($mainText), 50) }}</b> - 
                                                <span class="text-muted">{{ Str::limit(strip_tags($mainText), 100) }}</span>
                                                
                                                @if(!empty($changes))
                                                    <div class="notification-details">
                                                        <i class="fas fa-angle-right text-xs mr-1"></i> Details: {{ implode(', ', array_map(fn($c) => rtrim($c, '.'), $changes)) }}
                                                    </div>
                                                @endif
                                            </a>
                                        </td>
                                        
                                        <td class="mailbox-date" data-order="{{ $n->created_at->timestamp }}">
                                            {{ $n->created_at->diffForHumans() }}
                                        </td>
                                        
                                        <td class="text-right">
                                            <form action="{{ route('notifications.destroy', $n->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-default btn-sm"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer p-0">
                     {{-- Paginierung Platzhalter --}}
                     <div class="mailbox-controls">
                        <div class="float-right" id="dt-paginate-box"></div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    // DataTables Initialisierung
    var table = $('#notificationsTable').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[ 4, "desc" ]],        
        "language": { "url": "{{ asset('js/i18n/de-DE.json') }}"},
        "columnDefs": [
            { "orderable": false, "targets": [0, 5] } // Checkbox & Action nicht sortierbar
        ],
        // Verschiebe DT Elemente in AdminLTE Layout
        "dom": 'rt<"bottom">', 
        "initComplete": function() {
            // Paginierung verschieben
            $("#notificationsTable_paginate").appendTo("#dt-paginate-box");
            $("#notificationsTable_info").appendTo("#dt-info-box");
        }
    });

    // Custom Suche
    $('#customSearchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Filter Navigation (Links)
    $('.nav-pills a[id^="filter-"]').on('click', function(e) {
        e.preventDefault();
        $('.nav-pills a').removeClass('active');
        $(this).addClass('active');

        var val = '';
        if(this.id === 'filter-unread') val = 'Neu';
        if(this.id === 'filter-read') val = 'Gelesen';

        // Filter auf Spalte 1 (versteckter Status)
        table.column(1).search(val ? '^'+val+'$' : '', true, false).draw();
    });

    // Checkbox Toggle (AdminLTE Style)
    $('.checkbox-toggle').click(function () {
        var clicks = $(this).data('clicks');
        if (clicks) {
            // Uncheck all
            $('.mailbox-messages input[type=\'checkbox\']').prop('checked', false);
            $('.checkbox-toggle .far.fa-check-square').removeClass('fa-check-square').addClass('fa-square');
        } else {
            // Check all
            $('.mailbox-messages input[type=\'checkbox\']').prop('checked', true);
            $('.checkbox-toggle .far.fa-square').removeClass('fa-square').addClass('fa-check-square');
        }
        $(this).data('clicks', !clicks);
    });

    // Bulk Action Logic
    function runBulkAction(action) {
        var ids = [];
        $('.row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if(ids.length === 0) {
            alert('Bitte wählen Sie mindestens eine Nachricht aus.');
            return;
        }

        if(!confirm('Aktion für ' + ids.length + ' Elemente ausführen?')) return;

        var url = (action === 'destroy') 
            ? '{{ route('notifications.bulkDestroy') }}' 
            : '{{ route('notifications.bulkMarkRead') }}';

        // Erstelle temporäres Formular
        var form = $('<form action="' + url + '" method="POST"></form>');
        form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
        $.each(ids, function(i, val) {
            form.append('<input type="hidden" name="notification_ids[]" value="' + val + '">');
        });
        $('body').append(form);
        form.submit();
    }

    $('#bulk-destroy').click(function() { runBulkAction('destroy'); });
    $('#bulk-mark-read').click(function() { runBulkAction('mark_read'); });
});
</script>
@endpush