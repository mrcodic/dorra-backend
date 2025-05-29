<div class="modal modal-slide-in new-user-modal fade" id="addSpecModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addSpecForm" enctype="multipart/form-data"  method="post" action="{{ route("products.specifications.create") }}">
                @csrf
                <input type="hidden" name="product_id" id="product_id_input">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Spec</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <!-- Specifications -->

                    <div>
                        <!-- Outer Repeater for Specifications -->
                        <div class="outer-repeater">
                            <div data-repeater-list="specifications">
                                <div data-repeater-item>
                                    <!-- Specification Fields -->
                                    <div class="row mt-1">
                                        <div class="col-md-6">
                                            <div class="mb-1">
                                                <label class="form-label label-text">Name (EN)</label>
                                                <input type="text" name="name_en" class="form-control" placeholder="Specification Name (EN)" />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-1">
                                                <label class="form-label label-text">Name (AR)</label>
                                                <input type="text" name="name_ar" class="form-control" placeholder="Specification Name (AR)" />
                                            </div>
                                        </div>

                                        <!-- Inner Repeater for Specification Options -->
                                        <div class="inner-repeater">
                                            <div data-repeater-list="specification_options">
                                                <div data-repeater-item>
                                                    <div class="row d-flex align-items-end mt-2">
                                                        <!-- Option Name (EN) -->
                                                        <div class="col">
                                                            <label class="form-label label-text">Value (EN)</label>
                                                            <input type="text" name="value_en" class="form-control" placeholder="Option (EN)" />
                                                        </div>

                                                        <!-- Option Name (AR) -->
                                                        <div class="col">
                                                            <label class="form-label label-text">Value (AR)</label>
                                                            <input type="text" name="value_ar" class="form-control" placeholder="Option (AR)" />
                                                        </div>

                                                        <!-- Option Price -->
                                                        <div class="col">
                                                            <label class="form-label label-text">Price (EGP) (Optional)</label>
                                                            <input type="text" name="price" class="form-control" placeholder="Price" />
                                                        </div>
                                                    </div>
                                                    <div class="row d-flex align-items-end mt-2">
                                                        <!-- Option Image -->
                                                        <div class="col-md-12">
                                                            <label class="form-label label-text">Option Image</label>

                                                            <!-- Hidden real input -->
                                                            <!-- Hidden real input -->
                                                            <input type="file" name="image" class="form-control d-none option-image-input" accept="image/*">

                                                            <!-- Custom Upload Card -->
                                                            <div class="upload-card option-upload-area">
                                                                <div class="d-flex justify-content-center align-items-center gap-1">
                                                                    <i data-feather="upload" class="mb-2"></i>
                                                                    <p>Drag image here to upload</p>
                                                                </div>
                                                            </div>

                                                            <!-- Progress Bar -->
                                                            <div class="progress mt-2 d-none w-50 option-upload-progress">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                                                            </div>

                                                            <!-- Uploaded Image Preview -->
                                                            <div class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2 option-uploaded-image">
                                                                <img src="" alt="Uploaded" class="img-fluid rounded option-image-preview" style="width: 50px; height: 50px; object-fit: cover;">
                                                                <div class="file-details">
                                                                    <div class="file-name fw-bold option-file-name"></div>
                                                                    <div class="file-size text-muted small option-file-size"></div>
                                                                </div>
                                                                <button type="button" class="btn btn-sm position-absolute text-danger option-remove-image" style="top: 5px; right: 5px; background-color: #FFEEED">
                                                                    <i data-feather="trash"></i>
                                                                </button>
                                                            </div>

                                                            <div class="col-12 text-end mt-1 mb-2">
                                                                <button type="button" class="btn btn-outline-danger" data-repeater-delete>
                                                                    <i data-feather="x" class="me-25"></i> Delete Value
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Add Option Button -->
                                            <div class="row">
                                                <div class="col-12">
                                                    <button type="button" class="btn primary-text-color bg-white mt-2" data-repeater-create>
                                                        <i data-feather="plus"></i> <span> Add New Value</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End of Inner Repeater -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Add Spec</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

