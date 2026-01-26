@extends('layouts.app')

@section('title', 'Take Quiz: ' . $quiz->title)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Quiz Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-play-circle"></i> {{ $quiz->title }}
                            </h4>
                            <small>From: {{ $quiz->note->title }}</small>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-light text-dark fs-6" id="timer">
                                <i class="fas fa-clock"></i> <span id="time-display">00:00</span>
                            </div>
                            <div class="small mt-1">Estimated: {{ $quiz->formatted_estimated_time }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quiz Form -->
            <form id="quiz-form">
                @csrf
                <input type="hidden" id="time-taken" name="time_taken" value="0">
                
                <!-- Multiple Choice Questions -->
                @if(isset($quiz->formatted_questions['multiple_choice']) && count($quiz->formatted_questions['multiple_choice']) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list-ul text-primary"></i> 
                            Multiple Choice Questions 
                            <span class="badge bg-primary">{{ count($quiz->formatted_questions['multiple_choice']) }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($quiz->formatted_questions['multiple_choice'] as $index => $question)
                        <div class="question-item mb-4 p-3 border rounded">
                            <h6 class="fw-bold mb-3">
                                {{ $index + 1 }}. {{ $question['question'] }}
                            </h6>
                            <div class="options ms-3">
                                @foreach($question['options'] as $optionIndex => $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" 
                                           name="multiple_choice_{{ $index }}" 
                                           id="mcq_{{ $index }}_{{ $optionIndex }}"
                                           value="{{ substr($option, 0, 1) }}">
                                    <label class="form-check-label" for="mcq_{{ $index }}_{{ $optionIndex }}">
                                        {{ $option }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- True/False Questions -->
                @if(isset($quiz->formatted_questions['true_false']) && count($quiz->formatted_questions['true_false']) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-toggle-on text-success"></i> 
                            True/False Questions 
                            <span class="badge bg-success">{{ count($quiz->formatted_questions['true_false']) }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($quiz->formatted_questions['true_false'] as $index => $question)
                        <div class="question-item mb-4 p-3 border rounded">
                            <h6 class="fw-bold mb-3">
                                {{ $index + 1 }}. {{ $question['question'] }}
                            </h6>
                            <div class="options ms-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" 
                                           name="true_false_{{ $index }}" 
                                           id="tf_{{ $index }}_true"
                                           value="True">
                                    <label class="form-check-label" for="tf_{{ $index }}_true">
                                        <i class="fas fa-check text-success"></i> True
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" 
                                           name="true_false_{{ $index }}" 
                                           id="tf_{{ $index }}_false"
                                           value="False">
                                    <label class="form-check-label" for="tf_{{ $index }}_false">
                                        <i class="fas fa-times text-danger"></i> False
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Fill in the Blank Questions -->
                @if(isset($quiz->questions['fill_blank']) && count($quiz->questions['fill_blank']) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-edit text-warning"></i> 
                            Fill in the Blank Questions 
                            <span class="badge bg-warning">{{ count($quiz->questions['fill_blank']) }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($quiz->questions['fill_blank'] as $index => $question)
                        <div class="question-item mb-4 p-3 border rounded">
                            <h6 class="fw-bold mb-3">
                                {{ $index + 1 }}. {{ $question['question'] }}
                            </h6>
                            <div class="ms-3">
                                <input type="text" 
                                       class="form-control" 
                                       name="fill_blank_{{ $index }}"
                                       placeholder="Type your answer here..."
                                       style="max-width: 300px;">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Short Answer Questions -->
                @if(isset($quiz->questions['short_answer']) && count($quiz->questions['short_answer']) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-paragraph text-info"></i> 
                            Short Answer Questions 
                            <span class="badge bg-info">{{ count($quiz->questions['short_answer']) }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($quiz->questions['short_answer'] as $index => $question)
                        <div class="question-item mb-4 p-3 border rounded">
                            <h6 class="fw-bold mb-3">
                                {{ $index + 1 }}. {{ $question['question'] }}
                            </h6>
                            <div class="ms-3">
                                <textarea class="form-control" 
                                         name="short_answer_{{ $index }}"
                                         rows="4"
                                         placeholder="Write your answer here..."></textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Submit Section -->
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" id="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted mt-1" id="progress-text">0% Complete</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg" id="submit-btn">
                            <i class="fas fa-check"></i> Submit Quiz
                        </button>
                        <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Results Modal -->
<div class="modal fade" id="resultsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-chart-line"></i> Quiz Results
                </h5>
            </div>
            <div class="modal-body" id="results-content">
                <!-- Results will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let startTime = Date.now();
let timerInterval;

// Start timer
function startTimer() {
    timerInterval = setInterval(function() {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        
        document.getElementById('time-display').textContent = 
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        document.getElementById('time-taken').value = elapsed;
    }, 1000);
}

// Update progress
function updateProgress() {
    const form = document.getElementById('quiz-form');
    const inputs = form.querySelectorAll('input[type="radio"]:checked, input[type="text"]:not(:placeholder-shown), textarea:not(:placeholder-shown)');
    const totalQuestions = {{ $quiz->total_questions }};
    
    // Count unique questions answered
    const answeredQuestions = new Set();
    
    // Check radio buttons
    form.querySelectorAll('input[type="radio"]:checked').forEach(input => {
        answeredQuestions.add(input.name);
    });
    
    // Check text inputs
    form.querySelectorAll('input[type="text"]').forEach(input => {
        if (input.value.trim() !== '') {
            answeredQuestions.add(input.name);
        }
    });
    
    // Check textareas
    form.querySelectorAll('textarea').forEach(textarea => {
        if (textarea.value.trim() !== '') {
            answeredQuestions.add(textarea.name);
        }
    });
    
    const progress = (answeredQuestions.size / totalQuestions) * 100;
    document.getElementById('progress-bar').style.width = progress + '%';
    document.getElementById('progress-text').textContent = Math.round(progress) + '% Complete';
}

// Form submission
document.getElementById('quiz-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')) {
        return;
    }
    
    // Disable submit button
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    // Collect answers
    const formData = new FormData(this);
    const answers = {};
    
    for (let [key, value] of formData.entries()) {
        if (key !== '_token' && key !== 'time_taken') {
            answers[key] = value;
        }
    }
    
    // Submit quiz
    fetch('{{ route("quizzes.submit", $quiz) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({
            answers: answers,
            time_taken: document.getElementById('time-taken').value
        })
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(timerInterval);
        
        if (data.success) {
            // Update submit button to show completion
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Quiz Completed!';
            submitBtn.className = 'btn btn-success btn-lg';
            
            // Show quick results and redirect info
            const quickResults = `
                <div class="alert alert-success text-center" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Quiz Completed!</h4>
                    <p class="mb-2"><strong>Your Score: ${data.score.score}/${data.score.total} (${data.score.percentage}%)</strong></p>
                    <hr>
                    <p class="mb-0">
                        <i class="fas fa-spinner fa-spin"></i> 
                        Generating your personalized study recommendations...
                    </p>
                </div>
            `;
            
            // Show quick results in the form area
            document.getElementById('quiz-form').innerHTML = quickResults;
            
            // Redirect after a brief delay to show the success message
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 2000);
        } else {
            alert('Error submitting quiz: ' + (data.error || 'Unknown error'));
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit Quiz';
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to submit quiz. Please try again.');
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit Quiz';
        submitBtn.disabled = false;
    });
});

// Show results
function showResults(score) {
    // This function is kept as fallback but normally we redirect to detailed results page
    const modal = new bootstrap.Modal(document.getElementById('resultsModal'));
    
    let gradeColor = 'text-success';
    if (score.percentage < 60) gradeColor = 'text-danger';
    else if (score.percentage < 70) gradeColor = 'text-warning';
    else if (score.percentage < 80) gradeColor = 'text-info';
    
    document.getElementById('results-content').innerHTML = `
        <div class="text-center mb-4">
            <div class="display-4 ${gradeColor} mb-2">
                ${score.percentage}%
            </div>
            <h4 class="${gradeColor}">Grade: ${score.grade}</h4>
            <p class="text-muted">
                You answered ${score.score} out of ${score.total} questions correctly
            </p>
        </div>
        
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="text-primary">${score.score}</h5>
                        <small class="text-muted">Correct Answers</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="text-info">${score.total}</h5>
                        <small class="text-muted">Total Questions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="text-warning">${document.getElementById('time-display').textContent}</h5>
                        <small class="text-muted">Time Taken</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Quiz
            </a>
            <a href="{{ route('notes.show', $quiz->note) }}" class="btn btn-outline-secondary">
                <i class="fas fa-sticky-note"></i> View Note
            </a>
            <div class="mt-3">
                <small class="text-muted">Note: Detailed results with study recommendations are available on the results page.</small>
            </div>
        </div>
    `;
    
    modal.show();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    startTimer();
    updateProgress();
    
    // Add event listeners to all form inputs
    document.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('change', updateProgress);
        input.addEventListener('input', updateProgress);
    });
});

// Auto-save functionality (optional)
setInterval(function() {
    // Could implement auto-save here if needed
}, 30000); // Every 30 seconds
</script>
@endsection
