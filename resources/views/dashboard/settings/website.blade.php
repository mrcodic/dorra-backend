@extends('layouts/contentLayoutMaster')

@section('title', 'Settings Details')
@section('main-page', 'Details')

@section('vendor-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">


<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
<div class="card  ">
    {{-- Left Side: Vertical Tabs --}}
    <div class="card-body">
        <ul class="nav nav-tabs nav-fill border-bottom-0">
            <li class="nav-item">
                <a class="nav-link active custom-tab" data-bs-toggle="tab" href="#tab1">1. Hero</a>
            </li>
            <li class="nav-item">
                <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab2">2. Best Sellers</a>
            </li>
            <li class="nav-item">
                <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab3">
                    3. Popular Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab4">
                    4. Logo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab5">
                    5. Category
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab6">
                    6. Testimonials
                </a>
            </li>
        </ul>

        {{-- Right Side: Tab Content --}}
        <div class="tab-content flex-grow-1 p-3" id="v-pills-tabContent">
            <!-- tab1 Section -->
            <div class="tab-pane fade show active" id="tab1">
                <!-- Card -->
                <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-3"
                    style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                    <span class="fw-semibold text-black fs-4">Show Hero Section</span>

                    <!-- Toggle Switch -->
                    <div class="form-check form-switch">
                        <input class="form-check-input toggle-switch" type="checkbox" id="heroToggle">
                    </div>
                </div>

                <!-- first image -->
                <div class="col-md-12 mb-2">
                    <label class="form-label label-text" for="product-image-first">First photo (3:4, 323×432 px)</label>

                    <!-- Hidden File Input -->
                    <input type="file" name="image_first" id="product-image-first" class="form-control d-none" accept="image/*">

                    <!-- Upload Card -->
                    <div id="upload-area-first" class="upload-card">
                        <div id="upload-content-first" class="d-flex gap-1 justify-content-center align-items-center" class="d-flex gap-1 justify-content-center align-items-center">
                            <i data-feather="upload" class="mb-2"></i>
                            <p>Drag image here to upload</p>
                        </div>
                    </div>

                    <!-- Progress & Preview -->
                    <div>
                        <div id="upload-progress-first" class="progress mt-2 d-none w-50">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>

                        <div id="uploaded-image-first" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                            <img src="" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <div id="file-details-first" class="file-details">
                                <div class="file-name fw-bold"></div>
                                <div class="file-size text-muted small"></div>
                            </div>
                            <button type="button" id="remove-image-first" class="btn btn-sm position-absolute text-danger" style="top: 5px; right: 5px; background-color: #FFEEED">
                                <i data-feather="trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- second image -->
                <div class="col-md-12 mb-2">
                    <label class="form-label label-text" for="product-image-second">Second photo (3:4, 213*285 px)</label>

                    <!-- Hidden File Input -->
                    <input type="file" name="image_second" id="product-image-second" class="form-control d-none" accept="image/*">

                    <!-- Upload Card -->
                    <div id="upload-area-second" class="upload-card">
                        <div id="upload-content-second" class="d-flex gap-1 justify-content-center align-items-center">
                            <i data-feather="upload" class="mb-2"></i>
                            <p>Drag image here to upload</p>
                        </div>
                    </div>

                    <!-- Progress & Preview -->
                    <div>
                        <div id="upload-progress-second" class="progress mt-2 d-none w-50">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>

                        <div id="uploaded-image-second" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                            <img src="" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <div id="file-details-second" class="file-details">
                                <div class="file-name fw-bold"></div>
                                <div class="file-size text-muted small"></div>
                            </div>
                            <button type="button" id="remove-image-second" class="btn btn-sm position-absolute text-danger" style="top: 5px; right: 5px; background-color: #FFEEED">
                                <i data-feather="trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- third image -->
                <div class="col-md-12 mb-2">
                    <label class="form-label label-text" for="product-image-third">Third photo (3:4, 232*310 px)</label>

                    <!-- Hidden File Input -->
                    <input type="file" name="image_third" id="product-image-third" class="form-control d-none" accept="image/*">

                    <!-- Upload Card -->
                    <div id="upload-area-third" class="upload-card">
                        <div id="upload-content-third" class="d-flex gap-1 justify-content-center align-items-center">
                            <i data-feather="upload" class="mb-2"></i>
                            <p>Drag image here to upload</p>
                        </div>
                    </div>

                    <!-- Progress & Preview -->
                    <div>
                        <div id="upload-progress-third" class="progress mt-2 d-none w-50">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>

                        <div id="uploaded-image-third" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                            <img src="" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <div id="file-details-third" class="file-details">
                                <div class="file-name fw-bold"></div>
                                <div class="file-size text-muted small"></div>
                            </div>
                            <button type="button" id="remove-image-third" class="btn btn-sm position-absolute text-danger" style="top: 5px; right: 5px; background-color: #FFEEED">
                                <i data-feather="trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- fourth image -->
                <div class="col-md-12 mb-2">
                    <label class="form-label label-text" for="product-image-fourth">Fourth photo (3:4, 295*395 px)</label>

                    <!-- Hidden File Input -->
                    <input type="file" name="image_fourth" id="product-image-fourth" class="form-control d-none" accept="image/*">

                    <!-- Upload Card -->
                    <div id="upload-area-fourth" class="upload-card">
                        <div id="upload-content-fourth" class="d-flex gap-1 justify-content-center align-items-center">
                            <i data-feather="upload" class="mb-2"></i>
                            <p>Drag image here to upload</p>
                        </div>
                    </div>

                    <!-- Progress & Preview -->
                    <div>
                        <div id="upload-progress-fourth" class="progress mt-2 d-none w-50">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>

                        <div id="uploaded-image-fourth" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                            <img src="" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            <div id="file-details-fourth" class="file-details">
                                <div class="file-name fw-bold"></div>
                                <div class="file-size text-muted small"></div>
                            </div>
                            <button type="button" id="remove-image-fourth" class="btn btn-sm position-absolute text-danger" style="top: 5px; right: 5px; background-color: #FFEEED">
                                <i data-feather="trash"></i>
                            </button>
                        </div>
                    </div>
                </div>


            </div>

            <!-- tab2 -->
            <div class="tab-pane fade" id="tab2">
                <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                    style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                    <span class="fw-semibold text-black fs-4">Show Best Sellers Section</span>

                    <!-- Toggle Switch -->
                    <div class="form-check form-switch">
                        <input class="form-check-input toggle-switch" type="checkbox" id="bestSellersToggle">
                    </div>
                </div>

                <div class=" d-flex flex-row align-items-center p-1 mb-2"
                    style="background-color: #F4F6F6; border-radius: 10px; border: none;">
                    <span class="fw-semibold text-black fs-4">You can add up to </span><span class="fw-semibold fs-4 ms-1" style="color: #24B094;">8 Products</span>
                </div>

                <!-- Input and Add Button Row -->
                <p class="fw-semibold text-black fs-4">Product Name</p>
                <div class="row g-2 mb-2">
                    <div class="col-9">
                        <input type="text" class="form-control" placeholder="Enter product name">
                    </div>
                    <div class="col-3">
                        <button class="btn btn-primary w-100">Add Product</button>
                    </div>
                </div>
                <p class="fw-semibold text-black fs-4">Added Products</p>
                <!-- Products Grid -->
                <div class="row">
                    <!-- Product Card -->
                    <div class="col-md-6 mb-3">
                        <div class=" p-2 d-flex flex-row align-items-center" style="box-shadow: 0px 4px 6px 0px #4247460F; border-radius: 10px;">
                            <!-- Image -->
                            <img src="https://via.placeholder.com/80" alt="Product" class="me-3 rounded" style="width: 80px; height: 80px; object-fit: cover;">

                            <!-- Details -->
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-black fs-5">Product Name</div>
                                <div class="">Category: <span class="fw-semibold text-black">Category Value</span></div>
                            </div>

                            <!-- Remove Button -->
                            <button class="btn btn-outline-secondary btn-sm ms-2">
                                Remove
                            </button>
                        </div>
                    </div>

                    <!-- Duplicate the above .col-md-6 for each product added -->
                    <div class="col-md-6 mb-3">
                        <div class=" p-2 d-flex flex-row align-items-center" style="box-shadow: 0px 4px 6px 0px #4247460F; border-radius: 10px;">
                            <img src="https://via.placeholder.com/80" alt="Product" class="me-3 rounded" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-black fs-5">Another Product</div>
                                <div class="">Category: <span class="fw-semibold text-black">Another Category</span></div>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm ms-2">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- tab3 -->
            <div class="tab-pane fade" id="tab3">


            </div>

            <!-- tab4 -->
            <div class="tab-pane fade" id="tab4">

            </div>
            <!-- tab4 -->
            <div class="tab-pane fade" id="tab5">

            </div>
            <!-- tab4 -->
            <div class="tab-pane fade" id="tab6">

            </div>

        </div>
    </div>

</div>
</div>

@endsection

@section('vendor-script')
{{-- Vendor js files --}}
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/jszip.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/pdfmake.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/vfs_fonts.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/buttons.print.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
    $(document).ready(function() {
        const hash = window.location.hash;
        if (hash) {
            const tabTrigger = $('.nav-link[href="' + hash + '"]');
            if (tabTrigger.length) {
                tabTrigger.tab('show'); // Activates the tab
            }
        }
    })
</script>
<script>
    function initUploadHandlers(prefix) {
        let input = $(`#product-image-${prefix}`);
        let uploadArea = $(`#upload-area-${prefix}`);
        let progress = $(`#upload-progress-${prefix}`);
        let progressBar = progress.find('.progress-bar');
        let uploadedImage = $(`#uploaded-image-${prefix}`);
        let removeButton = $(`#remove-image-${prefix}`);

        // Trigger hidden input on click
        uploadArea.on('click', () => input.click());

        // File selected via input
        input.on('change', (e) => handleFiles(e.target.files));

        // Drag & drop events
        uploadArea.on('dragover', (e) => {
            e.preventDefault();
            uploadArea.addClass('dragover');
        });
        uploadArea.on('dragleave', (e) => {
            e.preventDefault();
            uploadArea.removeClass('dragover');
        });
        uploadArea.on('drop', (e) => {
            e.preventDefault();
            uploadArea.removeClass('dragover');
            handleFiles(e.originalEvent.dataTransfer.files);
        });

        // Remove image
        removeButton.on('click', () => {
            uploadedImage.addClass('d-none');
            input.val('');
        });

        function handleFiles(files) {
            if (files.length > 0) {
                let file = files[0];

                // Attach file to input
                let dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input[0].files = dataTransfer.files;

                progress.removeClass('d-none');
                progressBar.css('width', '0%');

                let fakeProgress = 0;
                let interval = setInterval(() => {
                    fakeProgress += 10;
                    progressBar.css('width', `${fakeProgress}%`);

                    if (fakeProgress >= 100) {
                        clearInterval(interval);

                        // Show preview
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            uploadedImage.find('img').attr('src', e.target.result);
                            uploadedImage.removeClass('d-none');
                            progress.addClass('d-none');

                            $(`#file-details-${prefix} .file-name`).text(file.name);
                            $(`#file-details-${prefix} .file-size`).text((file.size / 1024).toFixed(2) + ' KB');
                        };
                        reader.readAsDataURL(file);
                    }
                }, 100);
            }
        }
    }

    // Init all uploaders
    $(document).ready(() => {
        initUploadHandlers('first');
        initUploadHandlers('second');
        initUploadHandlers('third');
        initUploadHandlers('fourth');
    });
</script>


{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-product-list.js') }}?v={{ time() }}"></script>
@endsection