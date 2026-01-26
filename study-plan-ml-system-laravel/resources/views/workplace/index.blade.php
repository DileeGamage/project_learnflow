@extends('layouts.app')

@section('title', 'My Workplace')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>
                        My Workplace
                    </h4>
                    <p class="mb-0 small">Manage your learning materials and study plans</p>
                </div>
                <div class="card-body">
                    
                    <!-- Stats Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $totalNotes }}</h3>
                                            <p class="mb-0">Total Notes</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-sticky-note fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $textNotes }}</h3>
                                            <p class="mb-0">Text Notes</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-edit fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $pdfNotes }}</h3>
                                            <p class="mb-0">PDF Notes</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-file-pdf fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $favoriteNotes }}</h3>
                                            <p class="mb-0">Favorites</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-heart fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Quick Actions</h5>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <a href="{{ route('notes.create') }}" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-plus me-2"></i>
                                        New Note
                                    </a>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <a href="{{ route('notes.index') }}" class="btn btn-outline-info w-100">
                                        <i class="fas fa-folder me-2"></i>
                                        View All Notes
                                    </a>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <a href="{{ route('notes.index', ['favorite' => 1]) }}" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-heart me-2"></i>
                                        Favorite Notes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <!-- Recent Notes -->
                        <div class="col-md-8 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        Recent Notes
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($recentNotes->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($recentNotes as $note)
                                                <div class="list-group-item border-0 px-0">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">
                                                                <a href="{{ route('notes.show', $note) }}" class="text-decoration-none">
                                                                    {{ Str::limit($note->title, 50) }}
                                                                </a>
                                                            </h6>
                                                            <p class="mb-1 text-muted small">
                                                                {{ $note->subject_area ?? 'No subject' }}
                                                            </p>
                                                            <small class="text-muted">
                                                                {{ $note->created_at->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                        <div class="ms-2">
                                                            @if($note->is_pdf_note)
                                                                <span class="badge bg-warning">PDF</span>
                                                            @endif
                                                            @if($note->is_favorite)
                                                                <i class="fas fa-heart text-danger"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="{{ route('notes.index') }}" class="btn btn-sm btn-outline-primary">
                                                View All Notes
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-sticky-note fa-3x mb-3"></i>
                                            <p>No notes yet. Create your first note!</p>
                                            <a href="{{ route('notes.create') }}" class="btn btn-primary">
                                                Create Note
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>
                                        Notes Overview
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small">Text Notes</span>
                                            <span class="small">{{ $textNotes }}</span>
                                        </div>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $totalNotes > 0 ? ($textNotes / $totalNotes) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small">PDF Notes</span>
                                            <span class="small">{{ $pdfNotes }}</span>
                                        </div>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $totalNotes > 0 ? ($pdfNotes / $totalNotes) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small">Favorites</span>
                                            <span class="small">{{ $favoriteNotes }}</span>
                                        </div>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar bg-danger" style="width: {{ $totalNotes > 0 ? ($favoriteNotes / $totalNotes) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>

                                    @if($totalNotes > 0)
                                        <div class="text-center mt-3">
                                            <small class="text-muted">
                                                You've created {{ $totalNotes }} {{ Str::plural('note', $totalNotes) }} so far!
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection