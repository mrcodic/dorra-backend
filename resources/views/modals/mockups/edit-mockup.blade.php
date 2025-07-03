<div class="modal modal-slide-in new-user-modal fade" id="editMockupModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editMockupForm" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Mockup</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="">
                        <div class="form-group mb-2">
                            <label for="edit-mockup-name" class="label-text mb-1">Mockup Name</label>
                            <input type="text" id="edit-mockup-name" class="form-control" name="name"
                                   placeholder="Mockup Name">
                        </div>

                        <div class="form-group mb-2">
                            <label for="edit-mockup-type" class="label-text mb-1">Mockup Type</label>
                            <select id="edit-mockup-type" name="type" class="form-select">
                                <option value="" disabled>select mockup type</option>
                                @foreach(\App\Enums\Mockup\TypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}"> {{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label for="edit-products-select" class="label-text mb-1">Product</label>
                            <select id="edit-products-select" name="product_id" class="form-select">
                                <option value="" disabled>Choose product</option>
                                @foreach($associatedData['products'] as $product)
                                    <option
                                        value="{{ $product->id }}">{{ $product->getTranslation('name', app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Colors Section -->
                        <div class="form-group mb-2">
                            <label class="label-text mb-1 d-block">Colors</label>
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <button type="button" id="openEditColorPicker" class="gradient-edit-picker-trigger border"></button>
                                <div id="edit-selected-colors" class="d-flex gap-1 flex-wrap align-items-center">
                                    <div id="previous-colors" class="d-flex flex-wrap gap-1"></div>
                                </div>
                            </div>
                            <input type="hidden" name="colors[]" id="edit-colorsInput">
                        </div>


                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label label-text" for="edit-product-image-main">Mockup File</label>

                                <!-- Hidden real input -->
                                <input type="file" name="image" id="edit-product-image-main" class="form-control d-none" accept="image/*">

                                <!-- Custom Upload Card -->
                                <div id="edit-upload-area" class="upload-card">
                                    <div id="upload-content">
                                        <i data-feather="upload" class="mb-2"></i>
                                        <p>Drag file here to upload</p>
                                    </div>
                                </div>
                                <div>
                                    <!-- Progress Bar -->
                                    <div id="edit-upload-progress" class="progress mt-2 d-none w-50">
                                        <div id="edit-upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                             style="width: 0%"></div>
                                    </div>

                                    <!-- Uploaded Image Preview -->
                                    <div id="edit-uploaded-image"
                                         class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                                        <img src="" alt="Uploaded" class="img-fluid rounded"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <div id="edit-file-details" class="file-details">
                                            <div class="file-name fw-bold"></div>
                                            <div class="file-size text-muted small"></div>
                                        </div>
{{--                                        <button type="button" id="edit-remove-image"--}}
{{--                                                class="btn btn-sm position-absolute text-danger"--}}
{{--                                                style="top: 5px; right: 5px; background-color: #FFEEED"--}}
{{--                                                data-image-id="{{ $mockup->getFirstMedia('mockups')?->id }}"--}}
{{--                                        >--}}
{{--                                            <i data-feather="trash"></i>--}}
{{--                                        </button>--}}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Save Chenges</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                              aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .gradient-edit-picker-trigger {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-image: url('/images/AddColor.svg') !important;
        /* force override */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border: 1px solid #ccc;
        cursor: pointer;
        position: relative;
    }

    /* Hide any accidental injected .pcr-button */
    .gradient-edit-picker-trigger .pcr-button {
        display: none !important;
    }
    .remove-color-btn {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #F4F6F6 !important;
        color: #424746 !important;
        border: none !important;
        border-radius: 5px;
        width: 16px;
        height: 16px;
        font-size: 16px;
        line-height: 1;
        padding: 1px;
        display: none;
    }

    .selected-color-wrapper:hover .remove-color-btn {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<script>
    let editPickr; // Declare it in a broader scope

    $(document).ready(function() {
        // Only initialize Pickr once
        if (!editPickr) {
            const dummyEditElement = document.createElement('div');
            dummyEditElement.style.display = 'none';
            document.body.appendChild(dummyEditElement);

            editPickr = Pickr.create({
                el: dummyEditElement,
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

            // Handle save
            editPickr.on('save', (color) => {
                const hex = color.toHEXA().toString();
                if (!editSelectedColors.includes(hex) && !editPreviousColors.includes(hex)) {
                    editSelectedColors.push(hex);
                    renderAllColors();
                }
                editPickr.hide();
            });
        }

        let editSelectedColors = [];
        let editPreviousColors = [];

        $('#openEditColorPicker').on('click', function() {
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

        // The rest of your functions for rendering/removing colors...
        window.setPreviousColors = function(colorsArray) {
            editPreviousColors = colorsArray || [];
            renderAllColors();
        };

        window.removeEditColor = function(hex) {
            editSelectedColors = editSelectedColors.filter(c => c !== hex);
            renderAllColors();
        };

        window.removePreviousColor = function(hex) {
            editPreviousColors = editPreviousColors.filter(c => c !== hex);
            renderAllColors();
        };

        function updateCombinedColors() {
            const allColors = [...editPreviousColors, ...editSelectedColors];
            $('#edit-colorsInput').val(allColors.join(','));
        }

        function renderAllColors() {
            const container = document.getElementById('edit-selected-colors');
            container.innerHTML = '';

            const combined = [...editPreviousColors.map(c => ({
                color: c,
                isPrevious: true
            })), ...editSelectedColors.map(c => ({
                color: c,
                isPrevious: false
            }))];

            combined.forEach(({
                color,
                isPrevious
            }) => {
                const item = document.createElement('span');
                item.innerHTML = `
                    <div class="selected-color-wrapper position-relative">
                        <div class="selected-color-dot" style="background-color: #fff;">
                            <div class="selected-color-inner" style="background-color: ${color};"></div>
                        </div>
                        <button type="button" class="remove-color-btn" onclick="${isPrevious ? `removePreviousColor('${color}')` : `removeEditColor('${color}')`}">×</button>
                    </div>
                `;
                container.appendChild(item);
            });

            updateCombinedColors();
        }
    });
</script>

<script>
    $(document).ready(function() {
        handleAjaxFormSubmit("#editMockupForm", {
            successMessage: "Mockup Updated Successfully",
            onSuccess: function() {
                $('#editMockupModal').modal('hide');
                location.reload();
            }
        })




        // ----------------------- File Upload ----------------------------
        let editInput = $('#edit-product-image-main');
        let editUploadArea = $('#edit-upload-area');
        let editProgress = $('#edit-upload-progress');
        let editProgressBar = $('#edit-upload-progress-bar');
        let editUploadedImage = $('#edit-uploaded-image');
        let editRemoveButton = $('#edit-remove-image');
        let imageId = $(this).data('image-id');
        if (imageId) {
            $('<input>').attr({
                type: 'hidden',
                name: 'deleted_old_images[]',
                value: imageId
            }).appendTo('#editMockupForm');
        }

        editUploadArea.on('click', function() {
            editInput.click();
        });

        editInput.on('change', function(e) {
            handleEditFiles(e.target.files);
        });

        editUploadArea.on('dragover', function(e) {
            e.preventDefault();
            editUploadArea.addClass('dragover');
        });

        editUploadArea.on('dragleave', function(e) {
            e.preventDefault();
            editUploadArea.removeClass('dragover');
        });

        editUploadArea.on('drop', function(e) {
            e.preventDefault();
            editUploadArea.removeClass('dragover');
            handleEditFiles(e.originalEvent.dataTransfer.files);
        });

        function handleEditFiles(files) {
            if (files.length > 0) {
                let file = files[0];
                let dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                editInput[0].files = dataTransfer.files;

                editProgress.removeClass('d-none');
                editProgressBar.css('width', '0%');

                let fakeProgress = 0;
                let interval = setInterval(function() {
                    fakeProgress += 10;
                    editProgressBar.css('width', fakeProgress + '%');

                    if (fakeProgress >= 100) {
                        clearInterval(interval);

                        let reader = new FileReader();
                        reader.onload = function(e) {
                            editUploadedImage.find('img').attr('src', e.target.result);
                            editUploadedImage.removeClass('d-none');
                            editProgress.addClass('d-none');
                            $('#edit-file-details .file-name').text(file.name);
                            $('#edit-file-details .file-size').text((file.size / 1024).toFixed(2) + ' KB');
                        }
                        reader.readAsDataURL(file);
                    }
                }, 100);
            }
        }

        editRemoveButton.on('click', function() {
            editUploadedImage.addClass('d-none');
            editInput.val('');
        });

    });
</script>
