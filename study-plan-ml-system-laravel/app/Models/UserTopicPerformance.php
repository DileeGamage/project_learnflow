<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTopicPerformance extends Model
{
    protected $table = 'user_topic_performance';
    
    protected $fillable = [
        'user_id',
        'note_id',
        'topic_name',
        'questions_attempted',
        'questions_correct',
        'mastery_score',
        'mastery_level',
        'last_practiced_at',
        'consecutive_correct'
    ];

    protected $casts = [
        'last_practiced_at' => 'datetime',
        'mastery_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Update performance after quiz attempt
     */
    public function updatePerformance(bool $correct)
    {
        $this->questions_attempted++;
        
        if ($correct) {
            $this->questions_correct++;
            $this->consecutive_correct++;
        } else {
            $this->consecutive_correct = 0;
        }
        
        // Calculate mastery score (percentage)
        $this->mastery_score = ($this->questions_correct / $this->questions_attempted) * 100;
        
        // Determine mastery level
        $this->mastery_level = $this->calculateMasteryLevel();
        
        $this->last_practiced_at = now();
        $this->save();
    }

    /**
     * Calculate mastery level based on score and consistency
     */
    private function calculateMasteryLevel(): string
    {
        $score = $this->mastery_score;
        $consecutive = $this->consecutive_correct;
        
        if ($score >= 90 && $consecutive >= 5) {
            return 'mastered';
        } elseif ($score >= 75 && $consecutive >= 3) {
            return 'proficient';
        } elseif ($score >= 50) {
            return 'developing';
        } else {
            return 'weak';
        }
    }

    /**
     * Get weak topics for a user
     */
    public static function getWeakTopics($userId, $noteId = null)
    {
        $query = self::where('user_id', $userId)
            ->where('mastery_level', 'weak')
            ->orderBy('mastery_score', 'asc');
            
        if ($noteId) {
            $query->where('note_id', $noteId);
        }
        
        return $query->get();
    }

    /**
     * Get topics needing review (not practiced recently)
     */
    public static function getTopicsNeedingReview($userId, $days = 7)
    {
        return self::where('user_id', $userId)
            ->where('mastery_level', '!=', 'mastered')
            ->where(function($query) use ($days) {
                $query->whereNull('last_practiced_at')
                    ->orWhere('last_practiced_at', '<', now()->subDays($days));
            })
            ->orderBy('mastery_score', 'asc')
            ->get();
    }

    /**
     * Get all topics for a note
     */
    public static function getTopicsByNote($userId, $noteId)
    {
        return self::where('user_id', $userId)
            ->where('note_id', $noteId)
            ->orderBy('mastery_score', 'asc')
            ->get();
    }

    /**
     * Get mastery distribution for a user
     */
    public static function getMasteryDistribution($userId, $noteId = null)
    {
        $query = self::where('user_id', $userId);
        
        if ($noteId) {
            $query->where('note_id', $noteId);
        }
        
        return $query->selectRaw('mastery_level, COUNT(*) as count')
            ->groupBy('mastery_level')
            ->pluck('count', 'mastery_level')
            ->toArray();
    }
}
