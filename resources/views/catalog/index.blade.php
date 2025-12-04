@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Bußgeldkatalog (BBuG)</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        
        <!-- Accordion for Categories -->
        <div id="accordion">
            @foreach($categories as $section => $fines)
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h4 class="card-title w-100">
                            <a class="d-block w-100" data-toggle="collapse" href="#collapse{{ Str::slug($section) }}">
                                {{ $section }}
                            </a>
                        </h4>
                    </div>
                    <div id="collapse{{ Str::slug($section) }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-parent="#accordion">
                        <div class="card-body p-0">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Tatbestand</th>
                                        <th style="width: 150px">Bußgeld</th>
                                        <th style="width: 100px">Haft (HE)</th>
                                        <th style="width: 80px">Punkte</th>
                                        <th>Bemerkung</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fines as $fine)
                                        <tr>
                                            <td>{{ $fine->offense }}</td>
                                            <td class="text-danger font-weight-bold">{{ number_format($fine->amount, 0, ',', '.') }} €</td>
                                            <td>
                                                @if($fine->jail_time > 0)
                                                    <span class="badge badge-warning">{{ $fine->jail_time }} HE</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($fine->points > 0)
                                                    <span class="badge badge-danger">{{ $fine->points }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td><small>{{ $fine->remark }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endsection