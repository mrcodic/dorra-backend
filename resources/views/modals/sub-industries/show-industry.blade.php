<div class="modal modal-slide-in new-user-modal fade" id="showSubIndustryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0 fs-3">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Show Sub Industry</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <input type="hidden" name="" id="sub-industry-id">
                <input type="hidden" name="" id="parent-id">
                <!-- Name in Arabic and English -->
                <div class="row mb-1">
                    <div class="col-md-6">
                        <label class="form-label label-text">Name (EN)</label>
                        <input type="text" class="form-control" id="sub-industry-name-en" disabled />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-text">Name (AR)</label>
                        <input type="text" class="form-control" id="sub-industry-name-ar" disabled />
                    </div>
                </div>

                <div class="mb-1">
                    <label class="form-label label-text" for="parent-name">Main Industry</label>
                    <input type="text" class="form-control" id="parent-name" disabled />
                </div>

                <!-- Number of Products -->
                <div class="mb-1">
                    <label class="form-label label-text">Number of Templates</label>
                    <input type="number" id="sub-industry-templates" class="form-control" disabled />
                </div>

                <!-- Added Date -->
                <div class="mb-1">
                    <label class="form-label label-text">Added Date</label>
                    <input type="date" id="sub-industry-date" class="form-control" disabled />
                </div>
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary " data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
