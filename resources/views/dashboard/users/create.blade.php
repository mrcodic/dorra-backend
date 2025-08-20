@extends('layouts/contentLayoutMaster')

@section('title', 'Create User')
@section('main-page', 'Users')
@section('sub-page', 'Add New User')

@section('vendor-style')
<!-- Vendor css files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/wizard/bs-stepper.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/spinner/jquery.bootstrap-touchspin.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/toastr.min.css')) }}">
@endsection

@section('page-style')
<!-- Page css files -->
<link rel="stylesheet" href="{{ asset(mix('css/base/pages/app-ecommerce.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/pickers/form-pickadate.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-wizard.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-toastr.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-number-input.css')) }}">
@endsection

@section('content')
<div class="bs-stepper checkout-tab-steps">
    <form id="checkout-form" class="bs-stepper checkout-tab-steps add-new-user" method="post"
        enctype="multipart/form-data" action="{{ route('users.store') }}">
        @csrf
        <div class="user-media-ids"></div>
        <!-- Wizard starts -->
        <div class="bs-stepper-header">
            <div class="step" data-target="#step-info" role="tab" id="step-info-trigger">
                <button type="button" class="step-trigger d-flex align-items-center gap-1">
                    <span class="bs-stepper-box">
                        01
                    </span>
                    <span class="bs-stepper-label">
                        <span class="bs-stepper-title">User Information</span>
                    </span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#step-password" role="tab" id="step-password-trigger">
                <button type="button" class="step-trigger d-flex align-items-center gap-1">
                    <span class="bs-stepper-box">
                        02
                    </span>
                    <span class="bs-stepper-label">
                        <span class="bs-stepper-title">Password</span>
                    </span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#step-address" role="tab" id="step-address-trigger">
                <button type="button" class="step-trigger d-flex align-items-center gap-1">
                    <span class="bs-stepper-box">
                        03
                    </span>
                    <span class="bs-stepper-label">
                        <span class="bs-stepper-title">Address</span>
                    </span>
                </button>
            </div>
        </div>
        <!-- Wizard ends -->

        <div class="bs-stepper-content">
            <!-- step 1 -->
            <div id="step-info" class="content" role="tabpanel" aria-labelledby="step-info-trigger">

                <!-- Avatar Upload with Dropzone -->
                <div class="mb-2 text-center">
                    <div id="avatar-dropzone" class="dropzone" style="cursor:pointer;">
                        <div class="dz-message" data-dz-message>
                            <button type="button" class="btn btn-outline-primary" data-bs-target="#avatar-dropzone">
                                Upload Photo
                            </button>
                        </div>
                    </div>
                </div>



                <!-- First Name and Last Name -->
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label label-text ">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control"
                            placeholder="Enter first name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label label-text ">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control"
                            placeholder="Enter last name" required>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="mb-2">
                    <label for="email" class="form-label label-text ">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address"
                        required>
                </div>

                <!-- Phone Number (Country Code + Number) -->
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="phone-code" class="form-label label-text ">Country Code</label>
                        <select id="phone-code" name="country_code_id" class="form-select" required>
                            @foreach($associatedData['country_codes'] as $countryCode)
                            <option value="{{ $countryCode->id }}" data-phone-code="{{ $countryCode->phone_code }}">{{
                                $countryCode->phone_code }} ({{ $countryCode->iso_code }})</option>
                            @endforeach
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label for="phone_number" class="form-label label-text ">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control"
                            placeholder="Enter phone number" required>
                        <input type="hidden" name="full_phone_number" id="full_phone_number" />

                    </div>
                </div>

                <!-- Account Status Toggle -->
                <div class="">
                    <div
                        class="form-check form-switch border rounded-3 p-1 d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <label class="form-check-label text-dark" for="account_status">Account Active</label>
                            <span
                                class="active-label rounded-3 status-label primary-text-color text-center d-flex justify-content-center align-items-center">Active</span>
                        </div>

                        <!-- Visible Toggle -->
                        <input class="form-check-input" type="checkbox" id="account_status_toggle" checked>
                        <!-- Hidden field to hold 1 or 0 -->
                        <input type="hidden" name="status" id="account_status" value="1">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-1 justify-content-end mt-2">

                    <button type="button" class="btn btn-primary btn-next place-order">Next</button>
                </div>

            </div>

            <!-- step 2 -->
            <div id="step-password" class="content" role="tabpanel" aria-labelledby="step-password-trigger">
                <div id="checkout-address" class="list-view product-checkout">

                    <!-- Checkout Customer Address Right starts -->
                    <div class="mb-1">
                        <div class="d-flex justify-content-between">
                            <label class="form-label label-text" for="password">Password</label>

                        </div>
                        <div class="input-group input-group-merge form-password-toggle">
                            <input type="password" class="form-control form-control-merge" id="password" name="password"
                                tabindex="2" placeholder="Enter password" aria-describedby="password" />
                            <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                        </div>
                    </div>

                    <div class="mb-1">
                        <div class="d-flex justify-content-between">
                            <label class="form-label label-text" for="login-password">Confirm Password</label>

                        </div>
                        <div class="input-group input-group-merge form-password-toggle">
                            <input type="password" class="form-control form-control-merge" id="login-password"
                                name="password_confirmation" tabindex="2" placeholder="Confirm Password"
                                aria-describedby="login-password" />
                            <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                        </div>
                    </div>
                    <!-- Checkout Customer Address Right ends -->
                </div>
                <!-- Buttons -->
                <div class="d-flex gap-1 justify-content-end mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-prev place-order">Back</button>
                    <button type="button" class="btn btn-primary btn-next place-order">Next</button>

                </div>
            </div>
            <!-- step 3 -->
            {{-- <div id="step-payment" class="content" role="tabpanel" aria-labelledby="step-payment-trigger">--}}
                {{-- <div id="checkout-payment" class="list-view product-checkout p-3" onsubmit="return false;">--}}
                    {{--
                    <!-- Cardholder Name (Optional) Label -->--}}
                    {{-- <div class="mb-1">--}}
                        {{-- <label class="form-label">Cardholder Name (Optional)</label>--}}
                        {{-- <input type="text" name="label" id="label" class="form-control"
                            placeholder="Enter cardholder name" />--}}
                        {{-- </div>--}}
                    {{-- <div class="mb-1">--}}
                        {{-- <label class="form-label">Card Number</label>--}}
                        {{-- <input type="text" name="label" id="label" class="form-control"
                            placeholder="Enter Card Number" />--}}
                        {{-- </div>--}}
                    {{--
                    <!-- Country and State -->--}}
                    {{-- <div class="d-flex justify-content-between gap-1 mb-1">--}}
                        {{-- <div class="w-50">--}}
                            {{-- <label class="form-label">Expiration Date</label>--}}
                            {{-- <input type="date" name="label" id="label" class="form-control"
                                placeholder="Expiration Date" />--}}
                            {{-- </div>--}}
                        {{-- <div class="w-50">--}}
                            {{-- <label class="form-label">CVV</label>--}}
                            {{-- <input type="text" name="label" id="label" class="form-control"
                                placeholder="Enter CVV" />--}}
                            {{-- </div>--}}
                        {{-- </div>--}}

                    {{--
                    <!-- Address Line -->--}}
                    {{-- <div class="mb-1">--}}
                        {{-- <label for="line" class="form-label">Email Address</label>--}}
                        {{-- <input type="text" name="line" id="line" class="form-control"
                            placeholder="Enter email address" />--}}
                        {{-- </div>--}}

                    {{-- <div class="d-flex gap-1 justify-content-end">--}}
                        {{-- <button type="button"
                            class="btn btn-outline-secondary  btn-prev place-order">Back</button>--}}
                        {{-- <button type="button" class="btn btn-primary  btn-next place-order">Next</button>--}}

                        {{-- </div>--}}
                    {{--
                </div>--}}
                {{-- </div>--}}
            <!-- step 4 -->
            <div id="step-address" class="content" role="tabpanel" aria-labelledby="step-address-trigger">
                <div id="checkout-payment" class="list-view product-checkout" onsubmit="return false;">
                    <!-- Address Label -->
                    <div class="mb-1">
                        <label for="label" class="form-label">Address Label</label>
                        <input type="text" name="label" id="label" class="form-control"
                            placeholder="Enter Address Label" />
                    </div>

                    <!-- Country and State -->
                    <div class="d-flex justify-content-between gap-1 mb-1">
                        <div class="w-50">
                            <label for="country-select" class="form-label">Country</label>
                            <select id="country-select" class="form-select country-select">
                                <option value="">Select Country</option>
                                @foreach($associatedData['countries'] as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-50">
                            <label for="state_id" class="form-label">State</label>
                            <select name="state_id" id="state_id" class="form-select state-select">
                                <option value="">Select State</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address Line -->
                    <div class="mb-1">
                        <label for="line" class="form-label">Address Line</label>
                        <input type="text" name="line" id="line" class="form-control"
                            placeholder="Enter Address Line" />
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-1 justify-content-end mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-prev place-order">Back</button>
                        <button type="submit" class="btn btn-primary btn-next place-order saveChangesButton"
                            id="saveChangesButton">
                            <span class="btn-text">Save</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>


            </div>
    </form>
</div>

</div>
@endsection

@section('vendor-script')
<!-- Vendor js files -->
<script src="{{ asset(mix('vendors/js/forms/wizard/bs-stepper.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/spinner/jquery.bootstrap-touchspin.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>
@endsection

@section('page-script')
<!-- Page js files -->
<script src="{{ asset(mix('js/scripts/pages/app-ecommerce-checkout.js')) }}"></script>
<script>
    Dropzone.autoDiscover = false;

    const avatarDropzone = new Dropzone("#avatar-dropzone", {
        url: "{{ route('media.store') }}",
        paramName: "file",
        maxFiles: 1,
        acceptedFiles: "image/*",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        addRemoveLinks: true,
        init: function () {
            this.on("success", function (file, response) {
                if (response.data.success && response.data.url) {
                    $("#uploadedImage").val(response.data.url);
                }
            });
            this.on("removedfile", function () {
                $("#uploadedImage").val("");
            });
        } ,
        success: function (file, response) {
            // Add hidden input for submitted form
            let hidden = document.createElement('input');
            hidden.type = "hidden";
            hidden.name = "image_id"; // backend expects image_id
            hidden.value = response.data.id;
            file._hiddenInput = hidden;
            document.querySelector('.user-media-ids').appendChild(hidden);
        },
    });
</script>

<script !src="">
    $(document).ready(function () {
        // jQuery Validation for Add User form
        $(".add-new-user").validate({
            rules: {
                first_name: {
                    required: true,
                    maxlength: 255
                },
                last_name: {
                    required: true,
                    maxlength: 255
                },
                email: {
                    required: true,
                    email: true
                },
                phone_number: {
                    required: true,
                    minlength: 7,
                    maxlength: 15,
                    digits: true
                },
                country_code_id: {
                    required: true
                },
                password: {
                    required: true,
                    minlength: 8,
                    pwcheck: true
                },
                password_confirmation: {
                    required: true,
                    equalTo: "#password"
                },
                status: {
                    required: true
                },
                "addresses[0][label]": {
                    required: true,
                    minlength: 3
                },
                "addresses[0][line]": {
                    required: true,
                    minlength: 3
                },
                "addresses[0][state_id]": {
                    required: true
                }
            },
            messages: {
                first_name: "First name is required",
                last_name: "Last name is required",
                email: {
                    required: "Email is required",
                    email: "Enter a valid email"
                },
                phone_number: {
                    required: "Phone number is required",
                    digits: "Only digits allowed"
                },
                password: {
                    required: "Password is required",
                    minlength: "Password must be at least 8 characters"

                },
                password_confirmation: {
                    required: "Confirm your password",
                    equalTo: "Passwords do not match"
                },
                "addresses[0][label]": "Address label is required",
                "addresses[0][line]": "Address line is required",
                "addresses[0][state_id]": "Please select a state"
            },
            errorPlacement: function (error, element) {
                error.addClass("text-danger small");
                error.insertAfter(element);
            },
            submitHandler: function (form) {
                form.submit(); // Submit if valid
            }
        });
        // ✅ Strong password check
        $.validator.addMethod("pwcheck", function(value) {
            return /[A-Z]/.test(value) &&  // Uppercase
                /[a-z]/.test(value) &&  // Lowercase
                /\d/.test(value) &&     // Number
                /[^A-Za-z0-9]/.test(value); // Symbol
        });
    });

</script>
<script>
    const toggle = document.getElementById('account_status_toggle');
    const hiddenInput = document.getElementById('account_status');

    toggle.addEventListener('change', function () {
        const label = document.querySelector('.active-label');
        hiddenInput.value = this.checked ? '1' : '0';

        if (this.checked) {
            label.textContent = "Active";
            label.classList.remove('bg-secondary', 'text-white', 'bg-gray');
            label.classList.add('primary-text-color');
        } else {
            label.textContent = "Blocked";
            label.classList.remove('primary-text-color');
            label.classList.add('bg-secondary', 'text-white');
        }
    });


    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatarInput');
        const avatarCard = document.getElementById('avatarCard');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarName = document.getElementById('avatarName');

        avatarCard.addEventListener('click', () => {
            avatarInput.click(); // Open file picker
        });

        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    avatarPreview.src = e.target.result; // Update avatar image
                };

                reader.readAsDataURL(file);
                avatarName.textContent = file.name; // Show the file name
            }
        });
    });


    $(document).on("change", ".country-select", function () {

        const countryId = $(this).val();
        const stateSelect = $(".state-select");
        if (countryId) {
            $.ajax({
                url: "{{ route('states') }}",  // Make sure this is wrapped in quotes for the URL
                method: "GET",
                data: {
                    "filter[country_id]": countryId  // Corrected way to pass the data
                },
                success: function (response) {
                    stateSelect
                        .empty()
                        .append('<option value="">Select State</option>');
                    $.each(response.data, function (index, state) {
                        stateSelect.append(
                            `<option value="${state.id}">${state.name}</option>`
                        );
                    });
                },
                error: function () {
                    stateSelect
                        .empty()
                        .append('<option value="">Error loading states</option>');
                },
            });

        } else {
            stateSelect
                .empty()
                .append('<option value="">Select State</option>');
        }
    });

    $(".add-new-user").submit(function (event) {
        event.preventDefault();
        const saveButton = $('.saveChangesButton');
        const saveLoader = $('.saveLoader');
        const saveButtonText = $('.saveChangesButton .btn-text');
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        var form = $(this);

        var selectedOption = $("#phone-code option:selected");
        var phoneCode = selectedOption.data("phone-code");
        var phoneNumber = $("#phone_number").val();
        var fullPhoneNumber = phoneCode + phoneNumber.replace(/\D/g, "");
        $("#full_phone_number").val(fullPhoneNumber);

        var actionUrl = form.attr("action");
        let formData = new FormData(form[0]);
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    localStorage.setItem('UserAdded',1);
                    window.location.href = "/users";

                }
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
            },
            error: function (xhr) {
                var errors = xhr.responseJSON.errors;
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        Toastify({
                            text: errors[key][0],
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455", // red for errors
                            close: true,
                        }).showToast();
                    }
                }
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
            },

        });
    });

</script>
@endsection