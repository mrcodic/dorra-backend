@extends('layouts.fullLayoutMaster')

@section('title', 'Social Login')

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/pages/authentication.css')) }}">
@endsection

@section('content')
<div class="auth-wrapper auth-basic px-2">
    <div class="auth-inner my-2">
        <!-- Login basic -->
        <div class="shadow-sm p-3 mb-0">
            <div class="card-body">
                <div class="d-flex justify-content-center mb-5">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img id="logo" src="{{ asset('images/dorra-logo.svg') }}" alt="logo" style="width: 92px;" />
                    </a>
                </div>

                <h2 class="brand-text text-center">Welcome Back!</h2>

                <p class="login-card-text mb-2 text-center">
                    Choose a method to sign in or fill in the details to access your account
                </p>

                {{-- Social Login Buttons --}}
                <div class="d-grid gap-2 mb-2">
                    <a href="#" class="btn btn-outline-secondary d-flex align-items-center justify-content-center text-dark ">
                        <img src="{{ asset('images/google.svg') }}" alt="Google" style="width:20px; height:20px;" class="me-1">
                        Continue with Google
                    </a>
                    <a href="#" class="btn btn-outline-secondary d-flex align-items-center justify-content-center text-dark ">
                        <img src="{{ asset('images/apple.svg') }}" alt="Apple" style="width:20px; height:20px;" class="me-1">
                        Continue with Apple
                    </a>
                </div>

                {{-- Divider --}}
                <div class="d-flex align-items-center my-2">
                    <hr class="flex-grow-1">
                    <span class="mx-2 text-muted">or</span>
                    <hr class="flex-grow-1">
                </div>

                {{-- Email Login Button --}}
                <a href="{{ route('login') }}" class="btn btn-primary w-100" tabindex="4">
                    Sign in with Email
                </a>


            </div>
        </div>
        <!-- /Login basic -->
    </div>
</div>

@endsection

@section('vendor-script')
<script src="{{asset(mix('vendors/js/forms/validation/jquery.validate.min.js'))}}"></script>
@endsection

@section('page-script')
<script src="{{asset(mix('js/scripts/pages/auth-login.js'))}}"></script>
@endsection