@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow animate__animated animate__fadeIn">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Learning Preferences</h4>
        </div>
        <div class="card-body text-center p-5">
            <div class="mb-4 animate__animated animate__bounceIn animate__delay-1s">
                <i class="fas fa-user-check text-primary" style="font-size: 64px;"></i>
            </div>
            
            <h3>Welcome back to your learning journey!</h3>
            
            <p class="lead mt-4">
                You've already shared your study preferences with us {{ $lastCompleted }}.
            </p>
            
            <div class="mt-5 d-flex flex-column flex-md-row justify-content-center">
                <a href="{{ route('notes.index') }}" class="btn btn-primary btn-lg mx-md-2 mb-3 mb-md-0">
                    <i class="fas fa-arrow-right me-2"></i> Continue to My Notes
                </a>
                
                <a href="{{ route('learning_journey.habits', ['retake' => 1]) }}" class="btn btn-outline-primary btn-lg mx-md-2">
                    <i class="fas fa-redo me-2"></i> Retake Preferences Quiz
                </a>
            </div>
        </div>
    </div>
</div>
@endsection