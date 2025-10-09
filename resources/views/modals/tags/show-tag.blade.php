<div class="modal modal-slide-in new-user-modal fade" id="showTagModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title fs-3" id="exampleModalLabel">Show Tag</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <input type="hidden" id="tag-id">
                <!-- Name in Arabic and English -->
                <div class="row mb-1">
                    <div class="col-md-6">
                        <label class="form-label label-text">Name (EN)</label>
                        <input type="text" class="form-control" id="tag-name-en" disabled />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-text">Name (AR)</label>
                        <input type="text" class="form-control" id="tag-name-ar" disabled />
                    </div>
                </div>

                <!-- Number of Products -->
                <div class="mb-1">
                    <label class="form-label label-text">Number of Categories</label>
                    <input type="number" id="tag-products" class="form-control" disabled />
                </div>
                <div class="mb-1">
                    <label class="form-label label-text">Number of Products</label>
                    <input type="number" id="tag-categories" class="form-control" disabled />
                </div>
                <div class="mb-1">
                    <label class="form-label label-text">Number of Templates</label>
                    <input type="number" id="tag-templates" class="form-control" disabled />
                </div>

                <!-- Added Date -->
                <div class="mb-1">
                    <label class="form-label label-text">Added Date</label>
                    <input type="date" id="tag-date" class="form-control" disabled />
                </div>
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="editButton">Edit</button>
            </div>
        </div>
    </div>
</div>
