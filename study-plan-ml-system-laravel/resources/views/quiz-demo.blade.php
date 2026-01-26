@extends('layouts.app')

@section('title', 'Quiz Generation Demo')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Demo Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-robot"></i> AI Quiz Generation Demo
                    </h4>
                    <small>Test the ML-powered quiz generation from note content</small>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>How it works:</strong> Paste your note content below, and our AI will analyze it to generate intelligent quiz questions including multiple choice, true/false, and fill-in-the-blank questions.
                    </div>
                </div>
            </div>
            
            <!-- Content Input Form -->
            <div class="card shadow-sm mb-4" id="input-section">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Step 1: Enter Your Content
                    </h5>
                </div>
                <div class="card-body">
                    <form id="quiz-form">
                        <div class="mb-3">
                            <label for="content" class="form-label">Note Content</label>
                            <textarea class="form-control" id="content" name="content" rows="10" 
                                    placeholder="Paste your note content here...">Artificial Intelligence (AI) is a rapidly growing field in computer science that focuses on creating intelligent machines capable of performing tasks that typically require human intelligence. These tasks include learning, reasoning, problem-solving, perception, and language understanding.

Machine Learning is a subset of AI that enables computers to learn and improve from experience without being explicitly programmed. Deep Learning, which uses neural networks with multiple layers, has revolutionized many AI applications including image recognition, natural language processing, and speech recognition.

The history of AI dates back to the 1950s when Alan Turing proposed the famous Turing Test as a measure of machine intelligence. Since then, AI has evolved through various phases, experiencing both breakthroughs and periods of reduced funding known as AI winters.

Today, AI applications are everywhere - from recommendation systems on streaming platforms to autonomous vehicles, from virtual assistants like Siri and Alexa to advanced medical diagnosis systems. The field continues to advance rapidly with new developments in areas such as reinforcement learning, computer vision, and natural language generation.</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="num_questions" class="form-label">Number of Questions</label>
                                    <select class="form-control" id="num_questions" name="num_questions">
                                        <option value="5">5 Questions</option>
                                        <option value="8" selected>8 Questions</option>
                                        <option value="10">10 Questions</option>
                                        <option value="15">15 Questions</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Question Types</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="mcq" value="multiple_choice" checked>
                                            <label class="form-check-label" for="mcq">Multiple Choice</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="tf" value="true_false" checked>
                                            <label class="form-check-label" for="tf">True/False</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="fb" value="fill_blank" checked>
                                            <label class="form-check-label" for="fb">Fill Blank</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg" id="generate-btn">
                                <i class="fas fa-robot"></i> Generate Quiz with AI
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Loading Section -->
            <div class="card shadow-sm mb-4 d-none" id="loading-section">
                <div class="card-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mt-3">Analyzing Content & Generating Questions...</h5>
                    <p class="text-muted">Our AI is processing your content and creating intelligent quiz questions.</p>
                </div>
            </div>
            
            <!-- Results Section -->
            <div class="d-none" id="results-section">
                <!-- Content Analysis -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> Content Analysis
                        </h5>
                    </div>
                    <div class="card-body" id="analysis-content">
                        <!-- Analysis will be loaded here -->
                    </div>
                </div>
                
                <!-- Generated Quiz -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list-check"></i> Generated Quiz
                        </h5>
                    </div>
                    <div class="card-body" id="quiz-content">
                        <!-- Quiz will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('quiz-form').addEventListener('submit', function(e) {
    e.preventDefault();
    generateQuiz();
});

async function generateQuiz() {
    const content = document.getElementById('content').value.trim();
    if (!content) {
        alert('Please enter some content first.');
        return;
    }
    
    // Get selected question types
    const questionTypes = [];
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
        questionTypes.push(cb.value);
    });
    
    if (questionTypes.length === 0) {
        alert('Please select at least one question type.');
        return;
    }
    
    const numQuestions = document.getElementById('num_questions').value;
    
    // Show loading
    document.getElementById('input-section').classList.add('d-none');
    document.getElementById('loading-section').classList.remove('d-none');
    document.getElementById('results-section').classList.add('d-none');
    
    try {
        // Call our quiz generation service
        const response = await fetch('http://localhost:5001/generate-quiz', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                content: content,
                num_questions: parseInt(numQuestions),
                question_types: questionTypes
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayResults(data.quiz);
        } else {
            throw new Error(data.error || 'Failed to generate quiz');
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error generating quiz: ' + error.message);
        
        // Show input section again
        document.getElementById('input-section').classList.remove('d-none');
        document.getElementById('loading-section').classList.add('d-none');
    }
}

