<div class="modal modal-slide-in new-user-modal fade" id="editTagModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editCategoryForm" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close fs-3" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Tag</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" id="edit-tag-id" >



                    <!-- Name in Arabic and English -->
                    <div class="row my-3">
                        <div class="col-6">
                            <label class="form-label label-text">Name (EN)</label>
                            <input type="text" class="form-control" id="edit-tag-name-en" name="name[en]" />
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">Name (AR)</label>
                            <input type="text" class="form-control" id="edit-tag-name-ar" name="name[ar]" />
                        </div>
                    </div>


                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5" id="saveChangesButton">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

