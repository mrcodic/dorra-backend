<div class="modal modal-slide-in new-user-modal fade" id="editMockupModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editMockupForm" enctype="multipart/form-data">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title">Edit Mockup</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="">
                        <!-- Mockup Name -->
                        <div class="form-group mb-2">
                            <label for="edit-mockup-name" class="label-text mb-1">Mockup Name</label>
                            <input type="text" id="edit-mockup-name" class="form-control" name="name" placeholder="Mockup Name">
                        </div>

                        <!-- Mockup Type -->
                        <div class="form-group mb-2">
                            <label for="edit-mockup-type" class="label-text mb-1">Mockup Type</label>
                            <select id="edit-mockup-type" name="type" class="form-select">
                                <option value="" disabled>select mockup type</option>
                                @foreach(\App\Enums\Mockup\TypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product -->
                        <div class="form-group mb-2">
                            <label for="edit-products-select" class="label-text mb-1">Product</label>
                            <select id="edit-products-select" name="product_id" class="form-select">
                                <option value="" disabled>Choose product</option>
                                @foreach($associatedData['products'] as $product)
                                    <option value="{{ $product->id }}">{{ $product->getTranslation('name', app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Colors -->
                        <div class="form-group mb-2">
                            <label class="label-text mb-1 d-block">Colors</label>
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <button type="button" id="openEditColorPicker" class="gradient-edit-picker-trigger border"></button>
                                <div id="edit-selected-colors" class="d-flex gap-1 flex-wrap align-items-center">
                                    <div id="previous-colors" class="d-flex flex-wrap gap-1"></div>
                                </div>
                            </div>
                            <div id="editColorsInputContainer"></div>
                        </div>

                        <!-- File Upload -->
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label label-text" for="edit-product-image-main">Mockup File</label>
                                <input type="file" name="image" id="edit-product-image-main" class="form-control d-none" accept="image/*">

                                <!-- Custom Upload Card -->
                                <div id="edit-upload-area" class="upload-card">
                                    <div id="upload-content">
                                        <i data-feather="upload" class="mb-2"></i>
                                        <p>Drag file here to upload</p>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div id="edit-upload-progress" class="progress mt-2 d-none w-50">
                                    <div id="edit-upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                                </div>

                                <!-- Uploaded Image Preview -->
                                <div id="edit-uploaded-image" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                                    <img src="" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div id="edit-file-details" class="file-details">
                                        <div class="file-name fw-bold"></div>
                                        <div class="file-size text-muted small"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Colors + File Upload Script -->
<script>
    let editPickr;
    let editPreviousColors = [];

    $(document).ready(function () {
        // COLOR PICKR
        if (!editPickr) {
            const dummy = document.createElement('div');
            dummy.style.display = 'none';
            document.body.appendChild(dummy);

            editPickr = Pickr.create({
                el: dummy,
                theme: 'classic',
                components: {
                    preview: false,
                    opacity: false,
                    hue: true,
                    interaction: {
                        input: true,
                        save: true,
                        clear: true
                    }
                }
            });

            editPickr.on('save', (color) => {
                const hex = color.toHEXA().toString();
                if (!editPreviousColors.includes(hex)) {
                    editPreviousColors.push(hex);
                }
                renderAllColors();
                editPickr.hide();
            });
        }

        $('#openEditColorPicker').on('click', function () {
            const trigger = document.getElementById('openEditColorPicker');
            const rect = trigger.getBoundingClientRect();
            const modalScrollTop = document.querySelector('#editMockupModal .modal-body')?.scrollTop || 0;

            editPickr.show();

            setTimeout(() => {
                const pickerPanel = document.querySelector('.pcr-app.visible');
                if (pickerPanel) {
                    pickerPanel.style.position = 'absolute';
                    pickerPanel.style.left = `${rect.left + window.scrollX}px`;
                    pickerPanel.style.top = `${rect.bottom + window.scrollY + modalScrollTop + 5}px`;
                    pickerPanel.style.zIndex = 9999;
                }
            }, 0);
        });

        window.removePreviousColor = function (hex) {
            editPreviousColors = editPreviousColors.filter(c => c !== hex);
            renderAllColors();
        };

        function renderAllColors() {
            const container = document.getElementById('edit-selected-colors');
            container.innerHTML = '';
            editPreviousColors.forEach(color => {
                const item = document.createElement('span');
                item.innerHTML = `
                <div class="selected-color-wrapper position-relative">
                    <div class="selected-color-dot" style="background-color: #fff;">
                        <div class="selected-color-inner" style="background-color: ${color};"></div>
                    </div>
                    <button type="button" class="remove-color-btn" onclick="removePreviousColor('${color}')">×</button>
                </div>
                `;
                container.appendChild(item);
            });
            const colorInputContainer = document.getElementById('editColorsInputContainer');
            colorInputContainer.innerHTML = '';
            editPreviousColors.forEach(color => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'colors[]';
                hiddenInput.value = color;
                colorInputContainer.appendChild(hiddenInput);
            });
        }

        // FILE UPLOAD
        const editInput = $('#edit-product-image-main');
        const editUploadArea = $('#edit-upload-area');
        const editProgress = $('#edit-upload-progress');
        const editProgressBar = $('#edit-upload-progress-bar');
        const editUploadedImage = $('#edit-uploaded-image');

        editUploadArea.on('click', function () {
            editInput.click();
        });

        editInput.on('change', function (e) {
            handleEditFiles(e.target.files);
        });

        editUploadArea.on('dragover', function (e) {
            e.preventDefault();
            editUploadArea.addClass('dragover');
        });

        editUploadArea.on('dragleave', function (e) {
            e.preventDefault();
            editUploadArea.removeClass('dragover');
        });

        editUploadArea.on('drop', function (e) {
            e.preventDefault();
            editUploadArea.removeClass('dragover');
            handleEditFiles(e.originalEvent.dataTransfer.files);
        });

        function handleEditFiles(files) {
            if (files.length > 0) {
                const file = files[0];

                // CLEAN preview
                editUploadedImage.addClass('d-none');
                editUploadedImage.find('img').attr('src', '');
                $('#edit-file-details .file-name').text('');
                $('#edit-file-details .file-size').text('');
                editProgressBar.css('width', '0%');
                editProgress.removeClass('d-none');

                let fakeProgress = 0;
                const interval = setInterval(function () {
                    fakeProgress += 20;
                    editProgressBar.css('width', fakeProgress + '%');

                    if (fakeProgress >= 100) {
                        clearInterval(interval);

                        const reader = new FileReader();
                        reader.onload = function (e) {
                            editUploadedImage.find('img').attr('src', e.target.result);
                            editUploadedImage.removeClass('d-none');
                            $('#edit-file-details .file-name').text(file.name);
                            $('#edit-file-details .file-size').text((file.size / 1024).toFixed(2) + ' KB');
                            editProgress.addClass('d-none');
                        };
                        reader.readAsDataURL(file);
                    }
                }, 100);

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                editInput[0].files = dataTransfer.files;
            }
        }
    });
</script>
