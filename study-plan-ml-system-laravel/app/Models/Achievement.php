<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'category',
        'points_reward',
        'criteria',
        'is_active',
        'rarity_level'
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean'
    ];

    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    /**
     * Get rarity label
     */
    public function getRarityLabelAttribute()
    {
        $rarities = [
            1 => 'Common',
            2 => 'Rare',
            3 => 'Epic',
            4 => 'Legendary'
        ];

        return $rarities[$this->rarity_level] ?? 'Common';
    }

    /**
     * Get rarity color
     */
    public function getRarityColorAttribute()
    {
        $colors = [
            1 => '#95a5a6', // Gray
            2 => '#3498db', // Blue
            3 => '#9b59b6', // Purple
            4 => '#f39c12'  // Gold
        ];

        return $colors[$this->rarity_level] ?? '#95a5a6';
    }

    /**
     * Get category icon
     */
    public function getCategoryIconAttribute()
    {
        $icons = [
            'getting_started' => 'fas fa-play',
            'performance' => 'fas fa-chart-line',
            'consistency' => 'fas fa-calendar-check',
            'milestones' => 'fas fa-mountain',
            'levels' => 'fas fa-layer-group',
            'exploration' => 'fas fa-compass',
            'social' => 'fas fa-users'
        ];

        return $icons[$this->category] ?? 'fas fa-award';
    }

    /**
     * Check if user has unlocked this achievement
     */
    public function isUnlockedBy(User $user)
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get completion percentage for this achievement by user
     */
    public function getCompletionPercentage(User $user)
    {
        if ($this->isUnlockedBy($user)) {
            return 100;
        }

        // Calculate progress based on criteria
        $totalCriteria = count($this->criteria);
        $metCriteria = 0;

        foreach ($this->criteria as $criterion) {
            if ($this->evaluateSingleCriterion($user, $criterion)) {
                $metCriteria++;
            }
        }

        return $totalCriteria > 0 ? ($metCriteria / $totalCriteria) * 100 : 0;
    }

    /**
     * Evaluate a single criterion for a user
     */
    private function evaluateSingleCriterion(User $user, array $criterion)
    {
        $type = $criterion['type'];
        $value = $criterion['value'];

        switch ($type) {
            case 'total_quizzes':
                return $user->quizAttempts()->count() >= $value;

            case 'perfect_scores':
                return $user->quizAttempts()->where('percentage', 100)->count() >= $value;

            case 'daily_streak':
                $userPoints = $user->userPoints;
                return $userPoints && $userPoints->daily_streak >= $value;

            case 'total_points':
                $userPoints = $user->userPoints;
                return $userPoints && $userPoints->total_points >= $value;

            case 'level_reached':
                $userPoints = $user->userPoints;
                return $userPoints && $userPoints->current_level >= $value;

            case 'habits_completed':
                return $user->questionnaireResults()->count() >= $value;

            default:
                return false;
        }
    }

    /**
     * Scope for active achievements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for specific rarity
     */
    public function scopeRarity($query, $rarity)
    {
        return $query->where('rarity_level', $rarity);
    }
}