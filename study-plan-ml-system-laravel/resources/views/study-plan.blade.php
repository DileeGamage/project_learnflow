<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Plan Generator</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .status-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-healthy {
            background-color: #28a745;
        }

        .status-error {
            background-color: #dc3545;
        }

        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .results-section {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 30px;
            margin-top: 30px;
        }

        .result-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .result-card h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .quiz-question {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .quiz-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }

        .quiz-option {
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quiz-option:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }

        .quiz-option.selected {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .loading {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .day-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .day-card h4 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Study Plan Generator</h1>
            <p>AI-Powered Personalized Learning Assistant</p>
        </div>

        <div class="content">
            <!-- API Status Section -->
            <div class="status-section">
                <h3>🔗 System Status</h3>
                <div id="api-status">
                    <span class="status-indicator status-error"></span>
                    <span>Checking API connection...</span>
                </div>
            </div>

            <!-- User Profile Section -->
            <div class="form-section">
                <h3>👤 Your Learning Profile</h3>
                <form id="profile-form">
                    <div class="form-group">
                        <label for="learning-style">Learning Style</label>
                        <select id="learning-style" class="form-control" name="learning_preferences[]">
                            <option value="visual">Visual (diagrams, charts)</option>
                            <option value="auditory">Auditory (listening, discussion)</option>
                            <option value="kinesthetic">Kinesthetic (hands-on, practice)</option>
                            <option value="mixed">Mixed Approach</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="preferred-time">Preferred Study Time</label>
                        <select id="preferred-time" class="form-control" name="preferred_time">
                            <option value="morning">Morning (6AM - 12PM)</option>
                            <option value="afternoon">Afternoon (12PM - 6PM)</option>
                            <option value="evening" selected>Evening (6PM - 10PM)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="study-duration">Daily Study Duration (minutes)</label>
                        <input type="number" id="study-duration" class="form-control" name="study_duration" 
                               value="90" min="15" max="480">
                    </div>

                    <div class="form-group">
                        <label for="strong-subjects">Strong Subjects (comma-separated)</label>
                        <input type="text" id="strong-subjects" class="form-control" name="strong_subjects" 
                               placeholder="mathematics, science, english">
                    </div>

                    <div class="form-group">
                        <label for="weak-subjects">Areas for Improvement (comma-separated)</label>
                        <input type="text" id="weak-subjects" class="form-control" name="weak_subjects" 
                               placeholder="physics, chemistry, history">
                    </div>
                </form>
            </div>

            <!-- Quick Assessment Section -->
            <div class="form-section">
                <h3>📝 Quick Knowledge Assessment</h3>
                <p>Answer a few questions to help us understand your current level:</p>
                
                <div id="quiz-container">
                    <!-- Quiz questions will be loaded here -->
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-secondary" onclick="loadSampleQuiz()">
                        Load Sample Quiz
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center; margin: 30px 0;">
                <button type="button" class="btn btn-primary" onclick="generateStudyPlan()" 
                        style="margin-right: 10px;">
                    🚀 Generate My Study Plan
                </button>
                <button type="button" class="btn btn-secondary" onclick="runDemo()">
                    🎯 Run Demo
                </button>
            </div>

            <!-- Results Section -->
            <div id="results" class="results-section" style="display: none;">
                <h3>📊 Your Personalized Study Plan</h3>
                <div id="results-content"></div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let quizResponses = [];
        let apiBaseUrl = '/api/study-plan';

        // Check API status on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkApiStatus();
        });

        async function checkApiStatus() {
            try {
                const response = await fetch(`${apiBaseUrl}/health`);
                const data = await response.json();
                
                const statusElement = document.getElementById('api-status');
                if (data.ml_api_status === 'healthy') {
                    statusElement.innerHTML = `
                        <span class="status-indicator status-healthy"></span>
                        <span>✅ ML API Connected - Ready to generate study plans!</span>
                    `;
                } else {
                    statusElement.innerHTML = `
                        <span class="status-indicator status-error"></span>
                        <span>❌ ML API Unavailable - Check if Python server is running</span>
                    `;
                }
            } catch (error) {
                document.getElementById('api-status').innerHTML = `
                    <span class="status-indicator status-error"></span>
                    <span>❌ Connection Error: ${error.message}</span>
                `;
            }
        }

        function loadSampleQuiz() {
            const sampleQuestions = [
                {
                    id: 1,
                    topic: 'mathematics',
                    question: 'What is 15% of 80?',
                    options: ['A) 10', 'B) 12', 'C) 15', 'D) 20'],
                    correct: 'B'
                },
                {
                    id: 2,
                    topic: 'science',
                    question: 'What is the chemical symbol for water?',
                    options: ['A) H2O', 'B) CO2', 'C) NaCl', 'D) O2'],
                    correct: 'A'
                },
                {
                    id: 3,
                    topic: 'english',
                    question: 'Which is the correct spelling?',
                    options: ['A) Recieve', 'B) Receive', 'C) Recive', 'D) Receeve'],
                    correct: 'B'
                }
            ];

            const container = document.getElementById('quiz-container');
            container.innerHTML = '';

            sampleQuestions.forEach(q => {
                const questionDiv = document.createElement('div');
                questionDiv.className = 'quiz-question';
                questionDiv.innerHTML = `
                    <h4>Q${q.id}: ${q.question}</h4>
                    <div class="quiz-options">
                        ${q.options.map(option => `
                            <div class="quiz-option" onclick="selectOption(${q.id}, '${option.charAt(0)}', '${q.correct}', '${q.topic}', this)">
                                ${option}
                            </div>
                        `).join('')}
                    </div>
                `;
                container.appendChild(questionDiv);
            });
        }

        function selectOption(questionId, selectedAnswer, correctAnswer, topic, element) {
            // Remove previous selections in this question
            const questionDiv = element.parentElement;
            questionDiv.querySelectorAll('.quiz-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            // Mark current selection
            element.classList.add('selected');

            // Record response
            const existingIndex = quizResponses.findIndex(r => r.question_id === questionId);
            const response = {
                question_id: questionId,
                topic: topic,
                user_answer: selectedAnswer,
                correct_answer: correctAnswer,
                is_correct: selectedAnswer === correctAnswer,
                response_time: Math.floor(Math.random() * 60) + 10 // Random time between 10-70 seconds
            };

            if (existingIndex >= 0) {
                quizResponses[existingIndex] = response;
            } else {
                quizResponses.push(response);
            }
        }

        function getFormData() {
            const form = document.getElementById('profile-form');
            const formData = new FormData(form);
            
            const strongSubjects = document.getElementById('strong-subjects').value
                .split(',').map(s => s.trim()).filter(s => s);
            const weakSubjects = document.getElementById('weak-subjects').value
                .split(',').map(s => s.trim()).filter(s => s);

            return {
                user_id: 1,
                user_data: {
                    user_id: 1,
                    learning_preferences: [document.getElementById('learning-style').value],
                    preferred_time: document.getElementById('preferred-time').value,
                    study_duration: parseInt(document.getElementById('study-duration').value),
                    strong_subjects: strongSubjects,
                    weak_subjects: weakSubjects
                },
                quiz_responses: quizResponses,
                preferences: {
                    daily_study_time: parseInt(document.getElementById('study-duration').value),
                    preferred_time: document.getElementById('preferred-time').value
                }
            };
        }

        async function generateStudyPlan() {
            const resultsDiv = document.getElementById('results');
            const resultsContent = document.getElementById('results-content');
            
            // Show loading
            resultsDiv.style.display = 'block';
            resultsContent.innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p>🧠 AI is analyzing your profile and generating your personalized study plan...</p>
                </div>
            `;

            try {
                const data = getFormData();
                
                const response = await fetch(`${apiBaseUrl}/complete-workflow`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    displayResults(result.data);
                } else {
                    throw new Error(result.error || 'Failed to generate study plan');
                }
            } catch (error) {
                resultsContent.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            }
        }

        async function runDemo() {
            const resultsDiv = document.getElementById('results');
            const resultsContent = document.getElementById('results-content');
            
            resultsDiv.style.display = 'block';
            resultsContent.innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p>🎯 Running demo with sample data...</p>
                </div>
            `;

            try {
                const response = await fetch('/study-plan-demo');
                const result = await response.json();

                if (result.success) {
                    displayResults(result.ml_output);
                } else {
                    throw new Error(result.error || 'Demo failed');
                }
            } catch (error) {
                resultsContent.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Demo Error:</strong> ${error.message}
                    </div>
                `;
            }
        }

        function displayResults(data) {
            const resultsContent = document.getElementById('results-content');
            
            let html = `
                <div class="alert alert-success">
                    ✅ Your personalized study plan has been generated successfully!
                </div>
            `;

            // Study Plan Schedule
            if (data.study_plan && data.study_plan.study_schedule) {
                html += `
                    <div class="result-card">
                        <h3>📅 Your Weekly Study Schedule</h3>
                        <div class="schedule-grid">
                `;
                
                Object.entries(data.study_plan.study_schedule).forEach(([day, schedule]) => {
                    html += `
                        <div class="day-card">
                            <h4>${day}</h4>
                            <p><strong>Topic:</strong> ${schedule.topic}</p>
                            <p><strong>Duration:</strong> ${schedule.duration_minutes} minutes</p>
                            <p><strong>Time:</strong> ${schedule.time_slot}</p>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }

            // Knowledge Assessment
            if (data.knowledge_assessment) {
                html += `
                    <div class="result-card">
                        <h3>📊 Knowledge Assessment Results</h3>
                `;
                
                Object.entries(data.knowledge_assessment).forEach(([topic, assessment]) => {
                    const accuracy = Math.round(assessment.accuracy * 100);
                    html += `
                        <div style="margin-bottom: 15px; padding: 15px; background: white; border-radius: 6px;">
                            <strong>${topic.charAt(0).toUpperCase() + topic.slice(1)}:</strong> 
                            ${accuracy}% accuracy (${assessment.mastery_level})
                            <div style="margin-top: 10px;">
                                ${assessment.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                            </div>
                        </div>
                    `;
                });
                
                html += `</div>`;
            }

            // Recommendations
            if (data.recommendations && data.recommendations.priority_topics) {
                html += `
                    <div class="result-card">
                        <h3>🎯 Priority Topics & Recommendations</h3>
                `;
                
                data.recommendations.priority_topics.forEach(topic => {
                    const priorityColor = topic.priority === 'urgent' ? '#dc3545' : 
                                         topic.priority === 'high' ? '#fd7e14' : '#28a745';
                    html += `
                        <div style="margin-bottom: 15px; padding: 15px; background: white; border-radius: 6px; border-left: 4px solid ${priorityColor};">
                            <strong>${topic.topic}</strong> 
                            <span style="background: ${priorityColor}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
                                ${topic.priority}
                            </span>
                            <div style="margin-top: 8px;">
                                Current: ${Math.round(topic.current_accuracy * 100)}% → 
                                Target: ${Math.round(topic.target_accuracy * 100)}%
                            </div>
                            <div style="margin-top: 8px; color: #666;">
                                Recommended hours: ${topic.recommended_hours}
                            </div>
                        </div>
                    `;
                });
                
                html += `</div>`;
            }

            // Study Tips
            if (data.recommendations && data.recommendations.tips) {
                html += `
                    <div class="result-card">
                        <h3>💡 Personalized Study Tips</h3>
                        <ul>
                            ${data.recommendations.tips.map(tip => `<li>${tip}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            resultsContent.innerHTML = html;
        }
    </script>
</body>
</html>
