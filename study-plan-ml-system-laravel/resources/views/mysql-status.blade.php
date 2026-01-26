@extends('layouts.app')

@section('title', 'MySQL Database Status')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-database"></i> MySQL Database Status
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Database Configuration:</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Driver:</strong>
                                    <span class="badge bg-primary">{{ DB::connection()->getDriverName() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Database:</strong>
                                    <span class="badge bg-info">{{ DB::connection()->getDatabaseName() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Host:</strong>
                                    <span class="badge bg-secondary">{{ config('database.connections.mysql.host') }}:{{ config('database.connections.mysql.port') }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Table Records:</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Notes:</strong>
                                    <span class="badge bg-primary">{{ DB::table('notes')->count() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Quizzes:</strong>
                                    <span class="badge bg-success">{{ DB::table('quizzes')->count() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Quiz Attempts:</strong>
                                    <span class="badge bg-warning">{{ DB::table('quiz_attempts')->count() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Users:</strong>
                                    <span class="badge bg-info">{{ DB::table('users')->count() }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Recent Quizzes (from MySQL):</h6>
                            @php
                                $recentQuizzes = DB::table('quizzes')
                                    ->join('notes', 'quizzes.note_id', '=', 'notes.id')
                                    ->select('quizzes.*', 'notes.title as note_title')
                                    ->orderBy('quizzes.created_at', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            
                            @if($recentQuizzes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Quiz Title</th>
                                                <th>Note</th>
                                                <th>Questions</th>
                                                <th>Difficulty</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentQuizzes as $quiz)
                                            <tr>
                                                <td>{{ $quiz->id }}</td>
                                                <td>{{ $quiz->title }}</td>
                                                <td>{{ $quiz->note_title }}</td>
                                                <td>{{ $quiz->total_questions }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $quiz->difficulty_level == 'easy' ? 'success' : ($quiz->difficulty_level == 'medium' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($quiz->difficulty_level) }}
                                                    </span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($quiz->created_at)->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No quizzes found in MySQL database yet. 
                                    <a href="{{ route('quiz.demo') }}" class="btn btn-sm btn-primary ms-2">Generate First Quiz</a>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <h5 class="text-success">
                            <i class="fas fa-check-circle"></i> 
                            MySQL Database is Active and Working!
                        </h5>
                        <p class="text-muted">All data is being stored in MySQL database, not SQLite.</p>
                        
                        <div class="btn-group" role="group">
                            <a href="{{ route('quiz.demo') }}" class="btn btn-primary">
                                <i class="fas fa-robot"></i> Test Quiz Generation
                            </a>
                            <a href="{{ route('notes.index') }}" class="btn btn-success">
                                <i class="fas fa-sticky-note"></i> View Notes
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-info">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
