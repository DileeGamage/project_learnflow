<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\WorkplaceController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AuthController;
use App\Services\ContentStructureService;

// Test route
Route::get('/test', [TestController::class, 'index']);

// Test PDF upload route
Route::get('/test-pdf-upload', function() {
    return view('test-pdf-upload');
})->name('test.pdf.upload');

// PDF formatting comparison
Route::get('/pdf-formatting-comparison', function() {
    return view('pdf-formatting-comparison');
})->name('pdf.formatting.comparison');

Route::post('/test-pdf-upload', function(Illuminate\Http\Request $request) {
    try {
        if (!$request->hasFile('pdf_file')) {
            return response()->json(['error' => 'No PDF file uploaded'], 400);
        }
        
        $file = $request->file('pdf_file');
        
        // Validate file
        if (!$file->isValid()) {
            return response()->json(['error' => 'Invalid file upload'], 400);
        }
        
        if ($file->getMimeType() !== 'application/pdf') {
            return response()->json(['error' => 'File must be a PDF'], 400);
        }
        
        // Store the file temporarily
        $path = $file->store('temp-uploads', 'public');
        $fullPath = storage_path('app/public/' . $path);
        
        // Extract text using our service
        $pdfService = new \App\Services\PdfOcrService();
        $extractedText = $pdfService->extractText($fullPath);
        
        // Clean up temp file
        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        
        return response()->json([
            'success' => true,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'extracted_text' => $extractedText,
            'text_length' => strlen($extractedText),
            'word_count' => str_word_count($extractedText)
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'PDF processing failed: ' . $e->getMessage()
        ], 500);
    }
})->name('test.pdf.process');

// Demo routes for quiz generation
Route::get('/quiz-demo', function() {
    return view('quiz-demo');
})->name('quiz.demo');

// MySQL status page
Route::get('/mysql-status', function() {
    return view('mysql-status');
})->name('mysql.status');

// Demo routes for structured content
Route::get('/demo-structured-content', function() {
    return view('demo-structured');
})->name('demo.structured');

Route::post('/demo-process-text', function(Illuminate\Http\Request $request) {
    $service = new ContentStructureService();
    $result = $service->processPdfContent($request->input('content', ''));
    
    return response()->json([
        'success' => true,
        'structured_content' => $result['structured_content'],
        'content_outline' => $result['content_outline'],
        'content_sections' => $result['content_sections'],
        'document_type' => $result['document_type'],
        'formatted_html' => $service->formatStructuredContentAsHtml($result['structured_content'])
    ]);
})->name('demo.process');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('auth.profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('auth.password.update');
});

