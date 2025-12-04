<div class="card card-outline card-secondary mb-3">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold text-muted"><i class="fas fa-id-card mr-2"></i> Stammdaten</h3>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush bg-transparent">
            
            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">Personalnr.</span>
                <span class="font-weight-bold">{{ $user->personal_number ?? '-' }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">Status</span>
                <span>
                    @php
                        $statusClass = match ($user->status) {
                            'Aktiv' => 'bg-success',
                            'Probezeit', 'Beobachtung' => 'bg-info',
                            'Beurlaubt', 'Krankgeschrieben' => 'bg-warning',
                            'Suspendiert' => 'bg-danger',
                            'Ausgetreten' => 'bg-secondary',
                            'Bewerbungsphase' => 'bg-light text-dark',
                            default => 'bg-dark'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} px-2 py-1">{{ $user->status }}</span>
                </span>
            </li>

            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">Mitarbeiter ID</span>
                <span class="font-weight-bold text-monospace">{{ $user->employee_id ?? '-' }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">CFX.re ID</span>
                <span class="text-truncate" style="max-width: 150px;" title="{{ $user->cfx_id }}">{{ $user->cfx_id }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">E-Mail</span>
                <span>{{ $user->email ?? '-' }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">Geburtstag</span>
                <span>{{ $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('d.m.Y') : '-' }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">Discord</span>
                <span>{{ $user->discord_name ?? '-' }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">Forum</span>
                <span>{{ $user->forum_name ?? '-' }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between bg-transparent">
                <span class="text-muted">Einstellung</span>
                <span>{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('d.m.Y') : '-' }}</span>
            </li>

        </ul>
    </div>
</div>