<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GamificationService;
use App\Models\UserPoints;
use App\Models\Achievement;
use App\Models\DailyChallenge;
use App\Models\UserChallengeProgress;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Display the gamification dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $userStats = $this->gamificationService->getUserStats($user);
        $leaderboard = $this->gamificationService->getLeaderboard(10);
        
        // Get today's challenges
        $todaysChallenges = DailyChallenge::today()->active()->get()->map(function($challenge) use ($user) {
            $challenge->userProgress = $challenge->getUserProgress($user->id);
            return $challenge;
        });

        // Get user's recent achievements (last 5)
        $recentAchievements = $user->achievements()
            ->orderBy('user_achievements.created_at', 'desc')
            ->limit(5)
            ->get();

        // Get all available achievements
        $allAchievements = Achievement::active()->get()->map(function($achievement) use ($user) {
            $achievement->is_unlocked = $achievement->isUnlockedBy($user);
            return $achievement;
        });

        return view('gamification.dashboard', compact(
            'userStats',
            'leaderboard',
            'todaysChallenges',
            'recentAchievements',
            'allAchievements'
        ));
    }

    /**
     * Display the leaderboard
     */
    public function leaderboard(Request $request)
    {
        $limit = $request->get('limit', 50);
        $leaderboard = $this->gamificationService->getLeaderboard($limit);
        
        $user = Auth::user();
        $userStats = $this->gamificationService->getUserStats($user);

        return view('gamification.leaderboard', compact('leaderboard', 'userStats'));
    }

    /**
     * Display achievements page
     */
    public function achievements()
    {
        $user = Auth::user();
        
        // Get achievements grouped by category
        $achievements = Achievement::active()
            ->orderBy('category')
            ->orderBy('rarity_level')
            ->get()
            ->groupBy('category')
            ->map(function($categoryAchievements) use ($user) {
                return $categoryAchievements->map(function($achievement) use ($user) {
                    $achievement->is_unlocked = $achievement->isUnlockedBy($user);
                    if ($achievement->is_unlocked) {
                        $unlockRecord = $user->achievements()
                            ->where('achievement_id', $achievement->id)
                            ->first();
                        $achievement->unlocked_at = $unlockRecord ? $unlockRecord->pivot->created_at : null;
                    }
                    return $achievement;
                });
            });

        $userStats = $this->gamificationService->getUserStats($user);

        return view('gamification.achievements', compact('achievements', 'userStats'));
    }

    /**
     * Display daily challenges
     */
    public function challenges()
    {
        $user = Auth::user();
        
        // Get today's challenges with user progress
        $todaysChallenges = DailyChallenge::today()->active()->get()->map(function($challenge) use ($user) {
            $challenge->userProgress = $challenge->getUserProgress($user->id);
            return $challenge;
        });

        // Get this week's completed challenges
        $weeklyCompleted = UserChallengeProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->where('created_at', '>=', now()->startOfWeek())
            ->with('dailyChallenge')
            ->get();

        $userStats = $this->gamificationService->getUserStats($user);

        return view('gamification.challenges', compact(
            'todaysChallenges',
            'weeklyCompleted',
            'userStats'
        ));
    }

    /**
     * API endpoint to get gamification summary
     */
    public function summary()
    {
        $user = Auth::user();
        $userStats = $this->gamificationService->getUserStats($user);
        
        return response()->json([
            'level' => $userStats['current_level'],
            'level_title' => $userStats['level_title'],
            'points' => $userStats['total_points'],
            'streak' => $userStats['daily_streak'],
            'rank' => $userStats['rank'],
            'points_to_next_level' => $userStats['points_to_next_level'],
            'level_progress' => $userStats['level_progress']
        ]);
    }

    /**
     * Generate daily challenges (admin function)
     */
    public function generateChallenges()
    {
        $this->gamificationService->generateDailyChallenges();
        
        return redirect()->back()->with('success', 'Daily challenges generated successfully!');
    }

    /**
     * Award manual points (admin function)
     */
    public function awardPoints(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        
        $result = $this->gamificationService->awardPoints(
            $user,
            'manual_award',
            ['admin_awarded' => true],
            $request->reason
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => "Awarded {$request->points} points to {$user->name}",
                'result' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to award points'
        ], 500);
    }
}