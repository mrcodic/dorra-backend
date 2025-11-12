{{-- resources/views/dashboard/auth/reset.blade.php --}}
@extends('layouts.fullLayoutMaster')

@section('title', 'Reset Password')

@section('page-style')
    <link rel="stylesheet" href="{{ asset(mix('css/base/pages/authentication.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endsection

@section('content')
    <div class="auth-wrapper auth-basic px-2">
        <div class="auth-inner my-2">
            <div class="shadow-sm p-3 mb-0">
                <div class="card-body">
                    <h2 class="brand-text text-center mb-1">Set a new password</h2>

                    <form id="resetForm" method="post" action="{{ url('/reset-password') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="mb-1">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ $email }}" readonly>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                        <button class="btn btn-primary w-100 mt-2" type="submit">Save</button>
                    </form>

                    <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 mt-2">Back to sign in</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
@endsection

@section('page-script')
    <script>
        $('#resetForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: this.action,
                method: 'POST',
                data: $(this).serialize(),
                headers: { 'Accept': 'application/json' },
                success: function() {
                    Toastify({ text: "Password reset successfully. You can sign in now.", duration: 4000, gravity:"top", position:"right", backgroundColor:"#28c76f" }).showToast();
                    setTimeout(() => window.location = "{{ route('login') }}", 800);
                },
                error: function(xhr) {
                    let msg = 'Reset failed';
                    if (xhr.responseJSON?.errors) {
                        const errs = xhr.responseJSON.errors;
                        msg = Object.values(errs)[0][0] || msg;
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Toastify({ text: msg, duration: 4000, gravity:"top", position:"right", backgroundColor:"#EA5455" }).showToast();
                }
            });
        });
    </script>
@endsection
