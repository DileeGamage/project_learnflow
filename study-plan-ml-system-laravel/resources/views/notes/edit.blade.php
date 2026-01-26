@extends('layouts.app')

@section('title', 'Edit Note')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="fas fa-edit text-primary"></i> Edit Note: {{ $note->title }}
        </h1>
        <div class="text-muted">
            <span class="badge bg-primary me-2">{{ $note->subject_area }}</span>
            <small>
                <i class="fas fa-clock"></i> Last updated {{ $note->updated_at->format('M j, Y') }}
            </small>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('notes.show', $note) }}" class="btn btn-outline-secondary">
            <i class="fas fa-eye"></i> View Note
        </a>
        <a href="{{ route('notes.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Notes
        </a>
    </div>
</div>

<!-- Show errors -->
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Note Type Info -->
@if($note->is_pdf_note)
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="fas fa-file-pdf fa-2x me-3 text-danger"></i>
        <div>
            <h6 class="mb-1">PDF Note</h6>
            <p class="mb-0 small">This note was created from a PDF file. You can edit the extracted text content below.</p>
            @if($note->pdf_path)
                <a href="{{ asset('storage/' . $note->pdf_path) }}" target="_blank" class="btn btn-sm btn-outline-info mt-2">
                    <i class="fas fa-external-link-alt"></i> View Original PDF
                </a>
            @endif
        </div>
    </div>
@endif

