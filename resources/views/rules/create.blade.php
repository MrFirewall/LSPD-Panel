@extends('layouts.app')

@section('content')
<!-- Custom Styles für diesen View (Identisch zu Edit) -->
<style>
    /* --- CKEditor 5 Dark Mode Anpassungen --- */
    .ck.ck-editor__main > .ck-editor__editable {
        background-color: #2b3035 !important;
        color: #e0e0e0 !important;
        border-color: #495057 !important;
    }
    .ck.ck-editor .ck-toolbar {
        background-color: #1c1f23 !important;
        border-color: #495057 !important;
    }
    .ck.ck-editor .ck-button {
        color: #e0e0e0 !important;
        cursor: pointer;
    }
    .ck.ck-editor .ck-button:hover,
    .ck.ck-editor .ck-button.ck-on {
        background-color: #343a40 !important;
        color: #ffffff !important;
    }
    /* Dropdowns und Listen im Darkmode */
    .ck.ck-dropdown__panel, 
    .ck.ck-list, 
    .ck.ck-list__item, 
    .ck.ck-reset_all-excluded {
        background-color: #2b3035 !important;
        border-color: #495057 !important;
        color: #e0e0e0 !important;
    }
    
    .ck.ck-list__item .ck-button {
        color: #e0e0e0 !important;
    }
    
    .ck.ck-list__item .ck-button:hover {
        background-color: #343a40 !important;
    }
    
    /* Textfarbe in Input-Feldern */
    .ck.ck-input-text {
        background-color: #343a40 !important;
        color: white !important;
        border: 1px solid #495057 !important;
    }

    .ck.ck-editor__editable::before {
        color: #adb5bd !important;
    }

    /* --- LAYOUT FIXES --- */
    .ck.ck-editor {
        position: relative !important;
        z-index: 0 !important; 
        margin-bottom: 20px;
    }
    
    .ck.ck-sticky-panel__content_sticky {
        position: static !important;
        top: auto !important;
    }

    .page-bottom-spacer {
        padding-bottom: 150px !important;
    }
</style>

<div class="container page-bottom-spacer">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Neuen Regel-Abschnitt erstellen</h2>
        <a href="{{ route('rules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>
    </div>
    
    <form action="{{ route('rules.store') }}" method="POST">
        @csrf
        
        <div class="form-group mb-3">
            <label class="form-label">Titel / Paragraph</label>
            <input type="text" name="title" class="form-control" placeholder="z.B. §1 Allgemeine Regeln" required>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Reihenfolge (Sortierung)</label>
            <input type="number" name="order_index" class="form-control" value="{{ old('order_index', 0) }}">
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Inhalt</label>
            <div style="position: relative; z-index: 0;">
                <textarea id="editor" name="content" class="form-control" rows="10"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Speichern
            </button>
        </div>
    </form>
</div>

<!-- FALLBACK-LÖSUNG: Standard Classic Editor -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/translations/de.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        ClassicEditor
            .create(document.querySelector('#editor'), {
                language: 'de',
                // Wir definieren KEINE Plugins manuell -> Der Editor lädt seine Standard-Plugins.
                
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                    'insertTable', '|',
                    'undo', 'redo'
                ],
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Absatz', class: 'ck-heading_paragraph' },
                        { model: 'heading2', view: 'h2', title: 'Überschrift 1', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Überschrift 2', class: 'ck-heading_heading3' }
                    ]
                }
            })
            .then(editor => {
                // Manuelle Höhe setzen
                editor.ui.view.editable.element.style.minHeight = '400px';
                console.log('Standard CKEditor erfolgreich geladen.');
            })
            .catch(error => {
                console.error('CKEditor Fehler:', error);
            });
    });
</script>
@endsection