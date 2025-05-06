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
    <form id="checkout-form" class="bs-stepper checkout-tab-steps" enctype="multipart/form-data" action="{{ route("users.store") }}">
        <!-- Wizard starts -->
        <div class="bs-stepper-header">
            <div class="step" data-target="#step-info" role="tab" id="step-info-trigger">
                <button type="button" class="step-trigger">
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
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-box">
                        02
                    </span>
                    <span class="bs-stepper-label">
                        <span class="bs-stepper-title">Password</span>
                    </span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#step-payment" role="tab" id="step-payment-trigger">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-box">
                        03
                    </span>
                    <span class="bs-stepper-label">
                        <span class="bs-stepper-title">Payment</span>
                    </span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#step-address" role="tab" id="step-address-trigger">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-box">
                        04
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

                <!-- Avatar Upload -->
                <div class="mb-2 text-center">
                    <!-- Hidden input -->
                    <input type="file" id="avatarInput" name="image" accept="image/*" style="display: none;" />

                    <!-- Clickable avatar card -->
                    <div id="avatarCard" style="width: 150px; margin: auto; cursor: pointer;">
                        <img id="avatarPreview" src="{{asset('images/avatar.png')}}" alt="Avatar" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;">
                        <div id="avatarName" class="mt-2 " style="width: 150px; margin: auto; cursor: pointer; border: 1px solid #ccc; border-radius: 10px; padding: 10px;">
                            Upload Photo
                        </div>
                    </div>
                </div>


                <!-- First Name and Last Name -->
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label label-text ">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter first name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label label-text ">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter last name" required>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="mb-2">
                    <label for="email" class="form-label label-text ">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" required>
                </div>

                <!-- Phone Number (Country Code + Number) -->
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="country_code" class="form-label label-text ">Country Code</label>
                        <select id="country_code" name="country_code" class="form-select" required>
                            @foreach($associatedData['country_codes'] as $countryCode)
                                <option value="{{ $countryCode->id }}" data-phone-code="{{ $countryCode->phone_code }}">{{ $countryCode->phone_code }} ({{ $countryCode->iso_code }})</option>
                            @endforeach
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label for="phone_number" class="form-label label-text ">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="Enter phone number" required>
                    </div>
                </div>

                <!-- Account Status Toggle -->
                <div class="">
                    <div class="form-check form-switch  border rounded-3 p-1 d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <label class="form-check-label text-dark" for="account_status">Account Active</label>
                            <span class="active-label rounded-3 status-label primary-text-color text-center d-flex justify-content-center align-items-center">Active</span>
                        </div>

                        <input class="form-check-input" type="checkbox" id="account_status" name="status" checked>
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
                            <input
                                type="password"
                                class="form-control form-control-merge"
                                id="password"
                                name="password"
                                tabindex="2"
                                placeholder="Enter password"
                                aria-describedby="password" />
                            <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                        </div>
                    </div>

                    <div class="mb-1">
                        <div class="d-flex justify-content-between">
                            <label class="form-label label-text" for="login-password">Confirm Password</label>

                        </div>
                        <div class="input-group input-group-merge form-password-toggle">
                            <input
                                type="password"
                                class="form-control form-control-merge"
                                id="login-password"
                                name="password_confirmation"
                                tabindex="2"
                                placeholder="Confirm Password"
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
            <div id="step-payment" class="content" role="tabpanel" aria-labelledby="step-payment-trigger">
                <div id="checkout-payment" class="list-view product-checkout p-3" onsubmit="return false;">
                    <!-- Cardholder Name (Optional) Label -->
                    <div class="mb-1">
                        <label class="form-label">Cardholder Name (Optional)</label>
                        <input type="text" name="label" id="addresses.*.label" class="form-control" placeholder="Enter cardholder name" />
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Card Number</label>
                        <input type="text" name="label" id="addresses.*.label" class="form-control" placeholder="Enter Card Number" />
                    </div>
                    <!-- Country and State -->
                    <div class="d-flex justify-content-between gap-1 mb-1">
                        <div class="w-50">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="label" id="addresses.*.label" class="form-control" placeholder="Expiration Date" />
                        </div>
                        <div class="w-50">
                            <label class="form-label">CVV</label>
                            <input type="text" name="label" id="addresses.*.label" class="form-control" placeholder="Enter CVV" />
                        </div>
                    </div>

                    <!-- Address Line -->
                    <div class="mb-1">
                        <label for="addresses.*.line" class="form-label">Email Address</label>
                        <input type="text" name="line" id="addresses.*.line" class="form-control" placeholder="Enter email address" />
                    </div>

                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary  btn-prev place-order">Back</button>
                        <button type="button" class="btn btn-primary  btn-next place-order">Next</button>

                    </div>
                </div>
            </div>
            <!-- step 4 -->
            <div id="step-address" class="content" role="tabpanel" aria-labelledby="step-address-trigger">
                <div id="checkout-payment" class="list-view product-checkout p-3" onsubmit="return false;">
                    <!-- Address Label -->
                    <div class="mb-1">
                        <label for="addresses.*.label" class="form-label">Address Label</label>
                        <input type="text" name="label" id="addresses.*.label" class="form-control" placeholder="Enter Address Label" />
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
                            <label for="addresses.*.state_id" class="form-label">State</label>
                            <select name="state_id" id="addresses.*.state_id" class="form-select state-select">
                                <option value="">Select State</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address Line -->
                    <div class="mb-1">
                        <label for="addresses.*.line" class="form-label">Address Line</label>
                        <input type="text" name="line" id="addresses.*.line" class="form-control" placeholder="Enter Address Line" />
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-1 justify-content-end mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-prev place-order">Back</button>
                        <button type="submit" class="btn btn-primary btn-next place-order">Add New User</button>
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
</script>
@endsection
