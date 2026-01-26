<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserQuestionnaireResult;
use App\Models\QuizAttempt;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LearningJourneyController extends Controller
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }
    public function start()
    {
        // Show the welcome page first
        return view('learning_journey.welcome');
    }
    
    public function showHabitsQuestionnaire(Request $request)
    {
        // Check if user specifically wants to retake the questionnaire
        if ($request->has('retake')) {
            return view('learning_journey.habits_questionnaire');
        }
        
        // Check if user has completed habits questionnaire recently
        $user = auth()->user();
        $recentQuestionnaire = UserQuestionnaireResult::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->first();
            
        if ($recentQuestionnaire) {
            // Show an intermediate page with option to continue or retake
            return view('learning_journey.continue_or_retake', [
                'lastCompleted' => $recentQuestionnaire->created_at->diffForHumans()
            ]);
        }
        
        return view('learning_journey.habits_questionnaire');
    }
    
    public function processHabitsQuestionnaire(Request $request)
    {
        // Validate the expanded questionnaire data
        $validated = $request->validate([
            'age' => 'required|integer|min:16|max:35',
            'gender' => 'required|string|in:Male,Female,Prefer not to say',
            'study_hours_per_day' => 'required|integer|min:1|max:12',
            'revision_frequency' => 'required|string|in:Daily,Weekly,Before exams,Rarely',
            'preferred_study_time' => 'required|string|in:Morning,Afternoon,Evening,Night',
            'uses_online_learning' => 'required|string|in:Yes,No',
            'social_media_hours_per_day' => 'required|integer|min:0|max:8',
            'learning_style' => 'required|string|in:visual,auditory,reading,kinesthetic',
            'study_duration' => 'required|string|in:very_short,short,medium,long',
            'sleep_hours_per_day' => 'required|integer|min:4|max:12',
            'exam_stress_level' => 'required|integer|min:1|max:5',
        ]);
        
        // Store questionnaire responses
        $user = auth()->user();
        
        $result = new UserQuestionnaireResult;
        $result->user_id = $user->id;
        $result->responses = $request->except('_token');
        $result->save();

        // Award gamification points for completing the habits questionnaire
        $this->gamificationService->awardPoints(
            $user,
            'habits_questionnaire',
            [
                'questionnaire_id' => $result->id,
                'response_count' => count($result->responses)
            ]
        );
        
        // Redirect to the main notes page instead of the custom select note page
        return redirect()->route('notes.index')
            ->with('success', 'Your comprehensive study preferences have been saved! You\'ve earned points for completing the questionnaire. Now select a note to review and click "Create Quiz" on its page.');
    }
    
    public function selectNote()
    {
        $notes = Note::where('user_id', auth()->id())->get();
        return view('learning_journey.select_note', compact('notes'));
    }
    
    public function prepareQuiz($noteId)
    {
        $note = Note::findOrFail($noteId);
        
        // Get or create quiz for this note
        $quiz = Quiz::where('note_id', $noteId)->first();
        
        if (!$quiz) {
            // Create a new quiz if one doesn't exist
            // This would typically call your existing quiz generation logic
            $quiz = Quiz::create([
                'note_id' => $noteId,
                'title' => "Quiz on " . $note->title,
                'description' => "Generated from " . $note->title,
                'questions' => $this->generateQuestions($note)
            ]);
        }
        
        return redirect()->route('learning_journey.take_quiz', $quiz->id)
            ->with('transition', true);
    }
    
    public function takeQuiz($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        
        // Get user's study habits to personalize the quiz experience
        $user = auth()->user();
        $studyHabits = UserQuestionnaireResult::where('user_id', $user->id)
            ->latest()
            ->first();
            
        return view('learning_journey.quiz', compact('quiz', 'studyHabits'));
    }
    
    public function processQuiz($quizId, Request $request)
    {
        $quiz = Quiz::findOrFail($quizId);
        $user = auth()->user();
        $answers = $request->except('_token');
        
        // Calculate score based on quiz answers
        $questions = json_decode($quiz->questions, true) ?? [];
        $totalQuestions = count($questions);
        $score = 0;
        
        foreach ($questions as $index => $question) {
            $questionKey = $question['type'] . '_' . $index;
            $userAnswer = $answers[$questionKey] ?? null;
            
            if ($userAnswer && isset($question['correct_option']) && $userAnswer === $question['correct_option']) {
                $score++;
            }
        }
        
        // Create a quiz attempt record
        $attempt = new QuizAttempt();
        $attempt->quiz_id = $quiz->id;
        $attempt->user_id = $user->id;
        $attempt->score = $score;
        $attempt->total_questions = $totalQuestions;
        $attempt->percentage = $totalQuestions > 0 ? round(($score / $totalQuestions) * 100, 2) : 0;
        $attempt->time_taken = $request->time_taken ?? 0;
        $attempt->answers = $answers;
        $attempt->completed_at = now();
        $attempt->save();
        
        return redirect()->route('learning_journey.results', ['quizId' => $quiz->id, 'attemptId' => $attempt->id]);
    }
    
    public function showResults($quizId, $attemptId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $attempt = QuizAttempt::findOrFail($attemptId);
        
        // Get latest study habits
        $studyHabits = UserQuestionnaireResult::where('user_id', auth()->id())
            ->latest()
            ->first();
            
        // Generate personalized tips based on quiz results and study habits
        $tips = $this->generatePersonalizedTips($quiz, $attempt, $studyHabits);
        
        return view('learning_journey.results', compact('quiz', 'attempt', 'tips', 'studyHabits'));
    }
    
    private function generatePersonalizedTips($quiz, $attempt, $studyHabits)
    {
        // Logic to generate personalized tips based on both quiz performance
        // and study habits questionnaire responses
        $tips = [];
        
        // Base tips on quiz performance
        $percentage = $attempt->percentage;
        
        if ($percentage < 60) {
            $tips[] = "You might benefit from reviewing this material again. Consider breaking it down into smaller chunks.";
        } elseif ($percentage < 80) {
            $tips[] = "Good work! Focus on reviewing the questions you missed to improve your understanding.";
        } else {
            $tips[] = "Excellent! Consider exploring more advanced concepts related to this material.";
        }
        
        // Add tips based on study habits if available
        if ($studyHabits) {
            $responses = is_array($studyHabits->responses) ? $studyHabits->responses : json_decode($studyHabits->responses, true) ?? [];
            
            if (!empty($responses)) {
                // Study duration preferences
                if (isset($responses['study_duration'])) {
                    if (in_array($responses['study_duration'], ['very_short', 'short'])) {
                        $tips[] = "As you prefer shorter study sessions, try breaking down this material into 15-20 minute review blocks with short breaks in between.";
                    } elseif ($responses['study_duration'] === 'long') {
                        $tips[] = "Since you prefer longer study sessions, consider creating comprehensive mind maps or summaries for this material.";
                    }
                }
                
                // Learning style preferences
                if (isset($responses['learning_style'])) {
                    switch ($responses['learning_style']) {
                        case 'visual':
                            $tips[] = "As a visual learner, try creating diagrams, charts or mind maps for the concepts you're studying.";
                            break;
                        case 'auditory':
                            $tips[] = "Since you learn well through listening, consider recording yourself explaining these concepts and playing it back.";
                            break;
                        case 'reading':
                            $tips[] = "As you prefer reading/writing, try creating written summaries of the key points in this material.";
                            break;
                        case 'kinesthetic':
                            $tips[] = "As a hands-on learner, try applying these concepts in practical scenarios or teaching them to someone else.";
                            break;
                    }
                }
                
                // Study time preferences
                if (isset($responses['study_time'])) {
                    if ($responses['study_time'] === 'morning') {
                        $tips[] = "Since you're a morning person, schedule your review sessions early in the day when your concentration is at its peak.";
                    } elseif ($responses['study_time'] === 'evening' || $responses['study_time'] === 'late_night') {
                        $tips[] = "As you prefer studying later in the day, make sure to schedule your most challenging review sessions during your evening hours.";
                    }
                }
            }
        }
        
        // Ensure we have at least 3 tips
        $defaultTips = [
            "Try explaining these concepts to someone else to reinforce your understanding.",
            "Regular review is key - schedule a follow-up review in 7 days to strengthen retention.",
            "Connect these concepts to real-world examples to deepen your understanding.",
            "Try practicing with different question formats to test your knowledge more thoroughly."
        ];
        
        while (count($tips) < 3) {
            $randomTip = $defaultTips[array_rand($defaultTips)];
            if (!in_array($randomTip, $tips)) {
                $tips[] = $randomTip;
            }
        }
        
        return $tips;
    }
    
    private function generateQuestions($note)
    {
        // This is a simple placeholder for question generation
        // In a real implementation, you would call your existing question generation logic
        
        // For now, we'll create some sample questions based on the note title
        $sampleQuestions = [
            [
                'type' => 'multiple_choice',
                'text' => 'What is the main topic of "' . $note->title . '"?',
                'options' => ['Option A', 'Option B', 'Option C', 'Option D'],
                'correct_option' => 'Option A'
            ],
            [
                'type' => 'true_false',
                'text' => 'The note "' . $note->title . '" covers advanced concepts.',
                'options' => ['True', 'False'],
                'correct_option' => 'True'
            ],
            [
                'type' => 'multiple_choice',
                'text' => 'Which of the following is NOT covered in "' . $note->title . '"?',
                'options' => ['Topic 1', 'Topic 2', 'Topic 3', 'Topic 4'],
                'correct_option' => 'Topic 4'
            ],
            [
                'type' => 'multiple_choice',
                'text' => 'In the context of "' . $note->title . '", which statement is most accurate?',
                'options' => ['Statement A', 'Statement B', 'Statement C', 'Statement D'],
                'correct_option' => 'Statement B'
            ]
        ];
        
        return json_encode($sampleQuestions);
    }
}