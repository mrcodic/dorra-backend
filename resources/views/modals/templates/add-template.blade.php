<div class="modal modal-slide-in new-user-modal fade" id="addTemplateModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addTagForm" enctype="multipart/form-data" action="{{ route('tags.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Save as New Template</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="">
                        <div class="form-group mb-2">
                            <label for="templateName" class="label-text mb-1">Name</label>
                            <input type="text" id="templateName" class="form-control" placeholder="Template Name">
                        </div>

                        <div class="form-group mb-2">
                            <label for="templateDescription" class="label-text mb-1">Description</label>
                            <textarea id="templateDescription" class="form-control" rows="3" placeholder="Template Description"></textarea>
                        </div>

                        <div class="form-group mb-2">
                            <label for="tagsSelect" class="label-text mb-1">Tags</label>
                            <select id="tagsSelect" class="form-select select2" multiple>
                                <option value="">Choose tag</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="tagsSelect" class="label-text mb-1">Product</label>
                            <select id="tagsSelect" class="form-select select2">
                                <option value="">Choose Product</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label label-text" for="product-image-main">Image</label>

                                <!-- Hidden real input -->
                                <input type="file" name="image" id="product-image-main" class="form-control d-none" accept="image/*">

                                <!-- Custom Upload Card -->
                                <div id="upload-area" class="upload-card">
                                    <div id="upload-content">
                                        <i data-feather="upload" class="mb-2"></i>
                                        <p>Drag image here to upload</p>
                                    </div>


                                </div>
                                <div>
                                    <!-- Progress Bar -->
                                    <div id="upload-progress" class="progress mt-2 d-none w-50">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                                    </div>


                                    <!-- Uploaded Image Preview -->
                                    <div id="uploaded-image" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                                        <img src="" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div id="file-details" class="file-details">
                                            <div class="file-name fw-bold"></div>
                                            <div class="file-size text-muted small"></div>
                                        </div>
                                        <button type="button" id="remove-image" class="btn btn-sm position-absolute text-danger" style="top: 5px; right: 5px; background-color: #FFEEED">
                                            <i data-feather="trash"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Publish</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        let input = $('#product-image-main');
        let uploadArea = $('#upload-area');
        let progress = $('#upload-progress');
        let progressBar = $('.progress-bar');
        let uploadedImage = $('#uploaded-image');
        let removeButton = $('#remove-image');

        // Click on the upload area triggers the hidden input
        uploadArea.on('click', function() {
            input.click();
        });

        // Handle file selection
        input.on('change', function(e) {
            handleFiles(e.target.files);
        });

        // Handle Drag & Drop
        uploadArea.on('dragover', function(e) {
            e.preventDefault();
            uploadArea.addClass('dragover');
        });

        uploadArea.on('dragleave', function(e) {
            e.preventDefault();
            uploadArea.removeClass('dragover');
        });

        uploadArea.on('drop', function(e) {
            e.preventDefault();
            uploadArea.removeClass('dragover');
            handleFiles(e.originalEvent.dataTransfer.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                let file = files[0];

                // 🔽 This is the fix: assign the dropped file to the input element
                let dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input[0].files = dataTransfer.files;

                console.log('Input files:', input[0].files); // Make sure this logs a FileList with 1 file

                // Show loader
                progress.removeClass('d-none');
                progressBar.css('width', '0%');

                // Fake loading effect
                let fakeProgress = 0;
                let interval = setInterval(function() {
                    fakeProgress += 10;
                    progressBar.css('width', fakeProgress + '%');

                    if (fakeProgress >= 100) {
                        clearInterval(interval);

                        // Preview image
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            uploadedImage.find('img').attr('src', e.target.result);
                            uploadedImage.removeClass('d-none');
                            progress.addClass('d-none');

                            // Show file name and size
                            $('#file-details .file-name').text(file.name);
                            $('#file-details .file-size').text((file.size / 1024).toFixed(2) + ' KB');
                        }
                        reader.readAsDataURL(file);
                    }
                }, 100);
            }
        }

        // Remove image
        removeButton.on('click', function() {
            uploadedImage.addClass('d-none');
            input.val(''); // Clear the input
        });
    });
</script>