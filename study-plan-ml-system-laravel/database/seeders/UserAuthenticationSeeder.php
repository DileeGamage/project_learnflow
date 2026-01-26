<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserQuestionnaireResult;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserAuthenticationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create main test user
        $mainUser = User::firstOrCreate(
            ['email' => 'john@studyplan.com'],
            [
                'name' => 'John Smith',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Create additional test users
        $users = [$mainUser];
        for ($i = 0; $i < 4; $i++) {
            $email = $faker->unique()->safeEmail;
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $faker->name,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );
            $users[] = $user;
        }

        // Update existing notes to belong to users
        $notes = Note::all();
        foreach ($notes as $note) {
            if (!$note->user_id) {
                $note->update(['user_id' => $faker->randomElement($users)->id]);
            }
        }

        // Update existing quizzes to belong to users
        $quizzes = Quiz::all();
        foreach ($quizzes as $quiz) {
            if (!$quiz->user_id) {
                // Get the user who owns the note this quiz is based on
                $noteUserId = $quiz->note ? $quiz->note->user_id : $faker->randomElement($users)->id;
                $quiz->update(['user_id' => $noteUserId]);
            }
        }

        // Update existing quiz attempts
        $attempts = QuizAttempt::all();
        foreach ($attempts as $attempt) {
            if (!$attempt->user_id) {
                // Get the user who owns the quiz this attempt is for
                $quizUserId = $attempt->quiz ? $attempt->quiz->user_id : $faker->randomElement($users)->id;
                $attempt->update(['user_id' => $quizUserId]);
            }
        }

        // Create some user questionnaire results
        foreach ($users as $user) {
            UserQuestionnaireResult::create([
                'user_id' => $user->id,
                'responses' => [
                    'questionnaire_responses' => [
                        'learning_style' => $faker->randomElement(['visual', 'auditory', 'kinesthetic', 'reading']),
                        'study_time_preference' => $faker->randomElement(['morning', 'afternoon', 'evening', 'night']),
                        'difficulty_preference' => $faker->randomElement(['easy', 'medium', 'hard']),
                        'subject_interest' => $faker->randomElements(['mathematics', 'science', 'literature', 'history', 'technology'], 2),
                        'study_frequency' => $faker->randomElement(['daily', 'few_times_week', 'weekly', 'monthly']),
                        'break_frequency' => $faker->numberBetween(15, 60),
                        'motivation_level' => $faker->numberBetween(1, 10),
                        'attention_span' => $faker->numberBetween(10, 120),
                    ],
                    'analysis_result' => [
                        'dominant_learning_style' => $faker->randomElement(['visual', 'auditory', 'kinesthetic']),
                        'study_efficiency' => $faker->numberBetween(60, 95),
                        'attention_rating' => $faker->randomElement(['high', 'medium', 'low']),
                        'consistency_score' => $faker->numberBetween(1, 10),
                    ],
                    'learning_style' => $faker->randomElement(['visual', 'auditory', 'kinesthetic', 'reading']),
                    'study_preferences' => [
                        'optimal_study_duration' => $faker->numberBetween(30, 120),
                        'break_duration' => $faker->numberBetween(5, 20),
                        'preferred_subjects' => $faker->randomElements(['mathematics', 'science', 'literature'], 2),
                        'study_environment' => $faker->randomElement(['quiet', 'background_music', 'social']),
                    ],
                    'skill_assessment' => [
                        'reading_comprehension' => $faker->numberBetween(60, 100),
                        'analytical_thinking' => $faker->numberBetween(60, 100),
                        'memory_retention' => $faker->numberBetween(60, 100),
                        'problem_solving' => $faker->numberBetween(60, 100),
                    ],
                    'recommendations' => [
                        'study_techniques' => $faker->randomElements(['spaced_repetition', 'active_recall', 'mind_mapping'], 2),
                        'resource_types' => $faker->randomElements(['videos', 'articles', 'practice_tests'], 2),
                        'study_schedule' => 'Custom schedule based on preferences',
                    ],
                ],
            ]);
        }

        $this->command->info('User authentication seeder completed!');
        $this->command->info('Main test user created:');
        $this->command->info('Email: john@studyplan.com');
        $this->command->info('Password: password123');
        $this->command->info('');
        $this->command->info('Additional users created with email/password: [email]/password123');
        $this->command->info('All existing data has been associated with users.');
    }
}
