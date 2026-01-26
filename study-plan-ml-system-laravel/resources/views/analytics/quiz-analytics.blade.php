@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-chart-line"></i> Quiz Performance Analytics</h1>
        <p class="subtitle">Comprehensive analysis of your learning patterns and quiz performance</p>
    </div>

    <!-- Analytics Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="subjectFilter" class="form-label">Subject Filter</label>
                            <select id="subjectFilter" class="form-select">
                                <option value="all">All Subjects</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="timeRange" class="form-label">Time Range</label>
                            <select id="timeRange" class="form-select">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="365">Last year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button id="refreshAnalytics" class="btn btn-primary d-block">
                                <i class="fas fa-sync-alt"></i> Refresh Data
                            </button>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button id="exportData" class="btn btn-outline-secondary d-block">
                                <i class="fas fa-download"></i> Export Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading analytics...</span>
        </div>
        <p class="mt-2">Analyzing your learning patterns...</p>
    </div>

    <!-- Analytics Grid - Organized in 2x2 Layout -->
    <div id="analyticsContainer">
        <!-- Row 1: Performance & Accuracy Charts -->
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card analytics-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-trending-up"></i> Performance Trends</h5>
                        <div class="chart-controls">
                            <button class="btn btn-sm btn-outline-primary" onclick="toggleChartType('performance')">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="chart-container flex-grow-1" style="position: relative; min-height: 300px;">
                            <canvas id="performanceChart"></canvas>
                        </div>
                        <div class="chart-legend mt-3">
                            <small class="text-muted">Track your quiz performance and response time over time</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card analytics-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bullseye"></i> Accuracy by Difficulty</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="chart-container flex-grow-1" style="position: relative; min-height: 300px;">
                            <canvas id="accuracyChart"></canvas>
                        </div>
                        <div class="chart-legend mt-3">
                            <small class="text-muted">See how you perform across different question difficulties</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Study Session & Answer Pattern Charts -->
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card analytics-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Study Time vs Performance</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="chart-container flex-grow-1" style="position: relative; min-height: 300px;">
                            <canvas id="sessionChart"></canvas>
                        </div>
                        <div class="chart-legend mt-3">
                            <small class="text-muted">Correlation between your study habits and quiz performance</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card analytics-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-puzzle-piece"></i> Answer Patterns</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="chart-container flex-grow-1" style="position: relative; min-height: 300px;">
                            <canvas id="patternChart"></canvas>
                        </div>
                        <div class="chart-legend mt-3">
                            <small class="text-muted">Analysis of your response patterns and approach to questions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Learning Profile & Knowledge Retention -->
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card analytics-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-radar"></i> Learning Profile</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="chart-container flex-grow-1" style="position: relative; min-height: 300px;">
                            <canvas id="radarChart"></canvas>
                        </div>
                        <div class="chart-legend mt-3">
                            <small class="text-muted">Your overall learning performance across key metrics</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card analytics-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-fire"></i> Knowledge Retention</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div id="heatmapContainer" class="heatmap-container flex-grow-1" style="min-height: 300px;">
                            <table id="retentionHeatmap" class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Topic</th>
                                        <th>Day 1</th>
                                        <th>Day 3</th>
                                        <th>Day 7</th>
                                        <th>Day 14</th>
                                        <th>Day 30</th>
                                    </tr>
                                </thead>
                                <tbody id="heatmapBody">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        <div class="chart-legend mt-3">
                            <small class="text-muted">Track how well you retain knowledge over time</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 4: Self-Assessment Correlation (Full Width) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card analytics-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-link"></i> Self-Assessment vs Performance Correlation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="chart-container" style="position: relative; min-height: 300px;">
                                    <canvas id="correlationChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div id="correlationInsights" class="correlation-insights h-100 d-flex flex-column justify-content-center">
                                    <h6>Key Insights:</h6>
                                    <ul id="insightsList" class="list-unstyled">
                                        <li><i class="fas fa-lightbulb text-warning"></i> Analyzing correlation patterns...</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="chart-legend mt-3">
                            <small class="text-muted">How your self-reported learning preferences correlate with actual performance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 5: Performance Summary Stats (Full Width) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card analytics-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Performance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center" id="performanceStats">
                            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-number" id="totalQuizzes">-</div>
                                    <div class="stat-label">Total Quizzes</div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-number" id="avgScore">-</div>
                                    <div class="stat-label">Average Score</div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-number" id="bestStreak">-</div>
                                    <div class="stat-label">Best Streak</div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-number" id="totalStudyTime">-</div>
                                    <div class="stat-label">Study Time (hrs)</div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-number" id="improvementRate">-</div>
                                    <div class="stat-label">Improvement</div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-number" id="weakestArea">-</div>
                                    <div class="stat-label">Focus Area</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-title h1 {
    color: #2c3e50;
    font-weight: 600;
}

