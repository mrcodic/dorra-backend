<div class="modal modal-slide-in new-user-modal fade" id="showCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Show Category</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <!-- Image Upload -->
                <div class="mb-4 text-center border border-dashed border-secondary rounded p-3">
                    <input type="file" class="form-control d-none" id="imageUpload" />
                    <label for="imageUpload" class="text-muted small d-block cursor-pointer">Drag images here to upload</label>
                    <div class="text-muted small mt-1">image.jpg • 32.00 KB</div>
                </div>

                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="category-name" disabled />
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="category-description" rows="2"></textarea>
                </div>

                <!-- Subcategories -->
                <div class="mb-3">
                    <label class="form-label" id="category-subcategories">Subcategories</label>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border">Modern</span>
                        <span class="badge bg-light text-dark border">Fun</span>
                        <span class="badge bg-light text-dark border">Clean</span>
                        <span class="badge bg-light text-dark border">Simple</span>
                        <span class="badge bg-light text-dark border">Bold</span>
                    </div>
                </div>

                <!-- Number of Products -->
                <div class="mb-3">
                    <label class="form-label">Number of Products</label>
                    <input type="number" id="category-products" class="form-control"/>
                </div>


                <!-- Added Date -->
                <div class="mb-4">
                    <label class="form-label">Added Date</label>
                    <input type="date" id="category-date" class="form-control" disabled />
                </div>
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success">Edit</button>
            </div>
        </div>

        </div>
    </div>
</div>
