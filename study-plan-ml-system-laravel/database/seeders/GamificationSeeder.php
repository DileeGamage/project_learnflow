<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Achievement;
use App\Models\DailyChallenge;
use Carbon\Carbon;

class GamificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial achievements
        $achievements = [
            // Beginner achievements
            [
                'name' => 'First Steps',
                'description' => 'Complete your first quiz',
                'category' => 'milestones',
                'criteria' => [
                    ['type' => 'total_quizzes', 'value' => 1]
                ],
                'points_reward' => 25,
                'icon' => 'star',
                'rarity_level' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Perfect Score',
                'description' => 'Achieve 100% on any quiz',
                'category' => 'performance',
                'criteria' => [
                    ['type' => 'perfect_scores', 'value' => 1]
                ],
                'points_reward' => 50,
                'icon' => 'trophy',
                'rarity_level' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Streak Master',
                'description' => 'Maintain a 7-day learning streak',
                'category' => 'consistency',
                'criteria' => [
                    ['type' => 'daily_streak', 'value' => 7]
                ],
                'points_reward' => 100,
                'icon' => 'fire',
                'rarity_level' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Quiz Enthusiast',
                'description' => 'Complete 10 quizzes',
                'category' => 'milestones',
                'criteria' => [
                    ['type' => 'total_quizzes', 'value' => 10]
                ],
                'points_reward' => 75,
                'icon' => 'book',
                'rarity_level' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Knowledge Seeker',
                'description' => 'Complete the learning habits questionnaire',
                'category' => 'exploration',
                'criteria' => [
                    ['type' => 'habits_completed', 'value' => 1]
                ],
                'points_reward' => 30,
                'icon' => 'brain',
                'rarity_level' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Perfectionist',
                'description' => 'Achieve 100% on 5 different quizzes',
                'category' => 'performance',
                'criteria' => [
                    ['type' => 'perfect_scores', 'value' => 5]
                ],
                'points_reward' => 200,
                'icon' => 'crown',
                'rarity_level' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Point Collector',
                'description' => 'Earn 500 total points',
                'category' => 'milestones',
                'criteria' => [
                    ['type' => 'total_points', 'value' => 500]
                ],
                'points_reward' => 50,
                'icon' => 'gem',
                'rarity_level' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Dedicated Learner',
                'description' => 'Maintain a 30-day learning streak',
                'category' => 'consistency',
                'criteria' => [
                    ['type' => 'daily_streak', 'value' => 30]
                ],
                'points_reward' => 500,
                'icon' => 'medal',
                'rarity_level' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Rising Star',
                'description' => 'Reach Level 5',
                'category' => 'milestones',
                'criteria' => [
                    ['type' => 'level_reached', 'value' => 5]
                ],
                'points_reward' => 150,
                'icon' => 'star-fill',
                'rarity_level' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Quiz Master',
                'description' => 'Complete 50 quizzes',
                'category' => 'milestones',
                'criteria' => [
                    ['type' => 'total_quizzes', 'value' => 50]
                ],
                'points_reward' => 300,
                'icon' => 'graduation-cap',
                'rarity_level' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Elite Scholar',
                'description' => 'Reach Level 10',
                'category' => 'milestones',
                'criteria' => [
                    ['type' => 'level_reached', 'value' => 10]
                ],
                'points_reward' => 500,
                'icon' => 'diamond',
                'rarity_level' => 4,
                'is_active' => true
            ],
            [
                'name' => 'Unstoppable',
                'description' => 'Maintain a 100-day learning streak',
                'category' => 'consistency',
                'criteria' => [
                    ['type' => 'daily_streak', 'value' => 100]
                ],
                'points_reward' => 1000,
                'icon' => 'lightning',
                'rarity_level' => 4,
                'is_active' => true
            ]
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }

        // Generate today's daily challenges
        $today = Carbon::today();
        
        $dailyChallenges = [
            [
                'title' => 'Quiz Starter',
                'description' => 'Complete 2 quizzes today',
                'challenge_type' => 'quiz_count',
                'requirements' => ['target_count' => 2],
                'points_reward' => 30,
                'challenge_date' => $today,
                'is_active' => true
            ],
            [
                'title' => 'Perfect Score',
                'description' => 'Achieve 100% on any quiz',
                'challenge_type' => 'perfect_score',
                'requirements' => ['target_score' => 100],
                'points_reward' => 75,
                'challenge_date' => $today,
                'is_active' => true
            ],
            [
                'title' => 'High Achiever',
                'description' => 'Score 85% or higher on a quiz',
                'challenge_type' => 'quiz_score',
                'requirements' => ['target_score' => 85],
                'points_reward' => 25,
                'challenge_date' => $today,
                'is_active' => true
            ]
        ];

        foreach ($dailyChallenges as $challenge) {
            DailyChallenge::create($challenge);
        }

        $this->command->info('Gamification seeder completed successfully!');
        $this->command->info('Created ' . count($achievements) . ' achievements');
        $this->command->info('Created ' . count($dailyChallenges) . ' daily challenges for today');
    }
}