// Redirect root to login for guests, dashboard for authenticated users
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Notes Management
    Route::get('/notes/check-ocr-service', [NoteController::class, 'checkOcrService'])->name('notes.check-ocr-service');
    Route::resource('notes', NoteController::class);
    Route::post('/notes/{note}/toggle-favorite', [NoteController::class, 'toggleFavorite'])->name('notes.toggle-favorite');
    Route::get('/notes-search', [NoteController::class, 'search'])->name('notes.search');
    
    // Note Version Management
    Route::get('/notes/{note}/versions/{version}', [NoteController::class, 'viewVersion'])->name('notes.versions.view');
    Route::put('/notes/{note}/versions/{version}/restore', [NoteController::class, 'restoreVersion'])->name('notes.versions.restore');
    Route::delete('/notes/{note}/versions/{version}', [NoteController::class, 'deleteVersion'])->name('notes.versions.delete');
    
    // My Workplace
    Route::get('/workplace', [WorkplaceController::class, 'index'])->name('workplace.index');
    Route::get('/workplace/create', [WorkplaceController::class, 'create'])->name('workplace.create');
    Route::post('/workplace', [WorkplaceController::class, 'store'])->name('workplace.store');
    
    // Questionnaire System
    Route::get('/questionnaire', [QuestionnaireController::class, 'index'])->name('questionnaire.index');
    Route::post('/questionnaire/prediction', [QuestionnaireController::class, 'generatePrediction'])->name('questionnaire.prediction');
    Route::resource('questionnaires', QuestionnaireController::class);
    Route::post('/questionnaires/{questionnaire}/submit', [QuestionnaireController::class, 'submit'])->name('questionnaires.submit');
    Route::get('/questionnaires/{questionnaire}/results', [QuestionnaireController::class, 'results'])->name('questionnaires.results');
    
    // Quiz System - AI-Powered Generation
    Route::resource('quizzes', QuizController::class);
    Route::post('/quizzes/generate-with-openai', [QuizController::class, 'generateWithOpenAI'])->name('quizzes.generate-with-openai');
    Route::post('/quizzes/generate-with-enhanced-free', [QuizController::class, 'generateWithEnhancedFree'])->name('quizzes.generate-with-enhanced-free');
    Route::post('/quizzes/generate-with-gemini', [QuizController::class, 'generateGeminiQuiz'])->name('quizzes.generate-with-gemini');
    Route::get('/quizzes/{quiz}/take', [QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
    
    // Quiz Attempts
    Route::get('/quiz-attempts/{attempt}', [\App\Http\Controllers\QuizController::class, 'showAttempt'])->name('quiz-attempts.show');
    
    // Analytics & Charts
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics.index');
    Route::get('/analytics/performance', [DashboardController::class, 'performance'])->name('analytics.performance');
    Route::get('/analytics/subjects', [DashboardController::class, 'subjects'])->name('analytics.subjects');
    
    // Quiz Analytics
    Route::get('/analytics/quiz', [\App\Http\Controllers\QuizAnalyticsController::class, 'index'])->name('analytics.quiz');
    
    // Learning Journey Flow
    Route::get('/start-learning', [\App\Http\Controllers\LearningJourneyController::class, 'start'])->name('learning_journey.start');
    Route::get('/learning/habits', [\App\Http\Controllers\LearningJourneyController::class, 'showHabitsQuestionnaire'])->name('learning_journey.habits');
    Route::post('/learning/habits', [\App\Http\Controllers\LearningJourneyController::class, 'processHabitsQuestionnaire'])->name('learning_journey.habits.store');
    Route::get('/learning/select-note', [\App\Http\Controllers\LearningJourneyController::class, 'selectNote'])->name('learning_journey.select_note');
    Route::get('/learning/prepare/{noteId}', [\App\Http\Controllers\LearningJourneyController::class, 'prepareQuiz'])->name('learning_journey.prepare');
    Route::get('/learning/quiz/{quizId}', [\App\Http\Controllers\LearningJourneyController::class, 'takeQuiz'])->name('learning_journey.take_quiz');
    Route::post('/learning/quiz/{quizId}', [\App\Http\Controllers\LearningJourneyController::class, 'processQuiz'])->name('learning_journey.process_quiz');
    Route::get('/learning/results/{quizId}/{attemptId}', [\App\Http\Controllers\LearningJourneyController::class, 'showResults'])->name('learning_journey.results');
    
    // Gamification System
    Route::get('/gamification', [\App\Http\Controllers\GamificationController::class, 'dashboard'])->name('gamification.dashboard');
    Route::get('/gamification/leaderboard', [\App\Http\Controllers\GamificationController::class, 'leaderboard'])->name('gamification.leaderboard');
    Route::get('/gamification/achievements', [\App\Http\Controllers\GamificationController::class, 'achievements'])->name('gamification.achievements');
    Route::get('/gamification/challenges', [\App\Http\Controllers\GamificationController::class, 'challenges'])->name('gamification.challenges');
    Route::get('/api/gamification/summary', [\App\Http\Controllers\GamificationController::class, 'summary'])->name('api.gamification.summary');
    
    // Admin gamification routes
    Route::post('/admin/gamification/generate-challenges', [\App\Http\Controllers\GamificationController::class, 'generateChallenges'])->name('admin.gamification.generate-challenges');
    Route::post('/admin/gamification/award-points', [\App\Http\Controllers\GamificationController::class, 'awardPoints'])->name('admin.gamification.award-points');
    
// Debug route for testing JavaScript
Route::get('/test-js', function() {
    return response()->json(['success' => true, 'message' => 'JavaScript is working!']);
});

// Test quiz submission
Route::post('/test-quiz-submit', [\App\Http\Controllers\TestQuizController::class, 'testSubmit'])->name('test.quiz.submit');
});

// API routes without CSRF protection
Route::post('/api/quiz-analytics', [\App\Http\Controllers\QuizAnalyticsController::class, 'getAnalytics'])->name('api.quiz-analytics');

// Admin routes (require authentication and admin privilege)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [\App\Http\Controllers\AdminController::class, 'userDetails'])->name('users.show');
    Route::get('/research', [\App\Http\Controllers\AdminController::class, 'research'])->name('research');
    Route::get('/analytics', [\App\Http\Controllers\AdminController::class, 'analytics'])->name('analytics');
    Route::get('/experiments', [\App\Http\Controllers\AdminController::class, 'experiments'])->name('experiments');
    Route::post('/experiments/{user}/assign-group', [\App\Http\Controllers\AdminController::class, 'assignExperimentGroup'])->name('experiments.assign');
    
    // Model Metrics Routes
    Route::get('/model-metrics', [\App\Http\Controllers\ModelMetricsController::class, 'dashboard'])->name('model-metrics.dashboard');
    Route::get('/model-metrics/list', [\App\Http\Controllers\ModelMetricsController::class, 'index'])->name('model-metrics.index');
    Route::get('/model-metrics/{id}', [\App\Http\Controllers\ModelMetricsController::class, 'show'])->name('model-metrics.show');
});

// API Routes for Model Metrics
Route::prefix('api/model-metrics')->group(function () {
    Route::get('/', [\App\Http\Controllers\ModelMetricsController::class, 'apiIndex']);
    Route::get('/latest/{modelName}', [\App\Http\Controllers\ModelMetricsController::class, 'getLatestMetrics']);
    Route::get('/history/{modelName}', [\App\Http\Controllers\ModelMetricsController::class, 'getHistory']);
    Route::get('/compare/{modelName}', [\App\Http\Controllers\ModelMetricsController::class, 'compareModels']);
    Route::get('/best/{modelName}', [\App\Http\Controllers\ModelMetricsController::class, 'getBestModel']);
});
