@extends('layouts.app')

@section('title', $note->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $note->title }}</h1>
        <div class="text-muted">
            <span class="badge bg-primary me-2">{{ $note->subject_area }}</span>
            <small>
                <i class="fas fa-clock"></i> Created {{ $note->created_at->format('M j, Y') }}
                @if($note->updated_at != $note->created_at)
                    • Updated {{ $note->updated_at->format('M j, Y') }}
                @endif
            </small>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <a href="{{ route('notes.edit', $note) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="#" onclick="printNote()">
                        <i class="fas fa-print"></i> Print
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="generateEnhancedFreeQuiz()">
                        <i class="fas fa-brain text-success"></i> Generate Quiz with Phi-3 AI ⭐⭐⭐⭐⭐
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="generateEnhancedFreeQuiz()">
                        <i class="fas fa-brain text-success"></i> Generate Phi-3 AI Quiz (Recommended)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="generateOpenAIQuiz()">
                        <i class="fab fa-openai text-info"></i> Generate Quiz with ChatGPT ($)
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('notes.destroy', $note) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this note?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-trash"></i> Delete Note
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Tags -->
@if($note->tags && count($note->tags) > 0)
<div class="mb-3">
    @foreach($note->tags as $tag)
    <span class="badge bg-light text-dark me-1">
        <i class="fas fa-tag"></i> {{ $tag }}
    </span>
    @endforeach
</div>
@endif

