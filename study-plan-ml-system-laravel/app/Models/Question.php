<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'question_number',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'topic',
        'difficulty',
        'metadata'
    ];

    protected $casts = [
        'options' => 'array',
        'metadata' => 'array',
        'points' => 'integer',
        'question_number' => 'integer'
    ];

    /**
     * Get the quiz that owns this question
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
