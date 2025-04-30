@extends('layouts/contentLayoutMaster')

@section('title', 'Edit Products')
@section('main-page', 'Products')
@section('sub-page', 'Edit Product')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<section id="multiple-column-form">
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <form id="product-form" class="form" action="{{ route('products.update',$model->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method("PUT")
                        <div class="row">
                            <!-- Product Name EN/AR -->
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="product-name-en">Product Name (EN)</label>
                                    <input type="text" id="product-name-en" class="form-control" name="name[en]" value="{{ $model->getTranslation('name','en') }}" placeholder="Product Name (EN)" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="product-name-ar">Product Name (AR)</label>
                                    <input type="text" id="product-name-ar" class="form-control" name="name[ar]" value="{{ $model->getTranslation('name','ar') }}" placeholder="Product Name (AR)" />
                                </div>
                            </div>

                            <!-- Description EN/AR -->
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="description-en">Product Description (EN)</label>
                                    <textarea name="description[en]" id="description-en" class="form-control" placeholder="Product Description (EN)">{{ $model->getTranslation('name','en') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="description-ar">Product Description (AR)</label>
                                    <textarea name="description[ar]" id="description-ar" class="form-control" placeholder="Product Description (AR)">{{ $model->getTranslation('name','ar') }}</textarea>
                                </div>
                            </div>

                            <!-- Main Image Upload -->
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="product-image-main">Product Image (main)*</label>

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

                            <!-- Multiple Images Upload -->
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="product-images">Product Images</label>

                                    <!-- Hidden real input -->
                                    <input type="file" name="images[]" id="product-images" class="form-control d-none" multiple accept="image/*">

                                    <!-- Custom Upload Card -->
                                    <div id="multi-upload-area" class="upload-card">
                                        <div id="multi-upload-content">
                                            <i data-feather="upload" class="mb-2"></i>
                                            <p>Drag images here to upload</p>
                                        </div>
                                    </div>

                                    <!-- Uploaded Images Preview Area -->
                                    <div id="multi-uploaded-images" class=" mt-3"></div>
                                </div>
                            </div>

                            <!-- Category & Subcategory -->
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="category">Category</label>
                                    <select name="category_id" id="category" class="form-control category-select">
                                        <option value="">Select category</option>
                                        @foreach($associatedData['categories'] as $category)
                                        <option value="{{ $category->id }}" @selected($category->id == $model->category?->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="sub-category">Subcategory</label>
                                    <select name="sub_category_id" id="sub-category" class="form-control sub-category-select" data-sub-category-url="{{ route('sub-categories') }}">
                                        <option value="">Select subcategory</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="tags">Tags</label>
                                    <select name="tags[]" id="tags" class="select2 form-select" multiple>
                                        @foreach($associatedData['tags'] as $tag)
                                        <option value="{{ $tag->id }}" @if(in_array($tag->id, $model->tags->pluck('id')->toArray())) selected @endif >{{ $tag->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Price Option Toggle -->
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label label-text d-block">Quantity & Price Options</label>
                                    <label class="form-label label-text mt-2">Quantity Type</label>
                                    <div class="row gap-2 " style="margin: 2px;">
                                        <div class="col border rounded-3 p-1">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="has_custom_prices" id="customPrice" value="1"
                                                    @checked($model->has_custom_prices == 1)>
                                                <div>
                                                    <label class="form-check-label label-text d-block" for="customPrice">Add Quantity Manually</label>
                                                    <label class="form-check-label text-dark" for="customPrice">Custom Prices</label>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col border rounded-3 p-1">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input " type="radio" name="has_custom_prices" id="defaultPrice" value="0"
                                                    @checked($model->has_custom_prices == 0)>
                                                <div>
                                                    <label class="form-check-label label-text d-block" for="customPrice">Default Quantity</label>
                                                    <label class="form-check-label text-dark" for="defaultPrice">Default Price</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Prices -->
                            <div class="col-md-12" id="custom-price-section" style="{{ $model->has_custom_prices == 1 ? '' : 'display:none;' }}">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="invoice-repeater">
                                            <div data-repeater-list="prices">
                                                <div data-repeater-item>
                                                    <div class="row d-flex align-items-end">
                                                        @foreach ($model->prices as $index => $price)
                                                        <div class="col-md-4">
                                                            <div class="mb-1">
                                                                <label class="form-label label-text">Quantity</label>
                                                                <input type="number" name="prices[{{ $index }}][quantity]" value="{{ $price->quantity }}" class="form-control" placeholder="Add Quantity" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-1">
                                                                <label class="form-label label-text">Price</label>
                                                                <input type="text" name="prices[{{ $index }}][price]" value="{{ $price->price }}" class="form-control" placeholder="Add Price" />
                                                            </div>
                                                        </div>
                                                        @endforeach

                                                        <div class="col-md-4">
                                                            <div class="mb-1">
                                                                <button type="button" class="btn btn-outline-danger text-nowrap px-1" data-repeater-delete>
                                                                    <i data-feather="x" class="me-25"></i> <span>Delete</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button type="button" class="btn btn-icon btn-primary" data-repeater-create>
                                                        <i data-feather="plus" class="me-25"></i> <span>Add New</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Default Price -->
                            <div class="col-md-12" id="default-price-section" style="{{ $model->has_custom_prices == 0 ? '' : 'display:none;' }}">
                                <div class="mb-1">
                                    <label class="form-label label-text" for="base_price">Original Price (EGP) (Per Item)</label>
                                    <input type="text" id="base_price" name="base_price" value="{{ $model->base_price }}" class="form-control" placeholder="Original Price" />
                                </div>
                            </div>

                            <!-- Specifications -->
                            <div class="col-12">
                                <div class="mb-1">
                                    <label class="form-label label-text">Product Specs</label>
                                    <div class="">
                                        <div class="">
                                            <!-- Outer Repeater for Specifications -->
                                            <div class="outer-repeater">
                                                @foreach($model->specifications as $specification)

                                                <div data-repeater-list="specifications">
                                                    <div data-repeater-item>
                                                        <div class="row">
                                                            <!-- Specification Name (English) -->
                                                            <div class="col-md-6">
                                                                <div class="mb-1">
                                                                    <label class="form-label label-text">Specification Name (EN)</label>
                                                                    <input type="text" name="specifications[{{$loop->index}}][name_en]" value="{{ $specification->getTranslation('name','ar') }}" class="form-control" placeholder="Specification Name (EN)" />
                                                                </div>
                                                            </div>

                                                            <!-- Specification Name (Arabic) -->
                                                            <div class="col-md-6">
                                                                <div class="mb-1">
                                                                    <label class="form-label label-text">Specification Name (AR)</label>
                                                                    <input type="text" name="specifications[{{$loop->index}}][name_ar]" value="{{ $specification->getTranslation('name','ar') }}" class="form-control" placeholder="Specification Name (AR)" />
                                                                </div>
                                                            </div>

                                                            <!-- Inner Repeater for Specification Options -->
                                                            <div class="inner-repeater">
                                                                @foreach($specification->options as $option)
                                                                <div data-repeater-list="specification_options">
                                                                    <div data-repeater-item>
                                                                        <div class="row d-flex align-items-end mt-2">
                                                                            <!-- Option Name (English) -->
                                                                            <div class="col">
                                                                                <label class="label-text">Option (EN)</label>
                                                                                <input type="text" name="specifications[{{$loop->parent->index}}]specification_options[{{$loop->index}}][value_en]"
                                                                                    value="{{ $option->getTranslation('value','ar') }}"
                                                                                    class="form-control" placeholder="Option (EN)" />
                                                                            </div>

                                                                            <!-- Option Name (Arabic) -->
                                                                            <div class="col">
                                                                                <label class="label-text">Option (AR)</label>
                                                                                <input type="text" name="specifications[{{$loop->parent->index}}]specification_options[{{$loop->index}}][value_ar]"
                                                                                    value="{{ $option->getTranslation('value','ar') }}"
                                                                                    class="form-control" placeholder="Option (AR)" />
                                                                            </div>

                                                                            <!-- Option Price -->
                                                                            <div class="col">
                                                                                <label class="label-text">Price</label>
                                                                                <input type="text" name="specifications[{{$loop->parent->index}}]specification_options[{{$loop->index}}][price]" value="{{ $option->price }}" class="form-control" placeholder="Price" />
                                                                            </div>

                                                                        </div>
                                                                        <div class="row d-flex align-items-end mt-2">
                                                                            <!-- Option Image -->
                                                                            <div class="col-md-12">
                                                                                <label class="form-label label-text">Option Image</label>

                                                                                <!-- Hidden real input -->
                                                                                <input type="file" id="option-image-input" name="image" class="form-control d-none" accept="image/*">

                                                                                <!-- Custom Upload Card -->
                                                                                <div id="option-upload-area" class="upload-card">
                                                                                    <div id="option-upload-content" class="d-flex justify-content-center align-items-center gap-1">
                                                                                        <i data-feather="upload" class="mb-2"></i>
                                                                                        <p>Drag image here to upload</p>
                                                                                    </div>
                                                                                </div>

                                                                                <!-- Progress Bar -->
                                                                                <div id="option-upload-progress" class="progress mt-2 d-none w-50">
                                                                                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                                                                                </div>

                                                                                <!-- Uploaded Image Preview -->
                                                                                <div id="option-uploaded-image" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                                                                                    <img src="" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                                                    <div class="file-details">
                                                                                        <div class="file-name fw-bold"></div>
                                                                                        <div class="file-size text-muted small"></div>
                                                                                    </div>
                                                                                    <button type="button" id="option-remove-image" class="btn btn-sm position-absolute text-danger" style="top: 5px; right: 5px; background-color: #FFEEED">
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
                                                                @endforeach

                                                                <!-- Add Option Button -->
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <button type="button" class="btn primary-text-color bg-white mt-2 text-decoration-underline" style="padding: 0px;" data-repeater-create>
                                                                            <i data-feather="plus"></i> <span> Add New Value</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End of Inner Repeater -->

                                                            <!-- Delete Specification Button -->
                                                            <div class="col-12 text-end mt-1 mb-2">
                                                                <button type="button" class="btn btn-outline-danger" data-repeater-delete>
                                                                    <i data-feather="x" class="me-25"></i> Delete Spec
                                                                </button>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                                <!-- Add Specification Button -->
                                                <div class="row">
                                                    <div class="col-12">
                                                        <button type="button" class="w-100  rounded-3 p-1 text-dark" style="border:2px dashed #CED5D4;background-color:#EBEFEF" data-repeater-create>
                                                            <i data-feather="plus" class="me-25"></i> <span>Add New Spec</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Free Shipping -->
                                <div class="col-md-12 col-12 mb-2">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_free_shipping" value="0">
                                        <input type="checkbox" class="form-check-input" id="free-shipping" name="is_free_shipping" value="1">
                                        <label class="form-check-label" for="free-shipping">Product available for free shipping</label>
                                    </div>
                                </div>
                            </div>

                              <!-- Submit -->
                            <div class="col-12 d-flex justify-content-end gap-1">
                                <button type="reset" class="btn btn-outline-secondary fs-5">Cancel</button>
                                <button type="submit" class="btn btn-primary me-1 fs-5">Save Changes</button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('vendor-script')
<script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle pricing
        $('input[name="has_custom_prices"]').on('change', function() {
            const isCustom = $(this).val() === '1';
            $('#custom-price-section').toggle(isCustom).find('input').prop('disabled', !isCustom);
            $('#default-price-section').toggle(!isCustom).find('input').prop('disabled', isCustom).val('');
        });

        // Repeater
        $('.invoice-repeater').repeater({
            show: function() {
                $(this).slideDown();
                feather && feather.replace();
            },
            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);
            }
        });

        $('.outer-repeater').repeater({
            repeaters: [{
                selector: '.inner-repeater',
                show: function() {
                    $(this).slideDown();
                },
                hide: function(deleteElement) {
                    if (confirm('Are you sure you want to delete this value?')) {
                        $(this).slideUp(deleteElement);
                    }
                },
                nestedInputName: 'specification_options'
            }],
            show: function() {
                $(this).slideDown();
            },
            hide: function(deleteElement) {
                if (confirm('Are you sure you want to delete this specification?')) {
                    $(this).slideUp(deleteElement);
                }
            }
        });

        $('.select2').select2();

        // Category -> Subcategory
        $('.category-select').on('change', function() {
            const categoryId = $(this).val();
            const $subCategorySelect = $('.sub-category-select');
            $.ajax({
                url: `${$subCategorySelect.data('sub-category-url')}?filter[parent_id]=${categoryId}`,
                method: "GET",
                success: function(res) {
                    $subCategorySelect.empty().append('<option value="">Select subcategory</option>');
                    $.each(res.data, (i, s) => $subCategorySelect.append(`<option value="${s.id}">${s.name}</option>`));
                },
                error: function() {
                    $subCategorySelect.empty().append('<option value="">Error loading Subcategories</option>');
                }
            });
        });

        // Form submit
        $('#product-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: this.action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        sessionStorage.setItem('product_added', 'true');
                        window.location.href = '/products';
                    }
                },
                error: function(xhr) {
                    $.each(xhr.responseJSON.errors, (k, msgArr) => {
                        Toastify({
                            text: msgArr[0],
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true
                        }).showToast();
                    });
                }
            });
        });
    });
