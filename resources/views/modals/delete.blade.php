<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-edit-user">
    <div class="add-new-user modal-content pt-0 pb-1">
      <form id="deleteUserForm" method="POST" action="{{ route('tags.store') }}">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Delete User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
        </div>

        <div class="modal-body pt-0">
          <!-- Confirmation Messages -->
          <h6 class="mb-2 fw-bold fs-4 text-black">Are you sure you want to delete this user?</h6>
          <p class="mb-2 text-dark fs-4">Deleting the user will delete all the associated data.</p>

          <!-- Warning Box -->
          <div class="d-flex align-items-center p-1 shadow rounded-3">
            <img src="{{ asset('images/deleteIcon.svg') }}" alt="Warning" width="32" height="32" class="mx-2" />
            <div class="fs-16 text-black">This Act can’t be undone.</div>
          </div>
        </div>

        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>
