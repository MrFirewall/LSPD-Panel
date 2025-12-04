@extends('layouts.app')

@section('title', 'Mitarbeiter verwalten')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Mitarbeiter verwalten</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('users.create')
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-flat">
                            <i class="fas fa-plus"></i> Mitarbeiter anlegen
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="usersTable" class="table table-hover nowrap">
                <thead>
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
                        <td>
                            <div class="d-flex align-items-center">
                                {{-- Avatar Fallback Logik --}}
                                <img src="{{ $user->avatar ?? 'https://placehold.co/32x32/6c757d/FFFFFF?text=' . substr($user->name, 0, 1) }}" 
                                     alt="{{ $user->name }}" 
                                     width="32" height="32" 
                                     class="img-circle me-2 elevation-1">
                                <span>{{ $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->personal_number ?? '-' }}</td>        
                        <td>{{ $user->employee_id ?? '-' }}</td>
                        <td>
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
                                $color = $statusColors[$user->status] ?? 'dark';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ $user->status }}</span>
                        </td>
                        <td>
                            @forelse($user->roles as $role)
                                {{-- Wir zeigen das Label (falls vorhanden) oder den Namen --}}
                                <span class="badge bg-primary">{{ $role->label ?? ucfirst($role->name) }}</span>
                            @empty
                                <span class="badge bg-light text-dark">Keine</span>
                            @endforelse
                        </td>
                        <td>{{ $user->second_faction }}</td>
                        <td class="text-right">
                            @can('users.edit')
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-default btn-flat" data-toggle="tooltip" title="Personalakte einsehen">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary btn-flat" data-toggle="tooltip" title="Mitarbeiter bearbeiten">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @canImpersonate
                                @if($user->canBeImpersonated())
                                    <a href="{{ route('impersonate', $user->id) }}" class="btn btn-sm btn-secondary btn-flat" title="Als {{ $user->name }} einloggen">
                                        <i class="fas fa-user-secret"></i>
                                    </a>
                                @endif
                            @endCanImpersonate
                        </td>
                    </tr>
                    @empty
                    {{-- DataTables füllt das, aber für Blade lassen wir es leer --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
      $("#usersTable").DataTable({
        "language": {
            "url": "{{ asset('js/i18n/de-DE.json') }}"
        },
        "order": [[1, 'asc']] , 
        "responsive": {
            details: {
                display: DataTable.Responsive.display.modal({
                    header: function (row) {
                        var data = row.data();
                        return 'Details für ' + data[0]; // Nur Name im Modal Header
                    }
                }),
                renderer: DataTable.Responsive.renderer.tableAll({
                    tableClass: 'table'
                })
            }
        },
        "autoWidth": true,
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
    });
</script>
@endpush