@extends('layouts.fullLayoutMaster')

@section('title', 'Forget Password Page')

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


                <h2 class="brand-text  text-center">Reset Password</h2>


                <p class="login-card-text mb-2 text-center">Enter your email address, we’ll send you a reset code</p>

                <form class="auth-login-form mt-3" action="{{ route('login') }}" method="post">
                    @csrf
                    <div class="mb-1">
                        <label for="login-email" class="form-label label-text">Email Address</label>
                        <input
                            type="text"
                            class="form-control"
                            id="login-email"
                            name="email"
                            placeholder="Enter your email address"
                            aria-describedby="login-email"
                            tabindex="1"
                            autofocus />
                    </div>
                    <button class="btn btn-primary w-100 mt-3" tabindex="4">Confirm</button>
                </form>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary  w-100 mt-2 mb-5 text-dark d-flex justify-items-center justify-content-center" tabindex="4"><i data-feather="chevron-left"></i><span>Back to sign in</span></a>




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