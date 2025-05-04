@extends('layouts/contentLayoutMaster')

@section('title', 'Settings')
@section('main-page', 'Settings')

@section('vendor-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
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
<div class="card d-flex flex-row">
    {{-- Left Side: Vertical Tabs --}}
    <div class="nav flex-column nav-pills  px-2 py-3 gap-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
        <button class="btn profile-tab active" id="tab1-tab" data-bs-toggle="pill" data-bs-target="#tab1" type="button" role="tab" aria-controls="tab1" aria-selected="true">
            Account Information
        </button>
        <button class="btn profile-tab" id="tab2-tab" data-bs-toggle="pill" data-bs-target="#tab2" type="button" role="tab" aria-controls="tab2" aria-selected="false">
            Security
        </button>
    </div>

    {{-- Right Side: Tab Content --}}
    <div class="tab-content flex-grow-1 p-3" id="v-pills-tabContent">
        <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">

            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-1">
                    <img
                        id="userAvatar"
                        class="img-fluid rounded-circle mb-2"
                        src="{{ asset('images/portrait/small/avatar-s-2.jpg') }}"
                        height="48"
                        width="48"
                        alt="User avatar" />
                </div>
                <div>
                    <button class="btn bg-white text-danger fs-5">Remove Photo</button>

                    <!-- Hidden file input -->
                    <input type="file" accept="image/*" id="changeImageInput" style="display: none;" />
                    <button type="button" class="btn btn-outline-secondary fs-5" id="changePhotoBtn">Change Photo</button>
                </div>
            </div>

            <hr class="my-2" />
            <!-- First Name and Last Name -->
            <div class="row mb-2">
                <div class="col-md-10">
                    <label for="first_name" class="form-label label-text ">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter first name" required>
                </div>


                <!-- Button -->
                <div class="col-md-2 d-flex gap-1 justify-content-end mt-2">
                    <button type="button" class="btn btn-outline-secondary  place-order fs-16">Edit</button>
                </div>
            </div>
            <div class="row mb-2">

                <div class="col-md-10">
                    <label for="last_name" class="form-label label-text ">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter last name" required>
                </div>

                <!-- Button -->
                <div class="col-md-2 d-flex gap-1 justify-content-end mt-2">
                    <button type="button" class="btn btn-outline-secondary  place-order fs-16">Edit</button>
                </div>
            </div>

            <!-- Email Address -->
            <div class="row mb-2">
                <div class="col-md-10">
                    <label for="email" class="form-label label-text ">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" required>
                </div>
                <!-- Button -->
                <div class="col-md-2 d-flex gap-1 justify-content-end mt-2">
                    <button type="button" class="btn btn-outline-secondary  place-order fs-16">Edit</button>
                </div>
            </div>
            <!-- Phone Number (Country Code + Number) -->
            <div class="row mb-2">
                <div class="col-md-2">
                    <label for="country_code" class="form-label label-text ">Country Code</label>
                    <select id="country_code" name="country_code" class="form-select" required>
                        <option value="+1">🇺🇸 +1</option>
                        <option value="+44">🇬🇧 +44</option>
                        <option value="+20">🇪🇬 +20</option>
                        <!-- Add more countries as needed -->
                    </select>
                </div>
                <div class="col-md-8">
                    <label for="phone_number" class="form-label label-text ">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="Enter phone number" required>
                </div>
                <!-- Buttons -->
                <div class="col-md-2 d-flex gap-1 justify-content-end mt-2">
                    <button type="button" class="btn btn-outline-secondary  place-order fs-16">Edit</button>
                </div>
            </div>

        </div>
        <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
            <h2 class="mb-2 text-black">Change Password</h2>

            {{-- Old Password --}}
            <div id="old-password-section">
                <div class="mb-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label label-text" for="login-password">Old Password</label>

                    </div>
                    <div class="input-group input-group-merge form-password-toggle">
                        <input
                            type="password"
                            class="form-control form-control-merge"
                            id="old-password"
                            name="old-password"
                            placeholder="Enter old password" />
                    </div>
                    <div class="w-100 text-end">
                        <a href="#" class="text-primary text-decoration-underline fs-6 text-end" style="color: #24B094 !important;">Forgot password?</a>
                    </div>
                </div>

                <div class="d-flex gap-1 justify-content-end mt-2">
                    <button type="button" class="btn btn-outline-secondary fs-16">Cancel</button>
                    <button type="button" class="btn btn-primary fs-16" id="nextBtn">Next</button>
                </div>
            </div>

            {{-- New Password Fields (Initially hidden) --}}
            <div id="new-password-section" style="display: none;">
                <div class="mb-1 mt-1">
                    <label class="form-label label-text" for="new-password">New Password</label>
                    <input type="password" class="form-control" id="new-password" placeholder="Enter new password" />
                </div>
                <div class="mb-1">
                    <label class="form-label label-text" for="confirm-new-password">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm-new-password" placeholder="Confirm new password" />
                </div>
                <div class="d-flex gap-1 justify-content-end mt-2">
                    <button type="button" class="btn btn-outline-secondary fs-16">Cancel</button>
                    <button type="button" class="btn btn-primary fs-16">Save</button>
                </div>
            </div>
        </div>
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
    document.getElementById('changePhotoBtn').addEventListener('click', function () {
        document.getElementById('changeImageInput').click();
    });

    document.getElementById('changeImageInput').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('userAvatar').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<script>
    document.getElementById('nextBtn').addEventListener('click', function() {
        const oldPassword = document.getElementById('old-password').value;

        // Simulate password validation
        if (oldPassword === '123456') {
            document.getElementById('old-password-section').style.display = 'none';
            document.getElementById('new-password-section').style.display = 'block';
        } else {
            Toastify({
                text: "Incorrect old password!",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#ff3e1d",
            }).showToast();
        }
    });
</script>


{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-product-list.js') }}?v={{ time() }}"></script>
@endsection