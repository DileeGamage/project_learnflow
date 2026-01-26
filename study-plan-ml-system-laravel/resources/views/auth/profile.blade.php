@extends('layouts.main')

@section('title', 'My Profile - Study Plan System')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-user-circle me-2"></i>My Profile
                        </h1>
                        <p class="text-muted mb-0">Manage your account settings and personal information</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Profile Information Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('auth.profile.update') }}" id="profileForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                <i class="fas fa-user me-1"></i>Full Name
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar me-1"></i>Member Since
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $user->created_at->format('F j, Y') }}" 
                                   readonly>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="updateProfileBtn">
                                <i class="fas fa-save me-2"></i>
                                <span class="btn-text">Update Profile</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('auth.password.update') }}" id="passwordForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">
                                <i class="fas fa-key me-1"></i>Current Password
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-1"></i>New Password
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="fas fa-lock me-1"></i>Confirm New Password
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="password-strength mb-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Password must be at least 8 characters long
                            </small>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning" id="updatePasswordBtn">
                                <i class="fas fa-shield-alt me-2"></i>
                                <span class="btn-text">Update Password</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Your Activity Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-primary text-white">
                                    <i class="fas fa-book"></i>
                                </div>
                                <h4 class="stat-number" id="notesCount">0</h4>
                                <p class="stat-label">Notes Created</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-success text-white">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <h4 class="stat-number" id="quizzesCount">0</h4>
                                <p class="stat-label">Quizzes Taken</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-warning text-white">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <h4 class="stat-number" id="avgScore">0%</h4>
                                <p class="stat-label">Average Score</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-danger text-white">
                                    <i class="fas fa-star"></i>
                                </div>
                                <h4 class="stat-number" id="favoritesCount">0</h4>
                                <p class="stat-label">Favorite Notes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    text-align: center;
    padding: 1rem;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #333;
}

.stat-label {
    color: #666;
    margin: 0;
    font-size: 0.9rem;
}

.progress-bar {
    transition: all 0.3s ease;
}
</style>

<script>
$(document).ready(function() {
    // Toggle password visibility functions
    function togglePasswordField(buttonId, inputId) {
        $(buttonId).click(function() {
            const passwordInput = $(inputId);
            const icon = $(this).find('i');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    }

    // Initialize password toggles
    togglePasswordField('#toggleCurrentPassword', '#current_password');
    togglePasswordField('#toggleNewPassword', '#password');
    togglePasswordField('#toggleConfirmPassword', '#password_confirmation');

    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strengthBar = $('#passwordStrength');
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]/)) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/) || password.match(/[^a-zA-Z0-9]/)) strength += 25;
        
        strengthBar.css('width', strength + '%');
        
        if (strength < 50) {
            strengthBar.removeClass('bg-warning bg-success').addClass('bg-danger');
        } else if (strength < 75) {
            strengthBar.removeClass('bg-danger bg-success').addClass('bg-warning');
        } else {
            strengthBar.removeClass('bg-danger bg-warning').addClass('bg-success');
        }
    });

    // Password confirmation validation
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            if (!$(this).parent().siblings('.invalid-feedback').length) {
                $(this).parent().after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).parent().siblings('.invalid-feedback').remove();
        }
    });

    // Form submission handling
    $('#profileForm').submit(function() {
        const btn = $('#updateProfileBtn');
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        
        btn.prop('disabled', true);
        btnText.text('Updating...');
        spinner.removeClass('d-none');
    });

    $('#passwordForm').submit(function() {
        const btn = $('#updatePasswordBtn');
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        
        btn.prop('disabled', true);
        btnText.text('Updating...');
        spinner.removeClass('d-none');
    });

    // Load user statistics
    loadUserStats();

    function loadUserStats() {
        // This would typically be an AJAX call to get user statistics
        // For now, we'll use placeholder values
        $('#notesCount').text('{{ $user->notes()->count() ?? 0 }}');
        $('#quizzesCount').text('{{ $user->quizAttempts()->count() ?? 0 }}');
        $('#avgScore').text('{{ $user->quizAttempts()->avg("score") ? round($user->quizAttempts()->avg("score"), 1) : 0 }}%');
        $('#favoritesCount').text('{{ $user->notes()->where("is_favorite", true)->count() ?? 0 }}');
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endsection
