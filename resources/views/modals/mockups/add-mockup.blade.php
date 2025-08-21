<div class="modal modal-slide-in new-user-modal fade" id="addMockupModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addMockupForm" enctype="multipart/form-data" action="{{ route('mockups.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add Mockup</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="">
                        <div class="form-group mb-2">
                            <label for="templateName" class="label-text mb-1">Mockup Name</label>
                            <input type="text" id="templateName" class="form-control" name="name"
                                placeholder="Mockup Name">
                        </div>

                        <div class="form-group mb-2">
                            <label for="mockup-type" class="label-text mb-1">Mockup Type</label>
                            <div class="row">
                                @foreach(\App\Models\Type::all(['id','value']) as $type)
                                <div class="col-md-4 mb-1">
                                    <label class="radio-box">
                                        <input class="form-check-input type-checkbox" type="checkbox" name="types[]"
                                            value="{{ $type->value }}"
                                            data-type-name="{{ strtolower($type->value->name) }}">
                                        <span>{{ $type->value->label() }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>


                        <div class="form-group mb-2">
                            <label for="productsSelect" class="label-text mb-1">Product</label>
                            <select id="productsSelect" name="product_id" class="form-select">
                                <option value="" disabled selected>Choose product</option>
                                @foreach($associatedData['products'] as $product)
                                <option value="{{ $product->id }}">{{ $product->getTranslation('name',
                                    app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="label-text mb-1 d-block">Colors</label>
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <button type="button" id="openColorPicker"
                                    class="gradient-picker-trigger border"></button>


                                <span id="selected-colors" class=" d-flex gap-1 flex-wrap align-items-center"></span>
                            </div>
                            {{-- <input type="hidden" name="colors[]" id="colorsInput">--}}
                            <div id="colorsInputContainer"></div>

                        </div>

                        <div class="col-md-12">
                            <div id="fileInputsContainer" class="dynamic-upload-container mb-1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span class="btn-text">Create</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                role="status" aria-hidden="true"></span>
                        </button>
                    </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
                const checkboxes = document.querySelectorAll('.type-checkbox');
                const fileInputsContainer = document.getElementById('fileInputsContainer');

                function renderFileInputs() {
                if (!fileInputsContainer) return;

                fileInputsContainer.innerHTML = ''; // Clear existing inputs

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

                fileInputsContainer.appendChild(block);
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





<script>
    $(document).ready(function() {

            handleAjaxFormSubmit("#addMockupForm", {
                successMessage: "Mockup Created Successfully",
                onSuccess: function() {
                    $('#addMockupModal').modal('hide');
                    location.reload();
                }
            })
        });
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




    let selectedColors = [];
    let pickrInstance = null;

    $(document).ready(function () {
        $('#addMockupModal').on('shown.bs.modal', function () {
            // Destroy any existing instance
            if (pickrInstance) pickrInstance.destroyAndRemove();

            // Create hidden element for Pickr
            const dummyElement = document.createElement('div');
            document.body.appendChild(dummyElement);

            // Initialize Pickr
            pickrInstance = Pickr.create({
                el: dummyElement,
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

            // Save color
            pickrInstance.on('save', (color) => {
                const hex = color.toHEXA().toString();
                if (!selectedColors.includes(hex)) {
                    selectedColors.push(hex);
                    renderSelectedColors();
                }
                pickrInstance.hide();
            });
        });

        $('#openColorPicker').on('click', function () {
            const trigger = document.getElementById('openColorPicker');
            const rect = trigger.getBoundingClientRect();
            const modalScrollTop = document.querySelector('#addMockupModal .modal-body')?.scrollTop || 0;

            if (pickrInstance) {
                pickrInstance.show();

                setTimeout(() => {
                    const pickerPanel = document.querySelector('.pcr-app.visible');
                    if (pickerPanel) {
                        pickerPanel.style.position = 'absolute';
                        pickerPanel.style.left = `${rect.left + window.scrollX}px`;
                        pickerPanel.style.top = `${rect.bottom + window.scrollY + modalScrollTop + 5}px`;
                        pickerPanel.style.zIndex = 9999;
                    }
                }, 0);
            }
        });

        window.removeColor = function (hex) {
            selectedColors = selectedColors.filter(c => c !== hex);
            renderSelectedColors();
        };

        function renderSelectedColors() {
            const ul = document.getElementById('selected-colors');
            ul.innerHTML = '';

            const container = document.getElementById('colorsInputContainer');
            container.innerHTML = ''; // Clear previous

            selectedColors.forEach(c => {
                const li = document.createElement('li');
                li.innerHTML = `
                <div class="selected-color-wrapper position-relative">
                    <div class="selected-color-dot" style="background-color: #fff;">
                        <div class="selected-color-inner" style="background-color: ${c};"></div>
                    </div>
                    <button type="button" onclick="removeColor('${c}')" class="remove-color-btn">×</button>
                </div>
            `;
                ul.appendChild(li);

                // Add a hidden input for each color
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'colors[]';
                hiddenInput.value = c;
                container.appendChild(hiddenInput);
            });
        }

    });

</script>