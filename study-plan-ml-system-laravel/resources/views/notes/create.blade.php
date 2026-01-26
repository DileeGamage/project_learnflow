@extends('layouts.app')

@section('title', 'Create Note')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">📝 Create New Note</h4>
            </div>
            <div class="card-body">
                <!-- PDF Service Status Check -->
                <div id="serviceStatus" class="alert alert-info mb-3" style="display: none;">
                    <i class="fas fa-info-circle"></i> <span id="statusMessage">Checking PDF service...</span>
                </div>

                <form action="{{ route('notes.store') }}" method="POST" enctype="multipart/form-data" id="noteForm">
                    @csrf
                    
                    <!-- Hidden fallback for note_type -->
                    <input type="hidden" name="note_type" value="text" id="noteTypeHidden">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subject_area" class="form-label">Subject Area *</label>
                        <select class="form-select @error('subject_area') is-invalid @enderror" 
                                id="subject_area" name="subject_area" required>
                            <option value="">Choose a subject</option>
                            <option value="Mathematics" {{ old('subject_area') == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                            <option value="Science" {{ old('subject_area') == 'Science' ? 'selected' : '' }}>Science</option>
                            <option value="English" {{ old('subject_area') == 'English' ? 'selected' : '' }}>English</option>
                            <option value="History" {{ old('subject_area') == 'History' ? 'selected' : '' }}>History</option>
                            <option value="Programming" {{ old('subject_area') == 'Programming' ? 'selected' : '' }}>Programming</option>
                            <option value="Other" {{ old('subject_area') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('subject_area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Note Type Selection -->
                    <div class="mb-3">
                        <label class="form-label">Note Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="note_type" id="text_note" value="text" checked>
                            <label class="btn btn-outline-primary" for="text_note">
                                <i class="fas fa-keyboard"></i> Text Note
                            </label>
                            
                            <input type="radio" class="btn-check" name="note_type" id="pdf_note" value="pdf">
                            <label class="btn btn-outline-primary" for="pdf_note">
                                <i class="fas fa-file-pdf"></i> PDF Upload
                            </label>
                        </div>
                    </div>

                    <!-- Text Content Area -->
                    <div class="mb-3" id="textContentArea">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" name="content" rows="10" 
                                  placeholder="Write your notes here...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- PDF Upload Area -->
                    <div class="mb-3" id="pdfUploadArea" style="display: none;">
                        <label for="pdf_file" class="form-label">Upload PDF File</label>
                        <input type="file" class="form-control @error('pdf_file') is-invalid @enderror" 
                               id="pdf_file" name="pdf_file" accept=".pdf">
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Upload a PDF file and we'll extract the text automatically using our local OCR service.
                            Maximum file size: 10MB
                        </div>
                        @error('pdf_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- OCR Processing Status -->
                        <div id="ocrStatus" class="mt-2" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-spinner fa-spin"></i> Processing PDF and extracting text...
                            </div>
                        </div>
                    </div>

                    <!-- Tags (Optional) -->
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (Optional)</label>
                        <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                               id="tags" name="tags" value="{{ old('tags') }}" 
                               placeholder="Enter tags separated by commas">
                        <div class="form-text">
                            Example: chemistry, exam notes, chapter 5
                        </div>
                        @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('notes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Notes
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Create Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textNoteRadio = document.getElementById('text_note');
    const pdfNoteRadio = document.getElementById('pdf_note');
    const textContentArea = document.getElementById('textContentArea');
    const pdfUploadArea = document.getElementById('pdfUploadArea');
    const contentTextarea = document.getElementById('content');
    const pdfFileInput = document.getElementById('pdf_file');
    const noteForm = document.getElementById('noteForm');
    const submitBtn = document.getElementById('submitBtn');
    const ocrStatus = document.getElementById('ocrStatus');
    const serviceStatus = document.getElementById('serviceStatus');
    const statusMessage = document.getElementById('statusMessage');

    // Check PDF OCR service status
    async function checkServiceStatus() {
        try {
            const response = await fetch('/notes/check-ocr-service');
            const result = await response.json();
            
            if (result.available) {
                serviceStatus.className = 'alert alert-success mb-3';
                statusMessage.innerHTML = '<i class="fas fa-check-circle"></i> PDF OCR service is available and ready!';
            } else {
                serviceStatus.className = 'alert alert-warning mb-3';
                statusMessage.innerHTML = '<i class="fas fa-exclamation-triangle"></i> PDF OCR service is not available. PDF upload will be disabled.';
                pdfNoteRadio.disabled = true;
                pdfNoteRadio.parentElement.classList.add('disabled');
                // Ensure text note is selected when PDF is disabled
                textNoteRadio.checked = true;
                toggleNoteType();
            }
            serviceStatus.style.display = 'block';
        } catch (error) {
            serviceStatus.className = 'alert alert-danger mb-3';
            statusMessage.innerHTML = '<i class="fas fa-times-circle"></i> Cannot connect to PDF OCR service.';
            serviceStatus.style.display = 'block';
            pdfNoteRadio.disabled = true;
            pdfNoteRadio.parentElement.classList.add('disabled');
            // Ensure text note is selected when PDF is disabled
            textNoteRadio.checked = true;
            toggleNoteType();
        }
    }

    // Check service status on page load
    checkServiceStatus();

    // Toggle between text and PDF input
    function toggleNoteType() {
        const noteTypeHidden = document.getElementById('noteTypeHidden');
        
        if (pdfNoteRadio.checked && !pdfNoteRadio.disabled) {
            textContentArea.style.display = 'none';
            pdfUploadArea.style.display = 'block';
            contentTextarea.removeAttribute('required');
            contentTextarea.value = '';
            noteTypeHidden.value = 'pdf';
        } else {
            textContentArea.style.display = 'block';
            pdfUploadArea.style.display = 'none';
            contentTextarea.setAttribute('required', '');
            pdfFileInput.value = '';
            noteTypeHidden.value = 'text';
            // Ensure text radio is checked if PDF is disabled
            textNoteRadio.checked = true;
        }
    }

    textNoteRadio.addEventListener('change', toggleNoteType);
    pdfNoteRadio.addEventListener('change', toggleNoteType);

    // Handle form submission for PDF uploads
    noteForm.addEventListener('submit', function(e) {
        // Ensure note_type is set
        const noteTypeChecked = document.querySelector('input[name="note_type"]:checked');
        if (!noteTypeChecked) {
            // Fallback to text if no radio button is checked
            document.getElementById('noteTypeHidden').value = 'text';
            textNoteRadio.checked = true;
        } else {
            // Update hidden field with selected value
            document.getElementById('noteTypeHidden').value = noteTypeChecked.value;
        }
        
        // Validate based on note type
        const currentNoteType = document.getElementById('noteTypeHidden').value;
        if (currentNoteType === 'text') {
            if (!contentTextarea.value.trim()) {
                e.preventDefault();
                alert('Please enter some content for your text note');
                contentTextarea.focus();
                return;
            }
        } else if (currentNoteType === 'pdf') {
            if (!pdfFileInput.files.length) {
                e.preventDefault();
                alert('Please select a PDF file to upload');
                pdfFileInput.focus();
                return;
            }
        }
        
        if (pdfNoteRadio.checked && pdfFileInput.files.length > 0) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing PDF...';
            ocrStatus.style.display = 'block';
        }
    });

    // File size validation
    pdfFileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file && file.size > 10 * 1024 * 1024) { // 10MB
            alert('File size must be less than 10MB');
            this.value = '';
        }
    });

    // Auto-suggest tags based on subject area
    const subjectArea = document.getElementById('subject_area');
    const tagsInput = document.getElementById('tags');
    
    const subjectTags = {
        'Mathematics': 'algebra, calculus, geometry, statistics, equations',
        'Science': 'biology, chemistry, physics, experiment, lab',
        'English': 'literature, grammar, essay, reading, writing',
        'History': 'ancient, modern, world war, revolution, timeline',
        'Programming': 'code, algorithm, function, debug, syntax',
        'Other': 'study, notes, review, exam, assignment'
    };

    subjectArea.addEventListener('change', function() {
        const selectedSubject = this.value;
        if (selectedSubject && subjectTags[selectedSubject] && !tagsInput.value) {
            tagsInput.placeholder = `Suggested: ${subjectTags[selectedSubject]}`;
        }
    });
});
</script>
@endsection
