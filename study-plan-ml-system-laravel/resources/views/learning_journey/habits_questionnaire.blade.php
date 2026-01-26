@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📚 Study Habits Assessment</h4>
        </div>
        <div class="card-body">
            <p class="lead">
                Help us understand your learning patterns to create a personalized study plan.
                This assessment takes about 5 minutes and will analyze your study habits to predict
                your academic performance and provide tailored recommendations.
            </p>
            
            <form method="POST" action="{{ route('learning_journey.habits.store') }}" id="habits-form">
                @csrf
                
                <div class="progress mb-4">
                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                
                <!-- Question sections - only one is shown at a time -->
                <div class="question-section" data-question="1">
                    <h5>Personal Information</h5>
                    
                    <div class="form-group mb-3">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" class="form-control" 
                               min="16" max="35" required>
                        <div class="form-text text-muted" id="age-validation"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Gender</label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="gender_male" name="gender" value="Male" class="form-check-input" required>
                                <label class="form-check-label" for="gender_male">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="gender_female" name="gender" value="Female" class="form-check-input" required>
                                <label class="form-check-label" for="gender_female">Female</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="gender_other" name="gender" value="Prefer not to say" class="form-check-input" required>
                                <label class="form-check-label" for="gender_other">Prefer not to say</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="question-section" data-question="2" style="display: none;">
                    <h5>Study Patterns</h5>
                    
                    <div class="form-group mb-3">
                        <label for="study_hours_per_day">Daily Study Hours</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" id="study_hours_per_day" name="study_hours_per_day" 
                                   class="form-range w-75" min="1" max="12" value="4" required>
                            <div class="badge bg-primary">
                                <span id="study-hours-display">4</span> hours
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Revision Frequency</label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="revision_daily" name="revision_frequency" value="Daily" class="form-check-input" required>
                                <label class="form-check-label" for="revision_daily">Daily</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="revision_weekly" name="revision_frequency" value="Weekly" class="form-check-input" required>
                                <label class="form-check-label" for="revision_weekly">Weekly</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="revision_exams" name="revision_frequency" value="Before exams" class="form-check-input" required>
                                <label class="form-check-label" for="revision_exams">Before exams</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="revision_rarely" name="revision_frequency" value="Rarely" class="form-check-input" required>
                                <label class="form-check-label" for="revision_rarely">Rarely</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Preferred Study Time</label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="morning" name="preferred_study_time" value="Morning" class="form-check-input" required>
                                <label class="form-check-label" for="morning">Morning (6AM-12PM)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="afternoon" name="preferred_study_time" value="Afternoon" class="form-check-input" required>
                                <label class="form-check-label" for="afternoon">Afternoon (12PM-6PM)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="evening" name="preferred_study_time" value="Evening" class="form-check-input" required>
                                <label class="form-check-label" for="evening">Evening (6PM-10PM)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="night" name="preferred_study_time" value="Night" class="form-check-input" required>
                                <label class="form-check-label" for="night">Night (10PM-6AM)</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="question-section" data-question="3" style="display: none;">
                    <h5>Learning Preferences</h5>
                    
                    <div class="form-group mb-3">
                        <label>Do you use online learning resources?</label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="online_yes" name="uses_online_learning" value="Yes" class="form-check-input" required>
                                <label class="form-check-label" for="online_yes">Yes, frequently</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="online_no" name="uses_online_learning" value="No" class="form-check-input" required>
                                <label class="form-check-label" for="online_no">No, prefer traditional methods</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>What is your preferred learning style?</label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="visual" name="learning_style" value="visual" class="form-check-input" required>
                                <label class="form-check-label" for="visual">Visual (images, diagrams)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="auditory" name="learning_style" value="auditory" class="form-check-input" required>
                                <label class="form-check-label" for="auditory">Auditory (listening, discussion)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="reading" name="learning_style" value="reading" class="form-check-input" required>
                                <label class="form-check-label" for="reading">Reading/Writing</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="kinesthetic" name="learning_style" value="kinesthetic" class="form-check-input" required>
                                <label class="form-check-label" for="kinesthetic">Kinesthetic (hands-on activities)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="social_media_hours_per_day">Social Media Usage (hours per day)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" id="social_media_hours_per_day" name="social_media_hours_per_day" 
                                   class="form-range w-75" min="0" max="8" value="2" required>
                            <div class="badge bg-primary">
                                <span id="social-media-display">2</span> hours
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="question-section" data-question="4" style="display: none;">
                    <h5>Study Sessions</h5>
                    
                    <div class="form-group mb-3">
                        <label>How long are your typical study sessions?</label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <div class="form-check form-check-inline">
                                <input type="radio" id="very_short" name="study_duration" value="very_short" class="form-check-input" required>
                                <label class="form-check-label" for="very_short">Very short (15-30 minutes)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="short" name="study_duration" value="short" class="form-check-input" required>
                                <label class="form-check-label" for="short">Short (30-60 minutes)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="medium" name="study_duration" value="medium" class="form-check-input" required>
                                <label class="form-check-label" for="medium">Medium (1-2 hours)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="long" name="study_duration" value="long" class="form-check-input" required>
                                <label class="form-check-label" for="long">Long (2+ hours)</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="question-section" data-question="5" style="display: none;">
                    <h5>Lifestyle Factors</h5>
                    
                    <div class="form-group mb-3">
                        <label for="sleep_hours_per_day">Sleep Hours (per day)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" id="sleep_hours_per_day" name="sleep_hours_per_day" 
                                   class="form-range w-75" min="4" max="12" value="7" required>
                            <div class="badge bg-primary">
                                <span id="sleep-hours-display">7</span> hours
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="exam_stress_level">Exam Stress Level</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" id="exam_stress_level" name="exam_stress_level" 
                                   class="form-range w-75" min="1" max="5" value="3" required>
                            <div class="badge bg-primary">
                                <span id="stress-level-display">3</span> / 5 (Moderate)
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary prev-btn" style="display: none;">
                        <i class="fas fa-arrow-left me-1"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary next-btn">
                        Next <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    <button type="submit" class="btn btn-success submit-btn" style="display: none;">
                        <i class="fas fa-check me-1"></i> Complete Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('.question-section');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        const submitBtn = document.querySelector('.submit-btn');
        const progressBar = document.getElementById('progress-bar');
        
        // Set up slider value displays
        setupSliderDisplays();
        
        // Validate age input
        setupAgeValidation();
        
        let currentQuestion = 1;
        const totalQuestions = sections.length;
        
        // Update progress bar
        function updateProgress() {
            const progress = (currentQuestion - 1) / totalQuestions * 100;
            progressBar.style.width = `${progress}%`;
        }
        
        // Show a specific question section
        function showQuestion(questionNum) {
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            document.querySelector(`[data-question="${questionNum}"]`).style.display = 'block';
            
            // Update button visibility
            prevBtn.style.display = questionNum > 1 ? 'block' : 'none';
            nextBtn.style.display = questionNum < totalQuestions ? 'block' : 'none';
            submitBtn.style.display = questionNum === totalQuestions ? 'block' : 'none';
            
            currentQuestion = questionNum;
            updateProgress();
        }
        
        // Set up sliders and their displays
        function setupSliderDisplays() {
            // Study hours slider
            const studyHoursSlider = document.getElementById('study_hours_per_day');
            const studyHoursDisplay = document.getElementById('study-hours-display');
            if (studyHoursSlider && studyHoursDisplay) {
                studyHoursSlider.addEventListener('input', function() {
                    studyHoursDisplay.textContent = this.value;
                });
            }
            
            // Social media slider
            const socialMediaSlider = document.getElementById('social_media_hours_per_day');
            const socialMediaDisplay = document.getElementById('social-media-display');
            if (socialMediaSlider && socialMediaDisplay) {
                socialMediaSlider.addEventListener('input', function() {
                    socialMediaDisplay.textContent = this.value;
                });
            }
            
            // Sleep hours slider
            const sleepHoursSlider = document.getElementById('sleep_hours_per_day');
            const sleepHoursDisplay = document.getElementById('sleep-hours-display');
            if (sleepHoursSlider && sleepHoursDisplay) {
                sleepHoursSlider.addEventListener('input', function() {
                    sleepHoursDisplay.textContent = this.value;
                });
            }
            
            // Stress level slider
            const stressLevelSlider = document.getElementById('exam_stress_level');
            const stressLevelDisplay = document.getElementById('stress-level-display');
            const stressLabels = ['Very Low', 'Low', 'Moderate', 'High', 'Very High'];
            if (stressLevelSlider && stressLevelDisplay) {
                stressLevelSlider.addEventListener('input', function() {
                    const level = this.value;
                    stressLevelDisplay.textContent = level + ' / 5 (' + stressLabels[level - 1] + ')';
                });
            }
        }
        
        // Set up age validation
        function setupAgeValidation() {
            const ageInput = document.getElementById('age');
            const ageValidation = document.getElementById('age-validation');
            
            if (ageInput && ageValidation) {
                ageInput.addEventListener('input', function() {
                    const age = parseInt(this.value);
                    if (age >= 16 && age <= 35) {
                        ageValidation.textContent = '✓ Valid age range';
                        ageValidation.className = 'form-text text-success';
                    } else if (this.value) {
                        ageValidation.textContent = '⚠ Age must be between 16 and 35';
                        ageValidation.className = 'form-text text-danger';
                    } else {
                        ageValidation.textContent = '';
                    }
                });
            }
        }
        
        // Validate current question's inputs
        function validateCurrentQuestion() {
            const currentSection = document.querySelector(`[data-question="${currentQuestion}"]`);
            let isValid = true;
            
            // Check radio buttons if they exist
            const radioGroups = {};
            currentSection.querySelectorAll('input[type="radio"]').forEach(radio => {
                radioGroups[radio.name] = radioGroups[radio.name] || [];
                radioGroups[radio.name].push(radio);
            });
            
            for (const name in radioGroups) {
                const isChecked = radioGroups[name].some(radio => radio.checked);
                if (!isChecked) {
                    isValid = false;
                }
            }
            
            // Check required text/number inputs
            currentSection.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => {
                if (input.required && !input.value.trim()) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                alert('Please answer all questions before proceeding.');
            }
            
            return isValid;
        }
        
        // Event listeners for navigation buttons
        prevBtn.addEventListener('click', function() {
            if (currentQuestion > 1) {
                showQuestion(currentQuestion - 1);
            }
        });
        
        nextBtn.addEventListener('click', function() {
            if (validateCurrentQuestion() && currentQuestion < totalQuestions) {
                showQuestion(currentQuestion + 1);
            }
        });
        
        // Initialize the first question
        showQuestion(1);
    });
</script>
@endsection