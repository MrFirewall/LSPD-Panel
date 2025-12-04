<div class="card card-outline card-primary mb-4">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold"><i class="fas fa-file-signature mr-2 text-primary"></i> Prüfungen</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover text-nowrap mb-0">
            <thead>
                <tr>
                    <th class="pl-3">Datum</th>
                    <th>Titel</th>
                    <th>Prüfer</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // FIX: Sicherstellen, dass $examinations eine Collection ist, falls null übergeben wird.
                    $examsCollection = $examAttempts ?? collect();

                    $passedAttempts = $examsCollection->filter(function($attempt) {
                        return $attempt->status === 'evaluated' && $attempt->exam && $attempt->score >= $attempt->exam->pass_mark;
                    });
                @endphp
                
                @forelse($examsCollection as $attempt)
                    <tr>
                        <td class="pl-3 text-muted">{{ $attempt->completed_at->format('d.m.Y') }}</td>
                        <td>
                            <strong class="text-white">{{ $attempt->exam->title ?? 'Unbekannt' }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-dark border border-secondary">
                                {{ $attempt->evaluator->name ?? 'System' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">Keine bestandenen Prüfungen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>