@extends('layouts.app')

@section('title', 'Leaderboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-crown text-warning me-2"></i>
                Global Leaderboard
            </h1>
            <div class="text-end">
                <span class="badge bg-info">Your Rank: #{{ $userStats['rank'] }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Your Stats -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Your Performance</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="text-primary">{{ number_format($userStats['total_points']) }}</h4>
                        <p class="mb-0">Total Points</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-success">{{ $userStats['current_level'] }}</h4>
                        <p class="mb-0">Level</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-danger">{{ $userStats['daily_streak'] }}</h4>
                        <p class="mb-0">Day Streak</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info">#{{ $userStats['rank'] }}</h4>
                        <p class="mb-0">Global Rank</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaderboard -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list-ol"></i> Top Learners
                </h5>
            </div>
            <div class="card-body">
                @if(count($leaderboard) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Rank</th>
                                <th>Learner</th>
                                <th>Level</th>
                                <th>Points</th>
                                <th>Title</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaderboard as $entry)
                            <tr class="{{ $entry['user']->id === auth()->id() ? 'table-warning' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($entry['rank'] <= 3)
                                            @if($entry['rank'] == 1)
                                            <i class="fas fa-crown text-warning me-2"></i>
                                            @elseif($entry['rank'] == 2)
                                            <i class="fas fa-medal text-secondary me-2"></i>
                                            @else
                                            <i class="fas fa-award text-warning me-2"></i>
                                            @endif
                                        @endif
                                        <strong>#{{ $entry['rank'] }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $entry['user']->name }}</strong>
                                            @if($entry['user']->id === auth()->id())
                                            <span class="badge bg-primary ms-1">You</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-lg" style="background-color: {{ $entry['level_color'] ?? '#6c757d' }}; color: white;">
                                        Level {{ $entry['level'] }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-primary">{{ number_format($entry['points']) }}</strong>
                                </td>
                                <td>
                                    <em class="text-muted">{{ $entry['level_title'] ?? 'Learner' }}</em>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Total Users Info -->
                <div class="text-center mt-3">
                    <p class="text-muted">
                        Showing top {{ count($leaderboard) }} of {{ number_format($userStats['total_users']) }} total learners
                    </p>
                </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No leaderboard data available</h5>
                        <p class="text-muted">Complete some quizzes to join the leaderboard!</p>
                    </div>
                @endif
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
.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.2) !important;
}

.card.border-primary {
    border-width: 2px;
}

.table th {
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}
</style>
@endpush