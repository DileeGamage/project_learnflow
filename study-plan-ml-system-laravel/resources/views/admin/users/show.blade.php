@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">User Details: {{ $user->name }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Users</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- User Information -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-primary rounded-circle">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        </div>
                        <h5>{{ $user->name }}</h5>
                        <p class="text-muted">{{ $user->email }}</p>
                        @if($user->is_admin)
                            <span class="badge badge-danger">Administrator</span>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-6 text-center">
                            <strong>{{ $user->points ?? 0 }}</strong>
                            <br><small class="text-muted">Points</small>
                        </div>
                        <div class="col-6 text-center">
                            @if($user->experiment_group)
                                <span class="badge badge-{{ $user->experiment_group === 'control' ? 'primary' : 'success' }}">
                                    {{ ucfirst($user->experiment_group) }}
                                </span>
                            @else
                                <span class="badge badge-warning">Unassigned</span>
                            @endif
                            <br><small class="text-muted">Experiment Group</small>
                        </div>
                    </div>

                    <hr>

                    <p><strong>Role:</strong> {{ $user->role ?? 'Not set' }}</p>
                    <p><strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}</p>
                    <p><strong>Last Login:</strong> {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</p>

                    @if(!$user->experiment_group)
                        <hr>
                        <h6>Assign to Experiment Group</h6>
                        <div class="btn-group w-100" role="group">
                            <form method="POST" action="{{ route('admin.experiments.assign', $user) }}" class="w-50">
                                @csrf
                                <input type="hidden" name="group" value="control">
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                    Control Group
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.experiments.assign', $user) }}" class="w-50">
                                @csrf
                                <input type="hidden" name="group" value="gamified">
                                <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                    Gamified Group
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Research Metrics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Research Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-primary">{{ number_format($metrics['total_study_time'], 0) }}</h4>
                            <p class="mb-0">Minutes Studied</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success">{{ number_format($metrics['avg_quiz_score'], 1) }}%</h4>
                            <p class="mb-0">Avg Quiz Score</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">{{ number_format($metrics['quiz_improvement'], 1) }}%</h4>
                            <p class="mb-0">Score Improvement</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">{{ $metrics['learning_streak'] }}</h4>
                            <p class="mb-0">Day Streak</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Engagement Score:</strong> {{ number_format($metrics['engagement_score'], 0) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Activities:</strong> 0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Overview -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-center shadow">
                        <div class="card-body">
                            <i class="fas fa-sticky-note fa-2x text-primary mb-2"></i>
                            <h4>0</h4>
                            <p class="mb-0">Notes Created</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center shadow">
                        <div class="card-body">
                            <i class="fas fa-question-circle fa-2x text-success mb-2"></i>
                            <h4>0</h4>
                            <p class="mb-0">Quizzes Taken</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center shadow">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x text-info mb-2"></i>
                            <h4>0</h4>
                            <p class="mb-0">Study Sessions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Study Sessions</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center">No study sessions recorded.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Quiz Results</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center">No quizzes taken yet.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar-lg {
    width: 80px;
    height: 80px;
}
.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 600;
    width: 100%;
    height: 100%;
}
</style>
@endpush
@endsection