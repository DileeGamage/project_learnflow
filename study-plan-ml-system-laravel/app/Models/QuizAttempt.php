<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'answers',
        'score',
        'total_questions',
        'percentage',
        'time_taken',
        'completed_at'
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'integer',
        'total_questions' => 'integer',
        'percentage' => 'decimal:2',
        'time_taken' => 'integer',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the quiz that this attempt belongs to
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the user who made this attempt
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the attempt is completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Get letter grade based on percentage
     */
    public function getGradeAttribute(): string
    {
        if ($this->percentage >= 90) return 'A';
        if ($this->percentage >= 80) return 'B';
        if ($this->percentage >= 70) return 'C';
        if ($this->percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Get grade color class
     */
    public function getGradeColorAttribute(): string
    {
        return match($this->grade) {
            'A' => 'text-success',
            'B' => 'text-info',
            'C' => 'text-warning',
            'D' => 'text-orange',
            'F' => 'text-danger',
            default => 'text-secondary'
        };
    }

    /**
     * Get formatted time taken
     */
    public function getFormattedTimeAttribute(): string
    {
        if ($this->time_taken < 60) {
            return $this->time_taken . ' sec';
        }
        
        $minutes = floor($this->time_taken / 60);
        $seconds = $this->time_taken % 60;
        
        if ($minutes < 60) {
            return $minutes . 'm ' . $seconds . 's';
        }
        
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        
        return $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
    }

    /**
     * Scope to get completed attempts only
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope to get attempts by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent attempts
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
