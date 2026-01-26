<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'points_earned',
        'description',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the icon for this activity type
     */
    public function getActivityIconAttribute()
    {
        $icons = [
            'quiz_completed' => 'fas fa-clipboard-check',
            'quiz_perfect_score' => 'fas fa-star',
            'quiz_high_score' => 'fas fa-medal',
            'daily_streak' => 'fas fa-fire',
            'weekly_streak' => 'fas fa-calendar-week',
            'level_up' => 'fas fa-arrow-up',
            'achievement_unlocked' => 'fas fa-trophy',
            'habits_questionnaire' => 'fas fa-brain',
            'note_created' => 'fas fa-sticky-note',
            'first_quiz_day' => 'fas fa-flag'
        ];

        return $icons[$this->activity_type] ?? 'fas fa-plus';
    }

    /**
     * Get the color for this activity type
     */
    public function getActivityColorAttribute()
    {
        $colors = [
            'quiz_completed' => 'success',
            'quiz_perfect_score' => 'warning',
            'quiz_high_score' => 'info',
            'daily_streak' => 'danger',
            'weekly_streak' => 'primary',
            'level_up' => 'purple',
            'achievement_unlocked' => 'warning',
            'habits_questionnaire' => 'info',
            'note_created' => 'secondary',
            'first_quiz_day' => 'success'
        ];

        return $colors[$this->activity_type] ?? 'primary';
    }

    /**
     * Scope for recent transactions
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific activity types
     */
    public function scopeActivityType($query, $type)
    {
        return $query->where('activity_type', $type);
    }
}