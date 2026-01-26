<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPoints;
use App\Models\PointTransaction;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\DailyChallenge;
use App\Models\UserChallengeProgress;
use App\Models\QuizAttempt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    const POINTS = [
        'quiz_completed' => 10,
        'quiz_perfect_score' => 50,
        'quiz_high_score' => 25, // 80%+
        'quiz_good_score' => 15, // 70%+
        'daily_streak' => 5,
        'weekly_streak' => 20,
        'first_quiz_day' => 15,
        'habits_questionnaire' => 30,
        'note_created' => 5,
        'level_up' => 0, // Points calculated based on level
        'achievement_unlocked' => 0, // Points come from achievement itself
        'challenge_completed' => 0, // Points come from challenge itself
    ];

    /**
     * Award points to a user for completing an activity
     */
    public function awardPoints(User $user, string $activityType, array $metadata = [], string $description = null)
    {
        try {
            $points = $this->calculatePoints($activityType, $metadata);
            
            if ($points <= 0) return null;

            // Get or create user points record
            $userPoints = UserPoints::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'total_points' => 0,
                    'current_level' => 1,
                    'points_in_level' => 0,
                    'daily_streak' => 0,
                    'weekly_streak' => 0
                ]
            );

            // Create transaction record
            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'activity_type' => $activityType,
                'points_earned' => $points,
                'description' => $description ?? $this->getDefaultDescription($activityType, $metadata),
                'metadata' => $metadata
            ]);

            // Update user points
            $this->updateUserPoints($userPoints, $points);

            // Check for level up
            $levelUp = $this->checkLevelUp($userPoints);

            // Update streaks
            $streakInfo = $this->updateStreaks($userPoints);

            // Update daily challenges
            $challengeResults = $this->updateChallenges($user, $activityType, $metadata);

            // Check for achievements
            $newAchievements = $this->checkAchievements($user);

            Log::info("Points awarded", [
                'user_id' => $user->id,
                'activity_type' => $activityType,
                'points' => $points,
                'level_up' => $levelUp,
                'new_achievements' => count($newAchievements)
            ]);

            return [
                'transaction' => $transaction,
                'level_up' => $levelUp,
                'streak_info' => $streakInfo,
                'challenge_results' => $challengeResults,
                'new_achievements' => $newAchievements,
                'user_points' => $userPoints->fresh()
            ];

        } catch (\Exception $e) {
            Log::error("Failed to award points", [
                'user_id' => $user->id,
                'activity_type' => $activityType,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Calculate points for a specific activity
     */
    private function calculatePoints(string $activityType, array $metadata): int
    {
        $basePoints = self::POINTS[$activityType] ?? 0;

        // Add bonus points based on metadata
        switch ($activityType) {
            case 'quiz_completed':
                $score = $metadata['score'] ?? 0;
                if ($score >= 100) {
                    return self::POINTS['quiz_perfect_score'];
                } elseif ($score >= 80) {
                    return self::POINTS['quiz_high_score'];
                } elseif ($score >= 70) {
                    return self::POINTS['quiz_good_score'];
                }
                return $basePoints;

            case 'daily_streak':
                $streakDays = $metadata['streak_days'] ?? 1;
                // Bonus for longer streaks
                $bonus = min($streakDays - 1, 10) * 2; // Max 20 bonus points
                return $basePoints + $bonus;

            case 'level_up':
                $newLevel = $metadata['new_level'] ?? 1;
                return $newLevel * 25; // 25 points per level

            default:
                return $basePoints;
        }
    }

    /**
     * Update user's total points and level points
     */
    private function updateUserPoints(UserPoints $userPoints, int $points): void
    {
        $userPoints->total_points += $points;
        $userPoints->points_in_level += $points;
        $userPoints->save();
    }

    /**
     * Check if user should level up
     */
    private function checkLevelUp(UserPoints $userPoints): ?array
    {
        $pointsRequired = $userPoints->getPointsRequiredForLevel($userPoints->current_level + 1);
        
        if ($userPoints->points_in_level >= $pointsRequired) {
            $oldLevel = $userPoints->current_level;
            $userPoints->current_level++;
            $userPoints->points_in_level = $userPoints->points_in_level - $pointsRequired;
            $userPoints->save();

            // Award level up bonus
            $this->awardPoints($userPoints->user, 'level_up', [
                'old_level' => $oldLevel,
                'new_level' => $userPoints->current_level
            ]);

            return [
                'old_level' => $oldLevel,
                'new_level' => $userPoints->current_level,
                'title' => $userPoints->level_title,
                'points_earned' => $userPoints->current_level * 25
            ];
        }

        return null;
    }

    /**
     * Update user's daily and weekly streaks
     */
    private function updateStreaks(UserPoints $userPoints): array
    {
        $today = Carbon::today();
        $lastActivity = $userPoints->last_activity_date;
        $streakUpdated = false;
        $streakInfo = [];

        if (!$lastActivity || $lastActivity->lt($today)) {
            if ($lastActivity && $lastActivity->eq($today->clone()->subDay())) {
                // Consecutive day
                $userPoints->daily_streak++;
                $streakUpdated = true;
                $streakInfo['streak_continued'] = true;
            } else {
                // Reset streak or first day
                $oldStreak = $userPoints->daily_streak;
                $userPoints->daily_streak = 1;
                $streakInfo['streak_reset'] = $oldStreak > 1;
                $streakInfo['old_streak'] = $oldStreak;
            }

            $userPoints->last_activity_date = $today;
            $userPoints->save();

            $streakInfo['current_streak'] = $userPoints->daily_streak;

            // Award streak bonus for streaks of 3+ days
            if ($streakUpdated && $userPoints->daily_streak >= 3) {
                $this->awardPoints($userPoints->user, 'daily_streak', [
                    'streak_days' => $userPoints->daily_streak
                ]);
                $streakInfo['streak_bonus_awarded'] = true;
            }
        }

        return $streakInfo;
    }

    /**
     * Update user's progress on daily challenges
     */
    private function updateChallenges(User $user, string $activityType, array $metadata): array
    {
        $results = [];
        $todaysChallenges = DailyChallenge::today()->active()->get();

        foreach ($todaysChallenges as $challenge) {
            if ($this->isChallengeRelevant($challenge, $activityType)) {
                $progress = UserChallengeProgress::firstOrCreate([
                    'user_id' => $user->id,
                    'daily_challenge_id' => $challenge->id
                ], [
                    'progress' => []
                ]);

                $updated = $this->updateChallengeProgress($progress, $challenge, $metadata);
                
                if ($updated && $progress->completed) {
                    // Award challenge completion points
                    $this->awardPoints($user, 'challenge_completed', [
                        'challenge_id' => $challenge->id,
                        'challenge_title' => $challenge->title,
                        'points' => $challenge->points_reward
                    ]);
                    
                    $results[] = [
                        'challenge' => $challenge,
                        'completed' => true,
                        'points_earned' => $challenge->points_reward
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Check if a challenge is relevant to the current activity
     */
    private function isChallengeRelevant(DailyChallenge $challenge, string $activityType): bool
    {
        $relevantActivities = [
            'quiz_count' => ['quiz_completed'],
            'quiz_score' => ['quiz_completed'],
            'perfect_score' => ['quiz_completed'],
            'streak' => ['daily_streak'],
            'study_time' => ['quiz_completed', 'note_created']
        ];

        $challengeActivities = $relevantActivities[$challenge->challenge_type] ?? [];
        return in_array($activityType, $challengeActivities);
    }

    /**
     * Update progress for a specific challenge
     */
    private function updateChallengeProgress(UserChallengeProgress $progress, DailyChallenge $challenge, array $metadata): bool
    {
        $currentProgress = $progress->progress ?? [];
        $updated = false;

        switch ($challenge->challenge_type) {
            case 'quiz_count':
                $currentProgress['completed_quizzes'] = ($currentProgress['completed_quizzes'] ?? 0) + 1;
                $updated = true;
                break;

            case 'quiz_score':
                $score = $metadata['score'] ?? 0;
                if ($score > ($currentProgress['best_score'] ?? 0)) {
                    $currentProgress['best_score'] = $score;
                    $updated = true;
                }
                break;

            case 'perfect_score':
                $score = $metadata['score'] ?? 0;
                if ($score >= 100) {
                    $currentProgress['perfect_scores'] = ($currentProgress['perfect_scores'] ?? 0) + 1;
                    $updated = true;
                }
                break;

            case 'streak':
                $streakDays = $metadata['streak_days'] ?? 0;
                if ($streakDays > ($currentProgress['current_streak'] ?? 0)) {
                    $currentProgress['current_streak'] = $streakDays;
                    $updated = true;
                }
                break;
        }

        if ($updated) {
            return $progress->updateProgress($currentProgress);
        }

        return false;
    }

    /**
     * Check for new achievements
     */
    public function checkAchievements(User $user): array
    {
        $achievements = Achievement::active()->get();
        $newAchievements = [];
        
        foreach ($achievements as $achievement) {
            if (!$achievement->isUnlockedBy($user)) {
                if ($this->evaluateAchievementCriteria($user, $achievement->criteria)) {
                    // Unlock achievement
                    UserAchievement::create([
                        'user_id' => $user->id,
                        'achievement_id' => $achievement->id,
                        'unlocked_at' => now()
                    ]);

                    // Award points
                    if ($achievement->points_reward > 0) {
                        $this->awardPoints($user, 'achievement_unlocked', [
                            'achievement_id' => $achievement->id,
                            'achievement_name' => $achievement->name,
                            'points' => $achievement->points_reward
                        ]);
                    }

                    $newAchievements[] = $achievement;
                }
            }
        }

        return $newAchievements;
    }

    /**
     * Evaluate if user meets achievement criteria
     */
    private function evaluateAchievementCriteria(User $user, array $criteria): bool
    {
        foreach ($criteria as $criterion) {
            $type = $criterion['type'];
            $value = $criterion['value'];

            switch ($type) {
                case 'total_quizzes':
                    if ($user->quizAttempts()->count() < $value) return false;
                    break;

                case 'perfect_scores':
                    if ($user->quizAttempts()->where('percentage', 100)->count() < $value) return false;
                    break;

                case 'daily_streak':
                    $userPoints = $user->userPoints;
                    if (!$userPoints || $userPoints->daily_streak < $value) return false;
                    break;

                case 'total_points':
                    $userPoints = $user->userPoints;
                    if (!$userPoints || $userPoints->total_points < $value) return false;
                    break;

                case 'level_reached':
                    $userPoints = $user->userPoints;
                    if (!$userPoints || $userPoints->current_level < $value) return false;
                    break;

                case 'habits_completed':
                    if ($user->questionnaireResults()->count() < $value) return false;
                    break;
            }
        }

        return true;
    }

    /**
     * Get default description for activity
     */
    private function getDefaultDescription(string $activityType, array $metadata): string
    {
        switch ($activityType) {
            case 'quiz_completed':
                $score = $metadata['score'] ?? 0;
                return "Completed quiz with {$score}% score";
            
            case 'daily_streak':
                $days = $metadata['streak_days'] ?? 1;
                return "Maintained {$days} day learning streak";
            
            case 'habits_questionnaire':
                return "Completed learning habits assessment";
            
            case 'level_up':
                $level = $metadata['new_level'] ?? 1;
                return "Reached Level {$level}!";
            
            case 'achievement_unlocked':
                $name = $metadata['achievement_name'] ?? 'achievement';
                return "Unlocked: {$name}";

            case 'challenge_completed':
                $title = $metadata['challenge_title'] ?? 'challenge';
                return "Completed daily challenge: {$title}";
            
            default:
                return ucfirst(str_replace('_', ' ', $activityType));
        }
    }

    /**
     * Get leaderboard data
     */
    public function getLeaderboard(int $limit = 10): array
    {
        return UserPoints::with('user')
            ->orderBy('total_points', 'desc')
            ->orderBy('current_level', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($userPoints, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $userPoints->user,
                    'points' => $userPoints->total_points,
                    'level' => $userPoints->current_level,
                    'level_title' => $userPoints->level_title,
                    'level_color' => $userPoints->level_color
                ];
            })
            ->toArray();
    }

    /**
     * Get comprehensive user statistics
     */
    public function getUserStats(User $user): array
    {
        $userPoints = $user->userPoints;
        if (!$userPoints) {
            return $this->getDefaultStats();
        }

        $rank = UserPoints::where('total_points', '>', $userPoints->total_points)->count() + 1;
        $totalUsers = UserPoints::count();
        
        $recentTransactions = $user->pointTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $todaysPoints = $user->pointTransactions()
            ->whereDate('created_at', today())
            ->sum('points_earned');

        $weeklyPoints = $user->pointTransactions()
            ->where('created_at', '>=', now()->subWeek())
            ->sum('points_earned');

        return [
            'total_points' => $userPoints->total_points,
            'current_level' => $userPoints->current_level,
            'level_title' => $userPoints->level_title,
            'level_color' => $userPoints->level_color,
            'points_to_next_level' => $userPoints->points_to_next_level,
            'level_progress' => $userPoints->level_progress,
            'daily_streak' => $userPoints->daily_streak,
            'weekly_streak' => $userPoints->weekly_streak,
            'rank' => $rank,
            'total_users' => $totalUsers,
            'achievements_count' => $user->achievements()->count(),
            'recent_transactions' => $recentTransactions,
            'todays_points' => $todaysPoints,
            'weekly_points' => $weeklyPoints,
            'has_leveled_up_today' => $userPoints->hasLeveledUpToday()
        ];
    }

    /**
     * Get default stats for new users
     */
    private function getDefaultStats(): array
    {
        return [
            'total_points' => 0,
            'current_level' => 1,
            'level_title' => 'Novice Learner',
            'level_color' => '#95a5a6',
            'points_to_next_level' => 100,
            'level_progress' => 0,
            'daily_streak' => 0,
            'weekly_streak' => 0,
            'rank' => 0,
            'total_users' => UserPoints::count(),
            'achievements_count' => 0,
            'recent_transactions' => collect(),
            'todays_points' => 0,
            'weekly_points' => 0,
            'has_leveled_up_today' => false
        ];
    }

    /**
     * Create daily challenges for today
     */
    public function generateDailyChallenges(): void
    {
        $today = today();
        
        // Check if challenges already exist for today
        if (DailyChallenge::where('challenge_date', $today)->exists()) {
            return;
        }

        $challenges = [
            [
                'title' => 'Quiz Master',
                'description' => 'Complete 3 quizzes today',
                'challenge_type' => 'quiz_count',
                'requirements' => ['target_count' => 3],
                'points_reward' => 50,
                'challenge_date' => $today
            ],
            [
                'title' => 'Perfect Score',
                'description' => 'Achieve 100% on any quiz',
                'challenge_type' => 'perfect_score',
                'requirements' => ['target_score' => 100],
                'points_reward' => 75,
                'challenge_date' => $today
            ],
            [
                'title' => 'High Achiever',
                'description' => 'Score 85% or higher on a quiz',
                'challenge_type' => 'quiz_score',
                'requirements' => ['target_score' => 85],
                'points_reward' => 30,
                'challenge_date' => $today
            ]
        ];

        foreach ($challenges as $challenge) {
            DailyChallenge::create($challenge);
        }
    }
}