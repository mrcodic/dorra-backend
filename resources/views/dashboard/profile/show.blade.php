@extends('layouts/contentLayoutMaster')

@section('title', 'Settings')
@section('main-page', 'Settings')

@section('vendor-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}"
          xmlns="http://www.w3.org/1999/html">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">


    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
    @php
        $initialMediaId = optional(Auth::user()->media()->first())->id; // null if none
    @endphp

    <div class="card d-flex flex-column flex-md-row">
        {{-- Left Side: Vertical Tabs --}}
        <div class="nav d-flex flex-md-column nav-pills px-2 py-3 gap-1 gap-md-3" id="v-pills-tab" role="tablist">
            <button class="btn profile-tab active" id="tab1-tab" data-bs-toggle="pill" data-bs-target="#tab1"
                    type="button"
                    role="tab" aria-controls="tab1" aria-selected="true">
                Account Information
            </button>
            <button class="btn profile-tab" id="tab2-tab" data-bs-toggle="pill" data-bs-target="#tab2" type="button"
                    role="tab" aria-controls="tab2" aria-selected="false">
                Security
            </button>
        </div>

        {{-- Right Side: Tab Content --}}
        <div class="tab-content flex-grow-1 px-1 py-3" id="v-pills-tabContent">
            {{-- TAB 1: Account --}}
            <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                {{-- Avatar + Dropzone --}}
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-1">
                        <img id="userAvatar" class="img-fluid rounded-circle mb-2"
                             src="{{ Auth::user()->image?->getUrl() ?? asset('images/default-user.png') }}"
                             height="48" width="48" alt="User avatar"/>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn bg-white text-danger fs-5" id="removeAvatarBtn" type="button">Remove Photo
                        </button>
                    </div>
                </div>

                {{-- Dropzone area --}}
                <form action="{{ route('media.store') }}" method="post" class="dropzone border rounded p-2 mt-1"
                      id="avatarDropzone">
                    @csrf
                    <div class="dz-message needsclick text-center p-2">
                        <div class="fw-semibold">Drag & drop or click to upload</div>
                        <small class="text-muted d-block">JPG/PNG/WebP • Max 2MB • 1 file</small>
                    </div>
                </form>

                <hr class="my-2"/>

                {{-- Edit Profile form (keep this INSIDE tab1) --}}
                <form id="updateProfileForm" action="{{ route('profile.update',Auth::user()->id) }}" method="post">
                    @csrf
                    @method("PUT")
                    <div class="row mb-2">
                        <div class="col-md-10">
                            <label for="first_name" class="form-label label-text">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ Auth::user()->first_name }}"
                                   class="form-control" placeholder="Enter first name" required>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-10">
                            <label for="last_name" class="form-label label-text">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ Auth::user()->last_name }}"
                                   class="form-control" placeholder="Enter last name" required>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-10">
                            <label for="email" class="form-label label-text">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ Auth::user()->email }}"
                                   class="form-control" placeholder="Enter email address" required>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-10 col-lg-8">
                            <label for="phone_number" class="form-label label-text">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number"
                                   value="{{ Auth::user()->phone_number }}"
                                   class="form-control" placeholder="Enter phone number" required>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-secondary place-order fs-16">Edit</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TAB 2: Security --}}
            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                <h2 class="mb-2 text-black">Change Password</h2>

                <div id="old-password-section">
                    <form action="{{ route("check-old-password") }}" method="post" id="checkOldPasswordForm">
                        @csrf
                        <div class="mb-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label label-text" for="login-password">Old Password</label>
                            </div>

                            <div class="input-group input-group-merge form-password-toggle">
                                <input type="password" class="form-control form-control-merge" id="old-password"
                                       name="current_password" placeholder="Enter old password"/>
                            </div>


                            <div class="w-100 text-end">
                                <a href="{{ route("password.request") }}" class="text-primary text-decoration-underline fs-6 text-end"
                                   style="color:#24B094!important;">Forgot password?</a>
                            </div>
                        </div>

                        <div class="d-flex gap-1 justify-content-end mt-2">
                            <button type="reset" class="btn btn-outline-secondary fs-16">Cancel</button>
                            <button type="submit" class="btn btn-primary fs-16" id="nextBtn">Next</button>
                        </div>
                    </form>
                </div>
                <form action="{{ route("change-password") }}" method="post" id="changePasswordForm">
                    @csrf
                    <div id="new-password-section" style="display:none;">
                        <div class="mb-1 mt-1">
                            <label class="form-label label-text" for="new-password">New Password</label>
                            <input type="password" class="form-control" id="new-password" name="password"
                                   placeholder="Enter new password"/>
                        </div>
                        <div class="mb-1">
                            <label class="form-label label-text" for="confirm-new-password">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm-new-password"
                                   name="password_confirmation" placeholder="Confirm new password"/>
                        </div>
                        <div class="d-flex gap-1 justify-content-end mt-2">
                            <button type="reset" class="btn btn-outline-secondary fs-16">Cancel</button>
                            <button type="submit" class="btn btn-primary fs-16">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection

@section('vendor-script')
    {{-- Vendor js files --}}
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jszip.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/pdfmake.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/vfs_fonts.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.print.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
@endsection

