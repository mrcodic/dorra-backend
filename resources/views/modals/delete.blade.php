<div class="modal  new-user-modal fade" id="deleteModal">
  <div class="modal-dialog">
    <div class="add-new-user modal-content pt-0">
      <form id="addTagForm" enctype="multipart/form-data" action="{{ route('tags.store') }}" method="POST">
        @csrf

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

        <div class="modal-header mb-1">
          <h5 class="modal-title" id="exampleModalLabel">Delete User</h5>
        </div>

        <div class="modal-body pt-0">

        </div>

        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="">Delete</button>
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