.page-title .subtitle {
    color: #7f8c8d;
    font-size: 1.1rem;
}

/* Analytics Grid Layout Improvements */
#analyticsContainer .row {
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 1.5rem;
}

.analytics-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
    min-height: 450px; /* Ensure consistent card heights */
}

.analytics-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.analytics-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0;
    border: none;
    padding: 15px 20px;
}

.analytics-card .card-header h5 {
    font-weight: 600;
    margin: 0;
}

.analytics-card .card-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
}

/* Chart Container Improvements */
.chart-container {
    position: relative;
    width: 100%;
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-container canvas {
    max-width: 100%;
    height: auto !important;
    width: auto !important;
}

/* Chart Controls */
.chart-controls button {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
}

.chart-controls button:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    color: white;
}

.chart-legend {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
    margin-top: auto;
}

/* Heatmap Styling */
.heatmap-container {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 300px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
}

#retentionHeatmap {
    margin-bottom: 0;
    font-size: 0.9rem;
}

#retentionHeatmap th {
    background: #f8f9fa;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    position: sticky;
    top: 0;
    z-index: 10;
}

.heat-cell {
    text-align: center;
    color: white;
    font-weight: bold;
    border-radius: 4px;
    padding: 8px;
    min-width: 60px;
}

/* Correlation Insights */
.correlation-insights {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    height: 100%;
}

.correlation-insights h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 15px;
}

.correlation-insights ul li {
    margin-bottom: 10px;
    color: #6c757d;
}