@section('page-script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        const productsDataUrl = "{{ route('products.data') }}";
        const productsCreateUrl = "{{ route('products.create') }}";
    </script>


    <script>
        handleAjaxFormSubmit("#checkOldPasswordForm",
            {
                successMessage: "Password is correct",
                onSuccess: function () {
                    document.getElementById('old-password-section').style.display = 'none';
                    document.getElementById('new-password-section').style.display = 'block';
                }
            }
        )
        handleAjaxFormSubmit("#changePasswordForm",
            {
                successMessage: "Password changed successfully",
                onSuccess: function () {
                    location.reload()
                }
            }
        )

        $(document).on('submit', '#updateProfileForm', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(this);
            formData.append('_method', 'PUT');
            $.ajax({
                url: "{{ route('profile.update', auth()->user()->id) }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Toastify({
                        text: "Profile updated successfully!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28c76f", // success green
                    }).showToast();

                    // Optional: Update form fields with returned values if needed
                    if (response.user) {
                        $('#first_name').val(response.data.user.first_name);
                        $('#last_name').val(response.data.user.last_name);
                        $('#email').val(response.data.user.email);
                        $('#phone_number').val(response.data.user.phone_number);
                        $('#country_code').val(response.data.user.country_code_id);
                    }
                },
                error: function (xhr) {
                    let errorMessage = "Something went wrong!";
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        for (const key in errors) {
                            Toastify({
                                text: errors[key][0],
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#EA5455",
                                close: true,
                            }).showToast();
                        }
                    } else {
                        Toastify({
                            text: errorMessage,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#ff3e1d", // error red
                        }).showToast();
                    }

                }
            });
        });


    </script>

    <script>
        // Toastify CSS is already included above

        // Make a destroy-URL template and replace ":id" at runtime
        const destroyUrlTemplate = "{{ route('media.destroy', ['media' => ':id']) }}";

        // Seed currentMediaId from server (may be null)
        let currentMediaId = @json($initialMediaId);

        // Disable auto discover to avoid double-binding
        Dropzone.autoDiscover = false;

        const dz = new Dropzone("#avatarDropzone", {
            url: "{{ route('media.store',[
       'model_name'=> 'admin',
       'model'=> auth()->user()->id
      ]) }}",
            method: "post",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 2, // MB
            acceptedFiles: "image/jpeg,image/png,image/webp",
            addRemoveLinks: true,
            dictRemoveFile: "Remove",
            headers: {"X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            init: function () {
                this.on("maxfilesexceeded", function (file) {
                    this.removeAllFiles();
                    this.addFile(file);
                });

                this.on("success", function (file, response) {
                    // Expect the backend to return at least { url, id }.
                    // Try a few common shapes:
                    const newId =
                        response?.data?.id ??
                        null;

                    if (response?.data.url) {
                        document.getElementById("userAvatar").src = response.data.url;
                    }
                    if (newId) currentMediaId = newId;

                    Toastify({
                        text: "Avatar updated successfully",
                        duration: 2500, gravity: "top", position: "right",
                        backgroundColor: "#28c76f",
                    }).showToast();

                    setTimeout(() => this.removeFile(file), 1200);
                });

                this.on("error", function (file, errorMessage, xhr) {
                    let msg = errorMessage;
                    try {
                        if (xhr && xhr.responseText) {
                            const json = JSON.parse(xhr.responseText);
                            msg = json.message || msg;
                        }
                    } catch (e) {
                    }
                    Toastify({
                        text: msg || "Upload failed",
                        duration: 3000, gravity: "top", position: "right",
                        backgroundColor: "#ff3e1d",
                    }).showToast();
                    this.removeFile(file);
                });
            },
        });

        // Helper to build destroy URL
        function destroyUrl(id) {
            return destroyUrlTemplate.replace(':id', encodeURIComponent(String(id)));
        }

        // Remove avatar (only if we have an id)
        document.getElementById("removeAvatarBtn").addEventListener("click", function () {
            if (!currentMediaId) {
                Toastify({
                    text: "No avatar to remove",
                    duration: 2500, gravity: "top", position: "right",
                    backgroundColor: "#ff9f43",
                }).showToast();
                return;
            }

            $.ajax({
                url: destroyUrl(currentMediaId),
                method: "POST",
                data: {_method: "DELETE", _token: "{{ csrf_token() }}"},
                success: function (resp) {
                    // Reset media id and image
                    currentMediaId = null;
                    document.getElementById("userAvatar").src = resp?.fallback ?? "{{ asset('images/default-user.png') }}";
                    Toastify({
                        text: "Avatar removed",
                        duration: 2500, gravity: "top", position: "right",
                        backgroundColor: "#28c76f",
                    }).showToast();
                },
                error: function (xhr) {
                    let msg = "Failed to remove avatar";
                    if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                    Toastify({
                        text: msg,
                        duration: 3000, gravity: "top", position: "right",
                        backgroundColor: "#ff3e1d",
                    }).showToast();
                }
            });
        });
    </script>


    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/app-product-list.js') }}?v={{ time() }}"></script>
@endsection
