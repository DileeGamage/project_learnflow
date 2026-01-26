<?php

namespace App\Services;

use App\Models\UserTopicPerformance;
use App\Models\QuizAttempt;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class SmartRecommendationService
{
    /**
     * Generate personalized recommendations based on quiz performance
     */
    public function generateRecommendations($userId, $quizAttemptId)
    {
        $attempt = QuizAttempt::with('quiz')->findOrFail($quizAttemptId);
        
        // Get quiz questions and user answers (already decoded by Laravel casts)
        $questions = $attempt->quiz->questions ?? [];
        $userAnswers = $attempt->answers ?? [];
        
        // Handle if questions is stored in a nested structure
        if (isset($questions['multiple_choice']) || isset($questions['true_false'])) {
            // Flatten nested question structure
            $questions = $this->flattenQuestions($questions);
        }
        
        // Update topic performance
        $this->updateTopicPerformance($userId, $attempt, $questions, $userAnswers);
        
        // Generate comprehensive recommendations
        $recommendations = [
            'weak_topics' => $this->getWeakTopicRecommendations($userId, $attempt->quiz->note_id),
            'revision_strategy' => $this->getRevisionStrategy($userId, $attempt),
            'practice_recommendations' => $this->getPracticeRecommendations($userId, $attempt->quiz->note_id),
            'performance_insights' => $this->getPerformanceInsights($userId, $attempt),
            'study_schedule' => $this->generateStudySchedule($userId, $attempt->quiz->note_id),
        ];
        
        return $recommendations;
    }
    
    /**
     * Flatten nested question structure into a simple array
     */
    private function flattenQuestions($questions)
    {
        $flattened = [];
        
        foreach ($questions as $type => $typeQuestions) {
            if (is_array($typeQuestions)) {
                foreach ($typeQuestions as $question) {
                    if (is_array($question)) {
                        $question['type'] = $type; // Add type for reference
                        $flattened[] = $question;
                    }
                }
            }
        }
        
        return $flattened;
    }

    /**
     * Update topic performance based on quiz answers
     */
    private function updateTopicPerformance($userId, $attempt, $questions, $userAnswers)
    {
        foreach ($questions as $index => $question) {
            // Extract topic from question (you'll need to add topic extraction logic)
            $topic = $this->extractTopicFromQuestion($question);
            
            if (!$topic) continue;
            
            // Check if answer is correct
            $isCorrect = isset($userAnswers[$index]) && 
                        $userAnswers[$index] == $question['correct_answer'];
            
            // Update or create topic performance
            $performance = UserTopicPerformance::firstOrCreate([
                'user_id' => $userId,
                'note_id' => $attempt->quiz->note_id,
                'topic_name' => $topic,
            ]);
            
            $performance->updatePerformance($isCorrect);
        }
    }

    /**
     * Extract topic from question text or metadata
     */
    private function extractTopicFromQuestion($question)
    {
        // Try to get topic from question metadata
        if (isset($question['topic'])) {
            return $question['topic'];
        }
        
        // Otherwise, try to extract from question text using keywords
        $questionText = $question['question'] ?? '';
        
        // Add your topic extraction logic here
        // For now, return a generic topic based on keywords
        $topics = [
            'sorting' => ['sort', 'bubble', 'quick', 'merge', 'insertion'],
            'arrays' => ['array', 'list', 'index', 'element'],
            'trees' => ['tree', 'binary', 'node', 'leaf', 'root'],
            'graphs' => ['graph', 'vertex', 'edge', 'path'],
            'algorithms' => ['algorithm', 'complexity', 'time', 'space'],
        ];
        
        foreach ($topics as $topic => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($questionText, $keyword) !== false) {
                    return ucfirst($topic);
                }
            }
        }
        
        return 'General Concepts';
    }

    /**
     * Get weak topic recommendations with specific actions
     */
    private function getWeakTopicRecommendations($userId, $noteId)
    {
        $weakTopics = UserTopicPerformance::where('user_id', $userId)
            ->where('note_id', $noteId)
            ->whereIn('mastery_level', ['weak', 'developing'])
            ->orderBy('mastery_score', 'asc')
            ->limit(5)
            ->get();

        $recommendations = [];
        
        foreach ($weakTopics as $topic) {
            $priority = $this->calculatePriority($topic);
            
            $recommendations[] = [
                'topic' => $topic->topic_name,
                'priority' => $priority,
                'priority_label' => $this->getPriorityLabel($priority),
                'mastery_score' => round($topic->mastery_score, 1),
                'mastery_level' => $topic->mastery_level,
                'questions_attempted' => $topic->questions_attempted,
                'questions_correct' => $topic->questions_correct,
                'action' => $this->getTopicAction($topic),
                'estimated_time' => $this->estimateStudyTime($topic),
                'specific_steps' => $this->getSpecificSteps($topic),
            ];
        }
        
        return $recommendations;
    }

    /**
     * Calculate priority level for a topic
     */
    private function calculatePriority($topic): string
    {
        $score = $topic->mastery_score;
        $attempts = $topic->questions_attempted;
        
        if ($score < 30 || $attempts > 5) {
            return 'critical';
        } elseif ($score < 50) {
            return 'high';
        } elseif ($score < 70) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get priority label for display
     */
    private function getPriorityLabel($priority): string
    {
        return match($priority) {
            'critical' => 'Critical - Immediate Action Required',
            'high' => 'High Priority',
            'medium' => 'Medium Priority',
            'low' => 'Low Priority',
            default => 'Review Needed',
        };
    }

    /**
     * Get specific action for improving topic mastery
     */
    private function getTopicAction($topic): string
    {
        $level = $topic->mastery_level;
        $topicName = $topic->topic_name;
        
        return match($level) {
            'weak' => "📚 Focus on {$topicName}: Review fundamental concepts from your notes. Watch tutorial videos and work through basic examples step-by-step.",
            'developing' => "✍️ Practice {$topicName}: You understand the basics. Now solve practice problems to build confidence and speed.",
            'proficient' => "🎯 Master {$topicName}: Complete advanced problems and edge cases. Teach the concept to reinforce your understanding.",
            'mastered' => "⭐ Maintain {$topicName}: Excellent work! Do quick reviews every week to retain mastery.",
            default => "📖 Study {$topicName}: Start with the basics and build your understanding gradually.",
        };
    }

    /**
     * Get specific learning steps
     */
    private function getSpecificSteps($topic): array
    {
        $score = $topic->mastery_score;
        
        if ($score < 30) {
            return [
                "Read the '{$topic->topic_name}' section in your notes carefully",
                "Watch a beginner-friendly video tutorial on this topic",
                "Make flashcards for key concepts and definitions",
                "Solve 3-5 very basic practice problems",
                "Retake this section of the quiz tomorrow",
            ];
        } elseif ($score < 50) {
            return [
                "Review your incorrect answers for {$topic->topic_name}",
                "Practice 5-10 problems of medium difficulty",
                "Try explaining the concept out loud",
                "Create a summary sheet of important formulas/steps",
                "Practice for 30 minutes daily this week",
            ];
        } elseif ($score < 70) {
            return [
                "Solve 10-15 practice problems with varying difficulty",
                "Focus on understanding WHY answers are correct/incorrect",
                "Try teaching this topic to a study buddy",
                "Practice timed questions to improve speed",
                "Review again in 3 days",
            ];
        } else {
            return [
                "Attempt challenging problems on {$topic->topic_name}",
                "Review weekly to maintain your knowledge",
                "Help others who are struggling with this topic",
            ];
        }
    }

    /**
     * Estimate study time needed
     */
    private function estimateStudyTime($topic): string
    {
        $score = $topic->mastery_score;
        
        if ($score < 30) {
            return '2-3 hours (Deep study needed)';
        } elseif ($score < 50) {
            return '1-2 hours (Moderate practice)';
        } elseif ($score < 70) {
            return '30-60 minutes (Focused review)';
        } else {
            return '15-30 minutes (Quick revision)';
        }
    }

    /**
     * Get revision strategy based on overall performance
     */
    private function getRevisionStrategy($userId, $attempt)
    {
        $scorePercentage = $attempt->percentage;
        $timeSpent = $attempt->time_taken;
        
        $strategies = [];
        
        // Performance-based strategies
        if ($scorePercentage < 40) {
            $strategies[] = [
                'priority' => 'critical',
                'title' => '🚨 Fundamental Review Required',
                'description' => 'Your score indicates significant gaps. Don\'t worry! Focus on mastering basics first before moving to advanced topics.',
                'action' => 'Re-study weak topics below',
                'daily_time' => '2-3 hours',
                'expected_improvement' => 'Can improve by 40-50% with focused study',
                'timeline' => '1-2 weeks of consistent practice',
            ];
        } elseif ($scorePercentage < 60) {
            $strategies[] = [
                'priority' => 'high',
                'title' => '📖 Targeted Topic Review',
                'description' => 'Good start! Focus on the weak topics identified below to significantly improve your score.',
                'action' => 'Practice weak topics daily',
                'daily_time' => '1-2 hours',
                'expected_improvement' => 'Can reach 70-80% scores',
                'timeline' => '1 week of focused practice',
            ];
        } elseif ($scorePercentage < 80) {
            $strategies[] = [
                'priority' => 'medium',
                'title' => '✅ Polish & Perfect',
                'description' => 'Great progress! Work on the few remaining weak areas to achieve excellence.',
                'action' => 'Review mistakes and practice',
                'daily_time' => '45-60 minutes',
                'expected_improvement' => 'Can achieve 85-95% scores',
                'timeline' => '3-5 days',
            ];
        } else {
            $strategies[] = [
                'priority' => 'low',
                'title' => '🌟 Excellent Performance!',
                'description' => 'Outstanding work! Focus on maintaining your knowledge and helping others.',
                'action' => 'Maintenance review',
                'daily_time' => '15-30 minutes',
                'expected_improvement' => 'Maintain 80%+ consistently',
                'timeline' => 'Weekly reviews',
            ];
        }
        
        // Time-based insights
        if ($timeSpent && $timeSpent < 60) {
            $strategies[] = [
                'priority' => 'medium',
                'title' => '⏱️ Take Your Time',
                'description' => 'You completed this quiz very quickly. Take more time to read questions carefully and think through your answers.',
                'action' => 'Slow down and double-check',
                'impact' => 'Can reduce careless mistakes by 15-20%',
            ];
        } elseif ($timeSpent && $timeSpent > 600) {
            $strategies[] = [
                'priority' => 'medium',
                'title' => '⚡ Build Speed & Confidence',
                'description' => 'You\'re being thorough, which is good! As you master topics, aim to answer more confidently to save time.',
                'action' => 'Practice timed questions',
                'impact' => 'Build both accuracy and speed',
            ];
        }
        
        return $strategies;
    }

    /**
     * Get practice recommendations
     */
    private function getPracticeRecommendations($userId, $noteId)
    {
        $weakTopics = UserTopicPerformance::getWeakTopics($userId, $noteId);
        $needsReview = UserTopicPerformance::getTopicsNeedingReview($userId);
        
        $recommendations = [];
        
        // Active Learning Techniques
        $recommendations[] = [
            'title' => '📝 Active Learning Strategies',
            'description' => 'Engage actively with the material to improve retention and understanding.',
            'specific_actions' => [
                'Write out key concepts in your own words',
                'Create mind maps connecting related topics',
                'Teach the material to someone else (or to yourself out loud)',
                'Generate your own practice questions and solve them',
            ],
        ];
        
        // Spaced Repetition
        if ($weakTopics->count() > 0) {
            $topicsList = $weakTopics->take(3)->pluck('topic_name')->map(function($topic) {
                return ucwords(str_replace('_', ' ', $topic));
            })->join(', ');
            
            $recommendations[] = [
                'title' => '🔄 Spaced Repetition Practice',
                'description' => "Focus on these weak topics: {$topicsList}",
                'specific_actions' => [
                    'Day 1: Study the topic thoroughly',
                    'Day 3: Quick 10-minute review',
                    'Day 7: Test yourself with practice questions',
                    'Day 14: Final review to lock in knowledge',
                ],
            ];
        }
        
        // Practice Question Goals
        $questionCount = $this->suggestQuestionCount($weakTopics->count());
        $weeklyGoal = $questionCount * 5;
        $recommendations[] = [
            'title' => '🎯 Practice Question Goals',
            'description' => 'Set clear targets to measure your progress.',
            'specific_actions' => [
                "Solve {$questionCount} practice questions daily",
                "Complete {$weeklyGoal} questions this week",
                'Focus on understanding WHY answers are correct',
                'Review all incorrect answers immediately',
            ],
        ];
        
        // Self-Assessment
        if ($needsReview->count() > 0) {
            $reviewTopics = $needsReview->pluck('topic_name')->map(function($topic) {
                return ucwords(str_replace('_', ' ', $topic));
            })->take(3)->join(', ');
            
            $recommendations[] = [
                'title' => '✅ Self-Assessment & Review',
                'description' => "These topics need review: {$reviewTopics}",
                'specific_actions' => [
                    'Take a practice quiz without notes',
                    'Identify knowledge gaps honestly',
                    'Review weak areas immediately',
                    'Re-test after 2 days to measure improvement',
                ],
            ];
        }
        
        return $recommendations;
    }

    /**
     * Suggest number of practice questions
     */
    private function suggestQuestionCount($weakTopicsCount): int
    {
        if ($weakTopicsCount >= 5) {
            return 25;
        } elseif ($weakTopicsCount >= 3) {
            return 15;
        } elseif ($weakTopicsCount >= 1) {
            return 10;
        } else {
            return 5; // Maintenance
        }
    }

    /**
     * Generate a personalized study schedule
     */
    private function generateStudySchedule($userId, $noteId)
    {
        $weakTopics = UserTopicPerformance::where('user_id', $userId)
            ->where('note_id', $noteId)
            ->whereIn('mastery_level', ['weak', 'developing'])
            ->orderBy('mastery_score', 'asc')
            ->get();
        
        if ($weakTopics->isEmpty()) {
            return [];
        }
        
        $schedule = [];
        $days = ['Day 1 (Today)', 'Day 2 (Tomorrow)', 'Day 3', 'Day 4', 'Day 5'];
        $activities = ['Review Fundamentals', 'Practice Problems', 'Deep Study Session', 'Mixed Practice', 'Comprehensive Review'];
        
        // Create schedule for up to 5 days
        foreach ($weakTopics->take(5) as $index => $topic) {
            $schedule[] = [
                'day' => $days[$index % 5],
                'focus_topic' => $topic->topic_name,
                'activity' => $activities[$index % 5],
                'duration' => $this->estimateStudyTime($topic),
                'tasks' => $this->getSpecificSteps($topic),
                'priority' => $this->calculatePriority($topic),
            ];
        }
        
        // If less than 5 topics, fill remaining days with review
        $topicCount = count($schedule);
        if ($topicCount < 5 && $topicCount > 0) {
            for ($i = $topicCount; $i < 5; $i++) {
                $schedule[] = [
                    'day' => $days[$i],
                    'focus_topic' => 'General Review',
                    'activity' => 'Review all topics covered',
                    'duration' => '30-45 minutes',
                    'tasks' => ['Review all previous topics', 'Take practice quiz', 'Identify any remaining gaps'],
                    'priority' => 'low',
                ];
            }
        }
        
        return $schedule;
    }

    /**
     * Get performance insights
     */
    private function getPerformanceInsights($userId, $attempt)
    {
        $allPerformance = UserTopicPerformance::where('user_id', $userId)
            ->where('note_id', $attempt->quiz->note_id)
            ->get();
            
        if ($allPerformance->isEmpty()) {
            return [
                'message' => 'Complete more quizzes to see detailed insights',
            ];
        }
        
        $insights = [];
        
        // Mastery distribution
        $masteryCount = $allPerformance->groupBy('mastery_level')->map->count();
        $insights['mastery_distribution'] = [
            'weak' => $masteryCount['weak'] ?? 0,
            'developing' => $masteryCount['developing'] ?? 0,
            'proficient' => $masteryCount['proficient'] ?? 0,
            'mastered' => $masteryCount['mastered'] ?? 0,
        ];
        
        // Strongest topics
        $insights['strongest_topics'] = $allPerformance
            ->sortByDesc('mastery_score')
            ->take(3)
            ->map(function($topic) {
                return [
                    'name' => $topic->topic_name,
                    'score' => round($topic->mastery_score, 1) . '%',
                ];
            })
            ->values()
            ->toArray();
            
        // Weakest topics
        $insights['weakest_topics'] = $allPerformance
            ->sortBy('mastery_score')
            ->take(3)
            ->map(function($topic) {
                return [
                    'name' => $topic->topic_name,
                    'score' => round($topic->mastery_score, 1) . '%',
                ];
            })
            ->values()
            ->toArray();
            
        // Overall progress
        $avgScore = $allPerformance->avg('mastery_score');
        $insights['overall_mastery'] = round($avgScore, 1);
        
        // Progress message
        if ($avgScore >= 75) {
            $insights['message'] = '🎉 Excellent progress! You\'re mastering this subject. Keep it up!';
            $insights['status'] = 'excellent';
        } elseif ($avgScore >= 50) {
            $insights['message'] = '📈 Good progress! You\'re on the right track. Focus on weak areas to reach mastery.';
            $insights['status'] = 'good';
        } else {
            $insights['message'] = '💪 Keep working! Every study session brings you closer to mastery. Don\'t give up!';
            $insights['status'] = 'needs_work';
        }
        
        // Improvement trend (if we have previous attempts)
        $previousAttempts = QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $attempt->quiz_id)
            ->where('id', '<', $attempt->id)
            ->orderBy('id', 'desc')
            ->first();
            
        if ($previousAttempts) {
            $improvement = $attempt->percentage - $previousAttempts->percentage;
            $insights['improvement'] = [
                'value' => round($improvement, 1),
                'message' => $improvement > 0 
                    ? "📈 You improved by {$improvement}% since last attempt!" 
                    : "Keep practicing! Review the weak topics to improve next time.",
            ];
        }
        
        return $insights;
    }
}
