@extends('layouts.app')

@section('content')
<!-- Page Title Section -->
<div class="page-title">
    <h1><i class="fas fa-file-text"></i> Study Materials Management</h1>
    <p class="subtitle">Manage and organize your study material library</p>
    <div class="actions">
        <a href="{{ route('notes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Standard Document
        </a>
        <a href="{{ route('notes.create') }}" class="btn btn-outline-primary">
            <i class="fas fa-upload"></i> Alternative Upload
        </a>
    </div>
</div>

<!-- Stats Overview -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-file-alt text-primary"></i>
                    </div>
                    <div class="stats-number">96</div>
                    <p class="stats-label">TOTAL DOCUMENTS</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stats-number">96</div>
                    <p class="stats-label">ACTIVE DOCUMENTS</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-times-circle text-warning"></i>
                    </div>
                    <div class="stats-number">0</div>
                    <p class="stats-label">INACTIVE DOCUMENTS</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i class="fas fa-map-marker-alt text-info"></i>
                    </div>
                    <div class="stats-number">3</div>
                    <p class="stats-label">JURISDICTIONS</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-title">
            <i class="fas fa-filter"></i> Filter Documents
        </div>
        <div class="row">
            <div class="col-md-3">
                <select class="form-control">
                    <option>All Categories</option>
                    <option>Study Notes</option>
                    <option>Research Papers</option>
                    <option>Assignments</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control">
                    <option>All Subjects</option>
                    <option>Mathematics</option>
                    <option>Science</option>
                    <option>English</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Search documents...">
            </div>
        </div>
    </div>

    <!-- Quick Analytics Access -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Learning Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Track Your Learning Progress</h6>
                            <p class="text-muted mb-3">
                                Get comprehensive insights into your quiz performance, study patterns, and learning effectiveness through advanced visual analytics.
                            </p>
                            <div class="row">
                                <div class="col-sm-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Performance trends over time</li>
                                        <li><i class="fas fa-check text-success"></i> Difficulty-based accuracy analysis</li>
                                        <li><i class="fas fa-check text-success"></i> Study time correlation</li>
                                    </ul>
                                </div>
                                <div class="col-sm-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Answer pattern insights</li>
                                        <li><i class="fas fa-check text-success"></i> Knowledge retention tracking</li>
                                        <li><i class="fas fa-check text-success"></i> Self-assessment correlation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <a href="{{ route('analytics.quiz') }}" class="btn btn-primary btn-lg mb-2">
                                <i class="fas fa-chart-pie"></i> View Quiz Analytics
                            </a>
                            <br>
                            <small class="text-muted">Comprehensive performance insights</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Library -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-folder"></i> Documents Library
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status</th>
                            <th>Document Name</th>
                            <th>Category</th>
                            <th>Subject</th>
                            <th>Tags</th>
                            <th>Language</th>
                            <th>Type</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>225</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <strong>Québec Immigration ACT</strong><br>
                                <small class="text-muted">Immigration procedures and requirements</small>
                            </td>
                            <td><span class="badge bg-info">Immigration</span></td>
                            <td><span class="badge bg-primary">Acts</span></td>
                            <td>
                                <span class="badge bg-secondary">Legal</span>
                                <span class="badge bg-secondary">Immigration</span>
                            </td>
                            <td>English</td>
                            <td><span class="badge bg-secondary">No</span></td>
                            <td>
                                Aug 12, 2025<br>
                                <small class="text-muted">12:01</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Archive">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>224</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <strong>Privacy Act Guidelines</strong><br>
                                <small class="text-muted">Data protection and privacy regulations</small>
                            </td>
                            <td><span class="badge bg-info">Privacy</span></td>
                            <td><span class="badge bg-primary">Acts</span></td>
                            <td>
                                <span class="badge bg-secondary">Privacy</span>
                                <span class="badge bg-secondary">Data Protection</span>
                            </td>
                            <td>English</td>
                            <td><span class="badge bg-secondary">No</span></td>
                            <td>
                                Aug 12, 2025<br>
                                <small class="text-muted">15:30</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Archive">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>223</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <strong>Federal Court Rules</strong><br>
                                <small class="text-muted">Court procedures and filing requirements</small>
                            </td>
                            <td><span class="badge bg-warning">Court</span></td>
                            <td><span class="badge bg-primary">Rules</span></td>
                            <td>
                                <span class="badge bg-secondary">Federal</span>
                                <span class="badge bg-secondary">Court</span>
                            </td>
                            <td>English</td>
                            <td><span class="badge bg-secondary">No</span></td>
                            <td>
                                Aug 10, 2025<br>
                                <small class="text-muted">09:15</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Archive">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="{{ route('questionnaire.index') }}" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="action-icon">
                                            <i class="fas fa-brain text-primary mb-3" style="font-size: 2.5rem;"></i>
                                        </div>
                                        <h6 class="card-title">AI Study Assessment</h6>
                                        <p class="card-text text-muted">Take our AI-powered assessment to get personalized study recommendations and performance predictions</p>
                                        <span class="btn btn-outline-primary btn-sm">Start Assessment</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="{{ route('notes.index') }}" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="action-icon">
                                            <i class="fas fa-sticky-note text-warning mb-3" style="font-size: 2.5rem;"></i>
                                        </div>
                                        <h6 class="card-title">Study Notes</h6>
                                        <p class="card-text text-muted">Create, organize and search through your comprehensive study notes and materials</p>
                                        <span class="btn btn-outline-warning btn-sm">Manage Notes</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="{{ route('analytics.index') }}" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="action-icon">
                                            <i class="fas fa-chart-bar text-info mb-3" style="font-size: 2.5rem;"></i>
                                        </div>
                                        <h6 class="card-title">Performance Analytics</h6>
                                        <p class="card-text text-muted">Track your study progress, performance metrics and learning analytics</p>
                                        <span class="btn btn-outline-info btn-sm">View Analytics</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-chart-line"></i> Study Progress Analytics</h5>
                </div>
                <div class="card-body">
                    <canvas id="progressChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-chart-pie"></i> Subject Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="subjectChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Progress Chart
    const progressCtx = document.getElementById('progressChart').getContext('2d');
    new Chart(progressCtx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [{
                label: 'Study Progress',
                data: [75, 80, 78, 85, 88, 92],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: '#f1f5f9'
                    }
                },
                x: {
                    grid: {
                        color: '#f1f5f9'
                    }
                }
            }
        }
    });

    // Subject Distribution Chart
    const subjectCtx = document.getElementById('subjectChart').getContext('2d');
    new Chart(subjectCtx, {
        type: 'doughnut',
        data: {
            labels: ['Mathematics', 'Science', 'English', 'History'],
            datasets: [{
                data: [35, 28, 22, 15],
                backgroundColor: [
                    '#2563eb',
                    '#059669',
                    '#d97706',
                    '#dc2626'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
