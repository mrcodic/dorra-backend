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
                            <select id="edit-products-select" name="product_id" class="form-select select2" multiple>
                                <option value="" disabled>Choose product</option>
                                @foreach($associatedData['products'] as $product)
                                    <option
                                        value="{{ $product->id }}">{{ $product->getTranslation('name', app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="label-text mb-1">Colors</label>
                            <button id="edit-color-picker-trigger" type="button">Pick colors</button>
                            <ul id="edit-selected-colors"></ul>
                            <input type="hidden" name="colors[]" id="edit-colorsInput">
                            <div id="previous-colors" class="d-flex flex-wrap gap-2 mt-2"></div>

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
                                        <button type="button" id="edit-remove-image"
                                                class="btn btn-sm position-absolute text-danger"
                                                style="top: 5px; right: 5px; background-color: #FFEEED">
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
                        <span class="btn-text">Save Chenges</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                              aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        handleAjaxFormSubmit("#editMockupForm", {
            successMessage: "Mockup Created Successfully",
            onSuccess: function () {
                $('#editMockupModal').modal('hide');
                location.reload();
            }
        })
        $('#edit-products-select').select2({
            placeholder: 'Choose product',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editMockupModal')
        });

        const editPickr = Pickr.create({
            el: '#edit-color-picker-trigger',
            theme: 'classic',
            default: '#ff0000',
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    input: true,
                    save: true,
                    clear: true
                }
            }
        });

        let editSelectedColors = [];

        editPickr.on('save', (color) => {
            const hex = color.toHEXA().toString();
            if (!editSelectedColors.includes(hex)) {
                editSelectedColors.push(hex);
                renderEditSelectedColors();
            }
            editPickr.hide();
        });

        function renderEditSelectedColors() {
            const ul = document.getElementById('edit-selected-colors');
            ul.innerHTML = '';
            editSelectedColors.forEach(c => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <span class="color-dot rounded-circle" style="background:${c}; display:inline-block; width:20px; height:20px; border:1px solid #ccc;"></span>
                    <button type="button" onclick="removeEditColor('${c}')" class="btn btn-sm text-danger ms-1">x</button>
                `;
                ul.appendChild(li);
            });
            $('#edit-colorsInput').val(editSelectedColors.join(','));
        }

        window.removeEditColor = function (hex) {
            editSelectedColors = editSelectedColors.filter(c => c !== hex);
            renderEditSelectedColors();
        };


        let editInput = $('#edit-product-image-main');
        let editUploadArea = $('#edit-upload-area');
        let editProgress = $('#edit-upload-progress');
        let editProgressBar = $('#edit-upload-progress-bar');
        let editUploadedImage = $('#edit-uploaded-image');
        let editRemoveButton = $('#edit-remove-image');

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
                let file = files[0];
                let dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                editInput[0].files = dataTransfer.files;

                editProgress.removeClass('d-none');
                editProgressBar.css('width', '0%');

                let fakeProgress = 0;
                let interval = setInterval(function () {
                    fakeProgress += 10;
                    editProgressBar.css('width', fakeProgress + '%');

                    if (fakeProgress >= 100) {
                        clearInterval(interval);

                        let reader = new FileReader();
                        reader.onload = function (e) {
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

        editRemoveButton.on('click', function () {
            editUploadedImage.addClass('d-none');
            editInput.val('');
        });
    });
</script>
