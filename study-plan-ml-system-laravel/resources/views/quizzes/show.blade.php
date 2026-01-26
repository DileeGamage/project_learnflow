@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Quiz Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-quiz"></i> {{ $quiz->title }}
                            </h4>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark me-2">
                                <i class="fas fa-clock"></i> {{ $quiz->formatted_estimated_time }}
                            </span>
                            @if($quiz->is_random)
                            <span class="badge bg-success">
                                <i class="fas fa-random"></i> Random Quiz
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-sticky-note text-primary"></i> 
                                <strong>From Note:</strong> 
                                <a href="{{ route('notes.show', $quiz->note) }}" class="text-decoration-none">
                                    {{ $quiz->note->title }}
                                </a>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-tag text-info"></i> 
                                <strong>Subject:</strong> 
                                <span class="badge bg-primary">{{ $quiz->note->subject_area }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-question-circle text-success"></i> 
                                <strong>Questions:</strong> {{ $quiz->total_questions }}
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-signal text-warning"></i> 
                                <strong>Difficulty:</strong> 
                                <span class="badge {{ $quiz->difficulty_badge_class }}">
                                    {{ ucfirst($quiz->difficulty_level) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    @if($quiz->is_random)
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-random"></i> 
                        This is a random quiz with questions selected from the entire note content, providing comprehensive coverage of all topics.
                    </div>
                    @elseif($quiz->is_ml_generated)
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-robot"></i> 
                        This quiz was generated using AI analysis of the note content.
                    </div>
                    @endif
                    
                    <div class="mt-3">
                        <a href="{{ route('quizzes.take', $quiz) }}" class="btn btn-success btn-lg">
                            <i class="fas fa-play"></i> Start Quiz
                        </a>
                        <a href="{{ route('notes.show', $quiz->note) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Note
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quiz Content Analysis -->
            @if($quiz->content_analysis)
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-line"></i> Content Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(isset($quiz->content_analysis['word_count']))
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-primary">{{ number_format($quiz->content_analysis['word_count']) }}</h5>
                                <small class="text-muted">Words Analyzed</small>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($quiz->content_analysis['keywords']))
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-info">{{ count($quiz->content_analysis['keywords']) }}</h5>
                                <small class="text-muted">Key Concepts</small>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($quiz->content_analysis['reading_level']))
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-warning">{{ round($quiz->content_analysis['reading_level']) }}</h5>
                                <small class="text-muted">Reading Score</small>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-success">{{ $quiz->total_attempts }}</h5>
                                <small class="text-muted">Total Attempts</small>
                            </div>
                        </div>
                    </div>
                    
                    @if(isset($quiz->content_analysis['keywords']) && count($quiz->content_analysis['keywords']) > 0)
                    <hr>
                    <div>
                        <h6 class="mb-2">Key Topics:</h6>
                        @foreach(array_slice($quiz->content_analysis['keywords'], 0, 10) as $keyword)
                            @if(is_array($keyword))
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $keyword['keyword'] ?? $keyword }}</span>
                            @else
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $keyword }}</span>
                            @endif
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Question Types Preview -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-list"></i> Question Types</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $availableTypes = $quiz->getAvailableQuestionTypes() ?? [];
                        @endphp
                        @if(empty($availableTypes))
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No questions available in this quiz.
                                </div>
                            </div>
                        @else
                            @foreach($availableTypes as $type)
                            @php
                                $typeCount = count($quiz->getQuestionsByType($type));
                                $typeInfo = [
                                    'multiple_choice' => ['icon' => 'fas fa-list-ul', 'name' => 'Multiple Choice', 'color' => 'primary'],
                                    'true_false' => ['icon' => 'fas fa-toggle-on', 'name' => 'True/False', 'color' => 'success'],
                                    'fill_blank' => ['icon' => 'fas fa-edit', 'name' => 'Fill in the Blank', 'color' => 'warning'],
                                    'short_answer' => ['icon' => 'fas fa-paragraph', 'name' => 'Short Answer', 'color' => 'info']
                                ];
                                $info = $typeInfo[$type] ?? ['icon' => 'fas fa-question', 'name' => ucwords(str_replace('_', ' ', $type)), 'color' => 'secondary'];
                        @endphp
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card border-{{ $info['color'] }}">
                                <div class="card-body text-center">
                                    <i class="{{ $info['icon'] }} fa-2x text-{{ $info['color'] }} mb-2"></i>
                                    <h6>{{ $info['name'] }}</h6>
                                    <span class="badge bg-{{ $info['color'] }}">{{ $typeCount }} questions</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quiz Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Quiz Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Average Score:</span>
                            <strong>{{ number_format($quiz->average_score, 1) }}%</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Attempts:</span>
                            <strong>{{ $quiz->total_attempts }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Created:</span>
                            <strong>{{ $quiz->created_at->format('M j, Y') }}</strong>
                        </div>
                    </div>
                    
                    @if(auth()->check())
                        @php
                            $userAttempt = $quiz->latestAttemptByUser(auth()->id());
                            $bestAttempt = $quiz->bestAttemptByUser(auth()->id());
                        @endphp
                        
                        @if($userAttempt)
                        <hr>
                        <h6 class="text-muted">Your Performance</h6>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span>Last Score:</span>
                                <strong class="{{ $userAttempt->grade_color }}">
                                    {{ $userAttempt->percentage }}% ({{ $userAttempt->grade }})
                                </strong>
                            </div>
                        </div>
                        @if($bestAttempt && $bestAttempt->id !== $userAttempt->id)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span>Best Score:</span>
                                <strong class="{{ $bestAttempt->grade_color }}">
                                    {{ $bestAttempt->percentage }}% ({{ $bestAttempt->grade }})
                                </strong>
                            </div>
                        </div>
                        @endif
                        @endif
                    @endif
                </div>
            </div>
            
            <!-- Related Quizzes -->
            @php
                $relatedQuizzes = \App\Models\Quiz::where('note_id', $quiz->note_id)
                    ->where('id', '!=', $quiz->id)
                    ->active()
                    ->take(3)
                    ->get();
            @endphp
            
            @if($relatedQuizzes->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-link"></i> Related Quizzes</h6>
                </div>
                <div class="card-body">
                    @foreach($relatedQuizzes as $relatedQuiz)
                    <div class="mb-3">
                        <a href="{{ route('quizzes.show', $relatedQuiz) }}" class="text-decoration-none">
                            <div class="small fw-bold">{{ $relatedQuiz->title }}</div>
                            <div class="small text-muted">
                                {{ $relatedQuiz->total_questions }} questions • 
                                {{ $relatedQuiz->formatted_estimated_time }}
                            </div>
                        </a>
                    </div>
                    @if(!$loop->last)<hr class="my-2">@endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
