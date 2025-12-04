<div class="card card-outline card-info mb-4">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold"><i class="fas fa-star mr-2 text-warning"></i> Bewertungen</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover text-nowrap mb-0">
            <thead>
                <tr>
                    <th class="pl-3">Kategorie</th>
                    <th class="text-center">Erhalten</th>
                    <th class="text-center">Verfasst</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $labels = ['azubi' => 'Azubi', 'leitstelle' => 'Leitstelle', 'mitarbeiter' => 'Mitarbeiter', 'praktikant' => 'Praktikant'];
                @endphp
                @foreach($labels as $type => $label)
                    <tr>
                        <td class="pl-3 font-weight-bold text-muted">{{ $label }}</td>
                        <td class="text-center">
                            @if(($evaluationCounts['erhalten'][$type] ?? 0) > 0)
                                <span class="badge badge-success">{{ $evaluationCounts['erhalten'][$type] }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(($evaluationCounts['verfasst'][$type] ?? 0) > 0)
                                <span class="badge badge-info">{{ $evaluationCounts['verfasst'][$type] }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>