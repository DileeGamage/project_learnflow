@extends('layouts.app')

@section('title', 'Daily Challenges')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-bullseye text-primary me-2"></i>
                Daily Challenges
            </h1>
            <div class="text-end">
                <span class="badge bg-primary">{{ now()->format('M j, Y') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- User Stats Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">{{ number_format($userStats['total_points']) }}</h4>
                <p class="mb-0">Total Points</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-danger">{{ $userStats['daily_streak'] }}</h4>
                <p class="mb-0">Day Streak</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">{{ $weeklyCompleted->count() }}</h4>
                <p class="mb-0">This Week</p>
            </div>
        </div>
    </div>
</div>

<!-- Today's Challenges -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-day text-primary me-2"></i>
                    Today's Challenges
                </h5>
            </div>
            <div class="card-body">
                @if($todaysChallenges->count() > 0)
                    @foreach($todaysChallenges as $challenge)
                    <div class="card mb-3 {{ $challenge->userProgress && $challenge->userProgress->completed ? 'border-success' : 'border-primary' }}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($challenge->userProgress && $challenge->userProgress->completed)
                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                            @else
                                            <i class="fas fa-bullseye fa-2x text-primary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $challenge->title }}</h6>
                                            <p class="mb-1 text-muted">{{ $challenge->description }}</p>
                                            
                                            @if($challenge->userProgress && $challenge->userProgress->completed)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Completed
                                            </span>
                                            @else
                                            <span class="badge bg-primary">
                                                <i class="fas fa-clock"></i> In Progress
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="mb-2">
                                        @if($challenge->userProgress && $challenge->userProgress->completed)
                                        <span class="badge bg-success text-white">
                                            <i class="fas fa-trophy"></i> +{{ $challenge->points_reward }} points
                                        </span>
                                        @else
                                        <span class="badge bg-outline-primary">
                                            <i class="fas fa-gem"></i> {{ $challenge->points_reward }} points
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Progress indicator -->
                                    @if($challenge->userProgress && !$challenge->userProgress->completed)
                                    <div class="progress" style="height: 8px;">
                                        @php
                                            $progress = 0;
                                            if ($challenge->challenge_type === 'quiz_count') {
                                                $completed = $challenge->userProgress->progress['completed_quizzes'] ?? 0;
                                                $target = $challenge->requirements['target_count'] ?? 1;
                                                $progress = min(($completed / $target) * 100, 100);
                                            }
                                        @endphp
                                        <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ round($progress) }}% Complete</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No challenges for today</h5>
                        <p class="text-muted">Check back tomorrow for new challenges!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- This Week's Completed Challenges -->
@if($weeklyCompleted->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-week text-success me-2"></i>
                    This Week's Completed Challenges
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($weeklyCompleted as $completed)
                    <div class="col-md-6 mb-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-trophy fa-2x text-success"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $completed->dailyChallenge->title }}</h6>
                                        <p class="mb-1 text-muted">{{ $completed->dailyChallenge->description }}</p>
                                        <small class="text-muted">
                                            Completed {{ $completed->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success">
                                            +{{ $completed->dailyChallenge->points_reward }} pts
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Challenge Tips -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="text-primary">💡 Challenge Tips</h6>
                <ul class="mb-0">
                    <li>Complete quizzes to progress on quiz-related challenges</li>
                    <li>Aim for high scores to unlock performance challenges</li>
                    <li>Maintain daily streaks for consistency rewards</li>
                    <li>New challenges are generated daily - check back tomorrow!</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Back to Dashboard -->
<div class="row mt-4">
    <div class="col-12 text-center">
        <a href="{{ route('gamification.dashboard') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

@endsection

@push('styles')
<style>
.progress {
    border-radius: 10px;
}

.card.border-success {
    border-width: 2px;
}

.card.border-primary {
    border-width: 2px;
}

.badge.bg-outline-primary {
    color: #0d6efd;
    border: 1px solid #0d6efd;
    background-color: transparent;
}
</style>
@endpush