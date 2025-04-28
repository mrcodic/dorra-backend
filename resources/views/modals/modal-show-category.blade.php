<div class="modal modal-slide-in new-user-modal fade" id="showCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <button type="button" class="btn-close fs-3" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title fs-3" id="exampleModalLabel">Show Category</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <!-- Image Upload with Preview -->
                <div id="imagePreviewContainer" class="mt-2 d-flex justify-content-between align-items-between  ">
                    <label for="imageUpload" class="text-black small d-block cursor-pointer label-text">Image</label>
                    <img id="imagePreview" class="img-fluid rounded" style="max-width: 64px;" />
                </div>
                <input type="hidden" id="category-id">
                <div class="text-muted small mt-1" id="imageDetails" style="display: none;">image.jpg • 32.00 KB</div>

                <!-- Name in Arabic and English -->
                <div class="row my-3">
                    <div class="col-6">
                        <label class="form-label label-text">Name (EN)</label>
                        <input type="text" class="form-control" id="category-name-en" disabled />
                    </div>
                    <div class="col-6">
                        <label class="form-label label-text">Name (AR)</label>
                        <input type="text" class="form-control" id="category-name-ar" disabled />
                    </div>
                </div>

                <!-- Description in Arabic and English -->
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label label-text">Description (EN)</label>
                        <textarea class="form-control" id="category-description-en" rows="2" disabled></textarea>
                    </div>
                    <div class="col-6">
                        <label class="form-label label-text">Description (AR)</label>
                        <textarea class="form-control" id="category-description-ar" rows="2" disabled></textarea>
                    </div>
                </div>

                <!-- Subcategories -->
                <div class="mb-3">
                    <label class="form-label label-text" id="category-subcategories">Subcategories</label>
                    <div id="subcategories-container" class="d-flex flex-wrap gap-2"></div>
                </div>

                <!-- Number of Products -->
                <div class="mb-3">
                    <label class="form-label label-text">Number of Products</label>
                    <input type="number" id="category-products" class="form-control" disabled />
                </div>

                <!-- Added Date -->
                <div class="mb-4">
                    <label class="form-label label-text">Added Date</label>
                    <input type="date" id="category-date" class="form-control" disabled />
                </div>
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="editButton">Edit</button>
            </div>
        </div>
    </div>
</div>
