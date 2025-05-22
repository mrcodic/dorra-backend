<div class="modal modal-slide-in new-user-modal fade" id="addPermissionModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addPermissionForm" enctype="multipart/form-data" method="post" action="{{ route('permissions.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add Permission</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <!-- Name in Arabic and English -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Permission Name (EN)</label>
                            <input type="text" class="form-control" placeholder="Enter Permission Name(En)"
                                   id="add-tag-name-en" name="group[en]"/>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Permission Name (AR)</label>
                            <input type="text" class="form-control" placeholder="Enter Permission Name(Ar)"
                                   id="add-tag-name-ar" name="group[ar]"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
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
        $('#addPermissionForm').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $('#SaveChangesButton');
            const $loader = $('#saveLoader');

            // Show loader and disable button
            $submitBtn.prop('disabled', true);
            $loader.removeClass('d-none');

            const formData = new FormData(this);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function (response) {
                    Toastify({
                        text: "✅ Permission created successfully!",
                        duration: 3000,
                        gravity: "top",
                        backgroundColor: "#28a745",
                    }).showToast();

                    // Optional: close modal
                    $('#addPermissionModal').modal('hide');

                    // Reset form
                    $form.trigger('reset');
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
                                    backgroundColor: "#EA5455",
                                    close: true,
                                }).showToast();

                            }
                        }
                        saveButton.prop('disabled', false);
                        saveLoader.addClass('d-none');
                        saveButtonText.removeClass('d-none');
                    },
                complete: function () {
                    // Hide loader and enable button
                    $submitBtn.prop('disabled', false);
                    $loader.addClass('d-none');
                }
            });
        });
    });
</script>
