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
            <input type="text" name="title" class="form-control" placeholder="z.B. §1 Allgemeine Regeln" value="{{ old('title', $rule->title ?? '') }}" required>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Reihenfolge (Sortierung)</label>
            <input type="number" name="order_index" class="form-control" value="{{ old('order_index', $rule->order_index ?? 0) }}">
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Inhalt</label>
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

<!-- FIX: Konflikte bereinigen, bevor wir laden -->
<script>
    // Wenn CKEDITOR schon existiert, aber 'Essentials' fehlt, ist es die falsche Version (Classic statt Super-Build).
    // Wir löschen sie, damit das korrekte Script geladen wird.
    if (window.CKEDITOR && typeof window.CKEDITOR.Essentials === 'undefined') {
        window.CKEDITOR = undefined;
    }
</script>

<!-- Super-Build laden -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/translations/de.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Sicherstellen, dass wir den Super-Build haben
        const LSPD_CKEDITOR = window.CKEDITOR;

        if (!LSPD_CKEDITOR || typeof LSPD_CKEDITOR.ClassicEditor === 'undefined') {
            console.error('CKEditor Super-Build konnte nicht geladen werden.');
            return;
        }

        LSPD_CKEDITOR.ClassicEditor.create(document.querySelector('#editor'), {
            language: 'de',
            
            // Plugins aus dem Super-Build Namespace
            plugins: [
                LSPD_CKEDITOR.Essentials,
                LSPD_CKEDITOR.Paragraph,
                LSPD_CKEDITOR.Autoformat,
                LSPD_CKEDITOR.Bold,
                LSPD_CKEDITOR.Italic,
                LSPD_CKEDITOR.Underline,
                LSPD_CKEDITOR.Strikethrough,
                LSPD_CKEDITOR.Code,
                LSPD_CKEDITOR.Subscript,
                LSPD_CKEDITOR.Superscript,
                LSPD_CKEDITOR.BlockQuote,
                LSPD_CKEDITOR.Heading,
                LSPD_CKEDITOR.Link,
                LSPD_CKEDITOR.List,
                LSPD_CKEDITOR.Indent,
                LSPD_CKEDITOR.IndentBlock,
                LSPD_CKEDITOR.Image,
                LSPD_CKEDITOR.ImageCaption,
                LSPD_CKEDITOR.ImageStyle,
                LSPD_CKEDITOR.ImageToolbar,
                LSPD_CKEDITOR.ImageUpload,
                LSPD_CKEDITOR.Table,
                LSPD_CKEDITOR.TableToolbar,
                LSPD_CKEDITOR.Alignment,
                LSPD_CKEDITOR.Font,
                LSPD_CKEDITOR.HorizontalLine,
                LSPD_CKEDITOR.GeneralHtmlSupport,
                LSPD_CKEDITOR.SourceEditing
            ],
            
            toolbar: {
                items: [
                    'undo', 'redo', '|',
                    'sourceEditing', '|',
                    'heading', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'bold', 'italic', 'underline', 'strikethrough', 'code', '|',
                    'link', 'blockQuote', 'insertTable', 'horizontalLine', '|',
                    'alignment', '|',
                    'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                    'removeFormat'
                ],
                shouldNotGroupWhenFull: true
            },
            
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Courier New, Courier, monospace',
                    'Georgia, serif',
                    'Lucida Sans Unicode, Lucida Grande, sans-serif',
                    'Tahoma, Geneva, sans-serif',
                    'Times New Roman, Times, serif',
                    'Trebuchet MS, Helvetica, sans-serif',
                    'Verdana, Geneva, sans-serif'
                ],
                supportAllValues: true
            },
            
            fontSize: {
                options: [10, 12, 14, 'default', 18, 20, 22],
                supportAllValues: true
            },
            
            htmlSupport: {
                allow: [
                    {
                        name: /.*/,
                        attributes: true,
                        classes: true,
                        styles: true
                    }
                ]
            }
        })
        .then(editor => {
            editor.ui.view.editable.element.style.minHeight = '400px';
        })
        .catch(error => {
            console.error('CKEditor Init Fehler:', error);
        });
    });
</script>
@endsection