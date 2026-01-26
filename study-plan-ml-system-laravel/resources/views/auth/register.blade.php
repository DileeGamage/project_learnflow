@extends('layouts.guest')

@section('title', 'Register - Study Plan System')

@section('content')
<div class="min-vh-100 d-flex align-items-center bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-white text-center py-4">
                        <h3 class="mb-0 text-primary">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Join Study Plan System
                        </h3>
                        <p class="text-muted mb-0">Create your account to get started</p>
                    </div>
                    <div class="card-body px-5 py-4">
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
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" id="registerForm">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="name" class="form-label text-dark fw-semibold">
                                    <i class="fas fa-user me-1"></i>Full Name
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required 
                                       autofocus
                                       autocomplete="name"
                                       placeholder="Enter your full name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label text-dark fw-semibold">
                                    <i class="fas fa-envelope me-1"></i>Email Address
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required
                                       autocomplete="username"
                                       placeholder="Enter your email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label text-dark fw-semibold">
                                        <i class="fas fa-lock me-1"></i>Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               required
                                               autocomplete="new-password"
                                               placeholder="Enter password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label text-dark fw-semibold">
                                        <i class="fas fa-lock me-1"></i>Confirm Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               required
                                               autocomplete="new-password"
                                               placeholder="Confirm password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="registerBtn">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <span class="btn-text">Create Account</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-light text-center py-3">
                        <p class="mb-0 text-muted">
                            Already have an account? 
                            <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
    backdrop-filter: blur(10px);
    border-radius: 15px;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    transition: transform 0.2s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.progress-bar {
    transition: all 0.3s ease;
}
</style>

<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordInput = $('#password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#togglePasswordConfirm').click(function() {
        const passwordInput = $('#password_confirmation');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

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
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Form submission handling
    $('#registerForm').submit(function() {
        const btn = $('#registerBtn');
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        
        btn.prop('disabled', true);
        btnText.text('Creating Account...');
        spinner.removeClass('d-none');
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endsection
