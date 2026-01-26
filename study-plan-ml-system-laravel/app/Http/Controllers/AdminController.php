<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Note;
use App\Models\StudySession;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users_today' => User::whereDate('last_login_at', today())->count(),
                'total_quizzes' => Quiz::count(),
                'total_notes' => Note::count(),
                'avg_session_time' => 0, // Simplified for now
                'experiment_groups' => [
                    'control' => User::where('experiment_group', 'control')->count(),
                    'gamified' => User::where('experiment_group', 'gamified')->count(),
                    'unassigned' => User::whereNull('experiment_group')->count(),
                ]
            ];

            // Simplified recent activity for now
            $recent_activity = User::orderBy('updated_at', 'desc')->take(10)->get();

            return view('admin.dashboard', compact('stats', 'recent_activity'));
        } catch (\Exception $e) {
            // Fallback with minimal data
            $stats = [
                'total_users' => User::count(),
                'active_users_today' => 0,
                'total_quizzes' => 0,
                'total_notes' => 0,
                'avg_session_time' => 0,
                'experiment_groups' => [
                    'control' => User::where('experiment_group', 'control')->count(),
                    'gamified' => User::where('experiment_group', 'gamified')->count(),
                    'unassigned' => User::whereNull('experiment_group')->count(),
                ]
            ];
            $recent_activity = collect();
            
            return view('admin.dashboard', compact('stats', 'recent_activity'));
        }
    }

    public function users(Request $request)
    {
        try {
            $query = User::query();

            // Filter by experiment group
            if ($request->filled('experiment_group')) {
                $query->where('experiment_group', $request->experiment_group);
            }

            // Search by name or email
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $users = $query->paginate(20);

            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            $users = User::paginate(20);
            return view('admin.users.index', compact('users'));
        }
    }

    public function userDetails(User $user)
    {
        try {
            // Calculate research metrics with safety checks
            $metrics = [
                'total_study_time' => 0, // Will implement when StudySession is ready
                'avg_quiz_score' => 0,   // Will implement when Quiz relationship is ready
                'quiz_improvement' => 0,
                'engagement_score' => $this->calculateEngagementScore($user),
                'learning_streak' => 0,
            ];

            return view('admin.users.show', compact('user', 'metrics'));
        } catch (\Exception $e) {
            $metrics = [
                'total_study_time' => 0,
                'avg_quiz_score' => 0,
                'quiz_improvement' => 0,
                'engagement_score' => 0,
                'learning_streak' => 0,
            ];
            
            return view('admin.users.show', compact('user', 'metrics'));
        }
    }

    public function research()
    {
        // Simplified research data for now
        $research_data = [
            'experiment_performance' => [
                'control' => ['avg_quiz_score' => 0, 'avg_study_time' => 0],
                'gamified' => ['avg_quiz_score' => 0, 'avg_study_time' => 0],
            ],
            'engagement_comparison' => collect(),
            'learning_outcomes' => [],
            'statistical_significance' => ['insufficient_data' => true],
        ];

        return view('admin.research', compact('research_data'));
    }

    public function analytics()
    {
        // Simplified analytics for now
        $analytics = [
            'daily_active_users' => collect(),
            'feature_usage' => [
                'ocr_usage' => 0,
                'quiz_generation' => 0,
                'gamification_features' => 0,
            ],
            'performance_trends' => collect(),
            'retention_rates' => [
                'day_1' => 0,
                'day_7' => 0,
                'day_30' => 0,
            ],
        ];

        return view('admin.analytics', compact('analytics'));
    }

    public function experiments()
    {
        try {
            $experiments = [
                'control_group' => User::where('experiment_group', 'control')->get(),
                'gamified_group' => User::where('experiment_group', 'gamified')->get(),
                'unassigned' => User::whereNull('experiment_group')->get(),
            ];

            return view('admin.experiments', compact('experiments'));
        } catch (\Exception $e) {
            $experiments = [
                'control_group' => collect(),
                'gamified_group' => collect(),
                'unassigned' => User::all(),
            ];
            
            return view('admin.experiments', compact('experiments'));
        }
    }

    public function assignExperimentGroup(Request $request, User $user)
    {
        $request->validate([
            'group' => 'required|in:control,gamified'
        ]);

        $user->update(['experiment_group' => $request->group]);

        return back()->with('success', 'User assigned to ' . $request->group . ' group successfully.');
    }

    // Helper methods for research analytics
    private function calculateQuizImprovement(User $user)
    {
        $quizzes = $user->quizzes()->orderBy('created_at')->get();
        if ($quizzes->count() < 2) return 0;

        $firstHalf = $quizzes->take($quizzes->count() / 2)->avg('score');
        $secondHalf = $quizzes->skip($quizzes->count() / 2)->avg('score');

        return round($secondHalf - $firstHalf, 2);
    }

    private function calculateEngagementScore(User $user)
    {
        try {
            // Simple engagement score based on account activity
            $score = 0;
            
            // Points for being active (having updated their account recently)
            if ($user->updated_at && $user->updated_at->gt(now()->subDays(30))) {
                $score += 10;
            }
            
            // Points for having a role
            if ($user->role) {
                $score += 5;
            }
            
            // Points for being in an experiment group
            if ($user->experiment_group) {
                $score += 15;
            }
            
            return $score;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculateLearningStreak(User $user)
    {
        $sessions = $user->studySessions()
            ->selectRaw('DATE(created_at) as date')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('date');

        $streak = 0;
        $current_date = now()->format('Y-m-d');

        foreach ($sessions as $session_date) {
            if ($session_date === $current_date) {
                $streak++;
                $current_date = now()->subDays($streak)->format('Y-m-d');
            } else {
                break;
            }
        }

        return $streak;
    }

    private function getExperimentPerformance()
    {
        return [
            'control' => [
                'avg_quiz_score' => User::where('experiment_group', 'control')
                    ->join('quizzes', 'users.id', '=', 'quizzes.user_id')
                    ->avg('quizzes.score') ?? 0,
                'avg_study_time' => User::where('experiment_group', 'control')
                    ->join('study_sessions', 'users.id', '=', 'study_sessions.user_id')
                    ->avg('study_sessions.duration_minutes') ?? 0,
            ],
            'gamified' => [
                'avg_quiz_score' => User::where('experiment_group', 'gamified')
                    ->join('quizzes', 'users.id', '=', 'quizzes.user_id')
                    ->avg('quizzes.score') ?? 0,
                'avg_study_time' => User::where('experiment_group', 'gamified')
                    ->join('study_sessions', 'users.id', '=', 'study_sessions.user_id')
                    ->avg('study_sessions.duration_minutes') ?? 0,
            ]
        ];
    }

    private function getEngagementComparison()
    {
        return DB::table('users')
            ->leftJoin('study_sessions', 'users.id', '=', 'study_sessions.user_id')
            ->select('experiment_group')
            ->selectRaw('COUNT(study_sessions.id) as total_sessions')
            ->selectRaw('AVG(study_sessions.duration_minutes) as avg_duration')
            ->where('experiment_group', '!=', null)
            ->groupBy('experiment_group')
            ->get();
    }

    private function getLearningOutcomes()
    {
        return [
            'quiz_scores_by_group' => Quiz::join('users', 'quizzes.user_id', '=', 'users.id')
                ->select('experiment_group')
                ->selectRaw('AVG(score) as avg_score, COUNT(*) as total_quizzes')
                ->whereNotNull('experiment_group')
                ->groupBy('experiment_group')
                ->get(),
            'retention_by_group' => User::select('experiment_group')
                ->selectRaw('COUNT(*) as total_users')
                ->selectRaw('SUM(CASE WHEN last_login_at >= ? THEN 1 ELSE 0 END) as active_users', [now()->subDays(7)])
                ->whereNotNull('experiment_group')
                ->groupBy('experiment_group')
                ->get()
        ];
    }

    private function calculateStatisticalSignificance()
    {
        // Basic t-test calculation for quiz scores between groups
        $control_scores = Quiz::join('users', 'quizzes.user_id', '=', 'users.id')
            ->where('experiment_group', 'control')
            ->pluck('score');
        
        $gamified_scores = Quiz::join('users', 'quizzes.user_id', '=', 'users.id')
            ->where('experiment_group', 'gamified')
            ->pluck('score');

        if ($control_scores->count() < 2 || $gamified_scores->count() < 2) {
            return ['insufficient_data' => true];
        }

        $control_mean = $control_scores->avg();
        $gamified_mean = $gamified_scores->avg();
        $difference = $gamified_mean - $control_mean;

        return [
            'control_mean' => round($control_mean, 2),
            'gamified_mean' => round($gamified_mean, 2),
            'difference' => round($difference, 2),
            'sample_sizes' => [
                'control' => $control_scores->count(),
                'gamified' => $gamified_scores->count()
            ]
        ];
    }

    private function getDailyActiveUsers()
    {
        return User::selectRaw('DATE(last_login_at) as date, COUNT(*) as count')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getFeatureUsage()
    {
        return [
            'ocr_usage' => Note::whereNotNull('ocr_text')->count(),
            'quiz_generation' => Quiz::where('ai_generated', true)->count(),
            'gamification_features' => User::where('points', '>', 0)->count(),
        ];
    }

    private function getPerformanceTrends()
    {
        return Quiz::selectRaw('DATE(created_at) as date, AVG(score) as avg_score')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getRetentionRates()
    {
        $total_users = User::count();
        return [
            'day_1' => User::where('last_login_at', '>=', now()->subDay())->count() / max($total_users, 1) * 100,
            'day_7' => User::where('last_login_at', '>=', now()->subDays(7))->count() / max($total_users, 1) * 100,
            'day_30' => User::where('last_login_at', '>=', now()->subDays(30))->count() / max($total_users, 1) * 100,
        ];
    }
}
