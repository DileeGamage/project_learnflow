<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Habits Assessment</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 40px;
        }
        
        .question {
            margin: 30px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 5px solid #667eea;
            transition: transform 0.2s ease;
        }
        
        .question:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .question h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        
        .radio-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        
        .radio-option {
            position: relative;
        }
        
        .radio-option input[type="radio"] {
            display: none;
        }
        
        .radio-option label {
            display: block;
            padding: 12px 18px;
            background: #e9ecef;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            border: 2px solid transparent;
        }
        
        .radio-option input[type="radio"]:checked + label {
            background: #667eea;
            color: white;
            border-color: #5a67d8;
            transform: scale(1.05);
        }
        
        .form-input {
            width: 100%;
            padding: 12px 18px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 12px 18px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 40px auto 0;
            min-width: 300px;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .progress-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .small-text {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .required {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Study Habits Assessment</h1>
            <p>Help us create your personalized study plan with AI-powered recommendations</p>
            
            <?php
            session_start();
            if (isset($_SESSION['error'])) {
                echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;">';
                echo '<strong>Error:</strong> ' . htmlspecialchars($_SESSION['error']);
                echo '</div>';
                unset($_SESSION['error']);
            }
            ?>
            
            <div class="progress-bar">
                <div class="progress-fill" id="progressBar"></div>
            </div>
        </div>
        
        <div class="form-container">
            <form id="studyHabitsForm" action="process_questionnaire.php" method="POST">
                
                <!-- Question 1: Age -->
                <div class="question" data-question="1">
                    <h3>1. What is your age? <span class="required">*</span></h3>
                    <input type="number" name="age" class="form-input" min="16" max="30" required placeholder="Enter your age (16-30)">
                </div>

                <!-- Question 2: Gender -->
                <div class="question" data-question="2">
                    <h3>2. Gender <span class="required">*</span></h3>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="gender" value="Male" id="gender_male" required>
                            <label for="gender_male">👨 Male</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="gender" value="Female" id="gender_female" required>
                            <label for="gender_female">👩 Female</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="gender" value="Prefer not to say" id="gender_other" required>
                            <label for="gender_other">🤐 Prefer not to say</label>
                        </div>
                    </div>
                </div>

                <!-- Question 3: Study Hours -->
                <div class="question" data-question="3">
                    <h3>3. How many hours do you typically study per day? <span class="required">*</span></h3>
                    <select name="study_hours_per_day" class="form-select" required>
                        <option value="">Select study hours...</option>
                        <option value="1">1 hour</option>
                        <option value="2">2 hours</option>
                        <option value="3">3 hours</option>
                        <option value="4">4 hours</option>
                        <option value="5">5 hours</option>
                        <option value="6">6 hours</option>
                        <option value="7">7 hours</option>
                        <option value="8">8+ hours</option>
                    </select>
                </div>

                <!-- Question 4: Revision Frequency -->
                <div class="question" data-question="4">
                    <h3>4. How often do you revise previously learned material? <span class="required">*</span></h3>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="revision_frequency" value="Daily" id="revision_daily" required>
                            <label for="revision_daily">📅 Daily</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="revision_frequency" value="Weekly" id="revision_weekly" required>
                            <label for="revision_weekly">📊 Weekly</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="revision_frequency" value="Before exams" id="revision_exams" required>
                            <label for="revision_exams">📝 Before exams</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="revision_frequency" value="Rarely" id="revision_rarely" required>
                            <label for="revision_rarely">🤷 Rarely</label>
                        </div>
                    </div>
                </div>

                <!-- Question 5: Preferred Study Time -->
                <div class="question" data-question="5">
                    <h3>5. When do you study most effectively? <span class="required">*</span></h3>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="preferred_study_time" value="Morning" id="time_morning" required>
                            <label for="time_morning">🌅 Morning (6 AM - 12 PM)</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="preferred_study_time" value="Afternoon" id="time_afternoon" required>
                            <label for="time_afternoon">☀️ Afternoon (12 PM - 6 PM)</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="preferred_study_time" value="Evening" id="time_evening" required>
                            <label for="time_evening">🌆 Evening (6 PM - 10 PM)</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="preferred_study_time" value="Night" id="time_night" required>
                            <label for="time_night">🌙 Night (10 PM - 6 AM)</label>
                        </div>
                    </div>
                </div>

                <!-- Question 6: Online Learning -->
                <div class="question" data-question="6">
                    <h3>6. Do you use online learning platforms or digital resources? <span class="required">*</span></h3>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="uses_online_learning" value="Yes" id="online_yes" required>
                            <label for="online_yes">💻 Yes, regularly</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="uses_online_learning" value="No" id="online_no" required>
                            <label for="online_no">📚 No, prefer traditional methods</label>
                        </div>
                    </div>
                </div>

                <!-- Question 7: Social Media Hours -->
                <div class="question" data-question="7">
                    <h3>7. How many hours do you spend on social media per day? <span class="required">*</span></h3>
                    <select name="social_media_hours_per_day" class="form-select" required>
                        <option value="">Select hours...</option>
                        <option value="0">0 hours (I don't use social media)</option>
                        <option value="1">1 hour</option>
                        <option value="2">2 hours</option>
                        <option value="3">3 hours</option>
                        <option value="4">4 hours</option>
                        <option value="5">5 hours</option>
                        <option value="6">6+ hours</option>
                    </select>
                </div>

                <!-- Question 8: Sleep Hours -->
                <div class="question" data-question="8">
                    <h3>8. How many hours do you sleep per night on average? <span class="required">*</span></h3>
                    <select name="sleep_hours_per_day" class="form-select" required>
                        <option value="">Select hours...</option>
                        <option value="4">4 hours or less</option>
                        <option value="5">5 hours</option>
                        <option value="6">6 hours</option>
                        <option value="7">7 hours</option>
                        <option value="8">8 hours</option>
                        <option value="9">9+ hours</option>
                    </select>
                </div>

                <!-- Question 9: Exam Stress Level -->
                <div class="question" data-question="9">
                    <h3>9. How would you rate your stress level during exams? <span class="required">*</span></h3>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="exam_stress_level" value="1" id="stress_1" required>
                            <label for="stress_1">😌 1 - Very Low Stress</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="exam_stress_level" value="2" id="stress_2" required>
                            <label for="stress_2">🙂 2 - Low Stress</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="exam_stress_level" value="3" id="stress_3" required>
                            <label for="stress_3">😐 3 - Moderate Stress</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="exam_stress_level" value="4" id="stress_4" required>
                            <label for="stress_4">😰 4 - High Stress</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="exam_stress_level" value="5" id="stress_5" required>
                            <label for="stress_5">😫 5 - Very High Stress</label>
                        </div>
                    </div>
                </div>

                <!-- Question 10: Last Exam Score -->
                <div class="question" data-question="10">
                    <h3>10. What was your score percentage in your last major exam? <span class="required">*</span></h3>
                    <input type="number" name="last_exam_score_percent" class="form-input" min="0" max="100" step="0.1" required placeholder="Enter percentage (e.g., 85.5)">
                    <div class="small-text">Enter as a percentage (0-100)</div>
                </div>

                <button type="submit" class="submit-btn">
                    🤖 Generate My AI-Powered Study Plan
                </button>
            </form>
        </div>
    </div>

    <script>
        // Progress bar functionality
        const form = document.getElementById('studyHabitsForm');
        const progressBar = document.getElementById('progressBar');
        const totalQuestions = 10;
        
        function updateProgress() {
            const formData = new FormData(form);
            let answeredQuestions = 0;
            
            // Count answered questions
            const requiredFields = ['age', 'gender', 'study_hours_per_day', 'revision_frequency', 
                                  'preferred_study_time', 'uses_online_learning', 'social_media_hours_per_day', 
                                  'sleep_hours_per_day', 'exam_stress_level', 'last_exam_score_percent'];
            
            requiredFields.forEach(field => {
                if (formData.get(field) && formData.get(field) !== '') {
                    answeredQuestions++;
                }
            });
            
            const progress = (answeredQuestions / totalQuestions) * 100;
            progressBar.style.width = progress + '%';
        }
        
        // Update progress on any input change
        form.addEventListener('input', updateProgress);
        form.addEventListener('change', updateProgress);
        
        // Form submission with validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = form.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = '🔄 Processing...';
            submitBtn.disabled = true;
            
            // Simulate processing delay
            setTimeout(() => {
                form.submit();
            }, 1000);
        });
        
        // Initial progress update
        updateProgress();
    </script>
</body>
</html>
