@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@section('content')
<div class="container">
    <h2>Regel-Abschnitt Bearbeiten</h2>
    
    <form action="{{ route('rules.store') }}" method="POST">
        @csrf
        
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
            <textarea id="summernote" name="content" class="form-control">{{ $rule->content }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Speichern</button>
    </form>
</div>

<!-- Am Ende der Seite -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 300, // Höhe des Editors
            lang: 'de-DE' // Optional für Deutsch
        });
    });
</script>
@endsection