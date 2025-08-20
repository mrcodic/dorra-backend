<div class="modal modal-slide-in new-user-modal fade" id="editAdminModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editAdminForm" enctype="multipart/form-data" action="" method="POST">
                @csrf
                @method("PUT")
                <div class="edit-avatar-media-ids"></div> <!-- hidden input gets appended here -->

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Admin</h5>
                </div>

                <div class="modal-body pt-0">
                    <!-- Avatar + Upload -->
                    <div class="d-flex flex-column flex-md-row align-items-center gap-1 mb-3">
                        <img alt="Avatar" src="{{ asset('images/default-user.png') }}"
                            class="rounded-circle border avatarPreview" style="width: 48px; height: 48px;">

                        <div class="ms-2">
                            <div id="editAvatarDropzone" class="dropzone border rounded p-2"
                                style="width: 200px; cursor: pointer;">
                                <div class="dz-message">Drop avatar here or click</div>
                            </div>
                        </div>
                    </div>


                    <!-- First Name + Last Name -->
                    <div class="row mb-2">
                        <div class="col-12 col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="first_name">
                        </div>
                        <div class="col-12 col-md-6">
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
                        <select class="form-select" name="role" id="role">
                            <option selected disabled>Select Role</option>
                            @foreach($associatedData['roles'] as $role)
                            <option value="{{ $role->id }}">{{ $role->getTranslation('name',app()->getLocale()) }}
                            </option>
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
                    <button type="submit" class="btn btn-primary" id="saveChangesButton">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    Dropzone.autoDiscover = false;

    const editAvatarDropzone = new Dropzone("#editAvatarDropzone", {
        url: "{{ route('media.store') }}",
        paramName: "image",
        maxFiles: 1,
        acceptedFiles: "image/*",
        addRemoveLinks: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        init: function () {
            this.on("success", function (file, response) {
                // Clear previous hidden inputs
                document.querySelector('.edit-avatar-media-ids').innerHTML = "";

                // Create hidden input with image_id
                let hidden = document.createElement('input');
                hidden.type = "hidden";
                hidden.name = "image_id";
                hidden.value = response.data.id; // ✅ matches your response
                document.querySelector('.edit-avatar-media-ids').appendChild(hidden);

                // Update avatar preview
                const preview = document.querySelector('#editAdminModal .avatarPreview');
                preview.src = response.data.url;
            });

            this.on("removedfile", function () {
                // Reset preview
                const preview = document.querySelector('#editAdminModal .avatarPreview');
                preview.src = "{{ asset('images/default-user.png') }}";

                // Remove hidden input too
                document.querySelector('.edit-avatar-media-ids').innerHTML = "";
            });
        }
    });



</script>
<script>
    // For Edit Admin Modal
    document.querySelectorAll('#editAdminModal .avatarInput').forEach(input => {
        input.addEventListener('change', function (event) {
            const [file] = event.target.files;
            const preview = document.querySelector('#editAdminModal .avatarPreview');
            const defaultAvatarUrl = '{{ asset('images/avatar.png') }}';

            if (file) {
                const imageUrl = URL.createObjectURL(file);
                preview.src = imageUrl;
                preview.onload = function () {
                    URL.revokeObjectURL(imageUrl); // free memory
                };
            } else {
                preview.src = defaultAvatarUrl;
            }

            event.target.value = ''; // reset input so same file triggers change again
        });
    });

    // For Add Admin Modal
    document.querySelectorAll('#addAdminModal #avatarInput').forEach(input => {
        input.addEventListener('change', function (event) {
            const [file] = event.target.files;
            const preview = document.querySelector('#addAdminModal #avatarPreview');

            if (file) {
                const imageUrl = URL.createObjectURL(file);
                preview.src = imageUrl;
                preview.onload = function () {
                    URL.revokeObjectURL(imageUrl); // free memory
                };
            }

            event.target.value = ''; // reset input
        });
    });


        handleAjaxFormSubmit('#editAdminForm', {
            successMessage: "✅ Admin updated successfully!",
            closeModal: '#editAdminModal',
            onSuccess: function (response, $form) {
                $(".admin-list-table").DataTable().ajax.reload(null, false); // false = stay on current page
                location.reload()
            }
        });

</script>