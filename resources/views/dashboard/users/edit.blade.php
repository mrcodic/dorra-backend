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
<style>
    #edit-user-dropzone .dz-image img {
        width: 100% !important;
        /* fill container */
        height: auto !important;
        /* keep aspect ratio */
        object-fit: contain;
        /* prevent cropping */
    }
</style>
@endsection

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-sweet-alerts.css')) }}">
@endsection

@section('content')
<section class="app-user-view-account">
    <div class="row h-100">
        <!-- User Sidebar -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0 h-100">
            <!-- User Card -->
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex flex-column gap-1 my-1">
                        <div class="d-flex align-items-center gap-1">

                            <i data-feather="calendar" class="font-medium-2"></i>
                            <h4 class="mb-0">Joined {{ $model->created_at->format('j M Y') }}</h4>
                        </div>
                        <div class="d-flex align-items-center gap-1">

                        </div>
                        <!-- Account Status Toggle -->
                        <div class="">
                            <div
                                class="form-check form-switch  border rounded-3 p-1 d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-column">
                                    <label class="form-check-label text-dark" for="account_status">Account
                                        Active</label>
                                    <span class=" rounded-3 status-label
                         {{ $model->status == " Active" ? "primary-text-color" : "" }} text-center d-flex
                                        justify-content-center align-items-center"
                                        style="background-color: {{ $model->status == " Active" ?"#D7EEDD" : "#F0F0F0"
                                        }};">
                                        {{ $model->status == "Active" ? "Active" : "Blocked" }}</span>
                                </div>

                                <input class="form-check-input" type="checkbox" id="account_status" name="status" {{
                                    $model->status === "Active" ? "checked" : "" }}>
                            </div>
                        </div>
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
                            <a class="nav-link active custom-tab" data-bs-toggle="tab" href="#tab1"
                                style="font-size: 14px;">Account
                                Information</a>
                        </li>
                        <li><a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab2"
                                style="font-size: 14px;">Notification</a></li>
                        <li class="nav-item">
                            <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab3"
                                style="font-size: 14px;">Security</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab4"
                                style="font-size: 14px;">Teams</a>
                        </li>

                    </ul>
                    <div class="tab-content mt-3">
                        <!-- tab 1 content -->
                        <div class="tab-pane fade show active" id="tab1">
                            <form action="{{ route('users.update',$model->id) }}" id="editUserForm" method="post">
                                @csrf
                                @method("PUT")
                                <div class="edit-user-media-ids"></div>
                                <input type="hidden" id="status" name="status">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div id="edit-user-dropzone" class="dropzone border rounded upload-card mb-1 col-12"
                                        style="cursor:pointer; min-height:150px;">
                                        <div class="dz-message" data-dz-message>
                                            <button type="button" class="btn btn-outline" style="color: #E74943"
                                                data-bs-target="#avatar-dropzone">Remove
                                                Photo</button>
                                            <button type="button" class="btn btn-outline"
                                                style="border: 1px solid #CED5D4; border-radius: 15px"
                                                data-bs-target="#avatar-dropzone">Change
                                                Photo</button>

                                        </div>
                                    </div>

                                    <!-- Hidden input for image_id -->
                                    <input type="hidden" name="image_id" id="uploadedImage"
                                        value="{{ $model->getFirstMedia('users')?->id ?? '' }}">

                                    <!-- Pass existing image info -->
                                    <input type="hidden" id="existingImageUrl"
                                        value="{{ $model->getFirstMediaUrl('users') }}">
                                    <input type="hidden" id="existingImageName"
                                        value="{{ $model->getFirstMedia('users')?->file_name ?? 'Current Photo' }}">


                                </div>


                                <!-- First Name and Last Name -->
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label label-text ">First Name</label>
                                        <input type="text" id="first_name" value="{{ $model->first_name }}"
                                            name="first_name" class="form-control" placeholder="Enter first name"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label label-text ">Last Name</label>
                                        <input type="text" id="last_name" value="{{ $model->last_name }}"
                                            name="last_name" class="form-control" placeholder="Enter last name"
                                            required>
                                    </div>
                                </div>

                                <!-- Email Address -->
                                <div class="mb-2">
                                    <label for="email" class="form-label label-text ">Email Address</label>
                                    <input type="email" id="email" value="{{ $model->email }}" name="email"
                                        class="form-control" placeholder="Enter email address" required>
                                </div>

                                <!-- Phone Number (Country Code + Number) -->
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label for="phone-code" class="form-label label-text ">Country Code</label>
                                        <select class="form-select" id="phone-code" name="country_code_id">
                                            @foreach($associatedData['country_codes'] as $countryCode)
                                            <option value="{{ $countryCode->id }}"
                                                data-phone-code="{{ $countryCode->phone_code }}"
                                                @selected($countryCode->id == $model->countryCode?->id)
                                                >
                                                {{ $countryCode->phone_code }} ({{ $countryCode->iso_code }})
                                            </option>

                                            @endforeach

                                            <!-- Add more countries as needed -->
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="phone_number" class="form-label label-text ">Phone
                                            Number</label>
                                        <input type="tel" id="phone_number" value="{{ $model->phone_number }}"
                                            name="phone_number" class="form-control" placeholder="Enter phone number"
                                            required>

                                        <input type="hidden" name="full_phone_number" id="full_phone_number" />

                                    </div>
                                </div>
                                <!-- Buttons -->
                                <div class="d-flex gap-1 justify-content-end mt-2">
                                    <button type="button" class="btn btn-outline-secondary  place-order fs-16">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary  place-order fs-16 saveChangesButton"
                                        id="saveChangesButton">
                                        <span class="btn-text">Save</span>
                                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                            role="status" aria-hidden="true"></span>
                                    </button>
                                </div>

                            </form>
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
                                    <span class="fs-16">Receive notifications whenever something requires your
                                        attention</span>
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
                                <button type="button" class="btn btn-outline-secondary  place-order fs-16">Cancel
                                </button>
                                <button type="button" class="btn btn-primary  place-order fs-16">Save</button>

                            </div>
                        </div>
                        <!-- tab 3 content -->
                        <div class="tab-pane fade" id="tab3">
                            <!-- Total Reviews Section -->
                            <form action="{{ route('users.change-password', $model->id) }}" method="post">
                                @csrf
                                @method("PUT")
                                <div class="mb-1">
                                    <div class="d-flex justify-content-between">
                                        <label class="form-label label-text" for="password">New Password</label>

                                    </div>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" class="form-control form-control-merge" id="password"
                                            name="password" tabindex="2" placeholder="Enter your new password"
                                            aria-describedby="password" />
                                        <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                    </div>
                                </div>

                                <div class="mb-1">
                                    <div class="d-flex justify-content-between">
                                        <label class="form-label label-text" for="login-password">Confirm New
                                            Password</label>

                                    </div>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" class="form-control form-control-merge"
                                            id="login-password" name="password_confirmation" tabindex="2"
                                            placeholder="Confirm your new password" aria-describedby="login-password" />
                                        <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                    </div>
                                </div>
                                <!-- Buttons -->
                                <div class="d-flex gap-1 justify-content-end mt-2">
                                    <button type="button" class="btn btn-outline-secondary place-order fs-16">Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary place-order fs-16">Save</button>

                                </div>
                            </form>

                        </div>
                        <!-- tab 4 content -->
                        <div class="tab-pane fade" id="tab4">
                            <!-- Total Reviews Section -->
                            @forelse($model->teams as $team)
                            <div class="d-flex justify-content-between align-items-end ">
                                <!-- left: Icon and Info -->
                                <div
                                    class=" border rounded-3 p-1  d-flex gap-2 align-items-center justify-content-start w-50">
                                    <div class="">
                                        <i data-feather="users" class="text-primary"></i> <!-- User icon -->
                                    </div>
                                    <div class=" flex-grow-1">

                                        <h5>{{ $team->owner->name }}’s Team</h5>
                                        <div class="d-flex align-items-center ">
                                            <i data-feather="calendar"> </i> Joined {{ $team->created_at->format("j M
                                            Y") }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-1 justify-content-end mt-2">
                                    <button type="button" class="btn bg-white text-danger  place-order fs-16">
                                        Remove
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary place-order fs-16"
                                        data-bs-toggle="modal" data-bs-target="#teamAccessModal"
                                        data-team-id="{{ $team->id }}">
                                        Show
                                    </button>

                                </div>
                            </div>
                            @empty
                            <div
                                style="padding: 50px; background-color: #f9f9f9; border-radius: 8px; border: 1px dashed #ccc; font-size: 1.2rem; color: #6c757d; margin-top: 20px; text-align: center;">
                                <p style="margin: 0; font-weight: 500; font-size: 1.1rem;">No teams
                                    yet.</p>
                            </div>

                            @endforelse
                        </div>




                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--/ User Content -->
    </div>


    <div class="modal fade" id="teamAccessModal" tabindex="-1" aria-labelledby="teamAccessModalLabel"
        aria-hidden="true">
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
                            <img src="{{asset('images/avatar.png')}}" class="rounded-circle" alt="Avatar"
                                style="width: 40px; height: 40px; object-fit: cover;">
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
                            <img src="{{asset('images/avatar.png')}}" class="rounded-circle" alt="Avatar"
                                style="width: 40px; height: 40px; object-fit: cover;">
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
                    <button
                        class="btn btn-outline-secondary d-flex justify-content-center align-items-center text-dark gap-1">
                        <i data-feather="link-2"></i>Copy Link
                    </button>
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
<script>
    Dropzone.autoDiscover = false;

        const editDropzone = new Dropzone("#edit-user-dropzone", {
            url: "{{ route('media.store') }}", // route that uploads new media
            paramName: "file",
            maxFiles: 1,
            acceptedFiles: "image/*",

            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            addRemoveLinks: false,
            init: function () {
                let existingImageUrl = $("#existingImageUrl").val();
                let existingImageName = $("#existingImageName").val();

                // ✅ Preload user’s current avatar if exists
                if (existingImageUrl) {
                    let mockFile = { name: existingImageName, size: 12345, accepted: true };
                    this.emit("addedfile", mockFile);
                    this.emit("thumbnail", mockFile, existingImageUrl);
                    this.emit("complete", mockFile);
                    this.files.push(mockFile);
                }

                // ✅ When new image uploaded
                this.on("success", function (file, response) {
                    if (response.success && response.data) {
                        $("#uploadedImage").val(response.data.id);

                            // Add hidden input for submitted form
                            let hidden = document.createElement('input');
                            hidden.type = "hidden";
                            hidden.name = "image_id"; // backend expects image_id
                            hidden.value = response.data.id;
                            file._hiddenInput = hidden;
                            document.querySelector('.edit-user-media-ids').appendChild(hidden);

                    }
                });

                // ✅ On remove, clear hidden input
                this.on("removedfile", function () {
                    $("#uploadedImage").val("");
                });
            }
        });
</script>


<script>
    // Trigger the file input click when the "Change Photo" button is clicked
        document.getElementById('changePhotoBtn').addEventListener('click', function () {
            document.getElementById('photoInput').click();
        });

        // Handle the file selection event and send AJAX request
        $(document).on('change', '#photoInput', function () {
            var formData = new FormData();
            var fileInput = $('#photoInput')[0];

            if (fileInput.files.length > 0) {
                formData.append('image', fileInput.files[0]);

                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('resource', 'App\\Models\\User');

                $.ajax({
                    url: '{{ route("add-media", $model->id) }}',  // Adjust the route
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.success) {
                            $("img.uploaded-image").attr("src", response.data.original_url);
                            $("button.remove-old-image").data('image-id', response.data.id);
                            Toastify({
                                text: "Image uploaded successfully!",
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#28a745",
                                close: true
                            }).showToast();
                        }
                    },
                    error: function (xhr) {
                        console.log(xhr.responseJSON.errors); // Handle errors if needed
                        Toastify({
                            text: "Error uploading image.",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                            close: true
                        }).showToast();
                    }
                });
            }
        });
</script>

<script>
    $(document).ready(function () {
            $('form[action*="change-password"]').on('submit', function (e) {
                e.preventDefault();

                let $form = $(this);
                let actionUrl = $form.attr('action');
                let formData = $form.serialize();
                const saveButton = $('.saveChangesButton');
                const saveLoader = $('.saveLoader');
                const saveButtonText = $('.saveChangesButton .btn-text');
                saveButton.prop('disabled', true);
                saveLoader.removeClass('d-none');
                saveButtonText.addClass('d-none');
                $.ajax({
                    type: 'POST',
                    url: actionUrl,
                    data: formData,
                    success: function (response) {
                        Toastify({
                            text: "Password updated successfully!",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true
                        }).showToast();
                        saveButton.prop('disabled', false);
                        saveLoader.addClass('d-none');
                        saveButtonText.removeClass('d-none');
                        $form[0].reset(); // Reset form fields
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    Toastify({
                                        text: errors[key][0],
                                        duration: 4000,
                                        gravity: "top",
                                        position: "right",
                                        backgroundColor: "#EA5455",
                                        close: true,
                                    }).showToast();
                                }
                            }
                        } else {
                            Toastify({
                                text: "Something went wrong!",
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#28a745",
                                close: true
                            }).showToast();
                            saveButton.prop('disabled', false);
                            saveLoader.addClass('d-none');
                            saveButtonText.removeClass('d-none');
                        }
                    }
                });
            });
        });