<!-- Note Content -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            @if($note->versions()->count() > 0)
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="fas fa-file-alt"></i> Note Content</h6>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="contentVersion" id="originalContent" autocomplete="off">
                        <label class="btn btn-outline-primary" for="originalContent">
                            <i class="fas fa-history"></i> Original
                        </label>

                        <input type="radio" class="btn-check" name="contentVersion" id="latestContent" autocomplete="off" checked>
                        <label class="btn btn-outline-success" for="latestContent">
                            <i class="fas fa-edit"></i> Latest Version
                        </label>
                    </div>
                </div>
            @endif
            <div class="card-body">
                <!-- Original Content -->
                <div class="note-content" id="originalContentDiv" style="{{ $note->versions()->count() > 0 ? 'display: none;' : 'display: block;' }}">
                    @if($note->versions()->count() > 0)
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Original Content</strong> - This is the note as it was first created. 
                            {{ $note->versions()->count() }} version(s) available.
                        </div>
                    @endif
                    {!! $note->display_content !!}
                </div>
                
                <!-- Latest Version Content (shown by default) -->
                @if($note->versions()->count() > 0)
                    <div class="note-content" id="latestContentDiv">
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Latest Version</strong> - This is the most recent edited version. 
                            <a href="{{ route('notes.edit', $note) }}" class="alert-link">Edit further</a>
                        </div>
                        {!! $note->current_content !!}
                    </div>
                @endif
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
                    
                    <dt class="col-sm-5">Word Count:</dt>
                    <dd class="col-sm-7">{{ str_word_count(strip_tags($note->content)) }}</dd>
                    
                    <dt class="col-sm-5">Characters:</dt>
                    <dd class="col-sm-7">{{ strlen(strip_tags($note->content)) }}</dd>
                    
                    <dt class="col-sm-5">Created:</dt>
                    <dd class="col-sm-7">{{ $note->created_at->format('M j, Y g:i A') }}</dd>
                    
                    @if($note->updated_at != $note->created_at)
                    <dt class="col-sm-5">Last Updated:</dt>
                    <dd class="col-sm-7">{{ $note->updated_at->format('M j, Y g:i A') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Content Outline -->
        @if($note->is_pdf_note && $note->hasStructuredContent() && !empty($note->content_outline))
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-list-ul"></i> Content Outline</h6>
            </div>
            <div class="card-body">
                <div class="outline-tree">
                    @foreach($note->content_outline as $section)
                        <div class="outline-section mb-2">
                            <div class="outline-item fw-bold">
                                <i class="fas fa-bookmark text-primary"></i>
                                {{ $section['title'] }}
                            </div>
                            @if(!empty($section['subsections']))
                                <div class="outline-subsections ms-3 mt-1">
                                    @foreach($section['subsections'] as $subsection)
                                        <div class="outline-subitem small text-muted">
                                            <i class="fas fa-arrow-right"></i>
                                            {{ $subsection['title'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Document Information -->
        @if($note->is_pdf_note)
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-file-pdf"></i> Document Information</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    @if($note->document_type)
                    <dt class="col-sm-5">Type:</dt>
                    <dd class="col-sm-7">
                        <span class="badge bg-info">{{ ucfirst($note->document_type) }}</span>
                    </dd>
                    @endif
                    
                    @if($note->content_sections && count($note->content_sections) > 0)
                    <dt class="col-sm-5">Sections:</dt>
                    <dd class="col-sm-7">{{ count($note->content_sections) }}</dd>
                    @endif
                    
                    <dt class="col-sm-5">Processing:</dt>
                    <dd class="col-sm-7">
                        @if($note->hasStructuredContent())
                            <span class="badge bg-success">Structured</span>
                        @else
                            <span class="badge bg-warning">Basic</span>
                        @endif
                    </dd>
                    
                    @if($note->pdf_path)
                    <dt class="col-sm-5">Original PDF:</dt>
                    <dd class="col-sm-7">
                        <a href="{{ $note->pdf_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <!-- Quiz Generation Settings -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="questionCount" class="form-label small">Number of Questions:</label>
                        <select id="questionCount" class="form-select form-select-sm">
                            <option value="5">5 Questions</option>
                            <option value="10" selected>10 Questions</option>
                            <option value="15">15 Questions</option>
                            <option value="20">20 Questions</option>
                            <option value="25">25 Questions</option>
                            <option value="30">30 Questions</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="questionTypes" class="form-label small">Question Types:</label>
                        <select id="questionTypes" class="form-select form-select-sm">
                            <option value="mixed">Mixed (Multiple Choice + True/False)</option>
                            <option value="multiple_choice">Multiple Choice Only</option>
                            <option value="true_false">True/False Only</option>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <!-- AI-Powered Quiz Generation Options -->
                    <div class="btn-group-vertical" role="group">
                        <button class="btn btn-primary btn-sm" onclick="generateEnhancedFreeQuiz()">
                            <i class="fas fa-brain"></i> Generate Phi-3 AI Quiz
                            <small class="d-block text-light">ChatGPT-level quality • Free forever</small>
                        </button>
                        <button class="btn btn-success btn-sm" onclick="generateGeminiQuiz()">
                            <i class="fas fa-sparkles"></i> Generate Gemini AI Quiz
                            <small class="d-block text-light">Google AI • Free tier</small>
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="generateOpenAIQuiz()">
                            <i class="fas fa-robot"></i> Generate ChatGPT Quiz
                            <small class="d-block">Premium AI • API costs apply</small>
                        </button>
                    </div>
                    
                    <!-- Other Actions -->
                    <button class="btn btn-outline-info btn-sm" onclick="copyToClipboard()">
                        <i class="fas fa-copy"></i> Copy Content
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleFavorite()">
                        <i class="fas fa-heart"></i> 
                        {{ $note->is_favorite ? 'Remove from Favorites' : 'Add to Favorites' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Related Notes -->
        @if($relatedNotes && $relatedNotes->count() > 0)
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-link"></i> Related Notes</h6>
            </div>
            <div class="card-body">
                @foreach($relatedNotes as $relatedNote)
                <div class="mb-2">
                    <a href="{{ route('notes.show', $relatedNote) }}" class="text-decoration-none">
                        <div class="small fw-bold">{{ $relatedNote->title }}</div>
                        <div class="small text-muted">{{ $relatedNote->subject_area }}</div>
                    </a>
                </div>
                @if(!$loop->last)<hr class="my-2">@endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script>
function printNote() {
    window.print();
}

function generateEnhancedFreeQuiz() {
    // Get user selections
    const questionCount = document.getElementById('questionCount').value;
    const questionTypesSelection = document.getElementById('questionTypes').value;
    
    // Map question types
    let questionTypes;
    switch(questionTypesSelection) {
        case 'multiple_choice':
            questionTypes = ['multiple_choice'];
            break;
        case 'true_false':
            questionTypes = ['true_false'];
            break;
        case 'mixed':
        default:
            questionTypes = ['multiple_choice', 'true_false'];
            break;
    }
    
    if (confirm(`🧠 Generate a FREE AI-powered quiz with ${questionCount} questions from this note?\n\n✅ Uses advanced Hugging Face Transformers (T5, BART, DistilBERT)\n✅ ChatGPT-like quality (80-90%)\n✅ Completely FREE with no API costs\n✅ Runs locally on your machine\n\nThis may take a few moments to generate high-quality questions.`)) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Free AI is generating quiz...';
        button.disabled = true;
        
        // Call the Enhanced Free AI quiz generation API
        fetch('/quizzes/generate-with-enhanced-free', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                note_id: {{ $note->id }},
                num_questions: parseInt(questionCount),
                question_types: questionTypes,
                difficulty: 'medium',
                subject_area: '{{ $note->subject_area ?? "general" }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and redirect
                alert(`🎉 Phi-3 AI quiz generated successfully!\n\n🧠 ${questionCount} high-quality questions generated!\n⭐ ChatGPT-level quality • $0 cost\n🚀 Redirecting to your quiz...`);
                window.location.href = data.redirect_url;
            } else {
                alert('❌ Error generating Phi-3 AI quiz: ' + (data.error || 'Unknown error') + '\n\n💡 Tip: Make sure the Phi-3 AI service is running.\n\nTo start the service:\n1. Navigate to: study-plan-ml-system/\n2. Double-click: START_PHI3_SERVICE.bat\n3. Wait 10-15 seconds for model to load\n4. Try generating the quiz again');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Failed to generate Phi-3 AI quiz.\n\n🔧 Troubleshooting:\n1. Check if Phi-3 service is running (START_PHI3_SERVICE.bat)\n2. Verify service loaded (takes 10-15 seconds first time)\n3. Check http://localhost:5002/health\n4. Try again in a few moments');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

function generateGeminiQuiz() {
    // Get user selections
    const questionCount = document.getElementById('questionCount').value;
    const questionTypesSelection = document.getElementById('questionTypes').value;
    
    // Map question types for Gemini (supports MCQ and T/F)
    let questionTypes;
    switch(questionTypesSelection) {
        case 'multiple_choice':
            questionTypes = ['multiple_choice'];
            break;
        case 'true_false':
            questionTypes = ['true_false'];
            break;
        case 'mixed':
        default:
            questionTypes = ['multiple_choice', 'true_false'];
            break;
    }
    
    if (confirm(`🤖 Generate a Gemini AI quiz with ${questionCount} high-quality questions? This uses Google's advanced AI to create contextual questions that test real understanding, not memorization.`)) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gemini AI is generating quiz...';
        button.disabled = true;
        
        // Call the Gemini quiz generation API
        fetch('/quizzes/generate-with-gemini', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                note_id: {{ $note->id }},
                num_questions: parseInt(questionCount),
                question_types: questionTypes,
                difficulty: 'medium'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and redirect
                alert(`✨ Gemini AI quiz generated successfully!\n\n🎯 ${questionCount} high-quality questions created\n📚 Questions test understanding, not memorization\n🚀 Redirecting to your quiz...`);
                window.location.href = data.redirect_url;
            } else {
                alert('❌ Error generating Gemini AI quiz: ' + (data.error || 'Unknown error') + '\n\n💡 Tips:\n1. Make sure Gemini AI service is running (port 5003)\n2. Check that GEMINI_API_KEY is configured\n3. Verify your note has sufficient content\n\nTo start the service:\n- Run: python gemini_quiz_service.py');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Failed to generate Gemini AI quiz.\n\n🔧 Troubleshooting:\n1. Ensure Gemini service is running on port 5003\n2. Check API key is configured\n3. Try again in a few moments');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

function generateOpenAIQuiz() {
    // Get user selections
    const questionCount = document.getElementById('questionCount').value;
    const questionTypesSelection = document.getElementById('questionTypes').value;
    
    // Map question types
    let questionTypes;
    switch(questionTypesSelection) {
        case 'multiple_choice':
            questionTypes = ['multiple_choice'];
            break;
        case 'true_false':
            questionTypes = ['true_false'];
            break;
        case 'mixed':
        default:
            questionTypes = ['multiple_choice', 'true_false', 'fill_blank', 'short_answer'];
            break;
    }
    
    if (confirm(`🤖 Generate a ChatGPT-powered quiz with ${questionCount} questions from this note? This uses advanced AI to create dynamic, contextual questions and may take a few moments.`)) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ChatGPT is generating quiz...';
        button.disabled = true;
        
        // Call the OpenAI quiz generation API
        fetch('/quizzes/generate-with-openai', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                note_id: {{ $note->id }},
                num_questions: parseInt(questionCount),
                question_types: questionTypes,
                difficulty: 'medium',
                subject_area: '{{ $note->subject_area ?? "general" }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and redirect
                alert(`🤖 ChatGPT quiz generated successfully! ${questionCount} intelligent, contextual questions created. Redirecting to quiz...`);
                window.location.href = data.redirect_url;
            } else {
                alert('Error generating ChatGPT quiz: ' + (data.error || 'Unknown error') + '\n\nTip: Make sure your OpenAI API key is configured in the .env file.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to generate ChatGPT quiz. Please check your OpenAI configuration and try again.');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

function copyToClipboard() {
    const content = document.querySelector('.note-content').innerText;
    navigator.clipboard.writeText(content).then(function() {
        alert('Note content copied to clipboard!');
    });
}

function toggleFavorite() {
    // AJAX call to toggle favorite status
    fetch(`/notes/{{ $note->id }}/toggle-favorite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Print styles
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            .btn, .dropdown, .card-header, nav, footer { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .col-lg-4 { display: none !important; }
            .col-lg-8 { width: 100% !important; }
        }
    `;
    document.head.appendChild(style);
    
    // Content version toggle functionality
    const originalRadio = document.getElementById('originalContent');
    const latestRadio = document.getElementById('latestContent');
    const originalDiv = document.getElementById('originalContentDiv');
    const latestDiv = document.getElementById('latestContentDiv');
    
    if (originalRadio && latestRadio && originalDiv && latestDiv) {
        originalRadio.addEventListener('change', function() {
            if (this.checked) {
                originalDiv.style.display = 'block';
                latestDiv.style.display = 'none';
            }
        });
        
        latestRadio.addEventListener('change', function() {
            if (this.checked) {
                originalDiv.style.display = 'none';
                latestDiv.style.display = 'block';
            }
        });
    }
});
</script>

<style>
/* Structured Content Styles */
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

/* Outline Styles */
.outline-tree {
    font-size: 0.9rem;
}

.outline-section {
    border-left: 2px solid #e9ecef;
    padding-left: 0.75rem;
}

.outline-item {
    color: #495057;
    margin-bottom: 0.25rem;
}

.outline-subitem {
    margin-left: 0.5rem;
    margin-bottom: 0.1rem;
    color: #6c757d;
}

.outline-subitem i {
    font-size: 0.7rem;
    margin-right: 0.25rem;
}

/* Content Display Improvements */
.note-content {
    font-size: 1rem;
    color: #333;
}

.note-content h2, .note-content h3, .note-content h4 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.note-content ul, .note-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.note-content li {
    margin-bottom: 0.25rem;
}

/* Document type badges */
.badge.bg-info {
    background-color: #17a2b8 !important;
}

.badge.bg-success {
    background-color: #28a745 !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}
</style>
@endsection
