@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div class="header" style="background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center; margin-bottom: 30px; border: 1px solid #e9ecef;">
        <h1 style="font-size: 2.2rem; font-weight: 600; color: #2c3e50;">📚 Study Habits Assessment</h1>
        <p style="font-size: 1.1rem; color: #555; margin-top: 10px;">
            Help us understand your learning patterns to create a personalized study plan.<br>
            This assessment takes about 5 minutes and will analyze your study habits to predict your academic performance and provide tailored recommendations.
        </p>
    </div>

    <!-- The rest of your questionnaire form and content goes here. -->

    <!-- ...existing code... -->
            <!-- ...existing code... -->

        <div class="form-container">
            <div class="progress-container">
                <div class="progress-bar" id="progress-bar"></div>
            </div>

            <div id="status-message" style="display: none;"></div>

            <form id="assessment-form">
                <!-- Step 1: Personal Information -->
                <div class="form-step active" data-step="1">
                    <div class="mb-2"><span class="fw-bold text-primary">Personal Information</span></div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" id="age" name="age" class="form-control" min="16" max="35" required>
                            <div class="field-validation" id="age-validation"></div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <label class="form-label">Gender</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="gender" value="Male" required>
                                    Male
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="gender" value="Female" required>
                                    Female
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="gender" value="Prefer not to say" required>
                                    Prefer not to say
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Study Patterns -->
                <div class="form-step" data-step="2">
                    <div class="mb-2"><span class="fw-bold text-primary">Study Patterns</span></div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <label for="study_hours_per_day" class="form-label">Daily Study Hours</label>
                            <div class="slider-container">
                                <input type="range" id="study_hours_per_day" name="study_hours_per_day" class="slider" min="1" max="12" value="4" required>
                                <div class="slider-value">
                                    <span id="study-hours-display">4</span> hours per day
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <label class="form-label">Revision Frequency</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="revision_frequency" value="Daily" required>
                                    Daily
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="revision_frequency" value="Weekly" required>
                                    Weekly
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="revision_frequency" value="Before exams" required>
                                    Before exams
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="revision_frequency" value="Rarely" required>
                                    Rarely
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <label class="form-label">Preferred Study Time</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="preferred_study_time" value="Morning" required>
                                    Morning (6AM-12PM)
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="preferred_study_time" value="Afternoon" required>
                                    Afternoon (12PM-6PM)
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="preferred_study_time" value="Evening" required>
                                    Evening (6PM-10PM)
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="preferred_study_time" value="Night" required>
                                    Night (10PM-6AM)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Learning Preferences -->
                <div class="form-step" data-step="3">
                    <div class="form-section">
                        <h3 class="fw-bold text-primary">Learning Preferences</h3>
                        <div class="card mb-3">
                            <div class="card-body">
                                <label class="form-label">Do you use online learning resources?</label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="uses_online_learning" value="Yes" required>
                                        Yes, frequently
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="uses_online_learning" value="No" required>
                                        No, prefer traditional methods
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <label for="social_media_hours_per_day" class="form-label">Social Media Usage (hours per day)</label>
                                <div class="slider-container">
                                    <input type="range" id="social_media_hours_per_day" name="social_media_hours_per_day" class="slider" min="0" max="8" value="2" required>
                                    <div class="slider-value">
                                        <span id="social-media-display">2</span> hours per day
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Lifestyle Factors -->
                <div class="form-step" data-step="4">
                    <div class="form-section">
                        <h3 class="fw-bold text-primary">Lifestyle Factors</h3>
                        <div class="card mb-3">
                            <div class="card-body">
                                <label for="sleep_hours_per_day" class="form-label">Sleep Hours (per day)</label>
                                <div class="slider-container">
                                    <input type="range" id="sleep_hours_per_day" name="sleep_hours_per_day" class="slider" min="4" max="12" value="7" required>
                                    <div class="slider-value">
                                        <span id="sleep-hours-display">7</span> hours per day
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <label for="exam_stress_level" class="form-label">Exam Stress Level</label>
                                <div class="slider-container">
                                    <input type="range" id="exam_stress_level" name="exam_stress_level" class="slider" min="1" max="5" value="3" required>
                                    <div class="slider-value">
                                        <span id="stress-level-display">3</span> / 5 (Moderate)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-navigation">
                        <button type="button" class="btn btn-secondary" id="prev-btn" onclick="previousStep()">
                            ← Previous
                        </button>
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            Generate My Study Plan →
                        </button>
                    </div>
                </div>

                <!-- Navigation for other steps -->
                <div class="step-navigation" id="step-nav" style="display: none;">
                    <button type="button" class="btn btn-secondary" id="prev-btn-nav" onclick="previousStep()">
                        ← Previous
                    </button>
                    <button type="button" class="btn btn-primary" id="next-btn" onclick="nextStep()">
                        Next →
                    </button>
                </div>
            </form>

            <!-- Results Section -->
            <div id="results-section" style="display: none;">
                <div class="form-section">
                    <h3>🎯 Your Personalized Study Plan</h3>
                    <div id="results-content"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            updateProgress();
            setupSliders();
            setupRadioButtons();
            setupValidation();
        });

        function setupSliders() {
            // Study hours slider
            const studyHoursSlider = document.getElementById('study_hours_per_day');
            const studyHoursDisplay = document.getElementById('study-hours-display');
            studyHoursSlider.addEventListener('input', function() {
                studyHoursDisplay.textContent = this.value;
            });

            // Social media slider
            const socialMediaSlider = document.getElementById('social_media_hours_per_day');
            const socialMediaDisplay = document.getElementById('social-media-display');
            socialMediaSlider.addEventListener('input', function() {
                socialMediaDisplay.textContent = this.value;
            });

            // Sleep hours slider
            const sleepHoursSlider = document.getElementById('sleep_hours_per_day');
            const sleepHoursDisplay = document.getElementById('sleep-hours-display');
            sleepHoursSlider.addEventListener('input', function() {
                sleepHoursDisplay.textContent = this.value;
            });

            // Stress level slider
            const stressLevelSlider = document.getElementById('exam_stress_level');
            const stressLevelDisplay = document.getElementById('stress-level-display');
            const stressLabels = ['Very Low', 'Low', 'Moderate', 'High', 'Very High'];
            stressLevelSlider.addEventListener('input', function() {
                const level = this.value;
                stressLevelDisplay.textContent = `${level} / 5 (${stressLabels[level - 1]})`;
            });
        }

        function setupRadioButtons() {
            document.querySelectorAll('.radio-option').forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    const name = radio.name;
                    
                    // Remove selected class from all options in this group
                    document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                        r.closest('.radio-option').classList.remove('selected');
                    });
                    
                    // Add selected class to clicked option
                    radio.checked = true;
                    this.classList.add('selected');
                });
            });
        }

        function setupValidation() {
            const ageInput = document.getElementById('age');
            const ageValidation = document.getElementById('age-validation');
            
            ageInput.addEventListener('input', function() {
                const age = parseInt(this.value);
                if (age >= 16 && age <= 35) {
                    ageValidation.textContent = '✓ Valid age range';
                    ageValidation.className = 'field-validation valid';
                    this.classList.remove('invalid');
                    this.classList.add('valid');
                } else if (this.value) {
                    ageValidation.textContent = '⚠ Age must be between 16 and 35';
                    ageValidation.className = 'field-validation invalid';
                    this.classList.remove('valid');
                    this.classList.add('invalid');
                } else {
                    ageValidation.textContent = '';
                    ageValidation.className = 'field-validation';
                    this.classList.remove('valid', 'invalid');
                }
            });
        }

        function nextStep() {
            if (validateCurrentStep()) {
                currentStep++;
                updateStepDisplay();
                updateProgress();
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepDisplay();
                updateProgress();
            }
        }

        function updateStepDisplay() {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(step => {
                step.classList.remove('active');
            });

            // Show current step
            document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');

            // Update navigation
            const stepNav = document.getElementById('step-nav');
            const finalNav = document.querySelector('.step-navigation');
            
            if (currentStep === totalSteps) {
                stepNav.style.display = 'none';
            } else {
                stepNav.style.display = 'flex';
            }

            // Update previous button
            const prevBtn = document.getElementById('prev-btn-nav');
            if (prevBtn) {
                prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            }
        }

        function updateProgress() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progress-bar').style.width = `${progress}%`;
        }

        function validateCurrentStep() {
            const currentStepElement = document.querySelector(`[data-step="${currentStep}"]`);
            const requiredFields = currentStepElement.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (field.type === 'radio') {
                    const radioGroup = currentStepElement.querySelectorAll(`[name="${field.name}"]`);
                    const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                    if (!isChecked) {
                        isValid = false;
                        showMessage('Please answer all questions before proceeding.', 'error');
                    }
                } else if (!field.value.trim()) {
                    isValid = false;
                    field.focus();
                    showMessage('Please fill in all required fields.', 'error');
                }
            });

            return isValid;
        }

        function showMessage(message, type) {
            const messageElement = document.getElementById('status-message');
            messageElement.textContent = message;
            messageElement.className = `status-message status-${type}`;
            messageElement.style.display = 'block';
            
            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 3000);
        }

        // Form submission
        document.getElementById('assessment-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!validateCurrentStep()) {
                return;
            }

            // Show loading
            showMessage('Processing your assessment...', 'info');
            document.getElementById('submit-btn').innerHTML = 
                '<div class="spinner" style="width: 20px; height: 20px; margin: 0 10px 0 0; display: inline-block;"></div>Generating Study Plan...';
            document.getElementById('submit-btn').disabled = true;

            try {
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                // Convert string numbers to integers
                data.age = parseInt(data.age);
                data.study_hours_per_day = parseInt(data.study_hours_per_day);
                data.social_media_hours_per_day = parseInt(data.social_media_hours_per_day);
                data.sleep_hours_per_day = parseInt(data.sleep_hours_per_day);
                data.exam_stress_level = parseInt(data.exam_stress_level);

                const response = await fetch('/questionnaire/prediction', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    displayResults(result);
                } else {
                    throw new Error(result.error || 'Failed to generate study plan');
                }

            } catch (error) {
                showMessage(`Error: ${error.message}`, 'error');
                document.getElementById('submit-btn').innerHTML = 'Generate My Study Plan →';
                document.getElementById('submit-btn').disabled = false;
            }
        });

        function displayResults(data) {
            // Hide form
            document.getElementById('assessment-form').style.display = 'none';
            
            // Show results
            const resultsSection = document.getElementById('results-section');
            const resultsContent = document.getElementById('results-content');
            
            const score = data.predicted_exam_score;
            const category = data.performance_category;
            
            let categoryColor = '#28a745'; // Green for good performance
            if (score < 65) categoryColor = '#dc3545'; // Red for needs improvement
            else if (score < 75) categoryColor = '#ffc107'; // Yellow for average

            let html = `
                <div style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 12px; margin-bottom: 30px;">
                    <h2 style="color: ${categoryColor}; margin-bottom: 10px;">
                        Predicted Exam Score: ${score}%
                    </h2>
                    <p style="font-size: 1.2rem; color: #6c757d;">
                        Performance Category: <strong style="color: ${categoryColor};">${category}</strong>
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 2px solid #e9ecef;">
                        <h4 style="color: #2c3e50; margin-bottom: 15px;">📊 Your Profile Analysis</h4>
                        <ul style="list-style: none; padding: 0;">
            `;

            data.insights.forEach(insight => {
                html += `<li style="padding: 8px 0; border-bottom: 1px solid #f1f1f1;">• ${insight}</li>`;
            });

            html += `
                        </ul>
                    </div>

                    <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 2px solid #e9ecef;">
                        <h4 style="color: #2c3e50; margin-bottom: 15px;">🎯 Key Recommendations</h4>
                        <div>
            `;

            data.recommendations.forEach(rec => {
                const priorityColor = rec.priority === 'High' ? '#dc3545' : rec.priority === 'Medium' ? '#ffc107' : '#28a745';
                html += `
                    <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid ${priorityColor};">
                        <strong style="color: ${priorityColor};">${rec.category} (${rec.priority} Priority)</strong><br>
                        <span style="color: #495057;">${rec.recommendation}</span><br>
                        <small style="color: #6c757d; font-style: italic;">${rec.impact}</small>
                    </div>
                `;
            });

            html += `
                        </div>
                    </div>
                </div>

                <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 2px solid #e9ecef; margin-bottom: 30px;">
                    <h4 style="color: #2c3e50; margin-bottom: 20px;">📅 Your Personalized Weekly Schedule</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            `;

            Object.entries(data.study_plan.weekly_schedule).forEach(([day, schedule]) => {
                html += `
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #dee2e6;">
                        <h5 style="color: #4a90e2; margin-bottom: 10px;">${day}</h5>
                        <p><strong>Subject:</strong> ${schedule.subjects}</p>
                        <p><strong>Hours:</strong> ${schedule.study_hours}</p>
                        <p><strong>Time:</strong> ${schedule.time_slot}</p>
                    </div>
                `;
            });

            html += `
                    </div>
                    <div style="text-align: center; margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px;">
                        <strong>Total Weekly Study Hours: ${data.study_plan.total_weekly_hours}</strong>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-secondary" onclick="location.reload()">
                        Take Assessment Again
                    </button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        Print Study Plan
                    </button>
                </div>
            `;

            resultsContent.innerHTML = html;
            resultsSection.style.display = 'block';
            
            // Scroll to results
            resultsSection.scrollIntoView({ behavior: 'smooth' });
        }

        // Initialize navigation for first step
        updateStepDisplay();
    </script>
</body>
@endsection
