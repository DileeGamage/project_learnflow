@extends('layouts.app')

@section('title', 'Achievements')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-trophy text-warning me-2"></i>
                Achievements
            </h1>
            <div class="text-end">
                <span class="badge bg-warning text-dark">{{ $userStats['achievements_count'] }} Unlocked</span>
            </div>
        </div>
    </div>
</div>

<!-- User Stats Summary -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-primary">{{ number_format($userStats['total_points']) }}</h4>
                <p class="mb-0">Total Points</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-success">Level {{ $userStats['current_level'] }}</h4>
                <p class="mb-0">{{ $userStats['level_title'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Achievements by Category -->
@if(count($achievements) > 0)
    @foreach($achievements as $category => $categoryAchievements)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        @switch($category)
                            @case('milestones')
                                <i class="fas fa-flag text-primary"></i> Milestones
                                @break
                            @case('performance')
                                <i class="fas fa-chart-line text-success"></i> Performance
                                @break
                            @case('consistency')
                                <i class="fas fa-fire text-danger"></i> Consistency
                                @break
                            @case('exploration')
                                <i class="fas fa-compass text-info"></i> Exploration
                                @break
                            @default
                                <i class="fas fa-trophy text-warning"></i> {{ ucfirst($category) }}
                        @endswitch
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($categoryAchievements as $achievement)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 {{ $achievement->is_unlocked ? 'border-warning' : 'border-secondary' }}">
                                <div class="card-body text-center">
                                    <!-- Achievement Icon -->
                                    <div class="mb-3">
                                        @if($achievement->is_unlocked)
                                            <i class="fas fa-{{ $achievement->icon ?? 'trophy' }} fa-3x text-warning"></i>
                                        @else
                                            <i class="fas fa-{{ $achievement->icon ?? 'trophy' }} fa-3x text-muted"></i>
                                        @endif
                                    </div>
                                    
                                    <!-- Achievement Name -->
                                    <h6 class="card-title {{ $achievement->is_unlocked ? 'text-warning' : 'text-muted' }}">
                                        {{ $achievement->name }}
                                        @if($achievement->is_unlocked)
                                        <i class="fas fa-check-circle text-success ms-1"></i>
                                        @endif
                                    </h6>
                                    
                                    <!-- Achievement Description -->
                                    <p class="card-text small {{ $achievement->is_unlocked ? '' : 'text-muted' }}">
                                        {{ $achievement->description }}
                                    </p>
                                    
                                    <!-- Points and Rarity -->
                                    <div class="mt-auto">
                                        <span class="badge {{ $achievement->is_unlocked ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                            {{ $achievement->points_reward }} points
                                        </span>
                                        
                                        @switch($achievement->rarity_level)
                                            @case(1)
                                                <span class="badge bg-light text-dark">Common</span>
                                                @break
                                            @case(2)
                                                <span class="badge bg-primary">Rare</span>
                                                @break
                                            @case(3)
                                                <span class="badge bg-purple text-white">Epic</span>
                                                @break
                                            @case(4)
                                                <span class="badge bg-gradient" style="background: linear-gradient(45deg, #ff6b35, #f7931e);">Legendary</span>
                                                @break
                                        @endswitch
                                        
                                        @if($achievement->is_unlocked && isset($achievement->unlocked_at))
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                Unlocked {{ $achievement->unlocked_at ? $achievement->unlocked_at->diffForHumans() : 'recently' }}
                                            </small>
                                        </div>
                                        @endif
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
    @endforeach
@else
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-trophy fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No achievements available</h5>
                    <p class="text-muted">Check back later for new achievements to unlock!</p>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Back to Dashboard -->
<div class="row">
    <div class="col-12 text-center">
        <a href="{{ route('gamification.dashboard') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

@endsection

@push('styles')
<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
.card.border-warning {
    box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
}
</style>
@endpush