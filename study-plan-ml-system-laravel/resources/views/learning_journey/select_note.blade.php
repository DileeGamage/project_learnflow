@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('message'))
    <div class="alert alert-info">
        {{ session('message') }}
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Select Material to Review</h4>
        </div>
        <div class="card-body">
            <p class="lead">
                Awesome. Based on what you've shared, we're now ready to build your custom review.
                Please select the note you'd like to work with today.
            </p>
            
            @if($notes->isEmpty())
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    You don't have any notes yet. <a href="{{ route('notes.create') }}">Create your first note</a> to get started.
                </div>
            @else
                <div class="row">
                    @foreach($notes as $note)
                    <div class="col-md-6 col-xl-4 mb-4">
                        <div class="card h-100 border-hover-primary">
                            <div class="card-body">
                                <h5 class="card-title text-truncate">{{ $note->title }}</h5>
                                <p class="card-text text-muted small">
                                    {{ \Illuminate\Support\Str::limit($note->content, 100) }}
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $note->updated_at->diffForHumans() }}
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="{{ route('learning_journey.prepare', $note->id) }}" 
                                   class="btn btn-outline-primary btn-block">
                                   <i class="fas fa-book-open mr-1"></i> Study This Material
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-hover-primary:hover {
        border-color: var(--primary) !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-3px);
        transition: all 0.3s ease;
    }
</style>
@endpush
@endsection