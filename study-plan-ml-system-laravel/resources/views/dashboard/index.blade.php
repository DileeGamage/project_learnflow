@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">📊 Dashboard</h1>
    <div class="text-muted">
        <i class="fas fa-calendar"></i> {{ now()->format('F j, Y') }}
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-sticky-note text-primary"></i>
                </div>
                <h3 class="stats-number">{{ $stats['total_notes'] }}</h3>
                <p class="stats-label">Total Notes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-question-circle text-success"></i>
                </div>
                <h3 class="stats-number">{{ $stats['total_questionnaires'] }}</h3>
                <p class="stats-label">Questionnaires</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-chart-line text-info"></i>
                </div>
                <h3 class="stats-number">{{ $stats['completed_tests'] }}</h3>
                <p class="stats-label">Tests Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-book text-warning"></i>
                </div>
                <h3 class="stats-number">{{ $stats['subjects'] }}</h3>
                <p class="stats-label">Subjects</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Performance Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line"></i> Weekly Performance
                </h5>
            </div>
            <div class="card-body">
                <canvas id="performanceChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Subject Distribution -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-pie-chart"></i> Subject Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="subjectChart" width="300" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Notes -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sticky-note"></i> Recent Notes
                </h5>
                <a href="{{ route('notes.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recentNotes as $note)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-1">
                            <a href="{{ route('notes.show', $note) }}" class="text-decoration-none">
                                {{ Str::limit($note->title, 40) }}
                            </a>
                        </h6>
                        <small class="text-muted">
                            <span class="badge bg-primary">{{ $note->subject_area }}</span>
                            {{ $note->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('notes.show', $note) }}"><i class="fas fa-eye"></i> View</a></li>
                            <li><a class="dropdown-item" href="{{ route('notes.edit', $note) }}"><i class="fas fa-edit"></i> Edit</a></li>
                        </ul>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-sticky-note fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No notes yet</p>
                    <a href="{{ route('notes.create') }}" class="btn btn-primary btn-sm">Create Your First Note</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="{{ route('notes.create') }}" class="btn btn-primary w-100 d-flex align-items-center">
                            <i class="fas fa-plus me-2"></i>
                            <div class="text-start">
                                <div class="fw-bold">Create Note</div>
                                <small>Add new study material</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('questionnaires.create') }}" class="btn btn-success w-100 d-flex align-items-center">
                            <i class="fas fa-question-circle me-2"></i>
                            <div class="text-start">
                                <div class="fw-bold">Create Quiz</div>
                                <small>Generate questions</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('workplace.index') }}" class="btn btn-info w-100 d-flex align-items-center">
                            <i class="fas fa-briefcase me-2"></i>
                            <div class="text-start">
                                <div class="fw-bold">My Workplace</div>
                                <small>Manage materials</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('analytics.index') }}" class="btn btn-warning w-100 d-flex align-items-center">
                            <i class="fas fa-chart-bar me-2"></i>
                            <div class="text-start">
                                <div class="fw-bold">Analytics</div>
                                <small>View progress</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock"></i> Recent Activity
                </h5>
            </div>
            <div class="card-body">
                @if($recentResults->count() > 0)
                    @foreach($recentResults as $result)
                    <div class="d-flex align-items-center mb-3">
                        <div class="activity-icon">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div class="ms-3">
                            <div class="fw-bold">Quiz Completed</div>
                            <small class="text-muted">
                                Score: {{ $result->score ?? 'N/A' }}% • {{ $result->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                    @endforeach
                @else
                <div class="text-center py-3">
                    <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No recent activity</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Chart
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'line',
    data: {
        labels: @json($weeklyPerformance->pluck('week')),
        datasets: [{
            label: 'Average Score',
            data: @json($weeklyPerformance->pluck('score')),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Subject Distribution Chart
const subjectCtx = document.getElementById('subjectChart').getContext('2d');
const subjectChart = new Chart(subjectCtx, {
    type: 'doughnut',
    data: {
        labels: @json($subjectStats->pluck('subject_area')),
        datasets: [{
            data: @json($subjectStats->pluck('total')),
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6f42c1',
                '#20c997',
                '#fd7e14'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endsection
