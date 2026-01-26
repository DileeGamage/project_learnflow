@extends('layouts.app')

@section('title', 'Research Analytics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Research Analytics</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        <li class="breadcrumb-item active">Research</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Research Analytics Dashboard</h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                        <h5>Research Analytics Coming Soon</h5>
                        <p class="text-muted">Advanced research analytics and statistical comparisons will be available here once more data is collected.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection