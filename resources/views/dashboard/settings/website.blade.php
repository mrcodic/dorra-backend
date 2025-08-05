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
                    <a class="nav-link active custom-tab" data-bs-toggle="tab" href="#tab1">1. Navbar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab2">2. Hero</a>
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
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show categories in navbar</span>

                        <!-- Toggle Switch -->
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-switch" type="checkbox" id="bestSellersToggle">
                        </div>
                    </div>
                    <div class=" d-flex flex-row align-items-center p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: none;">
                        <span class="fw-semibold text-black fs-4">You can add up to </span><span
                            class="fw-semibold fs-4 ms-1" style="color: #24B094;">7 Categories</span>
                    </div>

                    <!-- Input and Add Button Row -->
                    <p class="fw-semibold text-black fs-4">Category</p>

                    <p class="fw-semibold text-black fs-4">Added Categories</p>
                    <div class="row">
                        <!-- Product Card -->
                        @forelse($categories as $category)
                            <!-- Product Card -->
                            <div class="col-md-6 mb-3">
                                <div class=" p-2 d-flex flex-row align-items-center"
                                     style="box-shadow: 0px 4px 6px 0px #4247460F; border-radius: 10px;">
                                    <!-- Image -->
                                    <img src="{{ $category->getFirstMediaUrl('categories')  }}" alt="Product"
                                         class="me-3 rounded" style="width: 80px; height: 80px; object-fit: cover;">

                                    <!-- Details -->
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-black fs-5">{{ $category->name }}</div>
                                    </div>

                                    <!-- Remove Button -->
                                    <button class="btn btn-outline-secondary btn-sm ms-2 remove-category"
                                            data-id="{{ $category->id }}">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center my-5">
                                <div class="mt-3 text-muted fs-5">No categories added yet.</div>
                                @endforelse
                                <div class="col-3 ms-auto">
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addLandingCategoryModal">Add Category</button>
                                </div>

                            </div>

                </div>

                <!-- tab2 -->
                    <div class="tab-pane fade" id="tab2">
                        <!-- Card -->
                        <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-3"
                             style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                            <span class="fw-semibold text-black fs-4">Show Hero Section</span>

                            <!-- Toggle Switch -->
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-switch" type="checkbox" id="heroToggle">
                            </div>
                        </div>

                        <div class="invoice-repeater">
                            <div class="invoice-repeater">

                                <div data-repeater-list="carousels">
                                    @foreach($carousels as $carousel)
                                        <div data-repeater-item>
                                            <div class="col-md-12 mb-2">
                                                <div class="card p-4 mb-4 border rounded shadow-sm">
                                                    <form action="{{ route("carousels.update", $carousel->id) }}"
                                                          method="POST"
                                                          enctype="multipart/form-data"
                                                          class="carousel">
                                                        @csrf
                                                        @method("PUT")

                                                        <!-- Website Image Upload -->
                                                        <label class="form-label">Website Image</label>
                                                        <div class="upload-wrapper">
                                                            <div class="upload-card border p-3 cursor-pointer text-center bg-light">
                                                                <div class="upload-content">
                                                                    <i data-feather="upload" class="mb-2"></i>
                                                                    <p>Click to upload website image</p>
                                                                </div>
                                                            </div>
                                                            <input type="file" name="image" class="form-control d-none image-input" accept="image/*">
                                                            <div class="uploaded-image{{ $carousel->getFirstMediaUrl('carousels') ? '':'d-none' }}  mt-2">
                                                                <img src="{{ $carousel->getFirstMediaUrl('carousels') }}"
                                                                     class="img-fluid rounded"
                                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                            </div>
                                                            <div class="progress upload-progress d-none">
                                                                <div class="progress-bar" style="width: 0%"></div>
                                                            </div>
                                                        </div>

                                                        <!-- Mobile Image Upload -->
                                                        <label class="form-label mt-3">Mobile Image</label>
                                                        <div class="upload-wrapper">
                                                            <div class="upload-card border p-3 cursor-pointer text-center bg-light">
                                                                <div class="upload-content">
                                                                    <i data-feather="upload" class="mb-2"></i>
                                                                    <p>Click to upload mobile image</p>
                                                                </div>
                                                            </div>
                                                            <input type="file" name="mobile_image" class="form-control d-none image-input" accept="image/*">
                                                            <div class="uploaded-image {{ $carousel->getFirstMediaUrl('mobile_carousels') ? '':'d-none' }} mt-2">
                                                                <img src="{{ $carousel->getFirstMediaUrl('mobile_carousels') }}"
                                                                     class="img-fluid rounded"
                                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                            </div>
                                                            <div class="progress upload-progress d-none">
                                                                <div class="progress-bar" style="width: 0%"></div>
                                                            </div>
                                                        </div>

                                                        <!-- Titles -->
                                                        <div class="row mb-3 mt-4">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Title in English</label>
                                                                <input type="text" name="title_en" class="form-control"
                                                                       value="{{ $carousel->getTranslation('title', 'en') }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Title in Arabic</label>
                                                                <input type="text" name="title_ar" class="form-control"
                                                                       value="{{ $carousel->getTranslation('title', 'ar') }}">
                                                            </div>
                                                        </div>

                                                        <!-- Subtitles -->
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Subtitle in English</label>
                                                                <input type="text" name="subtitle_en" class="form-control"
                                                                       value="{{ $carousel->getTranslation('subtitle', 'en') }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Subtitle in Arabic</label>
                                                                <input type="text" name="subtitle_ar" class="form-control"
                                                                       value="{{ $carousel->getTranslation('subtitle', 'ar') }}">
                                                            </div>
                                                        </div>

                                                        <!-- Product Selection -->
                                                        <div class="mb-2">
                                                            <label class="form-label">Select Product</label>
                                                            <select name="product_id" class="form-select">
                                                                <option disabled>Select a product</option>
                                                                @foreach($products as $product)
                                                                    <option value="{{ $product->id }}"
                                                                        {{ $carousel->product_id == $product->id ? 'selected' : '' }}>
                                                                        {{ $product->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <!-- Action Buttons -->
                                                        <div class="d-flex justify-content-between mt-3">
                                                            <button type="button" data-repeater-delete class="btn btn-outline-danger">
                                                                <i data-feather="x" class="me-1"></i> Delete
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i data-feather="save" class="me-1"></i> Save Changes
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Hidden template for new carousel --}}
                                {{-- Blank template for repeater --}}
                                <div data-repeater-item style="display: none;">
                                    <div class="col-md-12 mb-2">
                                        <div class="card p-4 mb-4 border rounded shadow-sm">
                                            <form method="POST" action="{{ route('carousels.update') }}" enctype="multipart/form-data" class="carousel">
                                                @csrf
                                                <input type="file" name="image" class="carousel-image-input form-control d-none" accept="image/*">

                                                <div class="upload-card">
                                                    <div class="upload-content">
                                                        <i data-feather="upload" class="mb-2"></i>
                                                        <p>Drag image here to upload</p>
                                                    </div>
                                                </div>

                                                <div class="uploaded-image position-relative mt-1 d-flex align-items-center gap-2 d-none">
                                                    <img src="" alt="Uploaded" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div class="file-details">
                                                        <div class="file-name fw-bold"></div>
                                                        <div class="file-size text-muted small"></div>
                                                    </div>
                                                </div>

                                                <div class="progress upload-progress d-none">
                                                    <div class="progress-bar" style="width: 0%"></div>
                                                </div>

                                                <div class="row mb-3 mt-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Title in English</label>
                                                        <input type="text" name="title_en" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Title in Arabic</label>
                                                        <input type="text" name="title_ar" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Subtitle in English</label>
                                                        <input type="text" name="subtitle_en" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Subtitle in Arabic</label>
                                                        <input type="text" name="subtitle_ar" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label">Select Product</label>
                                                    <select name="product_id" class="form-select">
                                                        <option disabled selected>Select a product</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="d-flex justify-content-between mt-2">
                                                    <button type="button" data-repeater-delete class="btn btn-outline-danger">
                                                        <i data-feather="x" class="me-25"></i> Delete
                                                    </button>
                                                    <button type="submit" class="btn btn-primary save-carousel-btn">
                                                        <i data-feather="save" class="me-25"></i> Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Add button --}}
                            <div class="text-start mt-1">
                                <button type="button" data-repeater-create class="btn btn-outline-secondary">
                                    <i data-feather="plus"></i> Add Carousel
                                </button>
                            </div>
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
    @include("modals.landing.add-category")


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
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>

@endsection

@section('page-script')

    <script>
        $(document).ready(function () {
            $('.carousel').each(function () {
                const form = $(this);
                const input = form.find('.mobile-carousel-image-input');
                const uploadArea = form.find('.upload-card');
                const uploadedImage = form.find('.mobile-uploaded-image');
                const progress = form.find('.mobile-upload-progress'); // Add this if needed
                const progressBar = progress.find('.mobile-progress-bar');

                uploadArea.on('click', () => input.click());

                input.on('change', function (e) {
                    const files = e.target.files;
                    if (files.length > 0) {
                        const file = files[0];

                        progress.removeClass('d-none');
                        progressBar.css('width', '0%');

                        let fakeProgress = 0;
                        let interval = setInterval(() => {
                            fakeProgress += 10;
                            progressBar.css('width', `${fakeProgress}%`);

                            if (fakeProgress >= 100) {
                                clearInterval(interval);

                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    uploadedImage.find('img').attr('src', e.target.result);
                                    uploadedImage.removeClass('d-none');
                                    progress.addClass('d-none');
                                };
                                reader.readAsDataURL(file);
                            }
                        }, 100);
                    }
                });
            });
        });

        $(document).on('change', 'input[type="file"][name="image"]', function (e) {
            const input = e.target;
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const previewContainer = $(input).closest('form').find('.uploaded-image');
                    previewContainer.find('img').attr('src', e.target.result);
                    previewContainer.removeClass('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        handleAjaxFormSubmit('.carousel', {
            onSuccess: function () {
                const currentTab = $('.nav-tabs .nav-link.active').attr('href');
                localStorage.setItem('activeTab', currentTab);
                location.reload();
            }
        });

    </script>

    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>

    <script>
        $(document).ready(function () {
            $('.invoice-repeater').repeater({
                show: function () {
                    $(this).slideDown();
                    feather && feather.replace();

                    const items = $(this).closest('.invoice-repeater').find('[data-repeater-item]');
                    items.each(function (index) {
                        // Hide delete button if it's the only one
                        $(this).find('[data-repeater-delete]').toggle(items.length > 1);
                    });
                },
                hide: function (deleteElement) {
                    const repeater = $(this).closest('.invoice-repeater');
                    const items = repeater.find('[data-repeater-item]');

                    // Prevent deleting if it's the only one
                    if (items.length === 1) {
                        alert("At least one item is required.");
                        return;
                    }

                    $(this).slideUp(deleteElement, function () {
                        $(this).remove();

                        const remainingItems = repeater.find('[data-repeater-item]');
                        remainingItems.each(function (index) {
                            $(this).find('[data-repeater-delete]').toggle(remainingItems.length > 1);
                        });
                    });
                }
            });
        });



    </script>
        <script>
            $(document).ready(function () {
            $('.upload-wrapper').each(function () {
                const wrapper = $(this);
                const input = wrapper.find('.image-input');
                const uploadCard = wrapper.find('.upload-card');
                const preview = wrapper.find('.uploaded-image');
                const progress = wrapper.find('.upload-progress');
                const progressBar = progress.find('.progress-bar');

                uploadCard.on('click', () => input.click());

                input.on('change', function (e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    preview.removeClass('d-none');
                    progress.removeClass('d-none');
                    progressBar.css('width', '0%');

                    let percent = 0;
                    const interval = setInterval(() => {
                        percent += 20;
                        progressBar.css('width', percent + '%');

                        if (percent >= 100) {
                            clearInterval(interval);
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                preview.find('img').attr('src', e.target.result);
                                progress.addClass('d-none');
                            };
                            reader.readAsDataURL(file);
                        }
                    }, 100);
                });
            });
        });

        $(document).on('click', '.remove-category', function (e) {
            e.preventDefault();

            const categoryId = $(this).data('id');

            $.ajax({
                url: '{{ route("categories.landing.remove") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    category_id: categoryId
                },
                success: function (response) {
                    Toastify({
                        text: "Category removed successfully.",
                        backgroundColor: "#24B094"
                    }).showToast();

                    // Optionally remove the element from DOM
                    location.reload();
                },
                error: function (xhr) {
                    let message = "Something went wrong.";

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat().join('\n');
                    }

                    Toastify({
                        text: message,
                        backgroundColor: "#FF6B6B"
                    }).showToast();
                }
            });
        });
    </script>

    <script>
        // Submit on Add Category
        handleAjaxFormSubmit(".landing-category-form", {
            successMessage: "Product added To Landing",
            onSuccess: function () {
                $('#addLandingCategoryModal').modal('hide');
                location.reload();
            }
        });

        let selectedCategoryId = null;

        $(document).ready(function () {
            // Handle live search
            $('#category-search').on('keyup', function () {
                const query = $(this).val();

                if (query.length < 2) {
                    $('#search-suggestions').empty();
                    selectedCategoryId = null;
                    return;
                }

                $.ajax({
                    url: '{{ route("categories.search") }}',
                    method: 'GET',
                    data: {search: query},
                    success: function (data) {
                        let suggestions = '';
                        data.data.forEach(function (item) {
                            suggestions += `<a href="#" class="list-group-item list-group-item-action category-option" data-id="${item.id}" data-name="${item.name}">${item.name}</a>`;
                        });

                        $('#search-suggestions').html(suggestions).show();
                    },
                    error: function () {
                        $('#search-suggestions').empty().hide();
                    }
                });
            });

            // When suggestion clicked
            $(document).on('click', '.category-option', function (e) {
                e.preventDefault();
                const name = $(this).data('name');
                selectedCategoryId = $(this).data('id');

                $('#category-search').val(name);
                $('#search-suggestions').empty().hide();
            });

            // Hide on click outside
            $(document).on('click', function (e) {
                if (!$(e.target).closest('#category-search, #search-suggestions').length) {
                    $('#search-suggestions').empty().hide();
                }
            });

        });
    </script>


    <script>
        $(document).ready(function () {
            const savedTab = localStorage.getItem('activeTab');
            if (savedTab) {
                const tabTrigger = $('.nav-link[href="' + savedTab + '"]');
                if (tabTrigger.length) {
                    tabTrigger.tab('show');
                    localStorage.removeItem('activeTab');
                }
            } else {
                const hash = window.location.hash;
                const tabTrigger = $('.nav-link[href="' + hash + '"]');
                if (tabTrigger.length) {
                    tabTrigger.tab('show');
                }
            }
        });

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
                            reader.onload = function (e) {
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


@endsection
