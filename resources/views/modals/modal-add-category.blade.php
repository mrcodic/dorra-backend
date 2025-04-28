<div class="modal modal-slide-in new-user-modal fade" id="addCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addCategoryForm" enctype="multipart/form-data" action="{{ route("categories.store") }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Category</h5>
                </div>
                <div class="modal-body flex-grow-1">

                    <!-- Image Upload -->
                    <div class="mb-4 text-center border border-dashed border-secondary rounded p-3 position-relative">
                        <input type="file" name="image" class="form-control d-none" id="add-image-upload" accept="image/*" />
                        <label for="add-image-upload" class="text-muted small d-block cursor-pointer">Drag images here to upload or click to select</label>

                        <!-- Image Preview + Delete Icon -->
                        <div id="add-image-preview-container" class="position-relative mt-2" style="display: none;">
                            <img id="add-image-preview" class="img-fluid rounded" style="max-width: 200px;" />
                            <button type="button" id="delete-image" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="transform: translate(25%, -25%);">
                                &times;
                            </button>
                        </div>

                        <div class="text-muted small mt-1" id="add-image-details" style="display: none;">image.jpg • 32.00 KB</div>
                    </div>

                    <!-- Name in Arabic and English -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Name (EN)</label>
                            <input type="text" class="form-control" placeholder="Enter Category Name(En)" id="add-category-name-en" name="name[en]" />
                        </div>
                        <div class="col-6">
                            <label class="form-label">Name (AR)</label>
                            <input type="text" class="form-control" placeholder="Enter Category Name(Ar)" id="add-category-name-ar" name="name[ar]" />
                        </div>
                    </div>

                    <!-- Description in Arabic and English -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Description (EN)</label>
                            <textarea class="form-control" id="add-category-description-en" placeholder="Enter Description Name(En)" name="description[en]" rows="2"></textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Description (AR)</label>
                            <textarea class="form-control" id="add-category-description-ar" placeholder="Enter Description Name(En)" name="description[ar]" rows="2"></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveChangesButton">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

