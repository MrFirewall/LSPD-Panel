@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Neuen Regel-Abschnitt erstellen</h2>
    
    <form action="{{ route('rules.update', $rule->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label>Titel / Paragraph</label>
            <input type="text" name="title" class="form-control" placeholder="z.B. §1 Allgemeine Regeln" value="{{ $rule->title }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Reihenfolge (Sortierung)</label>
            <input type="number" name="order_index" class="form-control" value="0">
        </div>

        <div class="form-group mb-3">
            <label>Inhalt</label>
            <textarea id="ruleEditor" name="content" class="form-control" rows="10">{{ $rule->content }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Speichern</button>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#ruleEditor',
    plugins: 'lists link table code preview',
    toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link'
  });
</script>
@endsection