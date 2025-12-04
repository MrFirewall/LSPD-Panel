<div class="card card-outline card-secondary h-100">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold"><i class="fas fa-folder-open mr-2"></i> Akteneinträge</h3>
    </div>

    {{-- Formular --}}
    <div class="card-body border-bottom border-light" style="background-color: rgba(255,255,255,0.02);">
        <form action="{{ route('admin.users.records.store', $user) }}" method="POST">
            @csrf
            <h6 class="text-muted small mb-3 text-uppercase font-weight-bold ls-1">Neuen Vermerk anlegen</h6>
            
            <div class="form-group">
                <label for="type" class="small text-muted">Art des Eintrags</label>
                <select class="form-control form-control-sm select2" name="type" id="type" required style="width: 100%;">
                    <option value="Positiver Vermerk">Positiver Vermerk</option>
                    <option value="Fortbildung">Fortbildung</option>
                    <option value="Negativer Vermerk">Negativer Vermerk</option>
                    <option value="Beförderung">Beförderung</option>
                    <option value="Degradierung">Degradierung</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="content" class="small text-muted">Inhalt</label>
                <textarea name="content" id="content" class="form-control form-control-sm" rows="3" required placeholder="Details zum Eintrag..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-sm btn-primary btn-block shadow-sm">
                <i class="fas fa-save mr-1"></i> Speichern
            </button>
        </form>
    </div>

    {{-- Liste --}}
    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
        <ul class="list-group list-group-flush bg-transparent">
            @forelse($serviceRecords as $record)
                @php
                    $borderClass = match($record->type) {
                        'Positiver Vermerk', 'Beförderung' => 'border-left-success',
                        'Negativer Vermerk', 'Degradierung' => 'border-left-danger',
                        default => 'border-left-info'
                    };
                @endphp
                <li class="list-group-item bg-transparent" style="border-left: 3px solid transparent; {{ $record->type == 'Positiver Vermerk' ? 'border-left-color: #28a745;' : '' }} {{ $record->type == 'Negativer Vermerk' ? 'border-left-color: #dc3545;' : '' }}">
                    <div class="d-flex w-100 justify-content-between mb-1">
                        <strong class="{{ $record->type == 'Negativer Vermerk' ? 'text-danger' : ($record->type == 'Positiver Vermerk' ? 'text-success' : 'text-info') }}">
                            {{ $record->type }}
                        </strong>
                        <span class="text-xs text-muted">{{ $record->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <p class="text-sm mb-1 text-light" style="opacity: 0.9;">{{ $record->content }}</p>
                    <small class="text-muted d-block text-right">
                        <i class="fas fa-pen-nib mr-1 text-xs"></i> {{ $record->author->name }}
                    </small>
                </li>
            @empty
                <li class="list-group-item text-center text-muted py-4 small">
                    <i class="fas fa-folder-open mb-2 fa-2x opacity-50"></i><br>
                    Keine Einträge in der Akte.
                </li>
            @endforelse
        </ul>
    </div>
</div>