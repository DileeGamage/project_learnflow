@extends('layouts.app')

@section('title', 'Create Workplace Item')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Create New Item
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Choose what you'd like to create:</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-sticky-note fa-3x text-primary mb-3"></i>
                                    <h5>Create Note</h5>
                                    <p class="text-muted">Create a new note or upload a PDF document</p>
                                    <a href="{{ route('notes.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>New Note
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-folder fa-3x text-info mb-3"></i>
                                    <h5>Browse Notes</h5>
                                    <p class="text-muted">View and manage your existing notes</p>
                                    <a href="{{ route('notes.index') }}" class="btn btn-info">
                                        <i class="fas fa-folder-open me-2"></i>View Notes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('workplace.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Workplace
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection