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

<!-- WICHTIG: Nutze exakt diesen Link für den Super-Build -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/translations/de.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        CKEDITOR.ClassicEditor.create(document.querySelector('#editor'), {
            language: 'de',
            
            // Hier war der Fehler: builtinPlugins entfernt, Essentials & Paragraph hinzugefügt
            plugins: [
                CKEDITOR.Essentials,
                CKEDITOR.Paragraph,
                CKEDITOR.Autoformat,
                CKEDITOR.Bold,
                CKEDITOR.Italic,
                CKEDITOR.Underline,
                CKEDITOR.Strikethrough,
                CKEDITOR.Code,
                CKEDITOR.Subscript,
                CKEDITOR.Superscript,
                CKEDITOR.BlockQuote,
                CKEDITOR.Heading,
                CKEDITOR.Link,
                CKEDITOR.List,
                CKEDITOR.Indent,
                CKEDITOR.IndentBlock,
                CKEDITOR.Image,
                CKEDITOR.ImageCaption,
                CKEDITOR.ImageStyle,
                CKEDITOR.ImageToolbar,
                CKEDITOR.ImageUpload,
                CKEDITOR.Table,
                CKEDITOR.TableToolbar,
                CKEDITOR.Alignment,
                CKEDITOR.Font,
                CKEDITOR.HorizontalLine,
                CKEDITOR.GeneralHtmlSupport,
                CKEDITOR.SourceEditing
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
            console.error(error);
        });
    });
</script>
@endsection