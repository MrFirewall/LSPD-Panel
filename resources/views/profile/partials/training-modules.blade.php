<div class="card card-outline card-primary mb-4">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold"><i class="fas fa-chalkboard-teacher mr-2 text-primary"></i> Module</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover text-nowrap mb-0">
            <thead>
                <tr>
                    <th class="pl-3">Datum</th>
                    <th>Modul</th>
                    <th>Ausbilder</th>
                </tr>
            </thead>
            <tbody>
                <!-- @php
                    $assignedModules = $trainingModules->filter(function($module) {
                        return !is_null($module->pivot->assigned_by_user_id);
                    });
                @endphp -->

                @forelse($assignedModules as $module)
                <tr>
                    <td class="pl-3 text-muted">{{ $module->pivot->created_at ? \Carbon\Carbon::parse($module->pivot->created_at)->format('d.m.Y') : '-' }}</td>
                    <td class="font-weight-bold text-white">{{ $module->name }}</td>
                    <td>
                        <span class="badge badge-dark border border-secondary">
                            {{ $module->pivot->assigner->name ?? 'System' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-3">Keine abgeschlossenen Module.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>