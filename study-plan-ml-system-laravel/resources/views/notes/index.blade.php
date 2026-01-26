@extends('layouts.app')

@section('title', 'My Notes')

@section('content')
<!-- Learning Journey Message -->
@if(session('message') || session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <div class="me-3">
            <i class="fas fa-graduation-cap fa-2x"></i>
        </div>
        <div>
            <h4 class="alert-heading">Continue Your Learning Journey</h4>
            <p class="mb-0">{{ session('message') ?? session('success') }}</p>
            <p class="mt-2 mb-0"><strong>Next Step:</strong> Select a note below and view it to create a quiz for your personalized learning journey.</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">📝 My Notes</h1>
    <a href="{{ route('notes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Note
    </a>
</div>

<!-- Filter and Search -->
<div class="row mb-4">
    <div class="col-md-3">
        <select class="form-select" id="subjectFilter">
            <option value="">All Subjects</option>
            <option value="Mathematics">Mathematics</option>
            <option value="Science">Science</option>
            <option value="English">English</option>
            <option value="History">History</option>
            <option value="Programming">Programming</option>
            <option value="Other">Other</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="typeFilter">
            <option value="">All Types</option>
            <option value="text">Text Notes</option>
            <option value="pdf">PDF Notes</option>
        </select>
    </div>
    <div class="col-md-6">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Search notes..." id="searchNotes">
            <button class="btn btn-outline-secondary" type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</div>

<!-- Notes Grid -->
<div class="row" id="notesContainer">
    @forelse($notes as $note)
    <div class="col-md-6 col-lg-4 mb-4 note-card" 
         data-subject="{{ $note->subject_area }}" 
         data-type="{{ $note->is_pdf_note ? 'pdf' : 'text' }}">
        <div class="card h-100 shadow-sm {{ $note->is_pdf_note ? 'border-warning' : '' }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-truncate">
                    @if($note->is_pdf_note)
                        <i class="fas fa-file-pdf text-danger me-1"></i>
                    @else
                        <i class="fas fa-sticky-note text-primary me-1"></i>
                    @endif
                    {{ $note->title }}
                </h6>
                <span class="badge bg-primary">{{ $note->subject_area }}</span>
            </div>
            <div class="card-body">
                <div class="note-preview text-muted small">
                    @if($note->is_pdf_note)
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-magic"></i> OCR Extracted
                            </span>
                            @if($note->versions()->count() > 0)
                                <span class="badge bg-info text-white ms-1">
                                    <i class="fas fa-history"></i> {{ $note->versions()->count() }} versions
                                </span>
                            @endif
                        </div>
                        {!! Str::limit(strip_tags($note->extracted_text), 150) !!}
                        @if($note->versions()->count() > 0)
                            <div class="mt-1">
                                <small class="text-info">
                                    <i class="fas fa-info-circle"></i> Showing original content. Latest edited version available.
                                </small>
                            </div>
                        @endif
                    @else
                        @if($note->versions()->count() > 0)
                            <div class="mb-2">
                                <span class="badge bg-info text-white">
                                    <i class="fas fa-history"></i> {{ $note->versions()->count() }} versions
                                </span>
                            </div>
                        @endif
                        {!! Str::limit(strip_tags($note->content), 150) !!}
                        @if($note->versions()->count() > 0)
                            <div class="mt-1">
                                <small class="text-info">
                                    <i class="fas fa-info-circle"></i> Showing original content. Latest edited version available.
                                </small>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> {{ $note->updated_at->format('M j, Y') }}
                    </small>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="btn-group w-100" role="group">
                    <a href="{{ route('notes.show', $note) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye"></i> View
                    </a>
                    @if($note->is_pdf_note && $note->pdf_path)
                        <a href="{{ asset('storage/' . $note->pdf_path) }}" 
                           target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-download"></i> PDF
                        </a>
                    @endif
                    <a href="{{ route('notes.edit', $note) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('notes.destroy', $note) }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this note?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                <h4>No Notes Yet</h4>
                <p class="text-muted">Start creating your first note to organize your study materials.</p>
                <a href="{{ route('notes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Your First Note
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($notes instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="d-flex justify-content-center">
    {{ $notes->links() }}
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const subjectFilter = document.getElementById('subjectFilter');
    const typeFilter = document.getElementById('typeFilter');
    const searchInput = document.getElementById('searchNotes');
    const noteCards = document.querySelectorAll('.note-card');

    function filterNotes() {
        const selectedSubject = subjectFilter.value;
        const selectedType = typeFilter.value;
        const searchTerm = searchInput.value.toLowerCase();

        noteCards.forEach(card => {
            const cardSubject = card.dataset.subject;
            const cardType = card.dataset.type;
            const cardTitle = card.querySelector('.card-header h6').textContent.toLowerCase();
            const cardContent = card.querySelector('.note-preview').textContent.toLowerCase();

            const subjectMatch = !selectedSubject || cardSubject === selectedSubject;
            const typeMatch = !selectedType || cardType === selectedType;
            const searchMatch = !searchTerm || cardTitle.includes(searchTerm) || cardContent.includes(searchTerm);

            if (subjectMatch && typeMatch && searchMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    subjectFilter.addEventListener('change', filterNotes);
    typeFilter.addEventListener('change', filterNotes);
    searchInput.addEventListener('input', filterNotes);
});
</script>
@endsection