</script>
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
<script>
    $(document).ready(function() {
        let input = $('#product-images');
        let uploadArea = $('#multi-upload-area');
        let uploadedImages = $('#multi-uploaded-images');

        // Click to open file input
        uploadArea.on('click', function() {
            input.click();
        });

        // Handle input change
        input.on('change', function(e) {
            handleFiles(e.target.files);
        });

        // Drag and Drop
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
            for (let i = 0; i < files.length; i++) {
                uploadFile(files[i]);
            }
        }

        function uploadFile(file) {
            if (!file.type.startsWith('image/')) return;

            const fileSizeKB = (file.size / 1024).toFixed(2) + ' KB';

            // Create wrapper with image hidden initially
            let wrapper = $(`
            <div class="image-wrapper position-relative mb-3 d-flex align-items-center gap-2">
                <img src="" alt="Uploading..." style="width: 50px; height: 50px; object-fit: cover; display: none;">
                <div class="file-info">
                    <div class="file-name fw-bold">${file.name}</div>
                    <div class="file-size text-muted small">${fileSizeKB}</div>
                    <div class="progress mt-2 w-50" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                    </div>
                </div>
                <button type="button" class="remove-btn btn btn-sm text-danger ms-auto" style="background-color:#FFEEED;">
                    <i data-feather="trash"></i>
                </button>
            </div>
        `);

            uploadedImages.append(wrapper);

            let progressBar = wrapper.find('.progress-bar');
            let imgTag = wrapper.find('img');

            // Fake upload progress
            let progress = 0;
            let interval = setInterval(function() {
                progress += 10;
                progressBar.css('width', progress + '%');

                if (progress >= 100) {
                    clearInterval(interval);

                    // Display the image after "upload" finishes
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        imgTag.attr('src', e.target.result).fadeIn();
                        wrapper.find('.progress').remove();
                    }
                    reader.readAsDataURL(file);
                }
            }, 100);

            // Remove button
            wrapper.find('.remove-btn').on('click', function() {
                wrapper.remove();
            });

            // Re-render feather icons
            feather.replace();
        }
    });
