@extends('layouts.app')

@section('title', 'Experiment Groups')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Research Experiment Groups</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        <li class="breadcrumb-item active">Experiments</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Experiment Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Control Group</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $experiments['control_group']->count() }}</div>
                            <small class="text-muted">Traditional Learning Approach</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Gamified Group</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $experiments['gamified_group']->count() }}</div>
                            <small class="text-muted">Enhanced Learning with Gamification</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gamepad fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unassigned</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $experiments['unassigned']->count() }}</div>
                            <small class="text-muted">Need Group Assignment</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Control Group -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Control Group (Traditional Learning)</h6>
                    <span class="badge badge-primary">{{ $experiments['control_group']->count() }} users</span>
                </div>
                <div class="card-body">
                    @if($experiments['control_group']->count() > 0)
                        <div class="row">
                            @foreach($experiments['control_group'] as $user)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-2">
                                                <div class="avatar-title bg-primary rounded-circle">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">{{ $user->email }}</small>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        Sessions: 0 | 
                                                        Quizzes: 0
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No users assigned to control group yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Gamified Group -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">Gamified Group (Enhanced Learning)</h6>
                    <span class="badge badge-success">{{ $experiments['gamified_group']->count() }} users</span>
                </div>
                <div class="card-body">
                    @if($experiments['gamified_group']->count() > 0)
                        <div class="row">
                            @foreach($experiments['gamified_group'] as $user)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-left-success">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-2">
                                                <div class="avatar-title bg-success rounded-circle">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">{{ $user->email }}</small>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        Sessions: 0 | 
                                                        Quizzes: 0 | 
                                                        Points: {{ $user->points ?? 0 }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No users assigned to gamified group yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Unassigned Users -->
    @if($experiments['unassigned']->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Unassigned Users</h6>
                    <span class="badge badge-warning">{{ $experiments['unassigned']->count() }} users</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($experiments['unassigned'] as $user)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-left-warning">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-2">
                                                <div class="avatar-title bg-warning rounded-circle">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    data-toggle="dropdown" title="Assign Group">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <form method="POST" action="{{ route('admin.experiments.assign', $user) }}" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="group" value="control">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-users mr-1 text-primary"></i>Control Group
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.experiments.assign', $user) }}" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="group" value="gamified">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-gamepad mr-1 text-success"></i>Gamified Group
                                                    </button>
                                                </form>
                                            </div>
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
</div>

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.avatar-sm {
    width: 32px;
    height: 32px;
}
.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    width: 100%;
    height: 100%;
}
</style>
@endpush
@endsection