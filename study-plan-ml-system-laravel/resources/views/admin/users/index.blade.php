@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">User Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.users') }}" class="row align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Users</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name or email...">
                        </div>
                        <div class="col-md-3">
                            <label for="experiment_group" class="form-label">Experiment Group</label>
                            <select class="form-control" id="experiment_group" name="experiment_group">
                                <option value="">All Groups</option>
                                <option value="control" {{ request('experiment_group') === 'control' ? 'selected' : '' }}>Control</option>
                                <option value="gamified" {{ request('experiment_group') === 'gamified' ? 'selected' : '' }}>Gamified</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Users ({{ $users->total() }} total)
                    </h6>
                </div>
                <div class="card-body">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Experiment Group</th>
                                        <th>Notes</th>
                                        <th>Quizzes</th>
                                        <th>Sessions</th>
                                        <th>Points</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm mr-2">
                                                    <div class="avatar-title bg-primary rounded-circle">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    @if($user->is_admin)
                                                        <span class="badge badge-danger ml-1">Admin</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role)
                                                <span class="badge badge-info">{{ ucfirst($user->role) }}</span>
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->experiment_group)
                                                <span class="badge badge-{{ $user->experiment_group === 'control' ? 'primary' : 'success' }}">
                                                    {{ ucfirst($user->experiment_group) }}
                                                </span>
                                            @else
                                                <span class="badge badge-warning">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light">0</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light">0</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light">0</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">{{ $user->points ?? 0 }}</span>
                                        </td>
                                        <td>
                                            @if($user->last_login_at)
                                                <small>{{ $user->last_login_at->diffForHumans() }}</small>
                                            @else
                                                <small class="text-muted">Never</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.users.show', $user) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(!$user->experiment_group)
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                data-toggle="dropdown" title="Assign Group">
                                                            <i class="fas fa-flask"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <form method="POST" action="{{ route('admin.experiments.assign', $user) }}" style="display: inline;">
                                                                @csrf
                                                                <input type="hidden" name="group" value="control">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-users mr-1"></i>Control Group
                                                                </button>
                                                            </form>
                                                            <form method="POST" action="{{ route('admin.experiments.assign', $user) }}" style="display: inline;">
                                                                @csrf
                                                                <input type="hidden" name="group" value="gamified">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-gamepad mr-1"></i>Gamified Group
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No users found matching your criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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