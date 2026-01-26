@extends('layouts.app')

@section('title', 'Test Dashboard')

@section('content')
<div class="container">
    <h1>Dashboard Test</h1>
    <p>If you can see this, the layout is working!</p>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>0</h3>
                    <p>Total Notes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>0</h3>
                    <p>Questionnaires</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>0</h3>
                    <p>Tests Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>0</h3>
                    <p>Subjects</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
