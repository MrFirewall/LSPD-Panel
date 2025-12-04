<div class="card card-outline card-warning mb-4">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold"><i class="fas fa-plane mr-2 text-warning"></i> Urlaub & Abwesenheit</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover text-nowrap mb-0">
            <thead>
                <tr>
                    <th class="pl-3">Zeitraum</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vacations as $vacation)
                    <tr>
                        <td class="pl-3">
                            {{ $vacation->start_date ? \Carbon\Carbon::parse($vacation->start_date)->format('d.m.Y') : '?' }} 
                            <i class="fas fa-arrow-right mx-1 text-xs text-muted"></i>
                            {{ $vacation->end_date ? \Carbon\Carbon::parse($vacation->end_date)->format('d.m.Y') : '?' }}
                        </td>
                        <td>
                            @if($vacation->status === 'approved')
                                <span class="badge badge-success">Genehmigt</span>
                            @elseif($vacation->status === 'pending')
                                <span class="badge badge-warning">Offen</span>
                            @else
                                <span class="badge badge-danger">Abgelehnt</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-3">Keine Einträge vorhanden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>