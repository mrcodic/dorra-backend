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
                            <select id="mockup-type" name="type" class="form-select">
                                <option value="" disabled>select mockup type</option>
                                @foreach(\App\Enums\Mockup\TypeEnum::cases() as $type)
                                <option value="{{ $type->value }}"> {{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label for="productsSelect" class="label-text mb-1">Product</label>
                            <select id="productsSelect" name="product_id" class="form-select select2" multiple>
                                <option value="" disabled>Choose product</option>
                                @foreach($associatedData['products'] as $product)
                                <option
                                    value="{{ $product->id }}">{{ $product->getTranslation('name', app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="label-text mb-1 d-block">Colors</label>
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <button type="button" id="openColorPicker" class="gradient-picker-trigger border"></button>

                                <span id="selected-colors" class=" d-flex gap-1 flex-wrap align-items-center"></span>
                            </div>
                            <input type="hidden" name="colors[]" id="colorsInput">

                        </div>

                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label label-text" for="product-image-main">Mockup File</label>

                                <!-- Hidden real input -->
                                <input type="file" name="image" id="product-image-main" class="form-control d-none"
                                    accept="image/*">

                                <!-- Custom Upload Card -->
                                <div id="upload-area" class="upload-card">
                                    <div id="upload-content">
                                        <i data-feather="upload" class="mb-2"></i>
                                        <p>Drag file here to upload</p>
                                    </div>


                                </div>
                                <div>
                                    <!-- Progress Bar -->
                                    <div id="upload-progress" class="progress mt-2 d-none w-50">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            style="width: 0%"></div>
                                    </div>


                                    <!-- Uploaded Image Preview -->
                                    <div id="uploaded-image"
                                        class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                                        <img src="" alt="Uploaded" class="img-fluid rounded"
                                            style="width: 50px; height: 50px; object-fit: cover;">
                                        <div id="file-details" class="file-details">
                                            <div class="file-name fw-bold"></div>
                                            <div class="file-size text-muted small"></div>
                                        </div>
                                        <button type="button" id="remove-image"
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
                        <span class="btn-text">Create</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .gradient-picker-trigger{
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-image: url('/images/AddColor.svg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border: 1px solid #ccc;
        cursor: pointer;
    }


    .selected-color-wrapper {
        position: relative;
        width: 28px;
        height: 28px;
    }

    .selected-color-dot {
        width: 100%;
        height: 100%;
        padding: 1px;
        border-radius: 50%;
        border: 2px solid #ccc;
        box-sizing: border-box;
        background-clip: content-box;
    }

    .selected-color-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
    }

    .remove-color-btn {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #F4F6F6 !important;
        color: #424746 !important;
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
    $(document).ready(function() {
        $('#productsSelect').select2({
            placeholder: 'Choose product',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#addMockupModal')
        });
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
    $(document).ready(function() {
        const dummyElement = document.createElement('div');
        document.body.appendChild(dummyElement); // <-- append to body

        const pickr = Pickr.create({
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

        let selectedColors = [];

        $('#openColorPicker').on('click', function() {
            const trigger = document.getElementById('openColorPicker');
            const rect = trigger.getBoundingClientRect();

            // Show the picker first so it gets rendered
            pickr.show();

            // Wait for next frame to ensure it's in the DOM
            setTimeout(() => {
                const pickerPanel = document.querySelector('.pcr-app');

                if (pickerPanel) {
                    pickerPanel.style.position = 'absolute';
                    pickerPanel.style.left = `${rect.left + window.scrollX}px`;
                    pickerPanel.style.top = `${rect.bottom + window.scrollY + 5}px`; // 5px gap
                    pickerPanel.style.zIndex = 9999; // Ensure it's on top
                }
            }, 0);
        });


        pickr.on('save', (color) => {
            const hex = color.toHEXA().toString();

            if (!selectedColors.includes(hex)) {
                selectedColors.push(hex);
                renderSelectedColors();
            }

            pickr.hide();
        });

        function renderSelectedColors() {
            const ul = document.getElementById('selected-colors');
            ul.innerHTML = '';
            selectedColors.forEach(c => {
                const li = document.createElement('li');
                li.innerHTML = `
                <div class="selected-color-wrapper">
                    <div class="selected-color-dot" style="background-color: #fff;">
                        <div class="selected-color-inner" style="background-color: ${c};"></div>
                    </div>
                    <button type="button" onclick="removeColor('${c}')" class="remove-color-btn">×</button>
                </div>
            `;
                ul.appendChild(li);
            });

            $('#colorsInput').val(selectedColors.join(','));
        }

        window.removeColor = function(hex) {
            selectedColors = selectedColors.filter(c => c !== hex);
            renderSelectedColors();
        };
    });
</script>