</script>
<script>
    $(document).ready(function() {
        let optionInput = $('#option-image-input');
        let optionUploadArea = $('#option-upload-area');
        let optionProgress = $('#option-upload-progress');
        let optionProgressBar = $('#option-upload-progress .progress-bar');
        let optionUploadedImage = $('#option-uploaded-image');
        let optionRemoveButton = $('#option-remove-image');

        // Click to open file input
        optionUploadArea.on('click', function() {
            optionInput.click();
        });

        // Handle input change
        optionInput.on('change', function(e) {
            handleOptionFiles(e.target.files);
        });

        // Drag and Drop
        optionUploadArea.on('dragover', function(e) {
            e.preventDefault();
            optionUploadArea.addClass('dragover');
        });

        optionUploadArea.on('dragleave', function(e) {
            e.preventDefault();
            optionUploadArea.removeClass('dragover');
        });

        optionUploadArea.on('drop', function(e) {
            e.preventDefault();
            optionUploadArea.removeClass('dragover');
            handleOptionFiles(e.originalEvent.dataTransfer.files);
        });

        function handleOptionFiles(files) {
            if (files.length > 0) {
                let file = files[0];

                optionProgress.removeClass('d-none');
                optionProgressBar.css('width', '0%');

                let fakeProgress = 0;
                let interval = setInterval(function() {
                    fakeProgress += 10;
                    optionProgressBar.css('width', fakeProgress + '%');

                    if (fakeProgress >= 100) {
                        clearInterval(interval);

                        let reader = new FileReader();
                        reader.onload = function(e) {
                            optionUploadedImage.find('img').attr('src', e.target.result);
                            optionUploadedImage.removeClass('d-none');
                            optionProgress.addClass('d-none');

                            // Show file name and size
                            $('#option-uploaded-image .file-name').text(file.name);
                            $('#option-uploaded-image .file-size').text((file.size / 1024).toFixed(2) + ' KB');
                        }
                        reader.readAsDataURL(file);
                    }
                }, 100);
            }
        }

        // Remove image
        optionRemoveButton.on('click', function() {
            optionUploadedImage.addClass('d-none');
            optionInput.val('');

            // Also clear file name and size
            $('#option-uploaded-image .file-name').text('');
            $('#option-uploaded-image .file-size').text('');
        });
    });
</script>
@endsection