function displayResults(quiz) {
    // Hide loading, show results
    document.getElementById('loading-section').classList.add('d-none');
    document.getElementById('results-section').classList.remove('d-none');
    
    // Display content analysis
    const analysis = quiz.content_analysis;
    const analysisHtml = `
        <div class="row">
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-primary">${analysis.word_count}</h4>
                    <small class="text-muted">Words Analyzed</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-info">${analysis.keywords.length}</h4>
                    <small class="text-muted">Keywords Found</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-warning">${quiz.estimated_time}</h4>
                    <small class="text-muted">Est. Minutes</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-success">${quiz.total_questions}</h4>
                    <small class="text-muted">Questions</small>
                </div>
            </div>
        </div>
        <hr>
        <div>
            <h6>Key Topics Identified:</h6>
            ${analysis.keywords.slice(0, 10).map(kw => `<span class="badge bg-light text-dark me-1 mb-1">${kw.keyword}</span>`).join('')}
        </div>
        <div class="mt-3">
            <h6>Subject Area: <span class="badge bg-primary">${analysis.subject_area}</span></h6>
            <h6>Difficulty Level: <span class="badge bg-warning">${analysis.difficulty_level}</span></h6>
        </div>
    `;
    document.getElementById('analysis-content').innerHTML = analysisHtml;
    
    // Display quiz questions
    let quizHtml = '';
    let questionNumber = 1;
    
    // Multiple Choice Questions
    if (quiz.questions.multiple_choice && quiz.questions.multiple_choice.length > 0) {
        quizHtml += `
            <h6 class="text-primary mb-3"><i class="fas fa-list-ul"></i> Multiple Choice Questions</h6>
        `;
        quiz.questions.multiple_choice.forEach(q => {
            quizHtml += `
                <div class="question-item mb-4 p-3 border rounded">
                    <h6 class="fw-bold">${questionNumber}. ${q.question}</h6>
                    <div class="ms-3">
                        ${q.options.map(opt => `<div class="mb-1">${opt}</div>`).join('')}
                    </div>
                    <div class="mt-2">
                        <small class="text-success"><strong>Answer:</strong> ${q.correct_answer}</small>
                        <br><small class="text-muted">${q.explanation}</small>
                    </div>
                </div>
            `;
            questionNumber++;
        });
    }
    
    // True/False Questions
    if (quiz.questions.true_false && quiz.questions.true_false.length > 0) {
        quizHtml += `
            <h6 class="text-success mb-3 mt-4"><i class="fas fa-toggle-on"></i> True/False Questions</h6>
        `;
        quiz.questions.true_false.forEach(q => {
            quizHtml += `
                <div class="question-item mb-4 p-3 border rounded">
                    <h6 class="fw-bold">${questionNumber}. ${q.question}</h6>
                    <div class="mt-2">
                        <small class="text-success"><strong>Answer:</strong> ${q.correct_answer}</small>
                        <br><small class="text-muted">${q.explanation}</small>
                    </div>
                </div>
            `;
            questionNumber++;
        });
    }
    
    // Fill in the Blank Questions
    if (quiz.questions.fill_blank && quiz.questions.fill_blank.length > 0) {
        quizHtml += `
            <h6 class="text-warning mb-3 mt-4"><i class="fas fa-edit"></i> Fill in the Blank Questions</h6>
        `;
        quiz.questions.fill_blank.forEach(q => {
            quizHtml += `
                <div class="question-item mb-4 p-3 border rounded">
                    <h6 class="fw-bold">${questionNumber}. ${q.question}</h6>
                    <div class="mt-2">
                        <small class="text-success"><strong>Answer:</strong> ${q.correct_answer}</small>
                        <br><small class="text-muted">${q.explanation}</small>
                    </div>
                </div>
            `;
            questionNumber++;
        });
    }
    
    quizHtml += `
        <div class="text-center mt-4">
            <button class="btn btn-outline-primary" onclick="startOver()">
                <i class="fas fa-redo"></i> Generate Another Quiz
            </button>
        </div>
    `;
    
    document.getElementById('quiz-content').innerHTML = quizHtml;
}

function startOver() {
    document.getElementById('input-section').classList.remove('d-none');
    document.getElementById('loading-section').classList.add('d-none');
    document.getElementById('results-section').classList.add('d-none');
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
@endsection
