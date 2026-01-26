@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-body text-center p-5">
            <h1 class="mb-4">Welcome to Your Personalized Learning Journey</h1>
            
            <div class="my-5">
                <p class="lead">
                    This system is designed to help you get the most out of your study sessions. 
                    We'll be creating a personalized review for you based on your notes and helping 
                    you identify your most effective study habits.
                </p>
                
                <div class="mt-5">
                    <img src="{{ asset('images/learning-illustration.svg') }}" alt="Learning Journey" 
                        class="img-fluid mb-5" style="max-height: 250px;">
                </div>
                
                <a href="{{ route('learning_journey.habits') }}" class="btn btn-primary btn-lg">
                    Let's Get Started <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection