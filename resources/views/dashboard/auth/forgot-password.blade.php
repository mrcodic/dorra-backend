@extends('layouts.fullLayoutMaster')

@section('title', 'Forget Password Page')

@section('page-style')
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/base/pages/authentication.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endsection

@section('content')
    <div class="auth-wrapper auth-basic px-2">
        <div class="auth-inner my-2">
            <div class="shadow-sm p-3 mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-center mb-5">
                        <a class="navbar-brand" href="{{ url('/') }}">
                            <img id="logo" src="{{ asset('images/dorra-logo.svg') }}" alt="logo" style="width: 92px;" />
                        </a>
                    </div>

                    <h2 class="brand-text text-center">Reset Password</h2>
                    <p class="login-card-text mb-2 text-center">Enter your email address, we’ll send you a reset link</p>

                    <form id="forgotForm" class="mt-3" method="post" action="{{ route("password.request") }}">
                        @csrf
                        <div class="mb-1">
                            <label for="login-email" class="form-label label-text">Email Address</label>
                            <input type="email" class="form-control" id="login-email" name="email"
                                   placeholder="Enter your email address" required autofocus />
                        </div>
                        <button class="btn btn-primary w-100 mt-3" type="submit">Confirm</button>
                    </form>

                    <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 mt-2 mb-5 text-dark d-flex justify-content-center">
                        <i data-feather="chevron-left"></i><span>Back to sign in</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
@endsection

@section('page-script')
    <script>
        // Submit via AJAX to stay on dashboard layout
        $('#forgotForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: this.action,
                method: 'POST',
                data: $(this).serialize(),
                headers: { 'Accept': 'application/json' },
                success: function() {
                    Toastify({ text: "If the email exists, a reset link has been sent.", duration: 4000, gravity:"top", position:"right", backgroundColor:"#28c76f" }).showToast();
                },
                error: function(xhr) {
                    let msg = 'Something went wrong';
                    if (xhr.responseJSON?.errors?.email) msg = xhr.responseJSON.errors.email[0];
                    Toastify({ text: msg, duration: 4000, gravity:"top", position:"right", backgroundColor:"#EA5455" }).showToast();
                }
            });
        });
    </script>
@endsection
