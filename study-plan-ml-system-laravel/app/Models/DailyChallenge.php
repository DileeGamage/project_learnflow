<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'challenge_type',
        'requirements',
        'points_reward',
        'challenge_date',
        'is_active'
    ];

    protected $casts = [
        'requirements' => 'array',
        'challenge_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function userProgress()
    {
        return $this->hasMany(UserChallengeProgress::class);
    }

    /**
     * Get progress for a specific user
     */
    public function getUserProgress($userId)
    {
        return $this->userProgress()->where('user_id', $userId)->first();
    }

    /**
     * Get challenge icon based on type
     */
    public function getChallengeIconAttribute()
    {
        $icons = [
            'quiz_score' => 'fas fa-target',
            'quiz_count' => 'fas fa-list-ol',
            'study_time' => 'fas fa-clock',
            'streak' => 'fas fa-fire',
            'perfect_score' => 'fas fa-star'
        ];

        return $icons[$this->challenge_type] ?? 'fas fa-trophy';
    }

    /**
     * Get difficulty level based on requirements
     */
    public function getDifficultyLevelAttribute()
    {
        $pointsReward = $this->points_reward;
        
        if ($pointsReward >= 100) return 'Hard';
        if ($pointsReward >= 50) return 'Medium';
        return 'Easy';
    }

    /**
     * Get difficulty color
     */
    public function getDifficultyColorAttribute()
    {
        switch ($this->difficulty_level) {
            case 'Hard': return 'danger';
            case 'Medium': return 'warning';
            default: return 'success';
        }
    }

    /**
     * Check if user has completed this challenge
     */
    public function isCompletedBy(User $user)
    {
        return $this->userProgress()
            ->where('user_id', $user->id)
            ->where('completed', true)
            ->exists();
    }

    /**
     * Get user's progress on this challenge
     */
    public function getProgressFor(User $user)
    {
        return $this->userProgress()
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Scope for today's challenges
     */
    public function scopeToday($query)
    {
        return $query->where('challenge_date', today());
    }

    /**
     * Scope for active challenges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific challenge type
     */
    public function scopeType($query, $type)
    {
        return $query->where('challenge_type', $type);
    }
}