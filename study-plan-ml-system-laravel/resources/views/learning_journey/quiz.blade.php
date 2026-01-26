@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('transition'))
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        Let's dive into your notes. We've turned key points into a set of questions to help you remember the material.
    </div>
    @endif
    
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">{{ $quiz->title }}</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('learning_journey.process_quiz', $quiz->id) }}" id="quiz-form">
                @csrf
                <input type="hidden" name="time_taken" id="time_taken" value="0">
                
                <div class="progress mb-4">
                    <div id="quiz-progress" class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                
                <!-- Questions will be loaded here -->
                <div id="questions-container">
                    @php
                        $questions = is_array($quiz->questions) ? $quiz->questions : [];
                    @endphp
                    
                    @foreach($questions as $index => $question)
                        @php
                            $questionNum = (int)$index + 1;
                        @endphp
                        <div class="question-card" data-question="{{ $questionNum }}" style="{{ (int)$index > 0 ? 'display: none;' : '' }}">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge badge-primary">Question {{ $questionNum }} of {{ count($questions) }}</span>
                                    
                                    @if(isset($question['type']))
                                        <span class="badge badge-info">
                                            @if($question['type'] == 'multiple_choice')
                                                <i class="fas fa-list-ul mr-1"></i> Multiple Choice
                                            @elseif($question['type'] == 'true_false')
                                                <i class="fas fa-check-circle mr-1"></i> True/False
                                            @else
                                                <i class="fas fa-question-circle mr-1"></i> {{ ucfirst($question['type']) }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                                
                                <h5>{{ $question['text'] ?? 'Question ' . $questionNum }}</h5>
                                
                                @if(isset($question['type']) && $question['type'] == 'multiple_choice')
                                    <div class="options-container mt-3">
                                        @foreach($question['options'] as $optionIndex => $option)
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" 
                                                    id="q{{ $questionNum }}_{{ $optionIndex }}" 
                                                    name="{{ $question['type'] }}_{{ $questionNum }}" 
                                                    value="{{ $option }}" 
                                                    class="custom-control-input">
                                                <label class="custom-control-label" for="q{{ $questionNum }}_{{ $optionIndex }}">
                                                    {{ $option }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(isset($question['type']) && $question['type'] == 'true_false')
                                    <div class="options-container mt-3">
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" id="q{{ $questionNum }}_true" name="{{ $question['type'] }}_{{ $questionNum }}" value="True" class="custom-control-input">
                                            <label class="custom-control-label" for="q{{ $questionNum }}_true">True</label>
                                        </div>
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" id="q{{ $questionNum }}_false" name="{{ $question['type'] }}_{{ $questionNum }}" value="False" class="custom-control-input">
                                            <label class="custom-control-label" for="q{{ $questionNum }}_false">False</label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Navigation buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary prev-question" style="display: none;">Previous</button>
                    <button type="button" class="btn btn-primary next-question">Next</button>
                    <button type="submit" class="btn btn-success finish-quiz" style="display: none;">Finish Quiz</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const questions = document.querySelectorAll('.question-card');
    const prevBtn = document.querySelector('.prev-question');
    const nextBtn = document.querySelector('.next-question');
    const finishBtn = document.querySelector('.finish-quiz');
    const progressBar = document.getElementById('quiz-progress');
    const timeInput = document.getElementById('time_taken');
    
    let currentQuestion = 1;
    const totalQuestions = questions.length;
    const startTime = Date.now();
    
    // Update time taken
    function updateTimeTaken() {
        const elapsedSeconds = Math.floor((Date.now() - startTime) / 1000);
        timeInput.value = elapsedSeconds;
    }
    
    // Update time every 5 seconds
    setInterval(updateTimeTaken, 5000);
    
    // Update progress bar
    function updateProgress() {
        const progress = ((currentQuestion - 1) / totalQuestions) * 100;
        progressBar.style.width = `${progress}%`;
    }
    
    // Show a specific question
    function showQuestion(questionNum) {
        questions.forEach(q => {
            q.style.display = 'none';
        });
        
        document.querySelector(`[data-question="${questionNum}"]`).style.display = 'block';
        
        // Update button visibility
        prevBtn.style.display = questionNum > 1 ? 'block' : 'none';
        nextBtn.style.display = questionNum < totalQuestions ? 'block' : 'none';
        finishBtn.style.display = questionNum === totalQuestions ? 'block' : 'none';
        
        currentQuestion = questionNum;
        updateProgress();
    }
    
    // Navigation button event listeners
    prevBtn.addEventListener('click', () => {
        if (currentQuestion > 1) {
            showQuestion(currentQuestion - 1);
        }
    });
    
    nextBtn.addEventListener('click', () => {
        // Check if current question is answered
        const currentQuestionEl = document.querySelector(`[data-question="${currentQuestion}"]`);
        const inputs = currentQuestionEl.querySelectorAll('input[type="radio"]');
        let answered = false;
        
        inputs.forEach(input => {
            if (input.checked) answered = true;
        });
        
        if (!answered) {
            alert('Please answer the question before continuing.');
            return;
        }
        
        if (currentQuestion < totalQuestions) {
            showQuestion(currentQuestion + 1);
        }
    });
    
    document.getElementById('quiz-form').addEventListener('submit', function() {
        updateTimeTaken(); // Get final time when submitting
    });
    
    // Initialize the first question
    showQuestion(1);
});
</script>
@endpush
@endsection