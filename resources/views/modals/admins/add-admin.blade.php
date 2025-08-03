<div class="modal modal-slide-in new-user-modal fade" id="addAdminModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addAdminForm" enctype="multipart/form-data" action="{{ route('admins.store') }}" method="POST">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Admin</h5>
                </div>

                <div class="modal-body pt-0">
                    <!-- Avatar + Upload -->
                    <div class="d-flex align-items-end mb-3">
                        <img id="avatarPreview" src="{{ asset('images/avatar.png') }}" alt="Avatar"
                             class="rounded-circle border" style="width: 48px; height: 48px;">
                        <div>
                            <label for="avatarInput" class="lined-btn mx-1">Add Photo</label>
                            <input type="file" class="d-none" id="avatarInput" name="image" accept="image/*">
                        </div>
                    </div>

                    <!-- First Name + Last Name -->
                    <div class="row mb-2">
                        <div class="col">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="first_name">
                        </div>
                        <div class="col">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="last_name">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-2">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" id="email">
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-2">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="phone_number" id="phone">
                    </div>

                    <!-- Password -->
                    <div class="mb-2">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password">
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-2">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation"
                               id="password_confirmation">
                    </div>

                    <!-- Role -->
                    <div class="mb-2">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role_id" id="role">
                            <option selected disabled>Select Role</option>
                            @foreach($associatedData['roles'] as $role)
                                <option value="{{ $role->id }}">{{ $role->getTranslation('name', app()->getLocale()) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="mb-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="1">Active</option>
                            <option value="0">Block</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Add</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Show avatar preview
        $('#avatarInput').on('change', function (e) {
            const [file] = e.target.files;
            if (file) {
                $('#avatarPreview').attr('src', URL.createObjectURL(file));
            }
        });

        // jQuery validation
        $('#addAdminForm').validate({
            rules: {
                first_name: { required: true, maxlength: 255 },
                last_name: { required: true, maxlength: 255 },
                email: { required: true, email: true },
                phone_number: { required: true, minlength: 11, maxlength: 11 },
                password: { required: true, minlength: 8 },
                password_confirmation: { required: true, equalTo: "#password" },
                status: { required: true },
                role_id: { required: false },
                image: { extension: "jpg|jpeg|png|svg" }
            },
            messages: {
                first_name: "Please enter the first name.",
                last_name: "Please enter the last name.",
                email: {
                    required: "Please enter an email.",
                    email: "Please enter a valid email."
                },
                phone_number: {
                    required: "Please enter a phone number.",
                    minlength: "Phone number must be 11 digits.",
                    maxlength: "Phone number must be 11 digits."
                },
                password: {
                    required: "Please enter a password.",
                    minlength: "Password must be at least 8 characters."
                },
                password_confirmation: {
                    required: "Please confirm the password.",
                    equalTo: "Passwords do not match."
                },
                image: {
                    extension: "Only JPG, JPEG, PNG, SVG allowed."
                }
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            errorPlacement: function (error, element) {
                if (element.parent('.input-group').length || element.is('select')) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            }
        });

        // ✅ Call handleAjaxFormSubmit once
        handleAjaxFormSubmit('#addAdminForm', {
            successMessage: "✅ Admin created successfully!",
            closeModal: '#addAdminModal',
            onSuccess: function (response, $form) {
                $form[0].reset();
                $form.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                $form.find('.invalid-feedback').remove();
                $('#avatarPreview').attr('src', '{{ asset("images/avatar.png") }}');
                $(".admin-list-table").DataTable().ajax.reload(null, false);
            }
        });
    });

</script>
