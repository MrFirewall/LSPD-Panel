@extends('layouts.app')

@section('title', 'Regelwerk Editor')

@section('content')
<!-- Custom Styles für CKEditor im Dark Mode & Dashboard Look -->
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
    /* Dropdowns und Listen */
    .ck.ck-dropdown__panel, 
    .ck.ck-list, 
    .ck.ck-list__item, 
    .ck.ck-reset_all-excluded {
        background-color: #2b3035 !important;
        border-color: #495057 !important;
        color: #e0e0e0 !important;
    }
    .ck.ck-list__item .ck-button { color: #e0e0e0 !important; }
    .ck.ck-list__item .ck-button:hover { background-color: #343a40 !important; }
    
    .ck.ck-input-text {
        background-color: #343a40 !important;
        color: white !important;
        border: 1px solid #495057 !important;
    }
    .ck.ck-editor__editable::before { color: #adb5bd !important; }

    /* Layout Fixes */
    .ck.ck-editor {
        position: relative !important;
        z-index: 0 !important; 
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    /* Input Styling passend zum Dashboard */
    .form-control-lg {
        background-color: rgba(255,255,255,0.05) !important;
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff !important;
    }
    .form-control-lg:focus {
        background-color: rgba(255,255,255,0.1) !important;
        border-color: #4b6cb7;
        box-shadow: none;
    }
    .input-group-text {
        background-color: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
    }
</style>

<!-- 1. Hero Section (Dashboard Style) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold mb-0">
                    <i class="fas fa-book-open mr-2" style="opacity: 0.6;"></i> Regelwerk
                </h1>
                <p class="lead mb-0 mt-2" style="opacity: 0.9;">
                    Neuen Abschnitt erstellen &bull; <span class="text-white-50">Interne Verwaltung</span>
                </p>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('rules.index') }}" class="btn btn-outline-light rounded-pill px-4 font-weight-bold shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Zurück zur Übersicht
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 2. Main Content -->
<section class="content pb-5">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <!-- Editor Card -->
                <div class="card border-0 shadow-lg" style="background-color: #2d3748;">
                    
                    <!-- Dekorativer Header-Streifen (Passend zum 'Meine Berichte' Gradient) -->
                    <div class="card-header border-0" style="background: linear-gradient(45deg, #4b6cb7 0%, #182848 100%); padding: 1.5rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold text-white mb-0">
                                <i class="fas fa-edit mr-2"></i> Editor
                            </h3>
                            <!-- Kleines Deko-Icon im Hintergrund -->
                            <div style="position: absolute; right: 20px; top: 10px; font-size: 3rem; opacity: 0.1; transform: rotate(15deg); color: white;">
                                <i class="fas fa-paragraph"></i>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('rules.store') }}" method="POST">
                        @csrf
                        <div class="card-body p-4">
                            
                            <div class="row">
                                <!-- Titel Input -->
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="text-uppercase font-weight-bold small text-muted" style="letter-spacing: 1px;">Titel / Paragraph <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                            </div>
                                            <input type="text" name="title" class="form-control form-control-lg" placeholder="z.B. §1 Allgemeine Regeln" value="{{ old('title') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sortierung Input -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="text-uppercase font-weight-bold small text-muted" style="letter-spacing: 1px;">Reihenfolge</label>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-sort-numeric-down"></i></span>
                                            </div>
                                            <input type="number" name="order_index" class="form-control form-control-lg" value="{{ old('order_index', 0) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label class="text-uppercase font-weight-bold small text-muted" style="letter-spacing: 1px;">Inhalt des Paragraphen <span class="text-danger">*</span></label>
                                <div style="position: relative; z-index: 0;">
                                    <textarea id="editor" name="content" class="form-control" rows="15">{{ old('content') }}</textarea>
                                </div>
                            </div>

                        </div>
                        
                        <div class="card-footer border-top border-secondary p-4 bg-transparent text-right">
                            <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 font-weight-bold shadow" style="background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%); border: none;">
                                <i class="fas fa-save mr-2"></i> SPEICHERN
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- FALLBACK-LÖSUNG: Standard Classic Editor (Stabil) -->
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
                editor.ui.view.editable.element.style.minHeight = '400px';
                console.log('CKEditor loaded.');
            })
            .catch(error => {
                console.error('CKEditor Error:', error);
            });
    });
</script>
@endsection