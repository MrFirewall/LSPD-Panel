<div class="card card-outline card-secondary mb-3">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold text-muted"><i class="fas fa-database mr-2"></i> Metadaten</h3>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush bg-transparent">
            <li class="list-group-item d-flex justify-content-between bg-transparent border-bottom border-light">
                <span class="text-muted">Letzte Änderung</span>
                <span class="text-xs">{{ $user->last_edited_at ? \Carbon\Carbon::parse($user->last_edited_at)->format('d.m.Y H:i') : '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between bg-transparent">
                <span class="text-muted">Bearbeiter</span>
                <span class="text-xs">{{ $user->last_editor ?? 'System' }}</span>
            </li>
        </ul>
    </div>
</div>