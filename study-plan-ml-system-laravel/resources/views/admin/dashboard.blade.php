@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Admin</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_users_today'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Quizzes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_quizzes'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Session (min)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['avg_session_time'], 1) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Experiment Groups Overview -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Research Experiment Groups</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h4 class="text-primary">{{ $stats['experiment_groups']['control'] }}</h4>
                            <p class="mb-0">Control Group</p>
                            <small class="text-muted">Traditional Learning</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-success">{{ $stats['experiment_groups']['gamified'] }}</h4>
                            <p class="mb-0">Gamified Group</p>
                            <small class="text-muted">Enhanced Learning</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-warning">{{ $stats['experiment_groups']['unassigned'] }}</h4>
                            <p class="mb-0">Unassigned</p>
                            <small class="text-muted">Need Assignment</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.users') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-users mr-2"></i>Manage Users
                        </a>
                        <a href="{{ route('admin.experiments') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-flask mr-2"></i>Experiment Groups
                        </a>
                        <a href="{{ route('admin.research') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-line mr-2"></i>Research Analytics
                        </a>
                        <a href="{{ route('admin.analytics') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-analytics mr-2"></i>System Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Active Users (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    @if($recent_activity->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Experiment Group</th>
                                        <th>Sessions This Week</th>
                                        <th>Last Activity</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_activity as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->experiment_group)
                                                <span class="badge badge-{{ $user->experiment_group === 'control' ? 'primary' : 'success' }}">
                                                    {{ ucfirst($user->experiment_group) }}
                                                </span>
                                            @else
                                                <span class="badge badge-warning">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>0</td>
                                        <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No recent activity found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush
@endsection
