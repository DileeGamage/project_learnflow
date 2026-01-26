<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserQuestionnaireResult;
use Carbon\Carbon;

class QuizAnalyticsSeeder extends Seeder
{
    public function run()
    {
        // Create a test user if none exists
        $user = User::first() ?? User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        // Create test notes
        $subjects = ['Mathematics', 'Science', 'History', 'Literature', 'Physics'];
        $notes = [];
        
        foreach ($subjects as $subject) {
            $note = Note::create([
                'user_id' => $user->id,
                'title' => "$subject Study Material",
                'content' => "Sample content for $subject",
                'subject_area' => $subject,
                'tags' => json_encode([strtolower($subject)]),
                'is_favorite' => false
            ]);
            $notes[] = $note;
        }

        // Create test quizzes
        $quizzes = [];
        foreach ($notes as $note) {
            $quiz = Quiz::create([
                'title' => "Quiz for " . $note->title,
                'note_id' => $note->id,
                'questions' => json_encode($this->generateSampleQuestions()),
                'total_questions' => 10,
                'estimated_time' => 600, // 10 minutes
                'difficulty_level' => 'Medium',
                'is_active' => true
            ]);
            $quizzes[] = $quiz;
        }

        // Create quiz attempts for the last 30 days
        $this->createQuizAttempts($user, $quizzes);
        
        // Create questionnaire results
        $this->createQuestionnaireResults($user);
    }

    private function createQuizAttempts($user, $quizzes)
    {
        $startDate = Carbon::now()->subDays(30);
        
        for ($i = 0; $i < 25; $i++) {
            $date = $startDate->copy()->addDays(rand(0, 30));
            $quiz = $quizzes[array_rand($quizzes)];
            
            // Simulate improvement over time
            $basePerfomance = 60 + ($i * 1.5); // Gradual improvement
            $score = min(100, max(40, $basePerfomance + rand(-15, 15)));
            
            $answers = $this->generateSampleAnswers($score);
            
            QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'answers' => json_encode($answers),
                'score' => count(array_filter($answers, fn($a) => $a['is_correct'])),
                'total_questions' => count($answers),
                'percentage' => $score,
                'time_taken' => rand(300, 900), // 5-15 minutes
                'completed_at' => $date,
                'created_at' => $date,
                'updated_at' => $date
            ]);
        }
    }

    private function createQuestionnaireResults($user)
    {
        $responses = [
            'study_time_preference' => 'Morning',
            'learning_style' => 'Visual',
            'difficulty_preference' => 'Medium',
            'study_duration' => 'Long sessions',
            'preferred_subjects' => 'Mathematics, Science'
        ];

        UserQuestionnaireResult::create([
            'user_id' => $user->id,
            'responses' => json_encode($responses),
            'created_at' => Carbon::now()->subDays(25),
            'updated_at' => Carbon::now()->subDays(25)
        ]);
    }

    private function generateSampleQuestions()
    {
        return [
            [
                'question' => 'What is 2 + 2?',
                'options' => ['3', '4', '5', '6'],
                'correct_option' => 1,
                'difficulty' => 'Easy'
            ],
            [
                'question' => 'What is the capital of France?',
                'options' => ['London', 'Berlin', 'Paris', 'Madrid'],
                'correct_option' => 2,
                'difficulty' => 'Medium'
            ],
            [
                'question' => 'What is the derivative of x²?',
                'options' => ['x', '2x', 'x²', '2x²'],
                'correct_option' => 1,
                'difficulty' => 'Hard'
            ]
        ];
    }

    private function generateSampleAnswers($targetScore)
    {
        $answers = [];
        $numQuestions = 10;
        $targetCorrect = round($targetScore / 10);
        
        for ($i = 0; $i < $numQuestions; $i++) {
            $isCorrect = $i < $targetCorrect;
            $difficulty = ['Easy', 'Medium', 'Hard'][rand(0, 2)];
            $responseTime = $difficulty === 'Easy' ? rand(5, 15) : 
                          ($difficulty === 'Medium' ? rand(15, 35) : rand(35, 60));
            
            $answers[] = [
                'question_id' => $i + 1,
                'selected_option' => rand(0, 3),
                'correct_option' => $isCorrect ? rand(0, 3) : (rand(0, 3) + 1) % 4,
                'is_correct' => $isCorrect,
                'response_time' => $responseTime,
                'difficulty' => $difficulty
            ];
        }
        
        return $answers;
    }
}
