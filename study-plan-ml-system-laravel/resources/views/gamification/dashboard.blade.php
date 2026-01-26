@extends('layouts.app')

@section('title', 'Gamification Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-gamepad text-primary me-2"></i>
                Your Learning Journey
            </h1>
            <div class="text-end">
                <small class="text-muted">Level {{ $userStats['current_level'] }}</small>
                <h5 class="mb-0 text-primary">{{ $userStats['level_title'] }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- User Stats Overview -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center">
                <i class="fas fa-gem fa-2x mb-2"></i>
                <h4 class="card-title">{{ number_format($userStats['total_points']) }}</h4>
                <p class="card-text">Total Points</p>
                @if($userStats['todays_points'] > 0)
                <small class="badge bg-light text-primary">+{{ $userStats['todays_points'] }} today</small>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center">
                <i class="fas fa-level-up-alt fa-2x mb-2"></i>
                <h4 class="card-title">{{ $userStats['current_level'] }}</h4>
                <p class="card-text">Current Level</p>
                <small>{{ $userStats['points_to_next_level'] }} points to next level</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body text-center">
                <i class="fas fa-fire fa-2x mb-2"></i>
                <h4 class="card-title">{{ $userStats['daily_streak'] }}</h4>
                <p class="card-text">Day Streak</p>
                @if($userStats['daily_streak'] >= 7)
                <small class="badge bg-light text-danger">🔥 On Fire!</small>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body text-center">
                <i class="fas fa-crown fa-2x mb-2"></i>
                <h4 class="card-title">#{{ $userStats['rank'] }}</h4>
                <p class="card-text">Global Rank</p>
                <small>out of {{ number_format($userStats['total_users']) }} learners</small>
            </div>
        </div>
    </div>
</div>

<!-- Level Progress -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Level Progress
                </h5>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-gradient" 
                                 role="progressbar" 
                                 style="width: {{ $userStats['level_progress'] }}%">
                                {{ round($userStats['level_progress']) }}%
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small>Level {{ $userStats['current_level'] }}</small>
                            <small>{{ $userStats['points_to_next_level'] }} points to next level</small>
                            <small>Level {{ $userStats['current_level'] + 1 }}</small>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        @if($userStats['has_leveled_up_today'])
                        <span class="badge bg-warning text-dark p-2">
                            <i class="fas fa-star"></i> Leveled Up Today!
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Challenges and Recent Achievements -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bullseye text-primary me-2"></i>
                    Today's Challenges
                </h5>
            </div>
            <div class="card-body">
                @if($todaysChallenges->count() > 0)
                    @foreach($todaysChallenges as $challenge)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                        <div>
                            <h6 class="mb-1">{{ $challenge->title }}</h6>
                            <p class="mb-1 text-muted">{{ $challenge->description }}</p>
                            @if($challenge->userProgress && $challenge->userProgress->completed)
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Completed
                            </span>
                            @else
                            <small class="text-muted">Reward: {{ $challenge->points_reward }} points</small>
                            @endif
                        </div>
                        <div class="text-end">
                            @if($challenge->userProgress && $challenge->userProgress->completed)
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                            @else
                            <i class="fas fa-clock fa-2x text-muted"></i>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-4">No challenges available today.</p>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('gamification.challenges') }}" class="btn btn-outline-primary">
                        View All Challenges
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy text-warning me-2"></i>
                    Recent Achievements
                </h5>
            </div>
            <div class="card-body">
                @if($recentAchievements->count() > 0)
                    @foreach($recentAchievements as $achievement)
                    <div class="d-flex align-items-center mb-3 p-3 border rounded">
                        <div class="me-3">
                            @if($achievement->icon)
                            <i class="fas fa-{{ $achievement->icon }} fa-2x text-warning"></i>
                            @else
                            <i class="fas fa-trophy fa-2x text-warning"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $achievement->name }}</h6>
                            <p class="mb-1 text-muted">{{ $achievement->description }}</p>
                            <small class="text-muted">
                                {{ $achievement->pivot && $achievement->pivot->created_at ? $achievement->pivot->created_at->diffForHumans() : 'Recently unlocked' }}
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark">
                                +{{ $achievement->points_reward }} pts
                            </span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-4">No achievements earned yet. Keep learning!</p>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('gamification.achievements') }}" class="btn btn-outline-warning">
                        View All Achievements
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaderboard Preview -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-crown text-primary me-2"></i>
                    Leaderboard
                </h5>
            </div>
            <div class="card-body">
                @if(count($leaderboard) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Level</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($leaderboard, 0, 5) as $entry)
                            <tr class="{{ $entry['user']->id === auth()->id() ? 'table-warning' : '' }}">
                                <td>
                                    @if($entry['rank'] <= 3)
                                        @if($entry['rank'] == 1)
                                        <i class="fas fa-crown text-warning"></i>
                                        @elseif($entry['rank'] == 2)
                                        <i class="fas fa-medal text-secondary"></i>
                                        @else
                                        <i class="fas fa-award text-info"></i>
                                        @endif
                                    @endif
                                    #{{ $entry['rank'] }}
                                </td>
                                <td>
                                    <strong>{{ $entry['user']->name }}</strong>
                                    @if($entry['user']->id === auth()->id())
                                    <span class="badge bg-primary">You</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $entry['level_color'] ?? '#6c757d' }}">
                                        Lvl {{ $entry['level'] }}
                                    </span>
                                </td>
                                <td><strong>{{ number_format($entry['points']) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-muted text-center py-4">No leaderboard data available.</p>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('gamification.leaderboard') }}" class="btn btn-outline-primary">
                        View Full Leaderboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add some animation to cards on load
    $('.card').each(function(index) {
        $(this).delay(index * 100).fadeIn();
    });
    
    // Refresh gamification widget
    if (typeof window.refreshGamificationWidget === 'function') {
        window.refreshGamificationWidget();
    }
});
</script>
@endpush