<div class="modal modal-slide-in new-user-modal fade" id="editCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editCategoryForm" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <input type="hidden" id="edit-category-id">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3">Edit Product</h5>
                </div>
                <div class="modal-body flex-grow-1">

                    <!-- Image Upload -->
                    <div class="mb-1">
                        <label class="form-label label-text" for="edit-category-image">Image*</label>

                        <!-- Hidden file input -->
                        <input type="file" name="image" id="edit-category-image" class="form-control d-none" accept="image/*">

                        <!-- Custom Upload Area -->
                        <div id="edit-upload-area" class="upload-card">
                            <div id="edit-upload-content">
                                <i data-feather="upload" class="mb-2"></i>
                                <p>Drag image here to upload</p>
                            </div>
                        </div>

                        <!-- Upload Progress -->
                        <div id="edit-upload-progress" class="progress mt-2 d-none w-50">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>

                        <!-- Uploaded Image Preview -->
                        <div id="edit-uploaded-image" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                            <img src="" id="edit-preview-image" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <div id="edit-file-details" class="file-details">
                                <div class="file-name fw-bold"></div>
                                <div class="file-size text-muted small"></div>
                            </div>
{{--                            <button type="button" id="edit-remove-image" data-image_id="" class="btn btn-sm position-absolute text-danger remove-old-image" style="top: 5px; right: 5px; background-color: #FFEEED">--}}
{{--                                <i data-feather="trash"></i>--}}
{{--                            </button>--}}
                        </div>
                    </div>

                    <!-- Name and Description Fields -->
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
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="editSaveChangesButton">
                        <span class="btn-text">Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const input = $('#edit-category-image');
        const uploadArea = $('#edit-upload-area');
        const progressBar = $('#edit-upload-progress .progress-bar');
        const progressContainer = $('#edit-upload-progress');
        const uploadedImage = $('#edit-uploaded-image');
        const imgPreview = $('#edit-preview-image');
        const fileNameDisplay = $('#edit-file-details .file-name');
        const fileSizeDisplay = $('#edit-file-details .file-size');
        const removeBtn = $('#edit-remove-image');

        // Click upload area to open file input
        uploadArea.on('click', function () {
            input.click();
        });

        // Drag over styles
        uploadArea.on('dragover', function (e) {
            e.preventDefault();
            uploadArea.addClass('dragover');
        });

        uploadArea.on('dragleave', function (e) {
            e.preventDefault();
            uploadArea.removeClass('dragover');
        });

        uploadArea.on('drop', function (e) {
            e.preventDefault();
            uploadArea.removeClass('dragover');
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) handleFile(files[0]);
        });

        // File input change
        input.on('change', function (e) {
            if (e.target.files.length > 0) handleFile(e.target.files[0]);
        });

        function handleFile(file) {
            if (!file.type.startsWith('image/')) return;

            const fileSizeKB = (file.size / 1024).toFixed(2) + ' KB';
            progressContainer.removeClass('d-none');
            progressBar.css('width', '0%');

            let progress = 0;
            const interval = setInterval(function () {
                progress += 10;
                progressBar.css('width', progress + '%');
                if (progress >= 100) {
                    clearInterval(interval);

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imgPreview.attr('src', e.target.result);
                        fileNameDisplay.text(file.name);
                        fileSizeDisplay.text(fileSizeKB);
                        uploadedImage.removeClass('d-none');
                        progressContainer.addClass('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            }, 100);
        }

        // Remove image
        removeBtn.on('click', function () {
            imgPreview.attr('src', '');
            fileNameDisplay.text('');
            fileSizeDisplay.text('');
            uploadedImage.addClass('d-none');
            input.val('');
        });

        feather.replace();
    });
</script>
