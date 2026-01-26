<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\OpenAIQuizService;
use App\Services\EnhancedFreeQuizService;
use App\Services\GeminiQuizService;
use App\Services\DirectQuizService;
use App\Services\GamificationService;
use App\Services\SmartRecommendationService;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    protected $openAIQuizService;
    protected $enhancedFreeQuizService;
    protected $geminiQuizService;
    protected $directQuizService;
    protected $gamificationService;
    protected $smartRecommendationService;
    
    public function __construct(
        OpenAIQuizService $openAIQuizService,
        EnhancedFreeQuizService $enhancedFreeQuizService,
        GeminiQuizService $geminiQuizService,
        DirectQuizService $directQuizService,
        GamificationService $gamificationService,
        SmartRecommendationService $smartRecommendationService
    ) {
        $this->openAIQuizService = $openAIQuizService;
        $this->enhancedFreeQuizService = $enhancedFreeQuizService;
        $this->geminiQuizService = $geminiQuizService;
        $this->directQuizService = $directQuizService;
        $this->gamificationService = $gamificationService;
        $this->smartRecommendationService = $smartRecommendationService;
    }

    /**
     * Display a listing of quizzes
     */
    public function index()
    {
        $quizzes = Quiz::with('note')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);
            
        return view('quizzes.index', compact('quizzes'));
    }

    /**
     * Show the form for creating a new quiz
     */
    public function create(Request $request)
    {
        $note_id = $request->get('note_id');
        $note = null;
        
        if ($note_id) {
            $note = Note::where('user_id', auth()->id())->findOrFail($note_id);
        }
        
        return view('quizzes.create', compact('note'));
    }

    /**
     * Generate quiz from note content using OpenAI ChatGPT
     */
    public function generateWithOpenAI(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note_id' => 'required|exists:notes,id',
            'num_questions' => 'nullable|integer|min:1|max:25',
            'question_types' => 'nullable|array',
            'question_types.*' => 'in:multiple_choice,true_false,fill_blank,short_answer',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'subject_area' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $note = Note::findOrFail($request->note_id);
            
            // Ensure user owns the note
            if ($note->user_id !== auth()->id()) {
                abort(403);
            }

            // Check if OpenAI service is available
            if (!$this->openAIQuizService->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'error' => 'OpenAI service is not configured. Please check your API key.'
                ], 503);
            }

            // Get note content
            $content = $note->current_content ?? $note->content ?? $note->extracted_text;
            
            if (empty(strip_tags($content))) {
                return response()->json([
                    'success' => false,
                    'error' => 'Note content is empty or invalid'
                ], 400);
            }

            // Prepare options for OpenAI
            $options = [
                'num_questions' => $request->get('num_questions', 10),
                'question_types' => $request->get('question_types', ['multiple_choice', 'true_false', 'fill_blank']),
                'difficulty' => $request->get('difficulty', 'medium'),
                'subject_area' => $request->get('subject_area', $note->subject_area ?? 'general')
            ];

            // Generate quiz using OpenAI
            $result = $this->openAIQuizService->generateQuiz(strip_tags($content), $options);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'OpenAI quiz generation failed');
            }

            // Save quiz to database
            $quiz = $this->saveOpenAIQuizToDatabase($note, $result['quiz']);

            return response()->json([
                'success' => true,
                'quiz_id' => $quiz->id,
                'quiz' => $result['quiz'],
                'redirect_url' => route('quizzes.show', $quiz)
            ]);

        } catch (\Exception $e) {
            Log::error('OpenAI Quiz generation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate quiz with ChatGPT: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate quiz from note content using Enhanced Free AI (Hugging Face)
     */
    public function generateWithEnhancedFree(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note_id' => 'required|exists:notes,id',
            'num_questions' => 'nullable|integer|min:1|max:20',
            'question_types' => 'nullable|array',
            'question_types.*' => 'in:multiple_choice,true_false,fill_blank,short_answer',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'subject_area' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $note = Note::findOrFail($request->note_id);
            
            // Ensure user owns the note
            if ($note->user_id !== auth()->id()) {
                abort(403);
            }

            // Check if Enhanced Free AI service is available
            $useDirectService = false;
            if (!$this->enhancedFreeQuizService->isAvailable()) {
                Log::warning('Enhanced Free AI service unavailable, falling back to direct service');
                $useDirectService = true;
            }

            // Get note content
            $content = $note->current_content ?? $note->content ?? $note->extracted_text;
            
            if (empty(strip_tags($content))) {
                return response()->json([
                    'success' => false,
                    'error' => 'Note content is empty or invalid'
                ], 400);
            }

            // Prepare options
            $options = [
                'num_questions' => $request->get('num_questions', 10),
                'question_types' => $request->get('question_types', ['multiple_choice', 'true_false']),
                'difficulty' => $request->get('difficulty', 'medium'),
                'subject_area' => $request->get('subject_area', $note->subject_area ?? 'general')
            ];

            // Generate quiz using Enhanced Free AI or Direct Service
            if ($useDirectService) {
                $result = $this->directQuizService->generateQuiz(
                    strip_tags($content),
                    $options['num_questions'],
                    $options['question_types']
                );
            } else {
                // EnhancedFreeQuizService now handles intelligent chunking
                $result = $this->enhancedFreeQuizService->generateQuiz(strip_tags($content), $options);
            }

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Enhanced Free AI quiz generation failed');
            }

            // Save quiz to database
            $quizData = $useDirectService ? $result : $result['quiz'];
            
            // Debug logging
            Log::info('Quiz data structure', [
                'has_questions' => isset($quizData['questions']),
                'questions_count' => isset($quizData['questions']) ? count($quizData['questions']) : 0,
                'keys' => array_keys($quizData)
            ]);
            
            if (!isset($quizData['questions']) || empty($quizData['questions'])) {
                throw new \Exception('No questions generated. Quiz data keys: ' . implode(', ', array_keys($quizData)));
            }
            
            $quiz = $this->saveEnhancedFreeQuizToDatabase($note, $quizData);

            return response()->json([
                'success' => true,
                'quiz_id' => $quiz->id,
                'quiz' => $quizData,
                'redirect_url' => route('quizzes.show', $quiz),
                'generated_by' => $useDirectService ? 'direct_service' : 'enhanced_free_ai'
            ]);

        } catch (\Exception $e) {
            Log::error('Enhanced Free AI Quiz generation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate quiz with Enhanced Free AI: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate quiz using Gemini AI
     */
    public function generateGeminiQuiz(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'note_id' => 'required|exists:notes,id',
                'num_questions' => 'integer|min:3|max:20',
                'question_types' => 'array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 400);
            }

            // Get the note
            $note = Note::findOrFail($request->note_id);
            
            // Check authorization
            if ($note->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            // Check if Gemini service is available
            if (!$this->geminiQuizService->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gemini AI service is not available. Please ensure the service is running and API key is configured.'
                ], 503);
            }

            // Get content
            $content = strip_tags($note->content);
            if (empty($content) || strlen($content) < 100) {
                return response()->json([
                    'success' => false,
                    'error' => 'Note content is too short or empty for quiz generation.'
                ], 400);
            }

            // Prepare options
            $options = [
                'num_questions' => $request->input('num_questions', 10),
                'question_types' => $request->input('question_types', ['multiple_choice', 'true_false']),
                'difficulty' => $request->input('difficulty', 'medium')
            ];

            // Generate quiz
            Log::info('Generating Gemini AI quiz', ['note_id' => $note->id, 'options' => $options]);
            
            $result = $this->geminiQuizService->generateQuiz($content, $options);
            
            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Gemini AI quiz generation failed');
            }
            
            // Get quiz data - support both 'quiz_data' and 'quiz' keys for compatibility
            $quizData = $result['quiz_data'] ?? $result['quiz'] ?? null;
            
            if (!$quizData) {
                throw new \Exception('No quiz data returned from Gemini AI service');
            }
            
            // Count total questions
            $totalQuestions = 0;
            if (isset($quizData['true_false'])) {
                $totalQuestions += count($quizData['true_false']);
            }
            if (isset($quizData['multiple_choice'])) {
                $totalQuestions += count($quizData['multiple_choice']);
            }
            
            // Create quiz record
            $quiz = Quiz::create([
                'note_id' => $note->id,
                'user_id' => auth()->id(),
                'title' => $note->title . ' - Gemini AI Quiz',
                'description' => 'AI-generated quiz using Google Gemini',
                'time_limit' => $quizData['estimated_time'] ?? 15,
                'estimated_time' => $quizData['estimated_time'] ?? 15,
                'total_questions' => $totalQuestions,
            ]);

            // Save questions
            $questionNumber = 1;
            
            // Debug: Log what we received
            Log::info('Quiz data structure', [
                'has_true_false' => isset($quizData['true_false']),
                'has_multiple_choice' => isset($quizData['multiple_choice']),
                'tf_count' => isset($quizData['true_false']) ? count($quizData['true_false']) : 0,
                'mc_count' => isset($quizData['multiple_choice']) ? count($quizData['multiple_choice']) : 0,
                'keys' => array_keys($quizData)
            ]);

            // Save True/False questions
            if (isset($quizData['true_false']) && is_array($quizData['true_false'])) {
                foreach ($quizData['true_false'] as $tfQuestion) {
                    $quiz->questions()->create([
                        'question_number' => $questionNumber++,
                        'question_text' => $tfQuestion['question'],
                        'question_type' => 'true_false',
                        'options' => json_encode(['True', 'False']),
                        'correct_answer' => $tfQuestion['correct_answer'] ?? $tfQuestion['answer'] ?? 'True',
                        'explanation' => $tfQuestion['explanation'] ?? '',
                        'points' => 1,
                        'topic' => $tfQuestion['topic'] ?? 'General',
                        'difficulty' => $tfQuestion['difficulty'] ?? 'medium',
                    ]);
                }
                Log::info('Saved ' . count($quizData['true_false']) . ' T/F questions');
            }

            // Save Multiple Choice questions
            if (isset($quizData['multiple_choice']) && is_array($quizData['multiple_choice'])) {
                foreach ($quizData['multiple_choice'] as $mcq) {
                    $quiz->questions()->create([
                        'question_number' => $questionNumber++,
                        'question_text' => $mcq['question'],
                        'question_type' => 'multiple_choice',
                        'options' => json_encode($mcq['options'] ?? []),
                        'correct_answer' => $mcq['correct_answer'] ?? 'A',
                        'explanation' => $mcq['explanation'] ?? '',
                        'points' => 1,
                        'topic' => $mcq['topic'] ?? 'General',
                        'difficulty' => $mcq['difficulty'] ?? 'medium',
                    ]);
                }
                Log::info('Saved ' . count($quizData['multiple_choice']) . ' MCQ questions');
            }

            Log::info('Gemini AI quiz generated successfully', ['quiz_id' => $quiz->id]);
            
            return response()->json([
                'success' => true,
                'quiz_id' => $quiz->id,
                'quiz' => $quizData,
                'redirect_url' => route('quizzes.show', $quiz),
                'generated_by' => 'gemini_ai'
            ]);

        } catch (\Exception $e) {
            Log::error('Gemini AI Quiz generation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate quiz with Gemini AI: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a manually created quiz
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'note_id' => 'required|exists:notes,id',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,fill_blank,short_answer',
            'questions.*.correct_answer' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $note = Note::where('user_id', auth()->id())->findOrFail($request->note_id);
            
            $quiz = Quiz::create([
                'title' => $request->title,
                'note_id' => $note->id,
                'user_id' => auth()->id(),
                'questions' => $request->questions,
                'total_questions' => count($request->questions),
                'estimated_time' => $this->calculateEstimatedTime($request->questions),
                'difficulty_level' => $request->get('difficulty_level', 'medium'),
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'quiz_id' => $quiz->id,
                'redirect_url' => route('quizzes.show', $quiz)
            ]);

        } catch (\Exception $e) {
            Log::error('Quiz creation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create quiz'
            ], 500);
        }
    }

    /**
     * Display the specified quiz
     */
    public function show(Quiz $quiz)
    {
        // Ensure user can only view their own quizzes
        if ($quiz->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this quiz.');
        }

        $quiz->load('note');
        
        return view('quizzes.show', compact('quiz'));
    }

    /**
     * Show quiz taking interface
     */
    public function take(Quiz $quiz)
    {
        // Ensure user can only take their own quizzes
        if ($quiz->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this quiz.');
        }

        // Load questions from database
        $dbQuestions = $quiz->questions()->orderBy('question_number')->get();
        
        // Format questions into the structure expected by the view
        $formattedQuestions = [
            'multiple_choice' => [],
            'true_false' => []
        ];
        
        foreach ($dbQuestions as $question) {
            $questionData = [
                'question' => $question->question_text,
                'correct_answer' => $question->correct_answer,
                'explanation' => $question->explanation ?? '',
                'topic' => $question->topic ?? 'General',
                'difficulty' => $question->difficulty ?? 'medium'
            ];
            
            if ($question->question_type === 'multiple_choice') {
                $questionData['options'] = json_decode($question->options, true) ?? [];
                $formattedQuestions['multiple_choice'][] = $questionData;
            } elseif ($question->question_type === 'true_false') {
                $formattedQuestions['true_false'][] = $questionData;
            }
        }
        
        // Merge formatted questions into quiz object for view
        $quiz->formatted_questions = $formattedQuestions;
        
        Log::info('Loading quiz for display', [
            'quiz_id' => $quiz->id,
            'mcq_count' => count($formattedQuestions['multiple_choice']),
            'tf_count' => count($formattedQuestions['true_false']),
            'total' => count($dbQuestions)
        ]);

        return view('quizzes.take', compact('quiz'));
    }

    /**
     * Submit quiz attempt
     */
    public function submit(Request $request, Quiz $quiz)
    {
        // Force JSON response for this endpoint
        $request->headers->set('Accept', 'application/json');
        
        // Log the request for debugging
        Log::info('Quiz submission attempt', [
            'quiz_id' => $quiz->id,
            'user_id' => auth()->id(),
            'answers_count' => count($request->get('answers', [])),
            'has_gamification_service' => isset($this->gamificationService),
            'request_headers' => $request->headers->all()
        ]);

        try {
            // Ensure user can only submit answers to their own quizzes
            if ($quiz->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to this quiz.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'answers' => 'required|array',
                'time_taken' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $answers = $request->answers;
            $score = $this->calculateScore($quiz, $answers);
            
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'user_id' => auth()->id(),
                'answers' => $answers,
                'score' => $score['score'],
                'total_questions' => $score['total'],
                'percentage' => $score['percentage'],
                'time_taken' => $request->get('time_taken', 0),
                'completed_at' => now()
            ]);

            // Award gamification points for quiz completion
            $gamificationResult = null;
            try {
                if (isset($this->gamificationService)) {
                    $gamificationResult = $this->gamificationService->awardPoints(
                        auth()->user(),
                        'quiz_completed',
                        [
                            'quiz_id' => $quiz->id,
                            'attempt_id' => $attempt->id,
                            'score' => $score['percentage'],
                            'time_taken' => $request->get('time_taken', 0),
                            'questions_count' => $score['total']
                        ]
                    );
                }
            } catch (\Exception $gamificationError) {
                Log::warning('Gamification service error: ' . $gamificationError->getMessage(), [
                    'file' => $gamificationError->getFile(),
                    'line' => $gamificationError->getLine()
                ]);
                // Continue with quiz submission even if gamification fails
            }

            $responseData = [
                'success' => true,
                'attempt_id' => $attempt->id,
                'score' => $score,
                'redirect_url' => route('quiz-attempts.show', $attempt)
            ];

            // Add gamification data to response if available
            if ($gamificationResult) {
                $responseData['gamification'] = [
                    'points_earned' => $gamificationResult['transaction']->points_earned ?? 0,
                    'level_up' => $gamificationResult['level_up'] ?? null,
                    'new_achievements' => $gamificationResult['new_achievements'] ?? [],
                    'challenge_results' => $gamificationResult['challenge_results'] ?? []
                ];
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Quiz submission error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'quiz_id' => $quiz->id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fallback quiz generation when ML service is unavailable
     */
    private function fallbackQuizGeneration(array $data): array
    {
        $content = $data['content'];
        $numQuestions = min($data['num_questions'], 5); // Limit fallback questions
        
        // Simple keyword extraction for fallback
        $words = str_word_count($content, 1);
        $keywords = array_unique(array_filter($words, function($word) {
            return strlen($word) > 4;
        }));
        
        $questions = [];
        $questionTypes = ['multiple_choice', 'true_false'];
        
        for ($i = 0; $i < $numQuestions && $i < count($keywords); $i++) {
            $keyword = array_values($keywords)[$i];
            $type = $questionTypes[$i % 2];
            
            if ($type === 'multiple_choice') {
                $questions['multiple_choice'][] = [
                    'question' => "What is the significance of '$keyword' in the content?",
                    'options' => [
                        'A) It is a key concept',
                        'B) It is mentioned briefly',
                        'C) It is not important',
                        'D) It is a supporting detail'
                    ],
                    'correct_answer' => 'A',
                    'explanation' => "Based on the content analysis, '$keyword' appears to be significant.",
                    'difficulty' => 'medium',
                    'topic' => 'content_analysis'
                ];
            } else {
                $questions['true_false'][] = [
                    'question' => "True or False: The content discusses '$keyword'.",
                    'correct_answer' => 'True',
                    'explanation' => "The term '$keyword' is mentioned in the content.",
                    'difficulty' => 'easy',
                    'topic' => 'comprehension'
                ];
            }
        }

        return [
            'quiz_id' => 'fallback_' . time(),
            'content_analysis' => [
                'word_count' => str_word_count($content),
                'keywords' => array_slice($keywords, 0, 10),
                'difficulty_level' => 'medium'
            ],
            'questions' => $questions,
            'total_questions' => $numQuestions,
            'estimated_time' => max(5, $numQuestions * 2),
            'difficulty_level' => 'medium',
            'note' => 'Generated using fallback method - ML service unavailable'
        ];
    }

    /**
     * Save quiz data to database
     */
    private function saveQuizToDatabase(Note $note, array $quizData): Quiz
    {
        $title = "Quiz: " . Str::limit($note->title, 50);
        
        $quiz = Quiz::create([
            'title' => $title,
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'questions' => $quizData['questions'],
            'content_analysis' => $quizData['content_analysis'] ?? null,
            'total_questions' => $quizData['total_questions'],
            'estimated_time' => $quizData['estimated_time'],
            'difficulty_level' => $quizData['difficulty_level'],
            'quiz_metadata' => [
                'generated_by' => 'ml_service',
                'generation_time' => now()->toISOString(),
                'ml_quiz_id' => $quizData['quiz_id'] ?? null
            ],
            'is_active' => true
        ]);

        return $quiz;
    }

    /**
     * Save OpenAI quiz data to database
     */
    private function saveOpenAIQuizToDatabase(Note $note, array $quizData): Quiz
    {
        $title = "ChatGPT Quiz: " . Str::limit($note->title, 45);
        
        $quiz = Quiz::create([
            'title' => $title,
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'questions' => $quizData['questions'],
            'content_analysis' => $quizData['content_analysis'] ?? null,
            'total_questions' => $quizData['total_questions'],
            'estimated_time' => $quizData['estimated_time'],
            'difficulty_level' => $quizData['difficulty_level'],
            'quiz_metadata' => [
                'generated_by' => $quizData['generated_by'] ?? 'openai_chatgpt',
                'generation_time' => $quizData['generation_time'] ?? now()->toISOString(),
                'ai_model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'features' => ['dynamic_questions', 'ai_powered', 'contextual_analysis']
            ],
            'is_active' => true
        ]);

        return $quiz;
    }

    /**
     * Save Gemini AI quiz data to database
     */
    private function saveGeminiQuizToDatabase(Note $note, array $quizData): Quiz
    {
        // Create the quiz record
        $quiz = Quiz::create([
            'note_id' => $note->id,
            'user_id' => $note->user_id,
            'title' => $note->title . ' - Gemini AI Quiz',
            'description' => 'AI-generated quiz using Google Gemini',
            'time_limit' => $quizData['estimated_time'] ?? 15,
            'total_questions' => $quizData['total_questions'] ?? 0,
        ]);

        // Process and save questions
        $questions = $quizData['questions'] ?? [];
        $questionNumber = 1;

        // Save True/False questions
        if (isset($questions['true_false'])) {
            foreach ($questions['true_false'] as $tfQuestion) {
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_number' => $questionNumber++,
                    'question_text' => $tfQuestion['question'],
                    'question_type' => 'true_false',
                    'options' => ['True', 'False'],
                    'correct_answer' => $tfQuestion['correct_answer'] ?? 'True',
                    'explanation' => $tfQuestion['explanation'] ?? '',
                    'points' => 1,
                    'topic' => $tfQuestion['topic'] ?? 'General',
                    'difficulty' => $tfQuestion['difficulty'] ?? 'medium',
                ]);
            }
        }

        // Save Multiple Choice questions
        if (isset($questions['multiple_choice'])) {
            foreach ($questions['multiple_choice'] as $mcq) {
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_number' => $questionNumber++,
                    'question_text' => $mcq['question'],
                    'question_type' => 'multiple_choice',
                    'options' => $mcq['options'] ?? [],
                    'correct_answer' => $mcq['correct_answer'] ?? 'A',
                    'explanation' => $mcq['explanation'] ?? '',
                    'points' => 1,
                    'topic' => $mcq['topic'] ?? 'General',
                    'difficulty' => $mcq['difficulty'] ?? 'medium',
                ]);
            }
        }

        return $quiz;
    }

    /**
     * Save Enhanced Free AI quiz data to database
     */
    private function saveEnhancedFreeQuizToDatabase(Note $note, array $quizData): Quiz
    {
        $title = "Free AI Quiz: " . Str::limit($note->title, 45);
        
        $quiz = Quiz::create([
            'title' => $title,
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'questions' => $quizData['questions'],
            'content_analysis' => $quizData['content_analysis'] ?? null,
            'total_questions' => $quizData['total_questions'],
            'estimated_time' => $quizData['estimated_time'],
            'difficulty_level' => $quizData['difficulty_level'],
            'quiz_metadata' => [
                'generated_by' => $quizData['generated_by'] ?? 'enhanced_free_ai',
                'generation_time' => $quizData['generation_time'] ?? now()->toISOString(),
                'models_used' => $quizData['models_used'] ?? 'Hugging Face Transformers',
                'cost' => $quizData['cost'] ?? '$0.00 (Free)',
                'features' => ['free_ai', 'transformers', 'local_processing', 'no_api_cost']
            ],
            'is_active' => true
        ]);

        return $quiz;
    }

    /**
     * Calculate estimated time for questions
     */
    private function calculateEstimatedTime(array $questions): int
    {
        $timePerType = [
            'multiple_choice' => 1.5,
            'true_false' => 1,
            'fill_blank' => 2,
            'short_answer' => 5
        ];

        $totalTime = 0;
        foreach ($questions as $question) {
            $type = $question['type'] ?? 'multiple_choice';
            $totalTime += $timePerType[$type] ?? 2;
        }

        return max(5, (int)$totalTime);
    }

    /**
     * Calculate quiz score
     */
    private function calculateScore(Quiz $quiz, array $userAnswers): array
    {
        $questions = $quiz->questions;
        $correct = 0;
        $total = 0;

        foreach ($questions as $questionType => $questionList) {
            foreach ($questionList as $index => $question) {
                $questionKey = "{$questionType}_{$index}";
                $userAnswer = $userAnswers[$questionKey] ?? null;
                $correctAnswer = $question['correct_answer'] ?? null;

                if ($userAnswer && $correctAnswer) {
                    $total++;
                    if (strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer))) {
                        $correct++;
                    }
                }
            }
        }

        $percentage = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        return [
            'score' => $correct,
            'total' => $total,
            'percentage' => $percentage,
            'grade' => $this->getGrade($percentage)
        ];
    }

    /**
     * Show quiz attempt with personalized study recommendations
     */
    public function showAttempt(\App\Models\QuizAttempt $attempt)
    {
        // Ensure user can only view their own attempts
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Get smart topic-based recommendations using the new service
        try {
            $smartRecommendations = $this->smartRecommendationService->generateRecommendations(
                auth()->id(),
                $attempt->id
            );
        } catch (\Exception $e) {
            Log::error('Smart recommendations error: ' . $e->getMessage());
            $smartRecommendations = null;
        }
        
        // ALWAYS get habit-based recommendations from study assessment questionnaire
        $habitRecommendations = $this->generateStudyRecommendations($attempt);
        
        // Use smart recommendations as the primary system, but keep habit recommendations separate
        $studyRecommendations = $smartRecommendations ?? $habitRecommendations;
        
        return view('quiz-attempts.show', compact('attempt', 'studyRecommendations', 'smartRecommendations', 'habitRecommendations'));
    }
    
    /**
     * Generate study recommendations based on quiz performance and user habits
     */
    private function generateStudyRecommendations(\App\Models\QuizAttempt $attempt): ?array
    {
        try {
            // Get user's latest study habits questionnaire
            $user = auth()->user();
            $habitsResult = \App\Models\UserQuestionnaireResult::where('user_id', $user->id)
                ->latest()
                ->first();
                
            if (!$habitsResult) {
                return null; // No questionnaire data available
            }
            
            $habitsData = $habitsResult->responses;
            
            // Enhance habits data with quiz performance
            $quizPerformance = [
                'quiz_score' => $attempt->percentage,
                'quiz_performance_level' => $this->getPerformanceLevel($attempt->percentage),
                'subject_area' => $attempt->quiz->note->title ?? 'General',
                'time_efficiency' => $this->calculateTimeEfficiency($attempt)
            ];
            
            // Combine habits data with quiz performance for comprehensive analysis
            $combinedData = array_merge($habitsData, $quizPerformance);
            
            // Generate recommendations using the QuestionnaireController logic
            $questionnaireController = new \App\Http\Controllers\QuestionnaireController();
            
            // Use reflection to call private methods
            $reflection = new \ReflectionClass($questionnaireController);
            
            // Prepare data in the format expected by questionnaire methods
            $standardizedData = $this->standardizeHabitsData($habitsData);
            
            $generateInsights = $reflection->getMethod('generateInsights');
            $generateInsights->setAccessible(true);
            
            $generateRecommendations = $reflection->getMethod('generateRecommendations'); 
            $generateRecommendations->setAccessible(true);
            
            $generateStudyPlan = $reflection->getMethod('generateStudyPlanFromPrediction');
            $generateStudyPlan->setAccessible(true);
            
            // Create prediction data with quiz performance
            $prediction = [
                'exam_score' => $this->estimateExamScore($attempt, $habitsData),
                'quiz_performance' => $attempt->percentage
            ];
            
            $insights = $generateInsights->invoke($questionnaireController, $standardizedData, $prediction);
            $recommendations = $generateRecommendations->invoke($questionnaireController, $standardizedData, $prediction);
            $studyPlan = $generateStudyPlan->invoke($questionnaireController, $prediction, $standardizedData);
            
            // Add quiz-specific insights
            $insights[] = "Recent quiz performance: {$attempt->percentage}% on {$attempt->quiz->title}";
            
            if ($attempt->percentage < 70) {
                $insights[] = "This quiz indicates areas needing focused attention";
                $recommendations[] = [
                    'category' => 'Subject Mastery',
                    'priority' => 'High',
                    'recommendation' => "Review and practice more on topics from: {$attempt->quiz->note->title}",
                    'impact' => 'Improves understanding of weak areas identified by quiz'
                ];
            }
            
            return [
                'insights' => $insights,
                'recommendations' => $recommendations,
                'study_plan' => $studyPlan,
                'quiz_analysis' => $quizPerformance
            ];
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate study recommendations: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Standardize habits data to match questionnaire format
     */
    private function standardizeHabitsData(array $habitsData): array
    {
        return [
            'age' => $habitsData['age'] ?? 20,
            'gender' => $habitsData['gender'] ?? 'Prefer not to say',
            'study_hours_per_day' => $habitsData['study_hours_per_day'] ?? 4,
            'revision_frequency' => $habitsData['revision_frequency'] ?? 'Weekly',
            'preferred_study_time' => $habitsData['preferred_study_time'] ?? 'Evening',
            'uses_online_learning' => $habitsData['uses_online_learning'] ?? 'Yes',
            'social_media_hours_per_day' => $habitsData['social_media_hours_per_day'] ?? 2,
            'sleep_hours_per_day' => $habitsData['sleep_hours_per_day'] ?? 7,
            'exam_stress_level' => $habitsData['exam_stress_level'] ?? 3
        ];
    }
    
    /**
     * Estimate exam score based on quiz performance and habits
     */
    private function estimateExamScore(\App\Models\QuizAttempt $attempt, array $habitsData): float
    {
        $baseScore = $attempt->percentage;
        
        // Adjust based on study habits
        if (isset($habitsData['study_hours_per_day']) && $habitsData['study_hours_per_day'] >= 5) {
            $baseScore += 5;
        }
        
        if (isset($habitsData['revision_frequency']) && $habitsData['revision_frequency'] === 'Daily') {
            $baseScore += 8;
        }
        
        return min(95, max(40, $baseScore));
    }
    
    /**
     * Get performance level from percentage
     */
    private function getPerformanceLevel(float $percentage): string
    {
        if ($percentage >= 85) return 'Excellent';
        if ($percentage >= 75) return 'Good';
        if ($percentage >= 65) return 'Average';
        return 'Needs Improvement';
    }
    
    /**
     * Calculate time efficiency score
     */
    private function calculateTimeEfficiency(\App\Models\QuizAttempt $attempt): string
    {
        $estimatedTime = $attempt->quiz->estimated_time_minutes * 60; // Convert to seconds
        $actualTime = $attempt->time_taken;
        
        if ($actualTime <= $estimatedTime * 0.8) {
            return 'Very Efficient';
        } elseif ($actualTime <= $estimatedTime) {
            return 'Efficient';
        } elseif ($actualTime <= $estimatedTime * 1.2) {
            return 'Average';
        } else {
            return 'Needs Improvement';
        }
    }

    /**
     * Get letter grade based on percentage
     */
    private function getGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }
}
