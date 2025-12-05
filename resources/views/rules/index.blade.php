@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Internes LSPD Regelwerk</h1>
        <a href="{{ route('rules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Neuen Abschnitt erstellen
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="accordion" id="rulesAccordion">
        @foreach($rules as $rule)
            <div class="card mb-2">
                <div class="card-header d-flex justify-content-between align-items-center" id="heading{{ $rule->id }}">
                    <h5 class="mb-0">
                        <button class="btn btn-link text-decoration-none text-dark fw-bold" type="button" data-toggle="collapse" data-target="#collapse{{ $rule->id }}" aria-expanded="true">
                            {{ $rule->title }}
                        </button>
                    </h5>
                    <div>
                        <a href="{{ route('rules.edit', $rule->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('rules.destroy', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Wirklich löschen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>

                <div id="collapse{{ $rule->id }}" class="collapse show" aria-labelledby="heading{{ $rule->id }}" data-parent="#rulesAccordion">
                    <div class="card-body">
                        {!! $rule->content !!}
                        
                        <hr>
                        <small class="text-muted">
                            Zuletzt bearbeitet: {{ $rule->updated_at->format('d.m.Y H:i') }} 
                            @if($rule->editor) von {{ $rule->editor->name }} @endif
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection