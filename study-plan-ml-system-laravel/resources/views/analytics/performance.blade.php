@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-trophy"></i> Performance Analytics</h1>
        <p class="subtitle">Deep dive into your learning performance and study patterns</p>
    </div>

    <!-- Performance Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Best Performing Subject</h6>
                    @if($bestSubject)
                        <div class="display-6 text-success mb-2">
                            {{ $bestSubject->subject_area }}
                        </div>
                        <div class="h3 text-success">{{ number_format($bestSubject->avg_score, 1) }}%</div>
                        <small class="text-muted">{{ $bestSubject->attempts }} attempts</small>
                    @else
                        <div class="text-muted">No data yet</div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Average Time per Quiz</h6>
                    <div class="display-6 text-primary mb-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="h3 text-primary">{{ gmdate('i:s', $avgTimePerQuiz) }}</div>
                    <small class="text-muted">Minutes:Seconds</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Needs Improvement</h6>
                    @if($worstSubject && $worstSubject->avg_score < 70)
                        <div class="display-6 text-warning mb-2">
                            {{ $worstSubject->subject_area }}
                        </div>
                        <div class="h3 text-warning">{{ number_format($worstSubject->avg_score, 1) }}%</div>
                        <small class="text-muted">{{ $worstSubject->attempts }} attempts</small>
                    @else
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-3x"></i>
                            <p class="mt-2">Great performance across all subjects!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Daily Performance Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    @if($dailyPerformance->count() > 0)
                        <canvas id="performanceTrendChart" height="80"></canvas>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <p>No performance data available for the last 30 days</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Progress Trend</h5>
                </div>
                <div class="card-body">
                    @if(count($progressTrend) > 0)
                        <div style="position: relative; height: 250px;">
                            <canvas id="progressTrendChart"></canvas>
                        </div>
                        <div class="mt-3">
                            @php
                                $firstWeek = $progressTrend[0]['score'] ?? 0;
                                $lastWeek = end($progressTrend)['score'] ?? 0;
                                $improvement = $lastWeek - $firstWeek;
                            @endphp
                            @if($improvement > 0)
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-arrow-up"></i> 
                                    <strong>Improving!</strong> 
                                    Up {{ number_format($improvement, 1) }}% in 4 weeks
                                </div>
                            @elseif($improvement < 0)
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-arrow-down"></i> 
                                    <strong>Needs Focus</strong> 
                                    Down {{ number_format(abs($improvement), 1) }}% in 4 weeks
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-minus"></i> 
                                    <strong>Stable</strong> 
                                    Consistent performance
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>Not enough data to show trends</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book-open"></i> Subject-wise Performance</h5>
                </div>
                <div class="card-body">
                    @if($subjectPerformance->count() > 0)
                        <canvas id="subjectPerformanceChart" height="150"></canvas>
                        <div class="mt-4">
                            <h6 class="mb-3">Subject Details</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th class="text-center">Attempts</th>
                                            <th class="text-center">Avg Score</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjectPerformance->sortByDesc('avg_score') as $subject)
                                            <tr>
                                                <td>{{ $subject->subject_area }}</td>
                                                <td class="text-center">{{ $subject->attempts }}</td>
                                                <td class="text-center">
                                                    <strong>{{ number_format($subject->avg_score, 1) }}%</strong>
                                                </td>
                                                <td class="text-center">
                                                    @if($subject->avg_score >= 80)
                                                        <span class="badge bg-success">Excellent</span>
                                                    @elseif($subject->avg_score >= 70)
                                                        <span class="badge bg-info">Good</span>
                                                    @elseif($subject->avg_score >= 60)
                                                        <span class="badge bg-warning">Fair</span>
                                                    @else
                                                        <span class="badge bg-danger">Needs Work</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-book-open fa-3x mb-3"></i>
                            <p>No subject data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Time of Day Performance</h5>
                </div>
                <div class="card-body">
                    @if($timeOfDayPerformance->count() > 0)
                        <canvas id="timeOfDayChart" height="150"></canvas>
                        <div class="mt-4">
                            @php
                                $bestHour = $timeOfDayPerformance->sortByDesc('avg_score')->first();
                                $bestTime = \Carbon\Carbon::createFromTime($bestHour->hour)->format('g A');
                            @endphp
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb"></i> 
                                <strong>Peak Performance:</strong> 
                                You perform best around {{ $bestTime }} 
                                ({{ number_format($bestHour->avg_score, 1) }}% average score)
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <p>Not enough data to analyze time patterns</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Study Habits Correlation -->
    @if($questionnaireResults->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-brain"></i> Study Habits & Performance Correlation</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Analysis of how your study habits correlate with quiz performance
                    </p>
                    <div class="row">
                        @foreach($questionnaireResults->take(3) as $result)
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $result->questionnaire->title ?? 'Assessment' }}</h6>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                Completed {{ $result->created_at->diffForHumans() }}
                                            </small>
                                        </p>
                                        @if($result->score)
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" role="progressbar" 
                                                     style="width: {{ $result->score }}%">
                                                    {{ number_format($result->score, 1) }}%
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($questionnaireResults->count() > 3)
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="toggleAllQuestionnaires()">
                                <i class="fas fa-chevron-down"></i> View All Assessments
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recommendations -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Personalized Recommendations</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($worstSubject && $worstSubject->avg_score < 70)
                            <div class="col-md-6 mb-3">
                                <div class="recommendation-item">
                                    <i class="fas fa-flag text-warning fa-2x mb-2"></i>
                                    <h6>Focus Area Identified</h6>
                                    <p>Consider reviewing {{ $worstSubject->subject_area }} materials. 
                                    Your current average is {{ number_format($worstSubject->avg_score, 1) }}%.</p>
                                    <a href="{{ route('notes.index') }}" class="btn btn-sm btn-outline-warning">
                                        Review Notes <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        @endif
                        
                        @if($dailyPerformance->count() < 5)
                            <div class="col-md-6 mb-3">
                                <div class="recommendation-item">
                                    <i class="fas fa-calendar-check text-info fa-2x mb-2"></i>
                                    <h6>Increase Practice Frequency</h6>
                                    <p>Regular practice helps! Try to take quizzes more consistently 
                                    to build a stronger learning pattern.</p>
                                    <a href="{{ route('notes.index') }}" class="btn btn-sm btn-outline-info">
                                        Practice Now <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        @endif
                        
                        @if($timeOfDayPerformance->count() > 0)
                            @php
                                $bestHour = $timeOfDayPerformance->sortByDesc('avg_score')->first();
                                $bestTime = \Carbon\Carbon::createFromTime($bestHour->hour)->format('g A');
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="recommendation-item">
                                    <i class="fas fa-clock text-success fa-2x mb-2"></i>
                                    <h6>Optimal Study Time</h6>
                                    <p>Your performance peaks around {{ $bestTime }}. 
                                    Try scheduling important study sessions during this time!</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($bestSubject && $bestSubject->avg_score >= 85)
                            <div class="col-md-6 mb-3">
                                <div class="recommendation-item">
                                    <i class="fas fa-star text-warning fa-2x mb-2"></i>
                                    <h6>Strength Identified</h6>
                                    <p>Excellent work in {{ $bestSubject->subject_area }}! 
                                    Your strong performance ({{ number_format($bestSubject->avg_score, 1) }}%) 
                                    shows great mastery.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <a href="{{ route('analytics.quiz') }}" class="btn btn-primary me-2">
                <i class="fas fa-chart-pie"></i> View Quiz Analytics
            </a>
            <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chart-bar"></i> Back to Overview
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance Trend Chart
    @if($dailyPerformance->count() > 0)
    const performanceTrendCtx = document.getElementById('performanceTrendChart');
    if (performanceTrendCtx) {
        new Chart(performanceTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyPerformance->pluck('date')->map(function($date) {
                    return \Carbon\Carbon::parse($date)->format('M d');
                })) !!},
                datasets: [{
                    label: 'Average Score (%)',
                    data: {!! json_encode($dailyPerformance->pluck('avg_score')->map(function($score) {
                        return round($score, 1);
                    })) !!},
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    @endif

    // Progress Trend Chart
    @if(count($progressTrend) > 0)
    const progressTrendCtx = document.getElementById('progressTrendChart');
    if (progressTrendCtx) {
        new Chart(progressTrendCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($progressTrend, 'week')) !!},
                datasets: [{
                    label: 'Weekly Average (%)',
                    data: {!! json_encode(array_column($progressTrend, 'score')) !!},
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    @endif

    // Subject Performance Chart
    @if($subjectPerformance->count() > 0)
    const subjectPerformanceCtx = document.getElementById('subjectPerformanceChart');
    if (subjectPerformanceCtx) {
        new Chart(subjectPerformanceCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($subjectPerformance->pluck('subject_area')) !!},
                datasets: [{
                    label: 'Average Score (%)',
                    data: {!! json_encode($subjectPerformance->pluck('avg_score')->map(function($score) {
                        return round($score, 1);
                    })) !!},
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgb(255, 159, 64)',
                    borderWidth: 2
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    @endif

    // Time of Day Chart
    @if($timeOfDayPerformance->count() > 0)
    const timeOfDayCtx = document.getElementById('timeOfDayChart');
    if (timeOfDayCtx) {
        new Chart(timeOfDayCtx, {
            type: 'radar',
            data: {
                labels: {!! json_encode($timeOfDayPerformance->map(function($item) {
                    return \Carbon\Carbon::createFromTime($item->hour)->format('g A');
                })) !!},
                datasets: [{
                    label: 'Average Score (%)',
                    data: {!! json_encode($timeOfDayPerformance->pluck('avg_score')->map(function($score) {
                        return round($score, 1);
                    })) !!},
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgb(153, 102, 255)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgb(153, 102, 255)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(153, 102, 255)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    @endif
});
</script>

<style>
.recommendation-item {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
    height: 100%;
}

.recommendation-item i {
    display: block;
}

.recommendation-item h6 {
    margin: 1rem 0 0.5rem 0;
    font-weight: 600;
}

.recommendation-item p {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1rem;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    font-weight: 600;
}

.bg-primary .card-header {
    background-color: #0d6efd !important;
}
</style>
@endsection
