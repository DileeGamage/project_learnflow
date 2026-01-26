<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChallengeProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'daily_challenge_id',
        'progress',
        'completed',
        'completed_at'
    ];

    protected $casts = [
        'progress' => 'array',
        'completed' => 'boolean',
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyChallenge()
    {
        return $this->belongsTo(DailyChallenge::class);
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->completed) {
            return 100;
        }

        $requirements = $this->dailyChallenge->requirements;
        $progress = $this->progress;

        // Calculate based on challenge type
        switch ($this->dailyChallenge->challenge_type) {
            case 'quiz_count':
                $target = $requirements['target_count'] ?? 1;
                $current = $progress['completed_quizzes'] ?? 0;
                return min(100, ($current / $target) * 100);

            case 'quiz_score':
                $target = $requirements['target_score'] ?? 80;
                $current = $progress['best_score'] ?? 0;
                return min(100, ($current / $target) * 100);

            case 'study_time':
                $target = $requirements['target_minutes'] ?? 60;
                $current = $progress['study_minutes'] ?? 0;
                return min(100, ($current / $target) * 100);

            case 'streak':
                $target = $requirements['target_days'] ?? 1;
                $current = $progress['current_streak'] ?? 0;
                return min(100, ($current / $target) * 100);

            default:
                return 0;
        }
    }

    /**
     * Update progress for this challenge
     */
    public function updateProgress(array $newProgress)
    {
        $currentProgress = $this->progress ?? [];
        $this->progress = array_merge($currentProgress, $newProgress);
        
        // Check if challenge is now completed
        if ($this->completion_percentage >= 100 && !$this->completed) {
            $this->completed = true;
            $this->completed_at = now();
        }
        
        $this->save();
        
        return $this->completed;
    }
}