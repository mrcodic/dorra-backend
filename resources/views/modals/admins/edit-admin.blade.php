<div class="modal modal-slide-in new-user-modal fade" id="editAdminModal">
  <div class="modal-dialog">
    <div class="add-new-user modal-content pt-0">
      <form id="editAdminForm" enctype="multipart/form-data" action="{{ route('tags.store') }}" method="POST">
        @csrf

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

        <div class="modal-header mb-1">
          <h5 class="modal-title" id="exampleModalLabel">Edit Admin</h5>
        </div>

        <div class="modal-body pt-0">
          <!-- Avatar + Upload -->
          <div class="d-flex align-items-end mb-3">
            <img id="avatarPreview" src="{{asset('images/avatar.png')}}" alt="Avatar" class="rounded-circle border" style="width: 48px; height: 48px;">
            <div>
              <label for="avatarInput" class="lined-btn mx-1">Change photo</label>
              <input type="file" class="d-none" id="avatarInput" name="avatar" accept="image/*">
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
            <input type="text" class="form-control" name="phone" id="phone">
          </div>

          <!-- Password -->
          <div class="mb-2">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password">
          </div>

          <!-- Confirm Password -->
          <div class="mb-2">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
          </div>

          <!-- Role -->
          <div class="mb-2">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" name="role" id="role">
              <option selected disabled>Select Role</option>
              <option value="admin">Admin</option>
              <option value="editor">Editor</option>
            </select>
          </div>

          <!-- Status -->
          <div class="mb-2">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" id="status">
              <option value="active">Active</option>
              <option value="block">Block</option>
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

<script>
  document.getElementById('avatarInput').addEventListener('change', function (event) {
    const [file] = event.target.files;
    if (file) {
      document.getElementById('avatarPreview').src = URL.createObjectURL(file);
    }
  });
</script>

