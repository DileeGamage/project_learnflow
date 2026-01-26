@extends('layouts.app')

@section('content')
<!-- Page Title Section -->
<div class="page-title">
    <h1><i class="fas fa-plus-circle"></i> Create New Study Plan</h1>
    <p class="subtitle">Design a personalized study schedule to achieve your learning goals</p>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-edit"></i> Study Plan Details
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('study-plans.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Plan Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject Area *</label>
                                    <select class="form-control" id="subject" name="subject" required>
                                        <option value="">Select Subject</option>
                                        <option value="mathematics">Mathematics</option>
                                        <option value="physics">Physics</option>
                                        <option value="chemistry">Chemistry</option>
                                        <option value="biology">Biology</option>
                                        <option value="english">English</option>
                                        <option value="history">History</option>
                                        <option value="computer_science">Computer Science</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe your study plan goals and objectives..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="daily_hours" class="form-label">Daily Study Hours</label>
                                    <input type="number" class="form-control" id="daily_hours" name="daily_hours" min="1" max="8" step="0.5" value="2">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="difficulty" class="form-label">Difficulty Level</label>
                                    <select class="form-control" id="difficulty" name="difficulty">
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate" selected>Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority Level</label>
                                    <select class="form-control" id="priority" name="priority">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Study Days</label>
                            <div class="row">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="study_days[]" value="{{ strtolower($day) }}" id="{{ strtolower($day) }}" {{ in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ strtolower($day) }}">
                                            {{ $day }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="goals" class="form-label">Learning Goals</label>
                            <textarea class="form-control" id="goals" name="goals" rows="4" placeholder="List your specific learning objectives and goals for this study plan..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('study-plans.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Study Plans
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" id="generateAiPlan">
                                    <i class="fas fa-robot"></i> Generate with AI
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Study Plan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-lightbulb"></i> Study Plan Tips
                    </h5>
                </div>
                <div class="card-body">
                    <div class="tip-item mb-3">
                        <h6><i class="fas fa-clock text-primary"></i> Time Management</h6>
                        <p class="small text-muted">Break study sessions into 25-50 minute focused blocks with short breaks.</p>
                    </div>
                    
                    <div class="tip-item mb-3">
                        <h6><i class="fas fa-target text-success"></i> Clear Goals</h6>
                        <p class="small text-muted">Set specific, measurable objectives for each study session.</p>
                    </div>
                    
                    <div class="tip-item mb-3">
                        <h6><i class="fas fa-repeat text-info"></i> Regular Review</h6>
                        <p class="small text-muted">Schedule regular review sessions to reinforce learning.</p>
                    </div>
                    
                    <div class="tip-item mb-3">
                        <h6><i class="fas fa-chart-line text-warning"></i> Track Progress</h6>
                        <p class="small text-muted">Monitor your progress and adjust the plan as needed.</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-calendar"></i> Quick Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="planPreview">
                        <p class="text-muted text-center">
                            <i class="fas fa-eye"></i><br>
                            Fill out the form to see a preview of your study plan
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update preview when form changes
    const formElements = ['title', 'subject', 'start_date', 'end_date', 'daily_hours'];
    
    formElements.forEach(elementId => {
        document.getElementById(elementId).addEventListener('input', updatePreview);
    });

    function updatePreview() {
        const title = document.getElementById('title').value || 'Untitled Plan';
        const subject = document.getElementById('subject').value || 'No subject';
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const dailyHours = document.getElementById('daily_hours').value || '2';

        let preview = `
            <div class="preview-item">
                <h6>${title}</h6>
                <p class="small">
                    <strong>Subject:</strong> ${subject.charAt(0).toUpperCase() + subject.slice(1)}<br>
                    <strong>Daily Hours:</strong> ${dailyHours} hours<br>
        `;

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const weeks = Math.floor(diffDays / 7);
            
            preview += `
                    <strong>Duration:</strong> ${weeks} weeks (${diffDays} days)<br>
                    <strong>Total Hours:</strong> ${diffDays * dailyHours} hours
            `;
        }

        preview += `
                </p>
            </div>
        `;

        document.getElementById('planPreview').innerHTML = preview;
    }

    // Generate AI Plan button
    document.getElementById('generateAiPlan').addEventListener('click', function() {
        // This would integrate with the Python ML API
        alert('AI Plan Generation will integrate with the ML system to create personalized study schedules based on your preferences and learning patterns.');
    });

    // Set default dates
    const today = new Date();
    const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
    const nextMonth = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
    
    document.getElementById('start_date').value = nextWeek.toISOString().split('T')[0];
    document.getElementById('end_date').value = nextMonth.toISOString().split('T')[0];
    
    updatePreview();
});
</script>
@endpush

@push('styles')
<style>
.tip-item {
    border-left: 3px solid #e2e8f0;
    padding-left: 15px;
}

.tip-item h6 {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.preview-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 15px;
}

.form-check-input:checked {
    background-color: #2563eb;
    border-color: #2563eb;
}
</style>
@endpush
@endsection
