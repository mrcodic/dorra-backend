@extends('layouts.fullLayoutMaster')

@section('title', 'Reset Password Page')

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


                <p class="login-card-text mb-2 text-center">We sent temporary sign in code to user1@gmail.com <a href="" class="primary-text-color text-decoration-underline">Not You?</a></p>
                <p class=" mb-2 text-center fs-3 text-dark">This code is valid for <span class="text-danger">4:35</span></p>
                <p class="text-dark text-decoration-underline text-center">Resend Code</p>
                <form class="auth-login-form mt-3" action="{{ route('login') }}" method="post">
                    @csrf

                    {{-- OTP Input Fields --}}
                    <div class="d-flex justify-content-center gap-1 mb-2">
                        @for ($i = 0; $i < 6; $i++)
                            <input type="text" name="otp[]" maxlength="1" class="form-control text-center rounded-3" style="width: 50px; height: 50px; font-size: 1.5rem;" >
                            @endfor
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
<script>
    document.querySelectorAll('input[name="otp[]"]').forEach((input, index, inputs) => {
        input.addEventListener('input', () => {
            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
    });
</script>
@endsection
