@extends('layouts.app')

@section('content')
<!-- Custom Styles für diesen View -->
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
    .ck.ck-editor__editable::before {
        color: #adb5bd !important;
    }

    /* --- LAYOUT FIXES --- */
    
    /* 1. Zwingt den Editor, auf einer niedrigen Ebene zu bleiben (unter dem Footer) */
    .ck.ck-editor {
        position: relative !important;
        z-index: 0 !important; 
        margin-bottom: 20px;
    }
    
    /* 2. Deaktiviert die "Sticky" Toolbar, die oft über Header/Footer rutscht */
    .ck.ck-sticky-panel__content_sticky {
        position: static !important;
        top: auto !important;
    }

    /* 3. WICHTIG: Fügt unten am Container massiv Platz hinzu, damit man weit genug scrollen kann */
    .page-bottom-spacer {
        padding-bottom: 150px !important;
    }
</style>

<div class="container page-bottom-spacer">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>{{ $rule->title ?? 'Regel' }} bearbeiten</h2>
        <a href="{{ route('rules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>
    </div>
    
    <form action="{{ route('rules.update', $rule->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group mb-3">
            <label class="form-label">Titel / Paragraph</label>
            <!-- Korrigiert: old('title') statt old('content') -->
            <input type="text" name="title" class="form-control" placeholder="z.B. §1 Allgemeine Regeln" value="{{ old('title', $rule->title ?? '') }}" required>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Reihenfolge (Sortierung)</label>
            <input type="number" name="order_index" class="form-control" value="{{ old('order_index', $rule->order_index ?? 0) }}">
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Inhalt</label>
            <!-- Wrapper um Z-Index Probleme sicher abzufangen -->
            <div style="position: relative; z-index: 0;">
                <textarea id="editor" name="content" class="form-control" rows="10">
                    {{ old('content', $rule->content ?? '') }}
                </textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> Speichern
            </button>
        </div>
    </form>
</div>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/translations/de.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        ClassicEditor
            .create(document.querySelector('#editor'), {
                language: 'de',
                toolbar: [ 
                    'heading', '|', 
                    'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                    'insertTable', 'undo', 'redo'
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
            })
            .catch(error => {
                console.error(error);
            });
    });
</script>
@endsection