<!-- Edit Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-sticky-note me-2"></i>
                    {{ $note->is_pdf_note ? 'Edit PDF Note Content' : 'Edit Note Content' }}
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('notes.update', $note) }}" method="POST" id="editNoteForm">
                    @csrf
                    @method('PUT')

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">
                            <i class="fas fa-heading text-primary"></i> Note Title *
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $note->getEditableTitle()) }}" 
                               required 
                               placeholder="Enter a descriptive title for your note">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subject Area -->
                    <div class="mb-4">
                        <label for="subject_area" class="form-label fw-bold">
                            <i class="fas fa-book text-primary"></i> Subject Area *
                        </label>
                        <select class="form-select @error('subject_area') is-invalid @enderror" 
                                id="subject_area" 
                                name="subject_area" 
                                required>
                            <option value="">Select a subject</option>
                            <option value="Mathematics" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                            <option value="Science" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Science' ? 'selected' : '' }}>Science</option>
                            <option value="English" {{ old('subject_area', $note->getEditableSubjectArea()) == 'English' ? 'selected' : '' }}>English</option>
                            <option value="History" {{ old('subject_area', $note->getEditableSubjectArea()) == 'History' ? 'selected' : '' }}>History</option>
                            <option value="Programming" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Programming' ? 'selected' : '' }}>Programming</option>
                            <option value="Physics" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Physics' ? 'selected' : '' }}>Physics</option>
                            <option value="Chemistry" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Chemistry' ? 'selected' : '' }}>Chemistry</option>
                            <option value="Biology" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Biology' ? 'selected' : '' }}>Biology</option>
                            <option value="Geography" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Geography' ? 'selected' : '' }}>Geography</option>
                            <option value="Economics" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Economics' ? 'selected' : '' }}>Economics</option>
                            <option value="Other" {{ old('subject_area', $note->getEditableSubjectArea()) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('subject_area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tags -->
                    <div class="mb-4">
                        <label for="tags" class="form-label fw-bold">
                            <i class="fas fa-tags text-primary"></i> Tags
                        </label>
                        <input type="text" 
                               class="form-control @error('tags') is-invalid @enderror" 
                               id="tags" 
                               name="tags" 
                               value="{{ old('tags', $note->getEditableTags() ? implode(', ', $note->getEditableTags()) : '') }}" 
                               placeholder="Enter tags separated by commas (e.g., algebra, equations, formulas)">
                        <div class="form-text">Add relevant tags to help organize and find your notes easily.</div>
                        @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Content Editor -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-bold">
                            <i class="fas fa-edit text-primary"></i> Note Content *
                        </label>
                        @if($note->is_pdf_note)
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> This content was extracted from a PDF. You can edit and format it as needed.
                            </div>
                            
                            <!-- Content Preview for PDF Notes -->
                            @if($note->hasStructuredContent())
                                <div class="card mb-3 border-info">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-info">
                                            <i class="fas fa-eye"></i> Structured Content Preview
                                        </h6>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="contentView" id="previewView" autocomplete="off">
                                            <label class="btn btn-outline-info" for="previewView">
                                                <i class="fas fa-eye"></i> Preview
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="contentView" id="editorView" autocomplete="off" checked>
                                            <label class="btn btn-outline-primary" for="editorView">
                                                <i class="fas fa-edit"></i> Editor
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body" id="contentPreview" style="max-height: 400px; overflow-y: auto;">
                                        {!! $note->getFormattedStructuredContent() !!}
                                    </div>
                                </div>
                            @endif
                        @endif
                        
                        <div id="editorContainer" style="display: none;">
                            <textarea id="content" 
                                      name="content" 
                                      class="form-control @error('content') is-invalid @enderror"
                                      rows="20">{{ old('content', $note->is_pdf_note && $note->hasStructuredContent() ? $note->getFormattedStructuredContent() : $note->getEditableContent()) }}</textarea>
                            
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Change Summary -->
                    <div class="mb-4">
                        <label for="change_summary" class="form-label fw-bold">
                            <i class="fas fa-comment text-primary"></i> Change Summary
                        </label>
                        <input type="text" 
                               class="form-control @error('change_summary') is-invalid @enderror" 
                               id="change_summary" 
                               name="change_summary" 
                               value="{{ old('change_summary') }}" 
                               placeholder="Briefly describe what you changed (optional)">
                        <div class="form-text">This will help you remember what was changed in this version.</div>
                        @error('change_summary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> 
                                Last updated: {{ $note->updated_at->format('M j, Y \a\t g:i A') }}
                            </small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="window.history.back()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="fas fa-save"></i> Update Note
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Note Info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Note Information</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Subject:</dt>
                                <dd class="col-sm-7">{{ $note->subject_area }}</dd>
                                
                                <dt class="col-sm-5">Type:</dt>
                                <dd class="col-sm-7">
                                    @if($note->is_pdf_note)
                                        <span class="badge bg-warning text-dark">PDF Note</span>
                                    @else
                                        <span class="badge bg-success">Text Note</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-5">Word Count:</dt>
                                <dd class="col-sm-7" id="wordCount">{{ str_word_count(strip_tags($note->content)) }}</dd>
                                
                                <dt class="col-sm-5">Characters:</dt>
                                <dd class="col-sm-7" id="charCount">{{ strlen(strip_tags($note->content)) }}</dd>
                                
                                <dt class="col-sm-5">Created:</dt>
                                <dd class="col-sm-7">{{ $note->created_at->format('M j, Y g:i A') }}</dd>
                                
                                <dt class="col-sm-5">Last Updated:</dt>
                                <dd class="col-sm-7">{{ $note->updated_at->format('M j, Y g:i A') }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertTemplate('lecture')">
                                    <i class="fas fa-chalkboard-teacher"></i> Insert Lecture Template
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="insertTemplate('meeting')">
                                    <i class="fas fa-users"></i> Insert Meeting Template
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="insertTemplate('research')">
                                    <i class="fas fa-search"></i> Insert Research Template
                                </button>
                                <hr>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="previewNote()">
                                    <i class="fas fa-eye"></i> Preview Changes
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Version History -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-history"></i> Version History</h6>
                            @if($versions->count() > 0)
                                <span class="badge bg-info">{{ $versions->count() }} versions</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($versions->count() > 0)
                                <div class="version-list" style="max-height: 400px; overflow-y: auto;">
                                    @foreach($versions as $version)
                                        <div class="version-item border-bottom pb-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <span class="badge bg-secondary me-2">v{{ $version->version_number }}</span>
                                                        {{ Str::limit($version->title, 20) }}
                                                    </h6>
                                                    @if($version->change_summary)
                                                        <p class="text-muted small mb-1">
                                                            <i class="fas fa-comment"></i> {{ $version->change_summary }}
                                                        </p>
                                                    @endif
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i> {{ $version->created_at->format('M j, Y g:i A') }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-chart-bar"></i> {{ $version->word_count }} words, {{ $version->character_count }} chars
                                                    </small>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="javascript:void(0)" onclick="previewVersion({{ $version->id }})">
                                                                <i class="fas fa-eye"></i> Preview
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="javascript:void(0)" onclick="loadVersion({{ $version->id }})">
                                                                <i class="fas fa-upload"></i> Load to Editor
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="javascript:void(0)" 
                                                               onclick="restoreVersion({{ $version->id }})"
                                                               title="This will save current content as a new version and restore this version">
                                                                <i class="fas fa-undo"></i> Restore Version
                                                            </a>
                                                        </li>
                                                        @if($versions->count() > 1)
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0)" 
                                                                   onclick="removeVersion({{ $version->id }}, {{ $version->version_number }})"
                                                                   title="Permanently delete this version from the database">
                                                                    <i class="fas fa-trash"></i> Remove Version
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-history fa-2x mb-3 opacity-50"></i>
                                    <p class="mb-0">No previous versions yet.</p>
                                    <small>Versions will appear here after you make edits.</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Editor Tips -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Editor Tips</h6>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">
                                <ul class="list-unstyled mb-0">
                                    <li><i class="fas fa-keyboard text-primary"></i> <strong>Ctrl+S:</strong> Save note</li>
                                    <li><i class="fas fa-expand text-info"></i> <strong>F11:</strong> Fullscreen mode</li>
                                    <li><i class="fas fa-save text-success"></i> Auto-saves every 3 seconds</li>
                                    <li><i class="fas fa-history text-warning"></i> Previous versions preserved</li>
                                </ul>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
@endsection

@section('scripts')
<!-- TinyMCE Editor -->
<script src="{{ asset('user_assets/lib/tinymce/tinymce.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle content view toggle (Preview vs Editor)
    const previewView = document.getElementById('previewView');
    const editorView = document.getElementById('editorView');
    const contentPreview = document.getElementById('contentPreview');
    const editorContainer = document.getElementById('editorContainer');
    
    if (previewView && editorView && contentPreview && editorContainer) {
        // Show editor by default for PDF notes with structured content
        contentPreview.style.display = 'none';
        editorContainer.style.display = 'block';
        // Initialize TinyMCE on load
        setTimeout(initializeTinyMCE, 100);
        
        previewView.addEventListener('change', function() {
            if (this.checked) {
                contentPreview.style.display = 'block';
                editorContainer.style.display = 'none';
                // Destroy TinyMCE when switching to preview
                const editor = tinymce.get('content');
                if (editor) {
                    editor.remove();
                }
            }
        });
        
        editorView.addEventListener('change', function() {
            if (this.checked) {
                contentPreview.style.display = 'none';
                editorContainer.style.display = 'block';
                // Initialize TinyMCE when switching to editor
                setTimeout(initializeTinyMCE, 100);
            }
        });
    } else {
        // For non-PDF notes or notes without structured content, show editor by default
        if (editorContainer) {
            editorContainer.style.display = 'block';
            initializeTinyMCE();
        }
    }

    // Ensure the content textarea exists before initializing TinyMCE
    const contentTextarea = document.getElementById('content');
    if (!contentTextarea) {
        console.error('Content textarea not found!');
        return;
    }

    // Initialize TinyMCE function
    function initializeTinyMCE() {
    tinymce.init({
        selector: 'textarea#content', // More specific selector
        height: 400,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'save',
            'autoresize', 'emoticons', 'codesample'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | ' +
                'forecolor backcolor removeformat | ' +
                'link image media table | ' +
                'code codesample | ' +
                'emoticons charmap | ' +
                'preview fullscreen help',
        toolbar_mode: 'wrap',
        contextmenu: 'link image table',
        skin: 'oxide',
        content_css: 'default',
        branding: false,
        elementpath: false,
        resize: true,
        autoresize_bottom_margin: 20,
        autoresize_max_height: 600,
        autoresize_min_height: 300,
        target: contentTextarea, // Explicitly target the textarea
        inline: false, // Ensure it's not inline mode
        fixed_toolbar_container: false, // Don't fix toolbar
        save_onsavecallback: function() {
            document.getElementById('editNoteForm').submit();
        },
        init_instance_callback: function(editor) {
            console.log('TinyMCE initialized successfully for textarea#content');
        },
        setup: function(editor) {
            editor.on('change keyup', function() {
                editor.save();
                updateWordCount();
            });
            
            // Auto-save functionality
            let autoSaveTimer;
            editor.on('keyup', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(function() {
                    console.log('Auto-saving...');
                    localStorage.setItem('note_content_' + {{ $note->id }}, editor.getContent());
                }, 3000);
            });
        },
        file_picker_types: 'image',
        file_picker_callback: function(callback, value, meta) {
            if (meta.filetype === 'image') {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                
                input.onchange = function() {
                    const file = this.files[0];
                    const reader = new FileReader();
                    
                    reader.onload = function() {
                        callback(reader.result, {
                            alt: file.name
                        });
                    };
                    
                    reader.readAsDataURL(file);
                };
                
                input.click();
            }
        },
        content_style: `
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                font-size: 14px;
                line-height: 1.6;
                padding: 15px;
            }
            h1, h2, h3, h4, h5, h6 {
                margin-top: 1.5em;
                margin-bottom: 0.5em;
                color: #333;
            }
            p {
                margin-bottom: 1em;
            }
            blockquote {
                border-left: 4px solid #007bff;
                margin: 1.5em 0;
                padding-left: 1em;
                color: #666;
                font-style: italic;
            }
            code {
                background-color: #f8f9fa;
                padding: 2px 4px;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
            }
            pre {
                background-color: #f8f9fa;
                padding: 1em;
                border-radius: 5px;
                overflow-x: auto;
            }
            table {
                border-collapse: collapse;
                width: 100%;
                margin: 1em 0;
            }
            table td, table th {
                border: 1px solid #ddd;
                padding: 8px;
            }
            table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
        `
    });
    } // End of initializeTinyMCE function

    // Initialize TinyMCE for non-PDF notes or notes without structured content
    @if(!$note->is_pdf_note || !$note->hasStructuredContent())
        initializeTinyMCE();
    @endif

    // Form submission handler
    document.getElementById('editNoteForm').addEventListener('submit', function(e) {
        // Update the textarea with TinyMCE content before submission
        const editor = tinymce.get('content');
        if (editor) {
            editor.save();
        }
        
        // Show loading state
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            saveBtn.disabled = true;
        }
    });

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.getElementById('editNoteForm').submit();
        }
    });
});

