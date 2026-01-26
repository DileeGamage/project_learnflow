@extends('layouts.guest')

@section('title', 'Login - Study Plan System')

@section('content')
<div class="min-vh-100 d-flex align-items-center bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-white text-center py-4">
                        <h3 class="mb-0 text-primary">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Study Plan System
                        </h3>
                        <p class="text-muted mb-0">Sign in to your account</p>
                    </div>
                    <div class="card-body px-5 py-4">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ session('status') }}
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

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf
                            
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
                                       autofocus
                                       autocomplete="username"
                                       placeholder="Enter your email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label text-dark fw-semibold">
                                    <i class="fas fa-lock me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required
                                           autocomplete="current-password"
                                           placeholder="Enter your password">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                                <label class="form-check-label" for="remember_me">
                                    Remember me
                                </label>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <span class="btn-text">Sign In</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>

                            @if (Route::has('password.request'))
                                <div class="text-center mb-3">
                                    <a href="{{ route('password.request') }}" class="text-muted text-decoration-none">
                                        Forgot your password?
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="card-footer bg-light text-center py-3">
                        <p class="mb-0 text-muted">
                            Don't have an account? 
                            <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-semibold">
                                Create one here
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

    // Form submission handling
    $('#loginForm').submit(function() {
        const btn = $('#loginBtn');
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        
        btn.prop('disabled', true);
        btnText.text('Signing In...');
        spinner.removeClass('d-none');
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endsection
