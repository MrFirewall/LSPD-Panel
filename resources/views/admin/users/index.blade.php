@extends('layouts.app')

@section('title', 'Mitarbeiter verwalten')

@section('content')

{{-- Styles für DataTables Pagination im Dark Mode --}}
<style>
    /* Pagination Container & Links */
    .dataTables_wrapper .page-item .page-link {
        background-color: #2d3748 !important; /* Card Dark Color */
        border-color: rgba(255,255,255,0.08) !important; /* Glass Border */
        color: #e2e8f0 !important; /* Text Light */
    }

    /* Aktiver Button */
    .dataTables_wrapper .page-item.active .page-link {
        background-color: #28a745 !important; /* Success Color passend zum "Erstellen" Button */
        border-color: #28a745 !important;
        color: #fff !important;
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.4);
    }

    /* Deaktivierte Buttons (Vorherige/Nächste wenn am Ende) */
    .dataTables_wrapper .page-item.disabled .page-link {
        background-color: #1a202c !important; /* Background Dark */
        color: #6c757d !important; /* Muted Text */
        border-color: rgba(255,255,255,0.05) !important;
    }

    /* Hover Status */
    .dataTables_wrapper .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #4a5568 !important; /* Lighter Dark */
        color: #fff !important;
        border-color: rgba(255,255,255,0.2) !important;
    }
</style>

{{-- 1. HERO HEADER --}}
<div class="content-header" style="background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%); color: white; padding: 2rem 1.5rem; margin-bottom: 1.5rem; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h5 class="text-uppercase font-weight-bold mb-1" style="opacity: 0.8; letter-spacing: 1px;">Personalabteilung</h5>
                <h1 class="display-4 font-weight-bold mb-0"><i class="fas fa-users-cog mr-3"></i>Mitarbeiter</h1>
            </div>
            <div class="col-sm-6 text-right">
                @can('users.create')
                    {{-- FIX: Button Farbe angepasst (Success statt Light) für besseren Kontrast --}}
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success font-weight-bold rounded-pill px-4 shadow-sm border-0">
                        <i class="fas fa-plus mr-2"></i> Mitarbeiter anlegen
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <div class="card card-outline card-primary shadow-lg border-0">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-list mr-2 text-primary"></i> Personalliste
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ count($users) }} Akten</span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <table id="usersTable" class="table table-hover table-striped nowrap w-100" style="width: 100%;">
                            <thead style="background-color: rgba(0,0,0,0.2); border-bottom: 2px solid rgba(255,255,255,0.1);">
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Personalnr.</th>
                                    <th scope="col">Mitarbeiternr.</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Gruppen</th>
                                    <th scope="col">2. Fraktion</th>
                                    <th scope="col" class="text-right no-sort no-search">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $user->avatar ?? 'https://placehold.co/40x40/6c757d/FFFFFF?text=' . substr($user->name, 0, 1) }}" 
                                                 alt="{{ $user->name }}" 
                                                 width="40" height="40" 
                                                 class="img-circle mr-3 border border-secondary elevation-1"
                                                 style="object-fit: cover;">
                                            <div>
                                                <span class="font-weight-bold d-block">{{ $user->name }}</span>
                                                <small class="text-muted">{{ $user->rankRelation->label ?? 'Kein Rang' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-monospace">{{ $user->personal_number ?? '-' }}</td>        
                                    <td class="align-middle text-monospace">{{ $user->employee_id ?? '-' }}</td>
                                    <td class="align-middle">
                                        @php
                                            $statusColors = [
                                                'Aktiv' => 'success',
                                                'Probezeit' => 'info',
                                                'Beobachtung' => 'info',
                                                'Beurlaubt' => 'warning',
                                                'Krankgeschrieben' => 'warning',
                                                'Suspendiert' => 'danger',
                                                'Ausgetreten' => 'secondary',
                                                'Bewerbungsphase' => 'light text-dark'
                                            ];
                                            $color = $statusColors[$user->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }} px-2 py-1">{{ $user->status }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex flex-wrap" style="gap: 4px;">
                                            @forelse($user->roles as $role)
                                                <span class="badge badge-primary" style="font-weight: normal;">{{ $role->label ?? ucfirst($role->name) }}</span>
                                            @empty
                                                <span class="text-muted text-xs font-italic">Keine</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @if($user->second_faction)
                                            <span class="badge badge-dark border border-secondary">{{ $user->second_faction }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right align-middle">
                                        <div class="btn-group btn-group-sm">
                                            @can('users.edit')
                                                {{-- FIX: Outline-Buttons statt btn-default für Dark Mode --}}
                                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info" title="Personalakte öffnen">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-warning" title="Bearbeiten">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                            @endcan
                                            
                                            @canImpersonate
                                                @if($user->canBeImpersonated() && $user->id !== auth()->id())
                                                    <a href="{{ route('impersonate', $user->id) }}" class="btn btn-outline-secondary" title="Als {{ $user->name }} einloggen">
                                                        <i class="fas fa-user-secret"></i>
                                                    </a>
                                                @endif
                                            @endCanImpersonate
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                    {{-- DataTables handled empty state --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        var table = $("#usersTable").DataTable({
            "language": {
                "url": "{{ asset('js/i18n/de-DE.json') }}"
            },
            "order": [[1, 'asc']], 
            "responsive": {
                details: {
                    display: DataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Details zum Mitarbeiter';
                        }
                    }),
                    renderer: DataTable.Responsive.renderer.tableAll({
                        tableClass: 'table'
                    })
                }
            },
            "autoWidth": false, 
            "paging": true,
            "ordering": true,
            "info": true,        
            "searching": true,         
            "lengthChange": true,
            "lengthMenu": [10, 25, 50, -1],
            "columnDefs": [ {
                "targets": 'no-sort',
                "orderable": false
            },
            {
                "targets": 'no-search',
                "searchable": false
            }],
            "layout": {
                bottomEnd: {
                    paging: {
                        firstLast: false
                    }
                }
            }
        });

        $(window).on('resize', function () {
            table.columns.adjust().responsive.recalc();
        });
        
        $(document).on('collapsed.lte.pushmenu shown.lte.pushmenu', function() {
            setTimeout(function(){
                table.columns.adjust().responsive.recalc();
            }, 300);
        });
    });
</script>
@endpush