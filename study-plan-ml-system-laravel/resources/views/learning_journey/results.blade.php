@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Your Results</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="display-4 font-weight-bold {{ $attempt->percentage >= 70 ? 'text-success' : ($attempt->percentage >= 50 ? 'text-warning' : 'text-danger') }} mb-2">
                            {{ $attempt->percentage }}%
                        </div>
                        <p class="lead">Great job completing the review!</p>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Question Summary</h5>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ $attempt->percentage }}%">
                                Correct ({{ $attempt->percentage }}%)
                            </div>
                            <div class="progress-bar bg-danger" style="width: {{ 100 - $attempt->percentage }}%">
                                Incorrect ({{ 100 - $attempt->percentage }}%)
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-2">Total Questions</h6>
                                    <p class="card-text h4">{{ $attempt->total_questions }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-2">Time Taken</h6>
                                    <p class="card-text h4">
                                        @if($attempt->time_taken < 60)
                                            {{ $attempt->time_taken }} seconds
                                        @else
                                            {{ floor($attempt->time_taken / 60) }} minutes {{ $attempt->time_taken % 60 }} seconds
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('learning_journey.select_note') }}" class="btn btn-primary">
                            <i class="fas fa-book mr-1"></i> Review Another Note
                        </a>
                        <a href="{{ route('analytics.quiz') }}" class="btn btn-outline-primary ml-2">
                            <i class="fas fa-chart-line mr-1"></i> View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Personalized Study Tips</h5>
                </div>
                <div class="card-body">
                    @if(!empty($tips))
                        <ul class="list-group list-group-flush">
                            @foreach($tips as $tip)
                                <li class="list-group-item d-flex">
                                    <i class="fas fa-lightbulb text-warning mr-3 mt-1"></i>
                                    <span>{{ $tip }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="card-text">Complete more quizzes to get personalized study tips.</p>
                    @endif
                    
                    @if($studyHabits)
                        @php
                            $responses = is_array($studyHabits->responses) ? $studyHabits->responses : json_decode($studyHabits->responses, true) ?? [];
                        @endphp
                        
                        <div class="mt-4">
                            <h6 class="text-muted">Based On Your Learning Profile:</h6>
                            <div class="card bg-light mt-2">
                                <div class="card-body py-2">
                                    <div class="small">
                                        @if(isset($responses['learning_style']))
                                            <span class="badge badge-pill badge-primary mr-1">{{ ucfirst($responses['learning_style']) }} Learner</span>
                                        @endif
                                        
                                        @if(isset($responses['study_duration']))
                                            <span class="badge badge-pill badge-info mr-1">
                                                @if($responses['study_duration'] == 'very_short')
                                                    Very Short Sessions
                                                @elseif($responses['study_duration'] == 'short')
                                                    Short Sessions
                                                @elseif($responses['study_duration'] == 'medium')
                                                    Medium Sessions
                                                @else
                                                    Long Sessions
                                                @endif
                                            </span>
                                        @endif
                                        
                                        @if(isset($responses['study_time']))
                                            <span class="badge badge-pill badge-secondary">
                                                {{ ucfirst($responses['study_time']) }} Study
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection