@extends('layouts.fullLayoutMaster')

@section('title', 'Login Page')

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


                <h2 class="brand-text  text-center">Welcome Back!</h2>


                <p class="login-card-text mb-2 text-center">Enter your information to sign in to your account</p>

                <form class="auth-login-form mt-3" action="{{ route('login') }}" method="post">
                    @csrf
                    <div class="mb-1">
                        <label for="login-email" class="form-label label-text">Email Address</label>
                        <input
                            type="text"
                            class="form-control"
                            id="login-email"
                            name="email"
                            placeholder="john@example.com"
                            aria-describedby="login-email"
                            tabindex="1"
                            autofocus />
                    </div>

                    <div class="mb-1">
                        <div class="d-flex justify-content-between">
                            <label class="form-label label-text" for="login-password">Password</label>

                        </div>
                        <div class="input-group input-group-merge form-password-toggle">
                            <input
                                type="password"
                                class="form-control form-control-merge"
                                id="login-password"
                                name="password"
                                tabindex="2"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="login-password" />
                            <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                        </div>
                    </div>
                    <div class="mb-1 d-flex justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" name="remember_token" type="checkbox" id="remember-me" tabindex="3" />
                            <label class="form-check-label" for="remember-me"> Remember Me </label>
                        </div>
                        <a href="{{url('/forgot-password')}}">
                            <small>Forgot Password?</small>
                        </a>
                    </div>
                    <button class="btn btn-primary w-100" tabindex="4">Sign in</button>
                </form>
              



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