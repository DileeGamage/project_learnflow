@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-chart-bar"></i> Analytics Overview</h1>
        <p class="subtitle">Your complete learning journey at a glance</p>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-file-alt text-primary"></i>
                    </div>
                    <div class="stats-number">{{ $stats['total_notes'] }}</div>
                    <p class="stats-label">TOTAL NOTES</p>
                    <small class="text-muted">Study materials created</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-brain text-success"></i>
                    </div>
                    <div class="stats-number">{{ $stats['total_quizzes'] }}</div>
                    <p class="stats-label">QUIZZES GENERATED</p>
                    <small class="text-muted">AI-powered assessments</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-tasks text-info"></i>
                    </div>
                    <div class="stats-number">{{ $stats['total_attempts'] }}</div>
                    <p class="stats-label">QUIZ ATTEMPTS</p>
                    <small class="text-muted">Total practice sessions</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-clipboard-check text-warning"></i>
                    </div>
                    <div class="stats-number">{{ $stats['total_questionnaires'] }}</div>
                    <p class="stats-label">ASSESSMENTS</p>
                    <small class="text-muted">Learning habit evaluations</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Average Score</h6>
                    <div class="display-4 text-primary mb-2">
                        {{ number_format($avgScore, 1) }}%
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ $avgScore }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">Overall performance across all quizzes</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Study Streak</h6>
                    <div class="display-4 text-success mb-2">
                        <i class="fas fa-fire"></i> {{ $studyStreak }}
                    </div>
                    <small class="text-muted">Consecutive days with activity</small>
                    @if($studyStreak >= 7)
                        <div class="badge bg-success mt-2">
                            <i class="fas fa-trophy"></i> Great consistency!
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">This Week's Activity</h6>
                    <div class="display-4 text-info mb-2">
                        {{ $recentActivity }}
                    </div>
                    <small class="text-muted">Quiz attempts in last 7 days</small>
                    @if($recentActivity > 10)
                        <div class="badge bg-info mt-2">
                            <i class="fas fa-star"></i> Highly active!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Distribution -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Subject Distribution</h5>
                </div>
                <div class="card-body">
                    @if($subjectStats->count() > 0)
                        <canvas id="subjectChart" height="250"></canvas>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <p>No subjects found yet. Start by adding study notes!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Recent Activity Timeline</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($recentAttempts->count() > 0)
                        <div class="timeline">
                            @foreach($recentAttempts as $attempt)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="timeline-icon me-3">
                                            @if($attempt->percentage >= 80)
                                                <i class="fas fa-check-circle text-success"></i>
                                            @elseif($attempt->percentage >= 60)
                                                <i class="fas fa-check-circle text-info"></i>
                                            @else
                                                <i class="fas fa-exclamation-circle text-warning"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $attempt->quiz->note->title ?? 'Quiz' }}</strong>
                                                <span class="badge bg-{{ $attempt->percentage >= 80 ? 'success' : ($attempt->percentage >= 60 ? 'info' : 'warning') }}">
                                                    {{ number_format($attempt->percentage, 1) }}%
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                {{ $attempt->created_at->diffForHumans() }}
                                                @if($attempt->quiz->note->subject_area)
                                                    • {{ $attempt->quiz->note->subject_area }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-history fa-3x mb-3"></i>
                            <p>No recent activity. Take a quiz to get started!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Notes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Recent Study Notes</h5>
                    <a href="{{ route('notes.index') }}" class="btn btn-sm btn-outline-primary">
                        View All Notes <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if($recentNotes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Quizzes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentNotes as $note)
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                {{ Str::limit($note->title, 50) }}
                                            </td>
                                            <td>
                                                @if($note->subject_area)
                                                    <span class="badge bg-light text-dark">{{ $note->subject_area }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $note->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $note->quizzes_count ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('notes.show', $note->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-file-upload fa-3x mb-3"></i>
                            <p>No notes created yet. Upload your first study material!</p>
                            <a href="{{ route('notes.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Create Note
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('notes.create') }}" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                Add New Note
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('analytics.quiz') }}" class="btn btn-outline-success btn-lg w-100">
                                <i class="fas fa-chart-line fa-2x d-block mb-2"></i>
                                Quiz Analytics
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('analytics.performance') }}" class="btn btn-outline-info btn-lg w-100">
                                <i class="fas fa-trophy fa-2x d-block mb-2"></i>
                                Performance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('notes.index') }}" class="btn btn-outline-warning btn-lg w-100">
                                <i class="fas fa-book fa-2x d-block mb-2"></i>
                                All Notes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Subject Distribution Chart
    @if($subjectStats->count() > 0)
    const subjectCtx = document.getElementById('subjectChart');
    if (subjectCtx) {
        new Chart(subjectCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($subjectStats->pluck('subject_area')) !!},
                datasets: [{
                    label: 'Notes by Subject',
                    data: {!! json_encode($subjectStats->pluck('count')) !!},
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(50, 205, 50, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} notes (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    @endif
});
</script>

<style>
.timeline-item {
    position: relative;
    padding-left: 0;
}

.timeline-icon {
    font-size: 1.5rem;
    width: 30px;
    text-align: center;
}

.stats-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stats-card .card-body {
    padding: 1.5rem;
}

.stats-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #333;
    margin: 0.5rem 0;
}

.stats-label {
    color: #666;
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
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
</style>
@endsection
