<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_id',
        'unlocked_at'
    ];

    protected $casts = [
        'unlocked_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }

    /**
     * Check if this achievement was unlocked recently (within 24 hours)
     */
    public function getIsNewAttribute()
    {
        return $this->unlocked_at >= now()->subDay();
    }

    /**
     * Get time since unlock in human readable format
     */
    public function getTimeAgoAttribute()
    {
        return $this->unlocked_at->diffForHumans();
    }

    /**
     * Scope for recent achievements
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('unlocked_at', '>=', now()->subDays($days));
    }
}