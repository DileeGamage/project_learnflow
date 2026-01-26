<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_admin',
        'experiment_group',
        'last_login_at',
        'points',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get all notes belonging to this user
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get all quiz attempts by this user
     */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get all quizzes created by this user
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Get all study sessions by this user
     */
    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    /**
     * Get user progress records
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    /**
     * Get questionnaire results for this user
     */
    public function questionnaireResults(): HasMany
    {
        return $this->hasMany(UserQuestionnaireResult::class);
    }

    /**
     * Get user's favorite notes
     */
    public function favoriteNotes()
    {
        return $this->notes()->where('is_favorite', true);
    }

    /**
     * Get user's average quiz score
     */
    public function getAverageScoreAttribute()
    {
        return $this->quizAttempts()->avg('score') ?: 0;
    }

    /**
     * Get user's total study time (in minutes)
     */
    public function getTotalStudyTimeAttribute()
    {
        return $this->quizAttempts()->sum('time_taken') ?: 0;
    }

    /**
     * Get user's best subject based on quiz performance
     */
    public function getBestSubjectAttribute()
    {
        return $this->quizAttempts()
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->join('notes', 'quizzes.note_id', '=', 'notes.id')
            ->select('notes.subject_area')
            ->selectRaw('AVG(quiz_attempts.score) as avg_score')
            ->groupBy('notes.subject_area')
            ->orderBy('avg_score', 'desc')
            ->first()
            ?->subject_area ?? 'No data';
    }

    /**
     * Get user's gamification points record
     */
    public function userPoints()
    {
        return $this->hasOne(UserPoints::class);
    }

    /**
     * Get user's point transactions
     */
    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * Get user's achievements
     */
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
                    ->withTimestamps()
                    ->withPivot('unlocked_at');
    }

    /**
     * Get user's challenge progress
     */
    public function challengeProgress(): HasMany
    {
        return $this->hasMany(UserChallengeProgress::class);
    }
}
