<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoints extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_points',
        'current_level',
        'points_in_level',
        'daily_streak',
        'weekly_streak',
        'last_activity_date'
    ];

    protected $casts = [
        'last_activity_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(PointTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Calculate points needed for next level
     */
    public function getPointsToNextLevelAttribute()
    {
        return $this->getPointsRequiredForLevel($this->current_level + 1) - $this->points_in_level;
    }

    /**
     * Get points required for a specific level
     */
    public function getPointsRequiredForLevel($level)
    {
        // Exponential progression: Level 1 = 100 points, Level 2 = 250 points, etc.
        return intval(100 * pow($level, 1.5));
    }

    /**
     * Get level name/title
     */
    public function getLevelTitleAttribute()
    {
        $titles = [
            1 => 'Novice Learner',
            2 => 'Curious Student', 
            3 => 'Dedicated Scholar',
            4 => 'Knowledge Seeker',
            5 => 'Bright Mind',
            6 => 'Academic Star',
            7 => 'Learning Champion',
            8 => 'Master Student',
            9 => 'Learning Legend',
            10 => 'Study Guru',
            15 => 'Grandmaster Scholar',
            20 => 'Ultimate Learner'
        ];

        // Find the highest level title that applies
        $applicableLevels = array_filter(array_keys($titles), function($level) {
            return $level <= $this->current_level;
        });

        $highestLevel = max($applicableLevels);
        return $titles[$highestLevel] ?? 'Legendary Scholar';
    }

    /**
     * Get level progress percentage
     */
    public function getLevelProgressAttribute()
    {
        $pointsRequired = $this->getPointsRequiredForLevel($this->current_level + 1);
        return $pointsRequired > 0 ? ($this->points_in_level / $pointsRequired) * 100 : 0;
    }

    /**
     * Get level color based on current level
     */
    public function getLevelColorAttribute()
    {
        if ($this->current_level >= 20) return '#ff6b35'; // Orange for ultimate
        if ($this->current_level >= 15) return '#8e44ad'; // Purple for grandmaster
        if ($this->current_level >= 10) return '#e74c3c'; // Red for guru
        if ($this->current_level >= 7) return '#f39c12';  // Yellow for champion
        if ($this->current_level >= 5) return '#3498db';  // Blue for bright mind
        if ($this->current_level >= 3) return '#2ecc71';  // Green for scholar
        return '#95a5a6'; // Gray for beginners
    }

    /**
     * Check if user leveled up today
     */
    public function hasLeveledUpToday()
    {
        return $this->transactions()
            ->where('activity_type', 'level_up')
            ->whereDate('created_at', today())
            ->exists();
    }
}