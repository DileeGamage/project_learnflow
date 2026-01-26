@extends('layouts.app')

@section('title', 'System Analytics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">System Analytics</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        <li class="breadcrumb-item active">Analytics</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Performance Analytics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-analytics fa-4x text-muted mb-3"></i>
                        <h5>System Analytics Coming Soon</h5>
                        <p class="text-muted">Detailed system performance metrics, user engagement analytics, and retention reports will be available here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
