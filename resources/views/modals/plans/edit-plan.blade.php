<div class="modal modal-slide-in new-user-modal fade" id="editPlanModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editPlanForm" enctype="multipart/form-data" action="" method="POST">
                @csrf
                @method("PUT")
                <div class="edit-avatar-media-ids"></div> <!-- hidden input gets appended here -->

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Plan</h5>
                </div>

                <div class="modal-body pt-0">
                    <div class="row mb-2">
                        <div class="col-12 col-md-12">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="name">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-2">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" class="form-control" name="description" id="" cols="10" rows="10"></textarea>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-md-6">
                            <label for="credits" class="form-label">Credits</label>
                            <input type="number" class="form-control" name="credits" id="credits">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" id="price">
                        </div>

                    </div>

                    <!-- Status -->
                    <div class="mb-2">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editStatusToggle" checked>
                            <label class="form-check-label" for="statusToggle">Active</label>
                        </div>

                        <!-- hidden input sent to backend -->
                        <input type="hidden" name="is_active" id="status" value="1">
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
    $('#editStatusToggle').on('change', function () {
        const isActive = $(this).is(':checked');
        console.log(isActive)
        $('#status').val(isActive ? 1 : 0);
        $(this).next('label').text(isActive ? 'Active' : 'Inactive');
    });


        handleAjaxFormSubmit('#editPlanForm', {
            successMessage: "✅ Plan updated successfully!",
            closeModal: '#editPlanModal',
            resetForm: false,
            onSuccess: function (response, $form) {
                $(".plan-list-table").DataTable().ajax.reload(null, false); // false = stay on current page
            }
        });

</script>