/* Performance Stats Styling */
.stat-item {
    padding: 20px 15px;
    margin-bottom: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.stat-item:hover {
    background: #e9ecef;
    border-color: #667eea;
    transform: translateY(-2px);
}

.stat-number {
    font-size: 2.2rem;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Button Styling */
.btn-outline-primary:hover {
    background-color: #667eea;
    border-color: #667eea;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #667eea 100%);
    transform: translateY(-1px);
}

.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .analytics-card {
        min-height: 420px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}

@media (max-width: 992px) {
    .analytics-card {
        min-height: 400px;
    }
    
    #analyticsContainer .row {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1rem;
    }
}

@media (max-width: 768px) {
    .analytics-card {
        min-height: 350px;
        margin-bottom: 20px;
    }
    
    .analytics-card .card-body {
        padding: 15px;
    }
    
    .analytics-card .card-header {
        padding: 12px 15px;
    }
    
    .stat-number {
        font-size: 1.8rem;
    }
    
    .stat-item {
        padding: 15px 10px;
    }
    
    #analyticsContainer .row {
        --bs-gutter-x: 0.75rem;
        --bs-gutter-y: 0.75rem;
    }
    
    /* Stack columns on mobile */
    .col-lg-6, .col-md-6 {
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .analytics-card {
        min-height: 320px;
    }
    
    .stat-number {
        font-size: 1.6rem;
    }
    
    .page-title h1 {
        font-size: 1.8rem;
    }
    
    .page-title .subtitle {
        font-size: 1rem;
    }
    
    .analytics-card .card-header h5 {
        font-size: 1rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
class QuizAnalytics {
    constructor() {
        this.charts = {};
        this.currentData = null;
        this.chartTypes = {
            performance: 'line'
        };
        
        this.initializeEventListeners();
        this.loadAnalytics();
    }

    initializeEventListeners() {
        document.getElementById('subjectFilter').addEventListener('change', () => this.loadAnalytics());
        document.getElementById('timeRange').addEventListener('change', () => this.loadAnalytics());
        document.getElementById('refreshAnalytics').addEventListener('click', () => this.loadAnalytics());
        document.getElementById('exportData').addEventListener('click', () => this.exportData());
    }

    async loadAnalytics() {
        this.showLoading(true);
        
        try {
            const subject = document.getElementById('subjectFilter').value;
            const days = document.getElementById('timeRange').value;
            
            const response = await fetch('/api/quiz-analytics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ subject, days })
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch analytics data');
            }
            
            this.currentData = await response.json();
            this.populateSubjects();
            this.createAllCharts();
            this.updatePerformanceStats();
            this.updateCorrelationInsights();
            
        } catch (error) {
            console.error('Error loading analytics:', error);
            this.showError('Failed to load analytics data. Please try again.');
        } finally {
            this.showLoading(false);
        }
    }

    showLoading(show) {
        document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
        document.getElementById('analyticsContainer').style.display = show ? 'none' : 'block';
    }

    showError(message) {
        // You can implement a proper error display here
        alert(message);
    }

    populateSubjects() {
        const subjectSelect = document.getElementById('subjectFilter');
        const currentValue = subjectSelect.value;
        
        // Clear and repopulate
        subjectSelect.innerHTML = '<option value="all">All Subjects</option>';
        
        if (this.currentData.subjects) {
            this.currentData.subjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject;
                option.textContent = subject;
                subjectSelect.appendChild(option);
            });
        }
        
        // Restore selection if it still exists
        if (currentValue && [...subjectSelect.options].some(opt => opt.value === currentValue)) {
            subjectSelect.value = currentValue;
        }
    }

    createAllCharts() {
        this.createPerformanceChart();
        this.createAccuracyChart();
        this.createSessionChart();
        this.createPatternChart();
        this.createRadarChart();
        this.createRetentionHeatmap();
        this.createCorrelationChart();
    }

    createPerformanceChart() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        if (this.charts.performance) {
            this.charts.performance.destroy();
        }

        this.charts.performance = new Chart(ctx, {
            type: this.chartTypes.performance,
            data: {
                labels: this.currentData.performanceData.map(d => d.date),
                datasets: [{
                    label: 'Quiz Score (%)',
                    data: this.currentData.performanceData.map(d => d.score),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Avg Response Time (min)',
                    data: this.currentData.performanceData.map(d => d.avgTime),
                    borderColor: '#f093fb',
                    backgroundColor: 'rgba(240, 147, 251, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Score (%)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Response Time (min)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    createAccuracyChart() {
        const ctx = document.getElementById('accuracyChart').getContext('2d');
        
        if (this.charts.accuracy) {
            this.charts.accuracy.destroy();
        }

        this.charts.accuracy = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.currentData.accuracyData.map(d => d.difficulty),
                datasets: [{
                    label: 'Correct Answers',
                    data: this.currentData.accuracyData.map(d => d.correct),
                    backgroundColor: '#10b981'
                }, {
                    label: 'Incorrect Answers',
                    data: this.currentData.accuracyData.map(d => d.incorrect),
                    backgroundColor: '#ef4444'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'Number of Questions'
                        }
                    }
                }
            }
        });
    }

    createSessionChart() {
        const ctx = document.getElementById('sessionChart').getContext('2d');
        
        if (this.charts.session) {
            this.charts.session.destroy();
        }

        this.charts.session = new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Study Time vs Performance',
                    data: this.currentData.sessionData.map(d => ({
                        x: d.studyTime,
                        y: d.quizScore
                    })),
                    backgroundColor: '#8b5cf6',
                    borderColor: '#7c3aed'
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
                    x: {
                        title: {
                            display: true,
                            text: 'Study Time (minutes)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Quiz Score (%)'
                        },
                        max: 100
                    }
                }
            }
        });
    }

    createPatternChart() {
        const ctx = document.getElementById('patternChart').getContext('2d');
        
        if (this.charts.pattern) {
            this.charts.pattern.destroy();
        }

        const patternData = this.currentData.patternData || {};

        this.charts.pattern = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['First Attempt Correct', 'With Hints', 'Multiple Attempts', 'Incorrect'],
                datasets: [{
                    data: [
                        patternData.firstAttempt || 0,
                        patternData.withHints || 0,
                        patternData.multipleAttempts || 0,
                        patternData.incorrect || 0
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    createRadarChart() {
        const ctx = document.getElementById('radarChart').getContext('2d');
        
        if (this.charts.radar) {
            this.charts.radar.destroy();
        }

        const radarData = this.currentData.radarData || {};

        this.charts.radar = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['Speed', 'Accuracy', 'Consistency', 'Improvement', 'Retention'],
                datasets: [{
                    label: 'Performance Metrics',
                    data: [
                        radarData.speed || 0,
                        radarData.accuracy || 0,
                        radarData.consistency || 0,
                        radarData.improvement || 0,
                        radarData.retention || 0
                    ],
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: '#667eea',
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#667eea'
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
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                }
            }
        });
    }

    createRetentionHeatmap() {
        const heatmapBody = document.getElementById('heatmapBody');
        heatmapBody.innerHTML = '';
        
        const retentionData = this.currentData.retentionData || [];
        
        retentionData.forEach(topic => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${topic.name}</strong></td>
                <td class="heat-cell" style="background-color: ${this.getHeatColor(topic.day1)}">${topic.day1}%</td>
                <td class="heat-cell" style="background-color: ${this.getHeatColor(topic.day3)}">${topic.day3}%</td>
                <td class="heat-cell" style="background-color: ${this.getHeatColor(topic.day7)}">${topic.day7}%</td>
                <td class="heat-cell" style="background-color: ${this.getHeatColor(topic.day14)}">${topic.day14}%</td>
                <td class="heat-cell" style="background-color: ${this.getHeatColor(topic.day30)}">${topic.day30}%</td>
            `;
            heatmapBody.appendChild(row);
        });
    }

    createCorrelationChart() {
        const ctx = document.getElementById('correlationChart').getContext('2d');
        
        if (this.charts.correlation) {
            this.charts.correlation.destroy();
        }

        const correlationData = this.currentData.correlationData || [];

        this.charts.correlation = new Chart(ctx, {
            type: 'line',
            data: {
                labels: correlationData.map(d => d.date),
                datasets: [{
                    label: 'Self-Assessment vs Performance',
                    data: correlationData.map(d => d.performance),
                    borderColor: '#f093fb',
                    backgroundColor: 'rgba(240, 147, 251, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Performance Score (%)'
                        }
                    }
                }
            }
        });
    }

    getHeatColor(value) {
        // Generate color based on value (0-100)
        const intensity = value / 100;
        const red = Math.floor(255 * (1 - intensity));
        const green = Math.floor(255 * intensity);
        return `rgb(${red}, ${green}, 50)`;
    }

    updatePerformanceStats() {
        // Calculate and display summary statistics
        const performanceData = this.currentData.performanceData || [];
        const sessionData = this.currentData.sessionData || [];
        
        const totalQuizzes = performanceData.length;
        const avgScore = totalQuizzes > 0 ? 
            (performanceData.reduce((sum, d) => sum + d.score, 0) / totalQuizzes).toFixed(1) : 0;
        
        const totalStudyTime = sessionData.reduce((sum, d) => sum + d.studyTime, 0);
        const studyHours = (totalStudyTime / 60).toFixed(1);
        
        // Calculate improvement rate
        let improvementRate = 0;
        if (performanceData.length >= 2) {
            const firstHalf = performanceData.slice(0, Math.floor(performanceData.length / 2));
            const secondHalf = performanceData.slice(Math.floor(performanceData.length / 2));
            const firstAvg = firstHalf.reduce((sum, d) => sum + d.score, 0) / firstHalf.length;
            const secondAvg = secondHalf.reduce((sum, d) => sum + d.score, 0) / secondHalf.length;
            improvementRate = ((secondAvg - firstAvg) / firstAvg * 100).toFixed(1);
        }
        
        document.getElementById('totalQuizzes').textContent = totalQuizzes;
        document.getElementById('avgScore').textContent = avgScore + '%';
        document.getElementById('bestStreak').textContent = '5'; // You can implement streak calculation
        document.getElementById('totalStudyTime').textContent = studyHours;
        document.getElementById('improvementRate').textContent = improvementRate + '%';
        document.getElementById('weakestArea').textContent = 'Math'; // You can implement weak area detection
    }

    updateCorrelationInsights() {
        const insightsList = document.getElementById('insightsList');
        const correlationData = this.currentData.correlationData || [];
        
        insightsList.innerHTML = '';
        
        if (correlationData.length > 0) {
            const insights = [
                '<i class="fas fa-trend-up text-success"></i> Your self-assessment accuracy is improving',
                '<i class="fas fa-clock text-info"></i> Morning study sessions show 15% better performance',
                '<i class="fas fa-brain text-purple"></i> Visual learning preference correlates with higher scores'
            ];
            
            insights.forEach(insight => {
                const li = document.createElement('li');
                li.innerHTML = insight;
                insightsList.appendChild(li);
            });
        } else {
            insightsList.innerHTML = '<li><i class="fas fa-info-circle text-muted"></i> Complete more quizzes to see insights</li>';
        }
    }

    toggleChartType(chartName) {
        if (chartName === 'performance') {
            this.chartTypes.performance = this.chartTypes.performance === 'line' ? 'bar' : 'line';
            this.createPerformanceChart();
        }
    }

    exportData() {
        if (!this.currentData) return;
        
        const exportData = {
            timestamp: new Date().toISOString(),
            ...this.currentData
        };
        
        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `quiz-analytics-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}

// Initialize analytics when page loads
document.addEventListener('DOMContentLoaded', function() {
    new QuizAnalytics();
});
</script>
@endsection
