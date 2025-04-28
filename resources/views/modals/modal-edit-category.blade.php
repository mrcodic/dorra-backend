<div class="modal modal-slide-in new-user-modal fade" id="editCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editCategoryForm" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close fs-3" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Category</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" id="edit-category-id" >
                    <!-- Image Upload -->
                
                    <label class="form-label label-text">Image</label>
                    <div class="mb-1 upload-card position-relative">
                        <input type="file" name="image" class="form-control d-none" id="edit-image-upload" accept="image/*" />
                        <label for="edit-image-upload" class="text-dark small d-flex justify-content-center align-items-center gap-1 fs-5 cursor-pointer"><i data-feather="upload" class="mb-2"></i>
                            <p>Drag image here to upload</p>
                        </label>

                    </div>
                    
                        <!-- Image Preview + Delete Icon -->
                        <div id="edit-image-preview-container" class="position-relative mt-2" style="display: none;">
                            <div class=" d-flex gap-1 align-items-center ">
                            <img id="edit-image-preview" class="img-fluid rounded" style="max-width: 50px;" />
                            <div class="text-dark small mt-1" id="edit-image-details" style="display: none;">image.jpg • 32.00 KB</div>
                            </div>
                    
                            <button type="button" id="delete-image-button" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="transform: translate(25%, -25%);">
                                &times;
                            </button>
                        </div>

                    <!-- Name in Arabic and English -->
                    <div class="row my-3">
                        <div class="col-6">
                            <label class="form-label label-text">Name (EN)</label>
                            <input type="text" class="form-control" id="edit-category-name-en" name="name[en]" />
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">Name (AR)</label>
                            <input type="text" class="form-control" id="edit-category-name-ar" name="name[ar]" />
                        </div>
                    </div>

                    <!-- Description in Arabic and English -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label label-text">Description (EN)</label>
                            <textarea class="form-control" id="edit-category-description-en" name="description[en]" rows="2"></textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">Description (AR)</label>
                            <textarea class="form-control" id="edit-category-description-ar" name="description[ar]" rows="2"></textarea>
                        </div>
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