// Update word count display
function updateWordCount() {
    const editor = tinymce.get('content');
    if (editor) {
        const content = editor.getContent({format: 'text'});
        const wordCount = content.trim() === '' ? 0 : content.trim().split(/\s+/).length;
        const charCount = content.length;
        
        document.getElementById('wordCount').textContent = wordCount;
        document.getElementById('charCount').textContent = charCount;
    }
}

// Preview note function
function previewNote() {
    const editor = tinymce.get('content');
    if (editor) {
        const content = editor.getContent();
        const title = document.getElementById('title').value;
        
        // Open preview in new window
        const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
        previewWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Preview: ${title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
                    .note-content { line-height: 1.6; }
                    .note-content h1, .note-content h2, .note-content h3 { margin-top: 1.5em; margin-bottom: 0.5em; }
                    .note-content blockquote { border-left: 4px solid #007bff; padding-left: 1em; margin: 1.5em 0; color: #666; font-style: italic; }
                    .note-content table { width: 100%; border-collapse: collapse; margin: 1em 0; }
                    .note-content table td, .note-content table th { border: 1px solid #ddd; padding: 8px; }
                    .note-content table th { background-color: #f2f2f2; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>${title}</h1>
                    <div class="note-content">
                        ${content}
                    </div>
                </div>
            </body>
            </html>
        `);
        previewWindow.document.close();
    }
}

// Function to insert template content
function insertTemplate(templateType) {
    const editor = tinymce.get('content');
    let template = '';
    
    switch(templateType) {
        case 'meeting':
            template = `
                <h2>Meeting Notes</h2>
                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                <p><strong>Participants:</strong> </p>
                <p><strong>Agenda:</strong></p>
                <ul>
                    <li></li>
                </ul>
                <p><strong>Key Points:</strong></p>
                <ul>
                    <li></li>
                </ul>
                <p><strong>Action Items:</strong></p>
                <ul>
                    <li></li>
                </ul>
            `;
            break;
        case 'lecture':
            template = `
                <h2>Lecture Notes</h2>
                <p><strong>Course:</strong> </p>
                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                <p><strong>Topic:</strong> </p>
                <h3>Key Concepts</h3>
                <ul>
                    <li></li>
                </ul>
                <h3>Important Formulas</h3>
                <p></p>
                <h3>Examples</h3>
                <p></p>
                <h3>Questions/Review</h3>
                <ul>
                    <li></li>
                </ul>
            `;
            break;
        case 'research':
            template = `
                <h2>Research Notes</h2>
                <p><strong>Topic:</strong> </p>
                <p><strong>Source:</strong> </p>
                <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                <h3>Summary</h3>
                <p></p>
                <h3>Key Findings</h3>
                <ul>
                    <li></li>
                </ul>
                <h3>Quotes</h3>
                <blockquote></blockquote>
                <h3>Personal Thoughts</h3>
                <p></p>
            `;
            break;
    }
    
    if (template) {
        editor.insertContent(template);
    }
}

// Version management functions
function previewVersion(versionId) {
    // Fetch version data and show in preview window
    fetch(`/notes/{{ $note->id }}/versions/${versionId}`)
        .then(response => response.json())
        .then(version => {
            const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
            previewWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Version ${version.version_number}: ${version.title}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
                        .version-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                        .note-content { line-height: 1.6; }
                        .note-content h1, .note-content h2, .note-content h3 { margin-top: 1.5em; margin-bottom: 0.5em; }
                        .note-content blockquote { border-left: 4px solid #007bff; padding-left: 1em; margin: 1.5em 0; color: #666; font-style: italic; }
                        .note-content table { width: 100%; border-collapse: collapse; margin: 1em 0; }
                        .note-content table td, .note-content table th { border: 1px solid #ddd; padding: 8px; }
                        .note-content table th { background-color: #f2f2f2; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="version-info">
                            <h4>Version ${version.version_number} Preview</h4>
                            <p><strong>Title:</strong> ${version.title}</p>
                            <p><strong>Subject:</strong> ${version.subject_area}</p>
                            <p><strong>Created:</strong> ${new Date(version.created_at).toLocaleString()}</p>
                            ${version.change_summary ? `<p><strong>Changes:</strong> ${version.change_summary}</p>` : ''}
                        </div>
                        <div class="note-content">
                            ${version.display_content}
                        </div>
                    </div>
                </body>
                </html>
            `);
            previewWindow.document.close();
        })
        .catch(error => {
            alert('Error loading version preview: ' + error.message);
        });
}

function loadVersion(versionId) {
    if (!confirm('This will replace the current content in the editor with the selected version. Continue?')) {
        return;
    }
    
    // Fetch version data and load into editor
    fetch(`/notes/{{ $note->id }}/versions/${versionId}`)
        .then(response => response.json())
        .then(version => {
            // Load version data into form fields
            document.getElementById('title').value = version.title;
            document.getElementById('subject_area').value = version.subject_area;
            document.getElementById('tags').value = version.tags_list || '';
            
            // Load content into TinyMCE
            const editor = tinymce.get('content');
            if (editor) {
                editor.setContent(version.display_content);
            }
            
            // Update change summary
            document.getElementById('change_summary').value = `Loaded from version ${version.version_number}`;
            
            alert(`Version ${version.version_number} loaded into editor. Don't forget to save your changes!`);
        })
        .catch(error => {
            alert('Error loading version: ' + error.message);
        });
}

function restoreVersion(versionId) {
    if (!confirm('This will save the current content as a new version and restore the selected version. This action cannot be undone. Continue?')) {
        return;
    }
    
    // Create a form to submit the restore request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/notes/{{ $note->id }}/versions/${versionId}/restore`;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);
    
    // Add method spoofing for PUT request
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'PUT';
    form.appendChild(methodField);
    
    document.body.appendChild(form);
    form.submit();
}

function removeVersion(versionId, versionNumber) {
    // Show confirmation dialog with details
    const confirmMessage = `Are you sure you want to permanently delete Version ${versionNumber}?\n\n` +
                          `This action cannot be undone and will remove the version from the database.\n\n` +
                          `Click OK to proceed or Cancel to abort.`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Show loading state
    const button = event.target.closest('a');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    button.style.pointerEvents = 'none';
    
    // Send DELETE request
    fetch(`/notes/{{ $note->id }}/versions/${versionId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message || `Version ${versionNumber} has been successfully deleted.`);
            
            // Reload the page to update the version list
            window.location.reload();
        } else {
            // Show error message
            alert(data.message || 'Failed to delete version. Please try again.');
            
            // Restore button state
            button.innerHTML = originalText;
            button.style.pointerEvents = 'auto';
        }
    })
    .catch(error => {
        console.error('Error deleting version:', error);
        alert('An error occurred while deleting the version. Please try again.');
        
        // Restore button state
        button.innerHTML = originalText;
        button.style.pointerEvents = 'auto';
    });
}
</script>

<!-- Custom Styles -->
<style>
/* Structured Content Styles for Preview */
.structured-content {
    line-height: 1.6;
}

.content-section {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin-bottom: 2rem;
}

.section-title {
    color: #007bff;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

.content-subsection {
    border-left: 2px solid #6c757d;
    padding-left: 1rem;
    margin-left: 1rem;
    margin-bottom: 1.5rem;
}

.subsection-title {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
}

.formatted-content p {
    text-align: justify;
    line-height: 1.7;
    margin-bottom: 1rem;
}

/* Content Preview Card */
#contentPreview {
    background-color: #fafafa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 1rem;
}

#contentPreview .content-section {
    background-color: #fff;
    border-radius: 0.25rem;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Toggle buttons styling */
.btn-group .btn-check:checked + .btn {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

/* Prevent TinyMCE from taking over the entire page */
.tox-tinymce {
    border-radius: 8px !important;
    border: 1px solid #dee2e6 !important;
    max-width: 100% !important;
    position: relative !important;
}

.tox .tox-toolbar {
    background: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6 !important;
}

.tox .tox-edit-area {
    border: none !important;
}

/* Ensure proper container behavior */
.container, .container-fluid {
    position: relative;
    z-index: 1;
}

/* Content area specific styling */
.content {
    padding: 20px;
    margin-left: 0;
    position: relative;
    z-index: 2;
}

/* Card styling */
.card {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    position: relative;
    z-index: 3;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.card-body {
    position: relative;
    z-index: 4;
}

/* Form improvements */
.form-control:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Sidebar styling */
.col-lg-4 .card {
    margin-bottom: 1rem;
}

/* Button styling */
.btn-group .btn {
    border-radius: 0.375rem;
}

.btn-group .btn:not(:last-child) {
    margin-right: 0.25rem;
}

/* Textarea specific styling */
#content {
    margin-left: var(--sidebar-width);
    min-height: 100vh;
    background: #f8fafc;
    transition: all 0.3s ease;
    width: calc(100% - var(--sidebar-width));
}

/* Ensure TinyMCE stays within bounds */
.tox-tinymce {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .col-lg-4 {
        margin-top: 2rem;
    }
}

/* Alert styling */
.alert {
    border: none;
    border-radius: 0.5rem;
    position: relative;
    z-index: 5;
}

.alert-info {
    background-color: #cff4fc;
    border-left: 4px solid #0dcaf0;
}

.alert-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

/* Prevent any potential overflow issues */
.row {
    position: relative;
    z-index: 2;
}

.col-lg-8, .col-lg-4 {
    position: relative;
    z-index: 3;
}

/* Additional safeguards */
body {
    overflow-x: hidden;
}

.wrapper {
    position: relative;
    z-index: 1;
}
</style>

<!-- Loading overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        <p class="mt-2 mb-0">Updating note...</p>
    </div>
</div>
@endsection
