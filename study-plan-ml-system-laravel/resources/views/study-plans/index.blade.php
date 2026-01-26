@extends('layouts.app')

@section('content')
<!-- Page Title Section -->
<div class="page-title">
    <h1><i class="fas fa-calendar-alt"></i> AI-Generated Study Plans</h1>
    <p class="subtitle">View and manage your personalized study schedules powered by machine learning</p>
    <div class="actions">
        <a href="{{ route('questionnaire.index') }}" class="btn btn-primary">
            <i class="fas fa-brain"></i> Take New Assessment
        </a>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewInsightsModal">
            <i class="fas fa-chart-line"></i> View Analytics
        </button>
    </div>
</div>

<!-- Stats Overview -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-robot text-primary"></i>
                    </div>
                    <div class="stats-number">3</div>
                    <p class="stats-label">AI GENERATED PLANS</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-tasks text-success"></i>
                    </div>
                    <div class="stats-number">23</div>
                    <p class="stats-label">COMPLETED TASKS</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div class="stats-number">8</div>
                    <p class="stats-label">PENDING TASKS</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-trophy text-info"></i>
                    </div>
                    <div class="stats-number">78%</div>
                    <p class="stats-label">PREDICTED PERFORMANCE</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Week Overview -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-calendar-week"></i> Current Week: August 12-18, 2025
            </h5>
            <div class="float-end">
                <button class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-chevron-left"></i> Previous Week
                </button>
                <button class="btn btn-sm btn-outline-primary">
                    Next Week <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $index => $day)
                <div class="col-lg-1-7 col-md-3 col-sm-6 mb-3">
                    <div class="day-card {{ $index == 2 ? 'today' : '' }}">
                        <div class="day-header">
                            <h6>{{ $day }}</h6>
                            <span class="date">{{ 12 + $index }}</span>
                        </div>
                        <div class="day-tasks">
                            @if($index == 0)
                                <div class="task completed">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Math Review</span>
                                </div>
                                <div class="task completed">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Physics Lab</span>
                                </div>
                            @elseif($index == 1)
                                <div class="task completed">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Essay Writing</span>
                                </div>
                                <div class="task pending">
                                    <i class="fas fa-clock"></i>
                                    <span>Chemistry Study</span>
                                </div>
                            @elseif($index == 2)
                                <div class="task active">
                                    <i class="fas fa-play"></i>
                                    <span>Literature Analysis</span>
                                </div>
                                <div class="task pending">
                                    <i class="fas fa-clock"></i>
                                    <span>Math Practice</span>
                                </div>
                            @elseif($index == 3)
                                <div class="task pending">
                                    <i class="fas fa-clock"></i>
                                    <span>History Research</span>
                                </div>
                                <div class="task pending">
                                    <i class="fas fa-clock"></i>
                                    <span>Science Project</span>
                                </div>
                            @else
                                <div class="task future">
                                    <i class="fas fa-calendar"></i>
                                    <span>Planned Study</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Study Plans List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-list"></i> My Study Plans
            </h5>
            <div class="float-end">
                <select class="form-control form-control-sm d-inline-block w-auto">
                    <option>All Plans</option>
                    <option>Active</option>
                    <option>Completed</option>
                    <option>Archived</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Plan Name</th>
                            <th>Subject</th>
                            <th>Duration</th>
                            <th>Progress</th>
                            <th>Next Task</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>Calculus Mastery</strong><br>
                                <small class="text-muted">Comprehensive calculus study plan</small>
                            </td>
                            <td><span class="badge bg-primary">Mathematics</span></td>
                            <td>4 weeks</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: 75%">75%</div>
                                </div>
                            </td>
                            <td>
                                <small>Integration Practice</small><br>
                                <span class="text-muted">Due: Tomorrow</span>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Physics Fundamentals</strong><br>
                                <small class="text-muted">Classical mechanics and thermodynamics</small>
                            </td>
                            <td><span class="badge bg-info">Physics</span></td>
                            <td>6 weeks</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-warning" style="width: 45%">45%</div>
                                </div>
                            </td>
                            <td>
                                <small>Newton's Laws Review</small><br>
                                <span class="text-muted">Due: Friday</span>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Literature Analysis</strong><br>
                                <small class="text-muted">Modern American literature study</small>
                            </td>
                            <td><span class="badge bg-warning">English</span></td>
                            <td>3 weeks</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: 90%">90%</div>
                                </div>
                            </td>
                            <td>
                                <small>Final Essay</small><br>
                                <span class="text-muted">Due: Next Week</span>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Generate AI Plan Modal -->
<div class="modal fade" id="generatePlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-robot"></i> Generate AI Study Plan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Subject Area</label>
                                <select class="form-control">
                                    <option>Mathematics</option>
                                    <option>Physics</option>
                                    <option>Chemistry</option>
                                    <option>Biology</option>
                                    <option>English</option>
                                    <option>History</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Study Duration</label>
                                <select class="form-control">
                                    <option>1 week</option>
                                    <option>2 weeks</option>
                                    <option>1 month</option>
                                    <option>2 months</option>
                                    <option>Custom</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Learning Goals</label>
                        <textarea class="form-control" rows="3" placeholder="Describe what you want to achieve..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Daily Study Time (hours)</label>
                                <input type="number" class="form-control" min="1" max="8" value="2">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Difficulty Level</label>
                                <select class="form-control">
                                    <option>Beginner</option>
                                    <option>Intermediate</option>
                                    <option>Advanced</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-magic"></i> Generate Plan
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.col-lg-1-7 {
    width: 14.285714%;
}

.day-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    height: 200px;
    overflow-y: auto;
    transition: all 0.3s ease;
}

.day-card.today {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
}

.day-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.day-header {
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 10px;
    margin-bottom: 10px;
}

.day-header h6 {
    margin: 0;
    font-weight: 600;
    color: #334155;
}

.day-header .date {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2563eb;
}

.day-tasks {
    space-y: 8px;
}

.task {
    display: flex;
    align-items: center;
    padding: 8px;
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 0.85rem;
}

.task i {
    margin-right: 8px;
    width: 16px;
}

.task.completed {
    background: rgba(34, 197, 94, 0.1);
    color: #059669;
}

.task.active {
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
}

.task.pending {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.task.future {
    background: rgba(148, 163, 184, 0.1);
    color: #64748b;
}

@media (max-width: 768px) {
    .col-lg-1-7 {
        width: 50%;
    }
}

@media (max-width: 576px) {
    .col-lg-1-7 {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add interactivity for task completion
    document.querySelectorAll('.task.pending').forEach(task => {
        task.addEventListener('click', function() {
            this.classList.remove('pending');
            this.classList.add('completed');
            this.querySelector('i').classList.remove('fa-clock');
            this.querySelector('i').classList.add('fa-check-circle');
        });
    });
});
</script>
@endpush
@endsection
