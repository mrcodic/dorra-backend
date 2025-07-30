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
                            <label for="mockup-type" class="label-text mb-1 d-block">Mockup Type</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(\App\Models\Type::all(['id','value']) as $type)
                                <label class="radio-box d-flex align-items-center gap-1">
                                    <input
                                        class="form-check-input type-checkbox"
                                        type="checkbox"
                                        name="types[]"
                                        value="{{ $type->value }}"
                                        data-type-id = {{ $type->id }}
                                        data-type-name="{{ strtolower($type->value->name) }}"
                                    >
                                    <span>{{ $type->value->label() }}</span>
                                </label>
                            @endforeach
                        </div>
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
                            <div class="col-md-12">
                                <div id="editFileInputsContainer" class="dynamic-upload-container mb-1">
                                </div>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="col-md-12">
                            <div id="editPreviewContainer" class="dynamic-upload-container mb-1"></div>
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
<script !src="">
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.type-checkbox');
        const editFileInputsContainer = document.getElementById('editFileInputsContainer');

        function renderFileInputs() {
            if (!editFileInputsContainer) return;

            editFileInputsContainer.innerHTML = ''; // Clear existing inputs

            let selectedTypes = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(cb => cb.dataset.typeName);

            selectedTypes.forEach(type => {
                const typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
                const uniqueId = type + '-' + Math.random().toString(36).substring(7);

                const block = document.createElement('div');
                block.classList.add('mb-3');

                block.innerHTML = `
                    <label class="form-label label-text">${typeLabel} Base Image</label>
                    <input type="file" name="${type}_base_image" id="${type}-base-input" class="d-none" accept="image/*">
                    <div class="upload-card upload-area" data-input-id="${type}-base-input">
                        <div class="upload-content">
                            <i data-feather="upload" class="mb-2"></i>
                            <p>${typeLabel} Base Image: Drag file here or click to upload</p>
                            <div class="preview mt-1"></div>
                        </div>
                    </div>

                    <label class="form-label label-text mt-2">${typeLabel} Mask Image</label>
                    <input type="file" name="${type}_mask_image" id="${type}-mask-input" class="d-none" accept="image/*">
                    <div class="upload-card upload-area" data-input-id="${type}-mask-input">
                        <div class="upload-content">
                            <i data-feather="upload" class="mb-2"></i>
                            <p>${typeLabel} Mask Image: Drag file here or click to upload</p>
                            <div class="preview mt-1"></div>
                        </div>
                    </div>
                `;

                editFileInputsContainer.appendChild(block);
            });

            feather.replace(); // Re-render feather icons
            bindUploadAreas(); // Bind dynamic handlers
        }

        function bindUploadAreas() {
            document.querySelectorAll('.upload-area').forEach(area => {
                const inputId = area.dataset.inputId;
                const input = document.getElementById(inputId);
                const preview = area.querySelector('.preview');

                area.addEventListener('click', () => input?.click());

                area.addEventListener('dragover', e => {
                    e.preventDefault();
                    area.classList.add('dragover');
                });

                area.addEventListener('dragleave', e => {
                    e.preventDefault();
                    area.classList.remove('dragover');
                });

                area.addEventListener('drop', e => {
                    e.preventDefault();
                    area.classList.remove('dragover');
                    handleFiles(e.dataTransfer.files, input, preview);
                });

                input?.addEventListener('change', e => {
                    handleFiles(e.target.files, input, preview);
                });
            });
        }

        function handleFiles(files, input, preview) {
            if (!files.length) return;

            const file = files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid rounded border" style="max-height: 120px;">`;
            };
            reader.readAsDataURL(file);

            // For drag/drop: manually assign to input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
        }

        function toggleCheckboxes() {
            let frontChecked = false;
            let backChecked = false;
            let noneChecked = false;

            checkboxes.forEach(checkbox => {
                const type = checkbox.dataset.typeName;
                if (type === 'front' && checkbox.checked) frontChecked = true;
                if (type === 'back' && checkbox.checked) backChecked = true;
                if (type === 'none' && checkbox.checked) noneChecked = true;
            });

            checkboxes.forEach(checkbox => {
                const type = checkbox.dataset.typeName;
                checkbox.disabled = (
                    (noneChecked && (type === 'front' || type === 'back')) ||
                    ((frontChecked || backChecked) && type === 'none')
                );
            });

            renderFileInputs();
        }

        checkboxes.forEach(checkbox => checkbox.addEventListener('change', toggleCheckboxes));
        toggleCheckboxes(); // Init
    });

</script>

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
