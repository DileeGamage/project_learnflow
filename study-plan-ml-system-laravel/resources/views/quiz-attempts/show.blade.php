@extends('layouts.app')

@section('title', 'Quiz Results')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Quiz Results Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-chart-line"></i> Quiz Results
                            </h4>
                            <small>{{ $attempt->quiz->title }} - Completed {{ $attempt->completed_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-light text-dark fs-5">
                                {{ $attempt->score }}/{{ $attempt->total_questions }} ({{ round($attempt->percentage) }}%)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Score Overview -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-primary">{{ $attempt->score }}</h3>
                                <p class="text-muted mb-0">Correct Answers</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-info">{{ round($attempt->percentage) }}%</h3>
                                <p class="text-muted mb-0">Score Percentage</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-warning">{{ gmdate('i:s', $attempt->time_taken) }}</h3>
                                <p class="text-muted mb-0">Time Taken</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $performanceLevel = $attempt->percentage >= 80 ? 'Excellent' : 
                                                   ($attempt->percentage >= 70 ? 'Good' : 
                                                   ($attempt->percentage >= 60 ? 'Average' : 'Needs Improvement'));
                                $performanceColor = $attempt->percentage >= 80 ? 'success' : 
                                                   ($attempt->percentage >= 70 ? 'primary' : 
                                                   ($attempt->percentage >= 60 ? 'warning' : 'danger'));
                            @endphp
                            <h3 class="text-{{ $performanceColor }}">{{ $performanceLevel }}</h3>
                            <p class="text-muted mb-0">Performance</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Smart Topic-Based Recommendations (New System) -->
            @if($smartRecommendations)
            
            <!-- Personalized Study Recommendations Overview -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb"></i> Personalized Study Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Performance Analysis (Left Side) - From Study Habits Assessment -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">📊 Performance Analysis</h6>
                            <div class="mb-3">
                                <strong>Quiz Performance:</strong> {{ round($attempt->percentage) }}% 
                                ({{ $attempt->score }}/{{ $attempt->total_questions }} correct)
                            </div>
                            
                            @if($habitRecommendations && isset($habitRecommendations['insights']))
                                <ul class="list-unstyled">
                                    @foreach($habitRecommendations['insights'] as $insight)
                                    <li class="mb-2">
                                        <i class="fas fa-info-circle text-info me-2"></i>{{ $insight }}
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="alert alert-light border">
                                    <p class="mb-2"><strong>Complete your study habits assessment to see:</strong></p>
                                    <ul class="mb-0">
                                        <li>Predicted exam score based on your habits</li>
                                        <li>Impact of sleep on learning capacity</li>
                                        <li>Social media usage analysis</li>
                                        <li>Personalized study insights</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Tailored Recommendations (Right Side) - Based on Study Habits -->
                        <div class="col-md-6">
                            <h6 class="text-success mb-3">🎯 Tailored Recommendations</h6>
                            
                            @if($habitRecommendations && isset($habitRecommendations['recommendations']))
                                @foreach($habitRecommendations['recommendations'] as $rec)
                                <div class="mb-3 p-3 border rounded" style="border-left: 4px solid 
                                    @if($rec['priority'] === 'High') #dc3545 
                                    @elseif($rec['priority'] === 'Medium') #ffc107 
                                    @else #28a745 @endif !important;">
                                    <strong class="text-{{ $rec['priority'] === 'High' ? 'danger' : ($rec['priority'] === 'Medium' ? 'warning' : 'success') }}">
                                        {{ $rec['category'] }} ({{ $rec['priority'] }} Priority)
                                    </strong><br>
                                    <span>{{ $rec['recommendation'] }}</span><br>
                                    <small class="text-muted">{{ $rec['impact'] }}</small>
                                </div>
                                @endforeach
                            @else
                                <div class="alert alert-warning border-start border-4 border-warning">
                                    <h6 class="mb-2">
                                        <i class="fas fa-clipboard-check me-2"></i>
                                        Get Personalized Recommendations
                                    </h6>
                                    <p class="mb-2">Complete your study habits assessment to receive tailored advice about:</p>
                                    <ul class="mb-2">
                                        <li>Sleep optimization for better retention</li>
                                        <li>Social media usage management</li>
                                        <li>Study schedule improvements</li>
                                        <li>Stress management techniques</li>
                                    </ul>
                                    <a href="{{ route('learning_journey.habits') }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-clipboard-list me-1"></i> Take Assessment Now
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Performance Insights -->
            @if(isset($smartRecommendations['performance_insights']))
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-chart-line"></i> Performance Insights
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $insights = $smartRecommendations['performance_insights'];
                        $overallMastery = $insights['overall_mastery'] ?? 0;
                        $masteryColor = $overallMastery >= 75 ? 'success' : ($overallMastery >= 50 ? 'warning' : 'danger');
                    @endphp
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-{{ $masteryColor }} border-start border-5">
                                <h6 class="mb-2">{{ $insights['message'] }}</h6>
                                @if(isset($insights['overall_mastery']))
                                <div class="mt-2">
                                    <strong>Overall Topic Mastery:</strong>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-{{ $masteryColor }}" role="progressbar" 
                                             style="width: {{ $overallMastery }}%;" 
                                             aria-valuenow="{{ $overallMastery }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $overallMastery }}%
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        @if(!empty($insights['strongest_topics']))
                        <div class="col-md-6">
                            <div class="card border-success mb-3">
                                <div class="card-header bg-success text-white">
                                    <strong>✅ Your Strongest Topics</strong>
                                </div>
                                <div class="card-body">
                                    @foreach($insights['strongest_topics'] as $topic)
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <span>{{ ucwords(str_replace('_', ' ', $topic['name'])) }}</span>
                                        <span class="badge bg-success">{{ $topic['score'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(!empty($insights['weakest_topics']))
                        <div class="col-md-6">
                            <div class="card border-danger mb-3">
                                <div class="card-header bg-danger text-white">
                                    <strong>⚠️ Topics Needing Focus</strong>
                                </div>
                                <div class="card-body">
                                    @foreach($insights['weakest_topics'] as $topic)
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <span>{{ ucwords(str_replace('_', ' ', $topic['name'])) }}</span>
                                        <span class="badge bg-danger">{{ $topic['score'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Weak Topics with Action Plans -->
            @if(!empty($smartRecommendations['weak_topics']))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bullseye"></i> Priority Topics for Improvement
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($smartRecommendations['weak_topics'] as $topic)
                    @php
                        $priorityColors = [
                            'Critical - Immediate Action Required' => ['bg' => 'danger', 'border' => '#dc3545', 'icon' => 'fa-exclamation-triangle'],
                            'High - Address Soon' => ['bg' => 'warning', 'border' => '#ffc107', 'icon' => 'fa-exclamation-circle'],
                            'Medium - Plan to Review' => ['bg' => 'info', 'border' => '#17a2b8', 'icon' => 'fa-info-circle'],
                            'Low - Monitor Progress' => ['bg' => 'secondary', 'border' => '#6c757d', 'icon' => 'fa-check-circle']
                        ];
                        $priority = $priorityColors[$topic['priority_label']] ?? $priorityColors['Medium - Plan to Review'];
                    @endphp
                    
                    <div class="mb-4 p-3 border rounded" style="border-left: 5px solid {{ $priority['border'] }} !important; background-color: rgba({{ $priority['bg'] === 'danger' ? '220, 53, 69' : ($priority['bg'] === 'warning' ? '255, 193, 7' : ($priority['bg'] === 'info' ? '23, 162, 184' : '108, 117, 125')) }}, 0.1);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-1">
                                    <i class="fas {{ $priority['icon'] }} text-{{ $priority['bg'] }} me-2"></i>
                                    {{ ucwords(str_replace('_', ' ', $topic['topic'])) }}
                                </h5>
                                <span class="badge bg-{{ $priority['bg'] }}">{{ $topic['priority_label'] }}</span>
                            </div>
                            <div class="text-end">
                                <div class="mb-1">
                                    <strong>Mastery:</strong>
                                    <span class="badge bg-{{ $priority['bg'] }}">{{ $topic['mastery_score'] }}%</span>
                                </div>
                                <small class="text-muted">
                                    {{ $topic['questions_correct'] }}/{{ $topic['questions_attempted'] }} correct
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $priority['bg'] }}" role="progressbar" 
                                     style="width: {{ $topic['mastery_score'] }}%;" 
                                     aria-valuenow="{{ $topic['mastery_score'] }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $topic['mastery_score'] }}%
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-light mb-2">
                            <strong>📋 Recommended Action:</strong><br>
                            {{ $topic['action'] }}
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>⏱️ Time Needed:</strong> {{ $topic['estimated_time'] }}</span>
                            @if(isset($topic['resources']) && !empty($topic['resources']))
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#resources-{{ $loop->index }}" aria-expanded="false">
                                <i class="fas fa-book"></i> Study Resources
                            </button>
                            @endif
                        </div>
                        
                        @if(isset($topic['resources']) && !empty($topic['resources']))
                        <div class="collapse mt-2" id="resources-{{ $loop->index }}">
                            <div class="card card-body">
                                <ul class="mb-0">
                                    @foreach($topic['resources'] as $resource)
                                    <li>{{ $resource }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Revision Strategy -->
            @if(!empty($smartRecommendations['revision_strategy']))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-book-reader"></i> Your Personalized Revision Strategy
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($smartRecommendations['revision_strategy'] as $strategy)
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-light">
                            <strong>{{ $strategy['title'] }}</strong>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">{{ $strategy['description'] }}</p>
                            <div class="row">
                                @if(isset($strategy['action']))
                                <div class="col-md-6">
                                    <small><strong>Action:</strong> {{ $strategy['action'] }}</small>
                                </div>
                                @endif
                                @if(isset($strategy['daily_time']))
                                <div class="col-md-6">
                                    <small><strong>Daily Time:</strong> {{ $strategy['daily_time'] }}</small>
                                </div>
                                @endif
                            </div>
                            @if(isset($strategy['expected_improvement']))
                            <div class="alert alert-success mt-2 mb-0">
                                <small><strong>Expected Result:</strong> {{ $strategy['expected_improvement'] }}</small>
                            </div>
                            @endif
                            @if(isset($strategy['impact']))
                            <div class="alert alert-info mt-2 mb-0">
                                <small><strong>Impact:</strong> {{ $strategy['impact'] }}</small>
                            </div>
                            @endif
                            @if(isset($strategy['timeline']))
                            <div class="mt-2">
                                <small class="text-muted"><strong>Timeline:</strong> {{ $strategy['timeline'] }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 5-Day Study Schedule -->
            @if(!empty($smartRecommendations['study_schedule']))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Your 5-Day Study Plan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($smartRecommendations['study_schedule'] as $day => $schedule)
                        <div class="col-md-12 mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <strong>{{ $schedule['day'] }}</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Focus Topic:</strong><br>
                                            <span class="text-primary">{{ ucwords(str_replace('_', ' ', $schedule['focus_topic'])) }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Activity:</strong><br>
                                            {{ $schedule['activity'] }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Duration:</strong><br>
                                            {{ $schedule['duration'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Practice Recommendations -->
            @if(!empty($smartRecommendations['practice_recommendations']))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-dumbbell"></i> Practice Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($smartRecommendations['practice_recommendations'] as $practice)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $practice['title'] }}</h6>
                                    <p class="card-text">{{ $practice['description'] }}</p>
                                    <ul class="list-unstyled">
                                        @foreach($practice['specific_actions'] as $action)
                                        <li><i class="fas fa-check text-success me-2"></i>{{ $action }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            
            @elseif($studyRecommendations)
            <!-- Fallback to Old Recommendations System -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb"></i> Personalized Study Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">📊 Performance Analysis</h6>
                            <div class="mb-3">
                                <strong>Quiz Performance:</strong> {{ round($attempt->percentage) }}% 
                                ({{ $attempt->score }}/{{ $attempt->total_questions }} correct)
                            </div>
                            @if(isset($studyRecommendations['insights']))
                            <ul class="list-unstyled">
                                @foreach($studyRecommendations['insights'] as $insight)
                                <li class="mb-2">
                                    <i class="fas fa-info-circle text-info me-2"></i>{{ $insight }}
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success mb-3">🎯 Tailored Recommendations</h6>
                            @if(isset($studyRecommendations['recommendations']))
                            @foreach($studyRecommendations['recommendations'] as $rec)
                            <div class="mb-3 p-3 border rounded" style="border-left: 4px solid 
                                @if($rec['priority'] === 'High') #dc3545 @elseif($rec['priority'] === 'Medium') #ffc107 @else #28a745 @endif !important;">
                                <strong class="text-{{ $rec['priority'] === 'High' ? 'danger' : ($rec['priority'] === 'Medium' ? 'warning' : 'success') }}">
                                    {{ $rec['category'] }} ({{ $rec['priority'] }} Priority)
                                </strong><br>
                                <span>{{ $rec['recommendation'] }}</span><br>
                                <small class="text-muted">{{ $rec['impact'] }}</small>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- No Study Habits Data Available -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Get Personalized Study Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2">Complete your study habits assessment to get personalized recommendations!</h6>
                            <p class="text-muted mb-0">
                                By completing a quick assessment about your study habits, we can provide you with:
                            </p>
                            <ul class="mt-2">
                                <li>Personalized study schedule based on your preferences</li>
                                <li>Tailored recommendations for improvement</li>
                                <li>Detailed performance analysis</li>
                                <li>Custom study techniques that match your learning style</li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <a href="{{ route('learning_journey.habits') }}" class="btn btn-warning btn-lg">
                                <i class="fas fa-clipboard-check"></i> Take Assessment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Study Plan Section -->
            @if(isset($studyRecommendations['study_plan']))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Your Personalized Study Plan
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($studyRecommendations['study_plan']['weekly_schedule']))
                    <h6 class="mb-3">📅 Weekly Schedule</h6>
                    <div class="row">
                        @foreach($studyRecommendations['study_plan']['weekly_schedule'] as $day => $schedule)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white text-center py-2">
                                    <strong>{{ $day }}</strong>
                                </div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>Subjects:</strong> {{ $schedule['subjects'] }}</p>
                                    <p class="mb-1"><strong>Hours:</strong> {{ $schedule['study_hours'] }}</p>
                                    <p class="mb-0"><strong>Time:</strong> {{ $schedule['time_slot'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if(isset($studyRecommendations['study_plan']['total_weekly_hours']))
                    <div class="text-center mt-3 p-3 bg-light rounded">
                        <strong>Total Weekly Study Hours: {{ $studyRecommendations['study_plan']['total_weekly_hours'] }}</strong>
                    </div>
                    @endif
                    @endif

                    @if(isset($studyRecommendations['study_plan']['focus_areas']))
                    <div class="mt-4">
                        <h6 class="mb-3">🎯 Focus Areas for Improvement</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($studyRecommendations['study_plan']['focus_areas'] as $area)
                            <span class="badge bg-warning text-dark">{{ $area }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h6 class="mb-3">What would you like to do next?</h6>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('quizzes.take', $attempt->quiz) }}" class="btn btn-success">
                            <i class="fas fa-redo"></i> Retake Quiz
                        </a>
                        <a href="{{ route('notes.show', $attempt->quiz->note) }}" class="btn btn-info">
                            <i class="fas fa-book-open"></i> Study Source Material
                        </a>
                        @if($studyRecommendations)
                        <a href="{{ route('questionnaire.index') }}" class="btn btn-warning">
                            <i class="fas fa-chart-bar"></i> Get Detailed Study Plan
                        </a>
                        @else
                        <a href="{{ route('learning_journey.habits') }}" class="btn btn-warning">
                            <i class="fas fa-clipboard-check"></i> Take Study Assessment
                        </a>
                        @endif
                        <a href="{{ route('quizzes.show', $attempt->quiz) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> Review Quiz Details
                        </a>
                        <a href="{{ route('notes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Notes
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Study Tips -->
            @if($studyRecommendations && isset($studyRecommendations['study_plan']['study_techniques']))
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap"></i> Recommended Study Techniques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($studyRecommendations['study_plan']['study_techniques'] as $technique)
                        <div class="col-md-6 mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>{{ $technique }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection