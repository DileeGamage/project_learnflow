<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserQuestionnaireResult;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get user-specific statistics
        $stats = [
            'total_notes' => $user->notes()->count(),
            'questionnaires' => $user->questionnaireResults()->count(),
            'tests_completed' => $user->quizAttempts()->count(),
            'subjects' => $user->notes()->distinct('subject_area')->count()
        ];

        // Get user's recent performance
        $recentAttempts = $user->quizAttempts()
            ->with('quiz.note')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $performance = [
            'labels' => $recentAttempts->pluck('created_at')->map(function($date) {
                return $date->format('M j');
            })->reverse()->values(),
            'data' => $recentAttempts->pluck('percentage')->reverse()->values()
        ];

        // Recent activity
        $recentNotes = $user->notes()->latest()->limit(5)->get();
        $recentQuizzes = $user->quizzes()->with('note')->latest()->limit(5)->get();

        return view('dashboard', compact('stats', 'performance', 'recentNotes', 'recentQuizzes', 'user'));
    }

    public function analytics()
    {
        $user = auth()->user();
        
        // Get overall statistics
        $stats = [
            'total_notes' => $user->notes()->count(),
            'total_quizzes' => $user->quizzes()->count(),
            'total_attempts' => $user->quizAttempts()->count(),
            'total_questionnaires' => $user->questionnaireResults()->count(),
        ];
        
        // Recent activity (last 7 days)
        $recentActivity = $user->quizAttempts()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
            
        // Overall average score
        $avgScore = $user->quizAttempts()->avg('percentage') ?? 0;
        
        // Study streak (consecutive days with activity)
        $studyStreak = $this->calculateStudyStreak($user);
        
        // Recent notes with quizzes count
        $recentNotes = $user->notes()
            ->withCount('quizzes')
            ->latest()
            ->limit(5)
            ->get();
        
        // Recent quiz attempts
        $recentAttempts = $user->quizAttempts()
            ->with(['quiz.note'])
            ->latest()
            ->limit(8)
            ->get();
            
        // Subject distribution
        $subjectStats = $user->notes()
            ->select('subject_area', \DB::raw('count(*) as count'))
            ->whereNotNull('subject_area')
            ->where('subject_area', '!=', '')
            ->groupBy('subject_area')
            ->get();
            
        return view('analytics.index', compact(
            'stats',
            'recentActivity',
            'avgScore',
            'studyStreak',
            'recentNotes',
            'recentAttempts',
            'subjectStats'
        ));
    }

    public function performance()
    {
        $user = auth()->user();
        
        // Get performance metrics for last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        
        // Daily performance data
        $dailyPerformance = $user->quizAttempts()
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(created_at) as date, AVG(percentage) as avg_score, COUNT(*) as attempts')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Subject-wise performance
        $subjectPerformance = $user->quizAttempts()
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->join('notes', 'quizzes.note_id', '=', 'notes.id')
            ->where('quiz_attempts.created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('notes.subject_area, AVG(quiz_attempts.percentage) as avg_score, COUNT(*) as attempts')
            ->groupBy('notes.subject_area')
            ->get();
            
        // Time management data
        $avgTimePerQuiz = $user->quizAttempts()
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->avg('time_taken') ?? 0;
            
        // Best and worst performing subjects
        $bestSubject = $subjectPerformance->sortByDesc('avg_score')->first();
        $worstSubject = $subjectPerformance->sortBy('avg_score')->first();
        
        // Study habits correlation
        $questionnaireResults = $user->questionnaireResults()
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->with('questionnaire')
            ->get();
            
        // Learning progress trend
        $progressTrend = $this->calculateProgressTrend($user, $thirtyDaysAgo);
        
        // Time of day performance
        $timeOfDayPerformance = $user->quizAttempts()
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('HOUR(created_at) as hour, AVG(percentage) as avg_score')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
            
        return view('analytics.performance', compact(
            'dailyPerformance',
            'subjectPerformance',
            'avgTimePerQuiz',
            'bestSubject',
            'worstSubject',
            'questionnaireResults',
            'progressTrend',
            'timeOfDayPerformance'
        ));
    }
    
    private function calculateStudyStreak($user)
    {
        $streak = 0;
        $currentDate = now()->startOfDay();
        
        while (true) {
            $hasActivity = $user->quizAttempts()
                ->whereDate('created_at', $currentDate)
                ->exists();
                
            if ($hasActivity) {
                $streak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }
        
        return $streak;
    }
    
    private function calculateProgressTrend($user, $startDate)
    {
        $weeks = [];
        $currentWeek = now()->startOfWeek();
        
        for ($i = 0; $i < 4; $i++) {
            $weekStart = $currentWeek->copy()->subWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();
            
            $avgScore = $user->quizAttempts()
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->avg('percentage') ?? 0;
                
            $weeks[] = [
                'week' => 'Week ' . (4 - $i),
                'score' => round($avgScore, 1)
            ];
        }
        
        return array_reverse($weeks);
    }

    public function subjects()
    {
        return view('analytics.subjects');
    }
}
