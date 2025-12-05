@extends('layouts.app')
<style>
    /* --- CKEditor 5 Dark Mode Anpassungen --- */

    /* 1. Das Haupt-Eingabefeld dunkel machen */
    .ck.ck-editor__main > .ck-editor__editable {
        background-color: #2b3035 !important; /* Dunkelgrau (passend zu Bootstrap Dark) */
        color: #e0e0e0 !important; /* Helle Schrift */
        border-color: #495057 !important;
    }

    /* 2. Die Toolbar oben dunkel machen */
    .ck.ck-editor .ck-toolbar {
        background-color: #1c1f23 !important;
        border-color: #495057 !important;
    }

    /* 3. Die Buttons in der Toolbar anpassen */
    .ck.ck-editor .ck-button {
        color: #e0e0e0 !important; /* Icon Farbe */
        cursor: pointer;
    }

    /* Hover-Effekt für Buttons */
    .ck.ck-editor .ck-button:hover,
    .ck.ck-editor .ck-button.ck-on {
        background-color: #343a40 !important;
        color: #ffffff !important;
    }

    /* 4. Dropdowns (z.B. für Überschriften) dunkel machen */
    .ck.ck-dropdown__panel {
        background-color: #2b3035 !important;
        border-color: #495057 !important;
    }
    
    .ck.ck-list__item .ck-button {
        color: #e0e0e0 !important;
    }
    
    .ck.ck-list__item .ck-button:hover {
        background-color: #343a40 !important;
    }

    /* Damit der Platzhalter-Text nicht verschwindet */
    .ck.ck-editor__editable::before {
        color: #adb5bd !important;
    }
</style>
@section('content')
<div class="container">
    <h2>{{ old('content', $rule->title ?? 'Regel') }} bearbeiten</h2>
    
    <form action="{{ route('rules.update', $rule->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label>Titel / Paragraph</label>
            <input type="text" name="title" class="form-control" placeholder="z.B. §1 Allgemeine Regeln" value="{{ old('content', $rule->title ?? '') }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Reihenfolge (Sortierung)</label>
            <input type="number" name="order_index" class="form-control" value="0">
        </div>

        <div class="form-group mb-3">
            <label>Inhalt</label>
            <textarea id="editor" name="content" class="form-control" rows="10">
                {{ old('content', $rule->content ?? '') }}
            </textarea>
        </div>

        <button type="submit" class="btn btn-success">Speichern</button>
    </form>
</div>

<!-- CKEditor 5 CDN (Kein Key notwendig) -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

<!-- Sprache Deutsch (Optional, falls gewünscht) -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/translations/de.js"></script>

<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            language: 'de', // Stellt die Oberfläche auf Deutsch
            toolbar: [ 
                'heading', '|', 
                'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                'insertTable', 'undo', 'redo'
            ],
            // Optional: Anpassen, welche Überschriften erlaubt sind
            heading: {
                options: [
                    { model: 'paragraph', title: 'Absatz', class: 'ck-heading_paragraph' },
                    { model: 'heading2', view: 'h2', title: 'Überschrift 1', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Überschrift 2', class: 'ck-heading_heading3' }
                ]
            }
        })
        .then(editor => {
            // Passt die Höhe an, damit es nicht so klein ist
            editor.ui.view.editable.element.style.minHeight = '300px';
        })
        .catch(error => {
            console.error(error);
        });
</script>

<!-- Kleines CSS für den Darkmode oder bessere Optik (Optional) -->
<style>
    .ck-editor__editable {
        min-height: 300px;
    }
</style>
@endsection