</script>

<script>
    function updateFullPhoneNumber() {
            const selectedOption = $("#phone-code option:selected");
            const phoneCode = selectedOption.data("phone-code") || '';
            const phoneNumber = $("#phone_number").val().replace(/\D/g, ''); // Remove non-digit characters
            const fullPhoneNumber = phoneCode + phoneNumber;

            $("#full_phone_number").val(fullPhoneNumber);
        }

        // Run the function on page load to set initial value
        $(document).ready(function () {
            updateFullPhoneNumber();

            // Update when country code changes
            $("#phone-code").on("change", updateFullPhoneNumber);

            // Update when phone number input changes
            $("#phone_number").on("input", updateFullPhoneNumber);
        });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
            const toggle = document.getElementById("account_status");
            const statusInput = document.getElementById("status");
            const statusLabel = document.querySelector("span.status-label");

            function updateStatusDisplay(isActive) {
                if (isActive) {
                    statusLabel.textContent = "Active";
                    statusLabel.style.backgroundColor = "#D7EEDD";
                    statusLabel.classList.add("primary-text-color");
                } else {
                    statusLabel.textContent = "Blocked";
                    statusLabel.style.backgroundColor = "#F0F0F0";
                    statusLabel.classList.remove("primary-text-color");
                }
            }

            // Initial update
            updateStatusDisplay(toggle.checked);
            statusInput.value = toggle.checked ? 1 : 0;

            toggle.addEventListener("change", function () {
                updateStatusDisplay(this.checked);
                statusInput.value = this.checked ? 1 : 0;
            });
        });
</script>

<script !src="">
    $('.remove-old-image').on('click', function (e) {
            e.preventDefault();
            var button = $(this);
            var imageId = button.data('image-id');

            $.ajax({
                url: '{{ url("api/media") }}/' + imageId,
                method: "DELETE",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $("img.uploaded-image").attr("src", "{{ asset("images/avatar.png") }}"); // Replace with the new media URL
                    Toastify({
                        text: "Image Removed Successfully",
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true
                    }).showToast();
                },
                error: function (xhr) {
                    console.log(xhr.responseJson.errors)
                }
            })

        });








    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js">
</script>
{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/modal-edit-user.js') }}?v={{ time() }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-user-view-account.js')) }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-user-view.js')) }}"></script>


@endsection