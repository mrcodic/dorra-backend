@extends('layouts/contentLayoutMaster')

@section('title', 'Edit User')
@section('main-page', 'Users')
@section('sub-page', 'Edit User')

@section('vendor-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/animate/animate.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
@endsection

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-sweet-alerts.css')) }}">
@endsection

@section('content')
<section class="app-user-view-account " style="height: 100vh;">
    <div class="row h-100">
        <!-- User Sidebar -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0 h-100">
            <!-- User Card -->
            <div class="card h-100">
                <div class="card-body">

                    <div class="d-flex flex-column gap-1 my-1">
                        <div class="d-flex align-items-center gap-1">

                            <i data-feather="calendar" class="font-medium-2"></i>
                            <h4 class="mb-0">Joined 13 Oct 2024</h4>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <i data-feather="briefcase" class="font-medium-2"></i>
                            <h4 class="mb-0">Basic Plan</h4>
                        </div>
                        <!-- Account Status Toggle -->
                        <div class="">
                            <div class="form-check form-switch  border rounded-3 p-1 d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-column">
                                    <label class="form-check-label text-dark" for="account_status">Account Active</label>
                                    <span class="active-label rounded-3 status-label primary-text-color text-center d-flex justify-content-center align-items-center">Active</span>
                                </div>

                                <input class="form-check-input" type="checkbox" id="account_status" name="account_status" checked>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <a href="javascript:;" class="btn btn-primary me-1 w-100" data-bs-target="#editUser"
                            data-bs-toggle="modal">
                            Edit User
                        </a>
                        <button class="btn btn-outline-danger me-1 w-100">
                            Delete User
                        </button>
                    </div>
                </div>
            </div>
            <!-- /User Card -->

        </div>
        <!--/ User Sidebar -->

        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1 h-100">
            <div class="card h-100">
                <div class="card-body">
                    <ul class="nav nav-tabs border-bottom-0">
                        <li class="nav-item">
                            <a class="nav-link active custom-tab" data-bs-toggle="tab" href="#tab1">Account Information</a>
                        </li>
                        <li> <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab2">Notification</a></li>
                        <li class="nav-item">
                            <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab3">Security</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab4">Teams</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <!-- tab 1 content -->
                        <div class="tab-pane fade show active" id="tab1">

                            <div class="d-flex align-items-center justify-content-between ">
                                <img
                                    class="img-fluid rounded-circle mb-2"
                                    src="{{$model->image?->getUrl() ?? asset('images/portrait/small/avatar-s-2.jpg')}}"
                                    height="48"
                                    width="48"
                                    alt="User avatar" />
                                <div>
                                    <button class="btn bg-white text-danger fs-5">Remove Photo</button>
                                    <button class="btn btn-outline-secondary fs-5">Change Photo</button>
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
                            </div>
                        </div>
                        <!-- Tab 2 content -->
                        <div class="tab-pane fade" id="tab2">
                            <h5 class="text-black fs-4">Notify me when</h5>

                            <!-- Checkbox options -->
                            <div class="form-check my-1">
                                <input class="form-check-input" type="checkbox" value="" id="option1">
                                <label class="form-check-label fs-16 text-black" for="option1">
                                    Option 1
                                </label>
                            </div>
                            <div class="form-check my-1">
                                <input class="form-check-input" type="checkbox" value="" id="option2">
                                <label class="form-check-label fs-16 text-black" for="option2">
                                    Option 2
                                </label>
                            </div>
                            <div class="form-check my-1">
                                <input class="form-check-input" type="checkbox" value="" id="option3">
                                <label class="form-check-label fs-16 text-black" for="option3">
                                    Option 3
                                </label>
                            </div>

                            <!-- Divider -->
                            <hr class="my-2">

                            <!-- Mobile App Notifications toggle -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="">
                                    <span class="fs-4 text-black d-block">Mobile app notifications</span>
                                    <span class="fs-16">Receive notifications whenever something requires your attention</span>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="mobileNotifToggle">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="">
                                    <span class="fs-4 text-black d-block">Email Notification</span>
                                    <span class="fs-16">Receive email whenever something requires your attention</span>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="mobileNotifToggle">
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex gap-1 justify-content-end mt-2">
                                <button type="button" class="btn btn-outline-secondary  place-order fs-16">Cancel</button>
                                <button type="button" class="btn btn-primary  place-order fs-16">Save</button>

                            </div>
                        </div>
                        <!-- tab 3 content -->
                        <div class="tab-pane fade" id="tab3"> <!-- Total Reviews Section -->
                            <div class="mb-1">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label label-text" for="password">New Password</label>

                                </div>
                                <div class="input-group input-group-merge form-password-toggle">
                                    <input
                                        type="password"
                                        class="form-control form-control-merge"
                                        id="password"
                                        name="password"
                                        tabindex="2"
                                        placeholder="Enter your new password"
                                        aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                </div>
                            </div>

                            <div class="mb-1">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label label-text" for="login-password">Confirm New Password</label>

                                </div>
                                <div class="input-group input-group-merge form-password-toggle">
                                    <input
                                        type="password"
                                        class="form-control form-control-merge"
                                        id="login-password"
                                        name="confirm-password"
                                        tabindex="2"
                                        placeholder="Confirm your new password"
                                        aria-describedby="login-password" />
                                    <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                </div>
                            </div>
                            <!-- Buttons -->
                            <div class="d-flex gap-1 justify-content-end mt-2">
                                <button type="button" class="btn btn-outline-secondary place-order fs-16">Cancel</button>
                                <button type="button" class="btn btn-primary place-order fs-16">Save</button>

                            </div>
                        </div>
                        <!-- tab 4 content -->
                        <div class="tab-pane fade" id="tab4"> <!-- Total Reviews Section -->

                            <div class="d-flex justify-content-between align-items-end ">
                                <!-- left: Icon and Info -->
                                <div class=" border rounded-3 p-1  d-flex gap-2 align-items-center justify-content-start w-50">
                                    <div class="">
                                        <i data-feather="users" class="text-primary"></i> <!-- User icon -->
                                    </div>
                                    <div class=" flex-grow-1">

                                        <h5>John Doe’s Team</h5>
                                        <div class="d-flex align-items-center ">
                                            <i data-feather="calendar"> </i> Joined 13 Oct 2024
                                        </div>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-1 justify-content-end mt-2">
                                    <button type="button" class="btn bg-white text-danger  place-order fs-16">Remove</button>
                                    <button type="button" class="btn btn-outline-secondary place-order fs-16" data-bs-toggle="modal" data-bs-target="#teamAccessModal">
                                        Show
                                    </button>

                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--/ User Content -->
    </div>


    <div class="modal fade" id="teamAccessModal" tabindex="-1" aria-labelledby="teamAccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content px-2 pb-2">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
            
                    <h5 class="modal-title fw-bold" id="teamAccessModalLabel">John Doe’s Team</h5>

               

                <!-- Invite input -->
                <div class=" mb-2 d-flex justify-content-between">
                    <input type="email" class="form-control w-75" placeholder="Add people by their email address">
                    <button class="btn btn-primary" type="button">Invite</button>
                </div>

                <!-- Who has access -->
                <h5 class="fw-semibold fs-16">Who has access to this file</h5>

                <!-- Members list -->
                <div>
                    <!-- Owner -->
                    <div class="d-flex justify-content-between align-items-center  py-2">
                        <div class="d-flex align-items-start gap-2">
                            <img src="{{asset('images/avatar.png')}}" class="rounded-circle" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <div class="fw-semibold text-black fs-5">John Doe</div>
                                <small class="">john@example.com</small>
                            </div>
                        </div>
                        <span class="border rounded-3 px-3 py-1">Creator</span>
                    </div>

                    <!-- Other Members -->
                    <div class="d-flex justify-content-between align-items-center  py-2">
                        <div class="d-flex align-items-start gap-2 w-75">
                            <img src="{{asset('images/avatar.png')}}" class="rounded-circle" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <div class="fw-semibold text-black fs-5">Jane Smith</div>
                                <small class="">jane@example.com</small>
                            </div>
                        </div>
                        <select class="form-select" style="width: 17%">
                            <option value="view">View</option>
                            <option value="edit">Edit</option>
                        </select>
                    </div>

                    <!-- Add more members here as needed -->
                </div>

                <!-- General access -->
                <div class="mt-4">
                    <h6 class="fw-semibold">General access</h6>
                    <select class="form-select mb-3">
                        <option>Anyone with the link can view this file</option>
                        <option>Anyone with the link can edit this file</option>
                        <option>Restricted</option>
                    </select>
                </div>

                <!-- Footer buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-outline-secondary d-flex justify-content-center align-items-center text-dark gap-1"><i data-feather="link-2"></i>Copy Link</button>
                    <button class="btn btn-primary">Done</button>
                </div>
            </div>
        </div>
    </div>

</section>


@endsection

@section('vendor-script')
{{-- Vendor js files --}}
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
{{-- data table --}}
<script src="{{ asset(mix('vendors/js/extensions/moment.min.js')) }}"></script>
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
<script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/extensions/polyfill.min.js')) }}"></script>
@endsection

@section('page-script')

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/modal-edit-user.js') }}?v={{ time() }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-user-view-account.js')) }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-user-view.js')) }}"></script>
@endsection