@extends('layouts/contentLayoutMaster')

@section('title', 'Settings Details')
@section('main-page', 'Website')

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
    <div class="card">
        {{-- Left Side: Vertical Tabs --}}
        <div class="card-body">
            <ul class="nav nav-tabs nav-fill border-bottom-0">
                <li class="nav-item">
                    <a class="nav-link active custom-tab" id="tab1-tab" data-bs-toggle="tab" href="#tab1" role="tab"
                       aria-controls="tab1">1. Navbar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab2-tab" data-bs-toggle="tab" href="#tab2" role="tab"
                       aria-controls="tab2">2. Hero</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab3-tab" data-bs-toggle="tab" href="#tab3" role="tab"
                       aria-controls="tab3">
                        3. Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab4-tab" data-bs-toggle="tab" href="#tab4" role="tab"
                       aria-controls="tab4">
                        4. Designs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab5-tab" data-bs-toggle="tab" href="#tab5" role="tab"
                       aria-controls="tab5">
                        5. Statistics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab6-tab" data-bs-toggle="tab" href="#tab6" role="tab"
                       aria-controls="tab6">
                        6. Logo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab7-tab" data-bs-toggle="tab" href="#tab7" role="tab"
                       aria-controls="tab7">
                        7. Testimonials
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab8-tab" data-bs-toggle="tab" href="#tab8" role="tab"
                       aria-controls="tab8">
                        8. Partners
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-tab" id="tab8-tab" data-bs-toggle="tab" href="#tab9" role="tab"
                       aria-controls="tab8">
                        9. FAQ
                    </a>
                </li>

            </ul>

            {{-- Right Side: Tab Content --}}
            <div class="tab-content flex-grow-1 p-3" id="v-pills-tabContent">
                <!-- tab1 Section -->
                <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show products in navbar</span>
                        <!-- Toggle Switch -->
                        <form id="navbarSectionForm" action="{{ route('landing-sections.update') }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="navbar_section">
                            <input type="hidden" name="value" value="{{ setting('navbar_section') ? 1 : 0 }}"
                                   id="navbarSectionValue">

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="navbarSectionToggle"
                                    {{ setting('navbar_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>

                    </div>

                    <div class="d-flex flex-row align-items-center p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: none;">
                        <span class="fw-semibold text-black fs-4">You can add up to</span>
                        <span class="fw-semibold fs-4 ms-1" style="color: #24B094;">7 Products</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                            <p class="fw-semibold text-black fs-16">Added Products</p>
                    <div class="col-3 ms-auto ">
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#addLandingCategoryModal">
                            Add Product
                        </button>
                    </div>
                    </div>

                    <div class="row">
                        @forelse($categories as $category)
                            <!-- Product Card -->
                            <div class="col-md-6 mb-3">
                                <div class="p-2 d-flex flex-row align-items-center"
                                     style="box-shadow: 0px 4px 6px 0px #4247460F; border-radius: 10px;">
                                    <!-- Image -->
                                    <img src="{{ $category->getFirstMediaUrl('categories') }}" alt="Product"
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
                            </div>
                        @endforelse


                    </div>
                </div>

                <!-- tab2 -->
                <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                    <!-- Card -->
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show Hero Section</span>

                        <!-- Toggle Switch -->
                        <form id="heroSectionForm" action="{{ route('landing-sections.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="hero_section">
                            <input type="hidden" name="value" value="{{ setting('hero_section') ? 1 : 0 }}"
                                   id="heroSectionValue">

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="heroSectionToggle"
                                    {{ setting('hero_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>

                    </div>

                    <div class="invoice-repeater">


                        <div class="invoice-repeater">

                            <div data-repeater-list="carousels">
                                @forelse($carousels as $carousel)
                                    @include('modals.delete',[
                            'id' => 'deleteCarouselModal',
                             'formId' => 'deleteCarouselForm',
                               'title' => 'Delete Carousel',
                             'action' => route("carousels.remove",$carousel->id),
                         ])
                                    <div data-repeater-item>

                                        <div class="col-md-12 mb-2">
                                            <div class="card p-1 mb-4 border rounded-md shadow-sm">
                                                <form action="{{ route("carousels.update") }}"
                                                      method="POST"
                                                      enctype="multipart/form-data"
                                                      class="carousel">
                                                    @csrf
                                                    @method("PUT")

                                                        <input type="hidden" name="carousels[{{ $loop->index }}][id]" value="{{ $carousel->id }}">
                                                    <!-- will be filled dynamically -->
                                                    <div class="website-media-ids"></div>
                                                    <div class="mobile-media-ids"></div>
                                                    <!-- Website Image Upload -->
                                                    <label class="form-label">Website Image</label>
                                                    <div class="dropzone website-dropzone"></div>

                                                    <small class="text d-block mb-2">Recommended: 1920*520 px, max 2 MB</small>

                                                    <div class="upload-wrapper">
                                                        <div class="uploaded-image {{ $carousel->getFirstMediaUrl('carousels') ? '': 'd-none' }}  mt-2">
                                                            <img
                                                                src="{{ $carousel->getFirstMediaUrl('carousels') }}"
                                                                class="img-fluid rounded"
                                                                style="width: 50px; height: 50px; object-fit: cover;">
                                                        </div>
                                                        <div class="progress upload-progress d-none">
                                                            <div class="progress-bar" style="width: 0%"></div>
                                                        </div>
                                                    </div>

                                                    <!-- Mobile Image Upload -->
                                                    <label class="form-label mt-3">Mobile Image</label>
                                                    <div class="dropzone mobile-dropzone"></div>

                                                    <small class="text d-block mb-2">Recommended: 375×672 px, max 2 MB</small>

                                                    <div class="upload-wrapper">
                                                        <div
                                                            class="uploaded-image {{ $carousel->getFirstMediaUrl('mobile_carousels') ? '':'d-none' }} mt-2">
                                                            <img
                                                                src="{{ $carousel->getFirstMediaUrl('mobile_carousels') }}"
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
                                                            <input type="text" name="subtitle_en"
                                                                   class="form-control"
                                                                   value="{{ $carousel->getTranslation('subtitle', 'en') }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Subtitle in Arabic</label>
                                                            <input type="text" name="subtitle_ar"
                                                                   class="form-control"
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
                                                        <button type="button"
                                                                class="btn btn-outline-danger open-delete-carousel-modal"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteCarouselModal"
                                                        >
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
                                @empty
                                    <div data-repeater-item>
                                        <div class="col-md-12 mb-2">
                                            <div class="card p-4 mb-4 border rounded shadow-sm">
                                                <form action="{{ route("carousels.update", null) }}"
                                                      method="POST"
                                                      enctype="multipart/form-data"
                                                      class="carousel">
                                                    @csrf
                                                    @method("PUT")
                                                    <div class="website-media-ids"></div>
                                                    <div class="mobile-media-ids"></div>
                                                    <input type="hidden" name="carousels[0][id]" value="">
                                                    <!-- Website Image Upload -->
                                                    <label class="form-label">Website Image</label>
                                                    <div class="dropzone website-dropzone"></div>
                                                    <small class="text d-block mb-2">Recommended: 1920*520 px, max 2 MB</small>
                                                    <div class="upload-wrapper">
                                                        <div
                                                            class="uploaded-image d-none  mt-2">
                                                            <img
                                                                src=""
                                                                class="img-fluid rounded"
                                                                style="width: 50px; height: 50px; object-fit: cover;">
                                                        </div>
                                                        <div class="progress upload-progress d-none">
                                                            <div class="progress-bar" style="width: 0%"></div>
                                                        </div>
                                                    </div>

                                                    <!-- Mobile Image Upload -->
                                                    <!-- Mobile Image Upload -->
                                                    <label class="form-label mt-3">Mobile Image</label>
                                                    <div class="dropzone mobile-dropzone"></div>
                                                    <small class="text d-block mb-2">Recommended: 375×672 px, max 2 MB</small>
                                                    <div class="upload-wrapper">
                                                        <div
                                                            class="uploaded-image d-none mt-2">
                                                            <img
                                                                src=""
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
                                                                   value="">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Title in Arabic</label>
                                                            <input type="text" name="title_ar" class="form-control"
                                                                   value="">
                                                        </div>
                                                    </div>

                                                    <!-- Subtitles -->
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Subtitle in English</label>
                                                            <input type="text" name="subtitle_en"
                                                                   class="form-control"
                                                                   value="">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Subtitle in Arabic</label>
                                                            <input type="text" name="subtitle_ar"
                                                                   class="form-control"
                                                                   value="">
                                                        </div>
                                                    </div>

                                                    <!-- Product Selection -->
                                                    <div class="mb-2">
                                                        <label class="form-label">Select Product</label>
                                                        <select name="product_id" class="form-select">
                                                            <option disabled selected>Select a product</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}"
                                                                >
                                                                    {{ $product->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Action Buttons -->
                                                    <div class="d-flex justify-content-between mt-3">
                                                        <button type="button" data-repeater-delete
                                                                class="btn btn-outline-danger"

                                                        >
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
                                @endforelse
                            </div>

                            {{-- Hidden template for new carousel --}}
                            <div data-repeater-item style="display: none;">
                                <div class="col-md-12 mb-2">
                                    <div class="card p-4 mb-4 border rounded shadow-sm">
                                        <form method="POST" action="{{ route('carousels.update') }}"
                                              enctype="multipart/form-data" class="carousel">
                                            @csrf
                                            <div class="website-media-ids"></div>
                                            <div class="mobile-media-ids"></div>

                                            <!-- Website Image Upload -->
                                            <label class="form-label">Website Image</label>
                                            <div class="dropzone website-dropzone"></div>
                                            <small class="text d-block mb-2">Recommended: 1920*520 px, max 2 MB</small>
                                            <div class="upload-wrapper">
                                                <div class="uploaded-image d-none mt-2">
                                                    <img src="" class="img-fluid rounded"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                </div>
                                                <div class="progress upload-progress d-none">
                                                    <div class="progress-bar" style="width: 0%"></div>
                                                </div>
                                            </div>

                                            <!-- Mobile Image Upload -->
                                            <label class="form-label mt-3">Mobile Image</label>
                                            <div class="dropzone mobile-dropzone"></div>
                                            <small class="text-muted d-block mb-2">Recommended: 375×672 px, max 2 MB</small>
                                            <div class="upload-wrapper">
                                                <div class="uploaded-image d-none mt-2">
                                                    <img src="" class="img-fluid rounded"
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
                                                    <input type="text" name="title_en" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Title in Arabic</label>
                                                    <input type="text" name="title_ar" class="form-control">
                                                </div>
                                            </div>

                                            <!-- Subtitles -->
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

                                            <!-- Product Selection -->
                                            <div class="mb-2">
                                                <label class="form-label">Select Product</label>
                                                <select name="product_id" class="form-select">
                                                    <option disabled selected>Select a product</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-between mt-2">
                                                <button type="button" data-repeater-delete
                                                        class="btn btn-outline-danger">
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
                        <div class="text-start d-flex justify-content-end mb-2">
                            <button type="button" data-repeater-create class="btn btn-primary">
                                <i data-feather="plus"></i> Add Carousel
                            </button>
                        </div>
                    </div>


                </div>

                <!-- tab3 -->
                <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show products</span>
                        <!-- Toggle Switch -->
                        <form id="categorySectionForm" action="{{ route('landing-sections.update') }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="categories_section">
                            <input type="hidden" name="value" value="{{ setting('categories_section') ? 1 : 0 }}"
                                   id="categorySectionValue">

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="categorySectionToggle"
                                    {{ setting('categories_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>

                    </div>

                </div>

                <!-- tab4 -->
                <div class="tab-pane fade" id="tab4" role="tabpanel" aria-labelledby="tab4-tab">
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show designs</span>
                        <!-- Toggle Switch -->
                        <form id="productSectionForm" action="{{ route('landing-sections.update') }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="designs_section">
                            <input type="hidden" name="value" value="{{ setting('designs_section') ? 1 : 0 }}"
                                   id="productSectionValue">

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="productSectionToggle"
                                    {{ setting('designs_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>

                    </div>
                    <div class=" d-flex flex-row align-items-center p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: none;">
                        <span class="fw-semibold text-black fs-4">You can add up to </span><span
                            class="fw-semibold fs-4 ms-1" style="color: #24B094;">8 designs</span>
                    </div>
                    <p class="fw-semibold text-black fs-4">Design Name</p>
                    <div class="position-relative">
                        <div class="row g-2 mb-2">
                            <div class="col-9">
                                <input type="text" id="design-search" class="form-control"
                                       placeholder="Enter design name">
                            </div>
                            <div class="col-3">
                                <button class="btn btn-primary w-100">Add Design</button>
                            </div>
                        </div>
                        <div id="search-suggestions" class="list-group position-absolute w-100"
                             style="z-index: 1000;"></div>
                    </div>

                    <p class="fw-semibold text-black fs-16">Added Designs</p>
                    <div class="row">
                        <!-- Product Card -->
                        @forelse($templates as $template)
                            <!-- Product Card -->
                            <div class="col-md-6 mb-3">
                                <div class=" p-2 d-flex flex-row align-items-center"
                                     style="box-shadow: 0px 4px 6px 0px #4247460F; border-radius: 10px;">
                                    <!-- Image -->
                                    <img src="{{ $template->getFirstMediaUrl('templates')  }}" alt="Product"
                                         class="me-3 rounded" style="width: 80px; height: 80px; object-fit: cover;">

                                    <!-- Details -->
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-black fs-5">{{ $template->name }}</div>
                                    </div>

                                    <!-- Remove Button -->
                                    <button class="btn btn-outline-secondary btn-sm ms-2 remove-template"
                                            data-id="{{ $template->id }}">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center my-5">
                                <div class="mt-3 text-muted fs-5">No designs added yet.</div>
                            </div>
                        @endforelse
                    </div>

                </div>

                <!-- tab5 -->

                <div class="tab-pane fade" id="tab5" role="tabpanel" aria-labelledby="tab5-tab">
                    <!-- Header with toggle -->
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-3"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show statistics section</span>

                        <form id="statisticsSectionForm" action="{{ route('landing-sections.update') }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="statistics_section">
                            <input type="hidden" name="value" value="{{ setting('statistics_section') ? 1 : 0 }}"
                                   id="statisticsSectionValue">

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="statisticsSectionToggle"
                                    {{ setting('statistics_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>
                    </div>

                    <!-- Form Inputs & Save Button -->
                    <form id="updateStatisticsForm" action="{{ route("statistics-section.update") }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div class="flex-grow-1">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customers</label>
                                    <input type="number" class="form-control" name="customers"
                                           placeholder="Enter customers number"
                                           value="{{ setting('customers') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Orders</label>
                                    <input type="number" class="form-control" name="orders"
                                           placeholder="Enter orders number"
                                           value="{{ setting('orders') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rate</label>
                                    <input type="number" step="0.1" class="form-control" name="rate"
                                           placeholder="Enter rate"
                                           value="{{ setting('rate') }}">
                                </div>
                            </div>


                        </div>
                        <div class="text-end ">
                            <button type="submit" class=" btn btn-primary">
                                <i data-feather="save" class="me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- tab6 -->
                <div class="tab-pane fade" id="tab6" role="tabpanel" aria-labelledby="tab6-tab">
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-3"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show logo section</span>

                        <form id="logoSectionForm" action="{{ route('landing-sections.update') }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="logo_section">
                            <input type="hidden" name="value" value="{{ setting('logo_section') ? 1 : 0 }}"
                                   id="logoSectionValue">

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="logoSectionToggle"
                                    {{ setting('logo_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>
                    </div>

                </div>

                <!-- tab7 -->

                <div class="tab-pane fade" id="tab7" role="tabpanel" aria-labelledby="tab7-tab">
                    <!-- Header with toggle -->
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show Testimonials Section</span>

                        <form id="testimonialSectionForm" action="{{ route('landing-sections.update') }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="testimonials_section">
                            <input type="hidden" name="value" value="{{ setting('testimonials_section') ? 1 : 0 }}"
                                   id="testimonialSectionValue">

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="testimonialSectionToggle"
                                    {{ setting('testimonials_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>
                    </div>
                    <div class="card mb-4">
                        <div class="card-body border rounded">
                            <p class="fs-4 text-black ">Reviews With Images</p>
                            <!-- Header with toggle -->
                            <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                                 style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                                <span class="fw-semibold text-black fs-4">Show Reviews With Images Section</span>
                                <!-- Toggle Switch -->
                                <form id="reviewsWithImagesSectionForm" action="{{ route('landing-sections.update') }}"
                                      method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="key" value="reviews_with_images_section">
                                    <input type="hidden" name="value"
                                           value="{{ setting('reviews_with_images_section') ? 1 : 0 }}"
                                           id="reviewsWithImagesSectionValue">

                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input toggle-switch"
                                            type="checkbox"
                                            id="reviewsWithImagesSectionToggle"
                                            {{ setting('reviews_with_images_section') ? 'checked' : '' }}
                                        >
                                    </div>
                                </form>

                            </div>


                            <!-- Review Form -->
                            <form id="reviews-images" action="{{ route("reviews-images.create") }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label>Customer</label>
                                        <input type="text" class="form-control" name="customer"
                                               placeholder="Enter name">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Rate</label>
                                        <select name="rate" class="form-select">
                                            <option value="">Select rate</option>
                                            @for($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}">{{ $i }} ★</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Date</label>
                                        <input type="date" name="date" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Review</label>
                                    <textarea name="review" class="form-control" placeholder="Add review"></textarea>
                                </div>

                                <!-- Upload Photo (Drag and Drop Area) -->
                                <div class="mb-3">
                                    <label>Photo</label>
                                    <input type="file" name="image" id="product-image-main" class="form-control d-none"
                                           accept="image/*">

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
                                        <input type="hidden" name="type" value="with_image">
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary mt-2">Add Review</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Added Products List -->
                            <div class="mt-2">
                                <h5 class="text-black fs-16">Added Reviews</h5>
                                <div class="row row-cols-1 row-cols-md-2 g-3 mt-2 ">
                                    @foreach($reviewsWithImages as $review)
                                        <div class="col-md-6 mb-2"  style="box-shadow: 0px 4px 6px 0px #4247460F; border-radius: 10px;">
                                                <div class="p-1 d-flex">
                                                    <img
                                                        src="{{ asset($review->getFirstMediaUrl('reviews_landing_images')) }}"
                                                        class="rounded me-3"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div>
                                                        <strong>{{ $review->customer }}</strong>
                                                        <div class="text-warning">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star{{ $i > $review->rate ? '-o' : '' }}"></i>
                                                            @endfor
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ \Carbon\Carbon::parse($review->date)->format('d/m/Y') }}</small>
                                                        <p class="mb-1">{{ $review->review }}</p>
                                                        <form class="remove-review"
                                                              action="{{ route("reviews.remove", $review->id) }}"
                                                              method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                Remove
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>


                        </div>

                        <div class="card mt-2 border rounded">

                            <div class="card-body">
                                  <p class="fs-4 text-black ">Words of Praise</p>
                                <!-- Header with toggle -->
                                <div
                                    class="card d-flex flex-row align-items-center justify-content-between p-1 mb-2"
                                    style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                                    <span class="fw-semibold text-black fs-4">Show Words of Praise Section</span>
                                    <!-- Toggle Switch -->
                                    <form id="reviewsWithoutImagesSectionForm"
                                          action="{{ route('landing-sections.update') }}"
                                          method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="key" value="reviews_with_images_section">
                                        <input type="hidden" name="value"
                                               value="{{ setting('reviews_with_images_section') ? 1 : 0 }}"
                                               id="reviewsWithoutImagesSectionValue">

                                        <div class="form-check form-switch">
                                            <input
                                                class="form-check-input toggle-switch"
                                                type="checkbox"
                                                id="reviewsWithoutImagesSectionToggle"
                                                {{ setting('reviews_with_images_section') ? 'checked' : '' }}
                                            >
                                        </div>
                                    </form>

                                </div>

                                <!-- Review Form -->
                                <form id="reviews" action="{{ route("reviews.create") }}" method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label>Customer</label>
                                            <input type="text" class="form-control" name="customer"
                                                   placeholder="Enter name">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Rate</label>
                                            <select name="rate" class="form-select">
                                                <option value="">Select rate</option>
                                                @for($i = 1; $i <= 5; $i++)
                                                    <option value="{{ $i }}">{{ $i }} ★</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Date</label>
                                            <input type="date" name="date" class="form-control">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label>Review</label>
                                        <textarea name="review" class="form-control"
                                                  placeholder="Add review"></textarea>
                                    </div>
                                    <input type="hidden" name="type" value="without_image">

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary ">Add Review</button>
                                    </div>
                                </form>
                                <!-- Added Products List -->
                                <div class="mt-2">
                                    <h5 class="text-black fs-16">Added Reviews</h5>
                                    <div class="row row-cols-1 row-cols-md-2 g-3 ">
                                        @foreach($reviewsWithoutImages as $review)
                                               <div class="col-md-6 mb-2"  style="box-shadow: 0px 4px 6px 0px #4247460F; border-radius: 10px;">

                                                    <div class="p-1 d-flex">
                                                        <div>
                                                            <strong>{{ $review->customer }}</strong>
                                                            <div class="text-warning">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <i class="fas fa-star{{ $i > $review->rate ? '-o' : '' }}"></i>
                                                                @endfor
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ \Carbon\Carbon::parse($review->date)->format('d/m/Y') }}</small>
                                                            <p class="mb-1">{{ $review->review }}</p>
                                                            <form class="remove-review"
                                                                  action="{{ route("reviews.remove", $review->id) }}"
                                                                  method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">
                                                                    Remove
                                                                </button>
                                                            </form>
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ \Carbon\Carbon::parse($review->date)->format('d/m/Y') }}</small>
                                                        <p class="mb-1">{{ $review->review }}</p>

                                                    </div>

                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                </div>




                <!-- tab8 -->
                <div class="tab-pane fade" id="tab8" role="tabpanel" aria-labelledby="tab8-tab">
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-3"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show partners section</span>

                        <form id="partnerSectionForm" action="{{ route('landing-sections.update') }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="partners_section">
                            <input type="hidden" name="value" value="{{ setting('partners_section') ? 1 : 0 }}"
                                   id="partnerSectionValue">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="partnerSectionToggle"
                                    {{ setting('partners_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>
                    </div>

                <p class="fw-semibold text-black fs-4">Photo</p>
                <div class="position-relative">
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <form id="createPartner" action="{{ route("partners.create") }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="image" id="partner-image-main" class="form-control d-none"
                                       accept="image/*">

                                <!-- Custom Upload Card -->
                                <div id="partner-upload-area" class="upload-card">
                                    <div id="partner-upload-content">
                                        <i data-feather="upload" class="mb-2"></i>
                                        <p>Drag image here to upload</p>
                                    </div>
                                </div>
                                <div>
                                    <!-- Progress Bar -->
                                    <div id="partner-upload-progress" class="progress mt-2 d-none w-50">
                                        <div class="partner-progress-bar progress-bar-striped progress-bar-animated"
                                             style="width: 0%"></div>
                                    </div>


                                    <!-- Uploaded Image Preview -->
                                    <div id="partner-uploaded-image"
                                         class="partner-uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                                        <img src="" alt="Uploaded" class="img-fluid rounded"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <div id="file-details" class="file-details">
                                            <div class="file-name fw-bold"></div>
                                            <div class="file-size text-muted small"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <!-- Progress Bar -->
                                        <div id="partner-upload-progress" class="progress mt-2 d-none w-50">
                                            <div class="partner-progress-bar progress-bar-striped progress-bar-animated"
                                                 style="width: 0%"></div>
                                        </div>


                                        <!-- Uploaded Image Preview -->
                                        <div id="partner-uploaded-image"
                                             class="partner-uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                                            <img src="" alt="Uploaded" class="img-fluid rounded"
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                            <div id="file-details" class="file-details">
                                                <div class="file-name fw-bold"></div>
                                                <div class="file-size text-muted small"></div>
                                            </div>
                                            <button type="button" id="partner-remove-image"
                                                    class="btn btn-sm position-absolute text-danger"
                                                    style="top: 5px; right: 5px; background-color: #FFEEED">
                                                <i data-feather="trash"></i>
                                            </button>
                                        </div>

                                    </div>
                                    <div class="row d-flex justify-content-end">
                                        <button type="submit" class="col-3 btn btn-primary  mt-3 mb-3">Add Partner
                                        </button>
                                    </div>

                                </div>

                            </form>
                            <p class="fw-semibold text-black fs-16">Added Partners</p>
                            <div class="row">
                                <!-- Product Card -->
                                @forelse($partners as $partner)
                                    <!-- Product Card -->
                                    <div class="col-md-6 mb-3">
                                        <div class="p-2 d-flex flex-row align-items-center justify-content-between"
                                             style="box-shadow: 0px 4px 6px 0px #4247460F; border-radius: 10px;">
                                            <!-- Image -->
                                            <img src="{{ $partner->getUrl() }}" alt="Product"
                                                 class="me-3 rounded"
                                                 style="width: 80px; height: 80px; object-fit: cover;">

                                                <!-- Remove Button -->
                                                <form id="remove-partner"
                                                      action="{{ route("partners.remove",$partner->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="btn btn-outline-secondary btn-sm ms-2"
                                                        data-id="{{ $partner->id }}">
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center my-5">
                                            <div class="mt-3 text-muted fs-5">No partners added yet.</div>
                                        </div>
                                    @endforelse
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                <!-- tab9 -->
                <div class="tab-pane fade" id="tab9" role="tabpanel" aria-labelledby="tab9-tab">
                    <div class="card d-flex flex-row align-items-center justify-content-between p-1 mb-3"
                         style="background-color: #F4F6F6; border-radius: 10px; border: 1px solid #CED5D4;">
                        <span class="fw-semibold text-black fs-4">Show faq section</span>

                        <form id="faqSectionForm" action="{{ route('landing-sections.update') }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="key" value="faq_section">
                            <input type="hidden" name="value" value="{{ setting('faq_section') ? 1 : 0 }}"
                                   id="faqSectionValue">

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input toggle-switch"
                                    type="checkbox"
                                    id="faqSectionToggle"
                                    {{ setting('faq_section') ? 'checked' : '' }}
                                >
                            </div>
                        </form>
                    </div>

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
                Dropzone.autoDiscover = false; // 🔑 prevents Dropzone from auto-binding

                document.addEventListener("DOMContentLoaded", function () {
                    @foreach($carousels as $index => $carousel)
                    initCarouselDropzone({{ $index }});
                    @endforeach

                    @if($carousels->isEmpty())
                    initCarouselDropzone(0);
                    @endif
                });
                function initCarouselDropzone($item) {
                    $item = $($item);

                    // ensure containers exist
                    if ($item.find('.website-media-ids').length === 0) {
                        $item.append('<div class="website-media-ids"></div>');
                    }
                    if ($item.find('.mobile-media-ids').length === 0) {
                        $item.append('<div class="mobile-media-ids"></div>');
                    }

                    let websiteEl = $item.find('.website-dropzone')[0];
                    if (websiteEl && !websiteEl.dropzone) {
                        new Dropzone(websiteEl, {
                            url: "{{ route('media.store') }}",
                            maxFilesize: 2,
                            maxFiles: 1,
                            dictDefaultMessage: "Drag/Drop files here",
                            dictRemoveFile: "✕",
                            acceptedFiles: ".jpeg,.jpg,.png,.svg",
                            addRemoveLinks: true,
                            init: function () {
                                this.on("maxfilesexceeded", function (file) {
                                    this.removeAllFiles();  // remove old one
                                    this.addFile(file);     // add the new one
                                });
                            },
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            success: function (file, response) {
                                let hidden = document.createElement('input');
                                hidden.type = "hidden";
                                hidden.name = $item.find('input[name*="[id]"]').attr('name')
                                    .replace('[id]', '[website_media_ids][]');
                                hidden.value = response.data.id;
                                $item.find('.website-media-ids').append(hidden);
                            },
                            removedfile: function (file) {
                                if (file.previewElement != null) {
                                    file.previewElement.parentNode.removeChild(file.previewElement);
                                }
                                if (file._hiddenInput) {
                                    file._hiddenInput.remove();
                                }

                                if (file.xhr) {
                                    let response = JSON.parse(file.xhr.response);

                                    fetch("{{ url('api/v1/media') }}/" + response.data.id, {
                                        method: "DELETE",
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        }
                                    });
                                }

                            }

                        });
                    }

                    let mobileEl = $item.find('.mobile-dropzone')[0];
                    if (mobileEl && !mobileEl.dropzone) {
                        new Dropzone(mobileEl, {
                            url: "{{ route('media.store') }}",
                            maxFilesize: 2,
                            maxFiles: 1,
                            acceptedFiles: ".jpeg,.jpg,.png,.svg",
                            addRemoveLinks: true,
                            dictDefaultMessage: "Drag/Drop files here",
                            
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            dictRemoveFile: "✕",
                            init: function () {
                                this.on("maxfilesexceeded", function (file) {
                                    this.removeAllFiles();  // remove old one
                                    this.addFile(file);     // add the new one
                                });
                            },
                            success: function (file, response) {
                                let hidden = document.createElement('input');
                                hidden.type = "hidden";
                                hidden.name = $item.find('input[name*="[id]"]').attr('name')
                                    .replace('[id]', '[mobile_media_ids][]');
                                hidden.value = response.data.id;
                                $item.find('.mobile-media-ids').append(hidden);
                            },
                            removedfile: function (file) {
                                if (file.previewElement != null) {
                                    file.previewElement.parentNode.removeChild(file.previewElement);
                                }
                                if (file._hiddenInput) {
                                    file._hiddenInput.remove();
                                }

                                if (file.xhr) {
                                    let response = JSON.parse(file.xhr.response);

                                    fetch("{{ url('api/v1/media') }}/" + response.data.id, {
                                        method: "DELETE",
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        }
                                    });
                                }

                            }

                        });
                    }
                }

            </script>
                <script !src="">
                    handleAjaxFormSubmit("#deleteCarouselForm", {
                        successMessage: "Carousel removed Successfully",
                        onSuccess: function () {
                            $('#deleteCarouselModal').modal('hide');
                            location.reload();
                        }
                    })
                    $(document).on("click", ".open-delete-carousel-modal", function () {

                        const carouselId = $(this).data("id");
                        $("#deleteCarouselForm").data("id", carouselId);
                    });
                    handleAjaxFormSubmit(".remove-review", {
                        successMessage: "Review removed Successfully", onSuccess: function () {
                            location.reload();
                        }
                    })
                    handleAjaxFormSubmit("#remove-partner", {
                        successMessage: "partner removed Successfully", onSuccess: function () {
                            location.reload();
                        }
                    })
                    handleAjaxFormSubmit("#reviews-images", {
                        successMessage: "Review Added Successfully", onSuccess: function () {
                            location.reload();
                        }
                    })
                    handleAjaxFormSubmit("#reviews", {
                        successMessage: "Review Added Successfully", onSuccess: function () {
                            location.reload();
                        }
                    })
                    handleAjaxFormSubmit("#createPartner", {
                        successMessage: "Asset Uploaded Successfully",
                        onSuccess: function () {
                            location.reload();
                        }
                    })
                    $(document).ready(function () {
                        $(document).on('click', '.upload-card', function () {
                            $(this).siblings('input[type="file"]').trigger('click');
                        });
                        $(document).on('change', 'input[type="file"]', function (e) {
                            const input = this;
                            const wrapper = $(this).closest('.upload-wrapper');
                            const preview = wrapper.find('.uploaded-image');
                            const img = preview.find('img');

                            if (input.files && input.files[0]) {
                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    img.attr('src', e.target.result);
                                    preview.removeClass('d-none');
                                };
                                reader.readAsDataURL(input.files[0]);
                            } else {
                                img.attr('src', '');
                                preview.addClass('d-none');
                            }
                        });

                        let input = $('#partner-image-main');
                        let uploadArea = $('#partner-upload-area');
                        let progress = $('#partner-upload-progress');
                        let progressBar = $('.partner-progress-bar');
                        let uploadedImage = $('#partner-uploaded-image');
                        let removeButton = $('#partner-remove-image');

                        // Click on the upload area triggers the hidden input
                        uploadArea.on('click', function () {
                            input.click();
                        });

                        // Handle file selection
                        input.on('change', function (e) {
                            handleFiles(e.target.files);
                        });

                        // Handle Drag & Drop
                        uploadArea.on('dragover', function (e) {
                            e.preventDefault();
                            uploadArea.addClass('dragover');
                        });

                        uploadArea.on('dragleave', function (e) {
                            e.preventDefault();
                            uploadArea.removeClass('dragover');
                        });

                        uploadArea.on('drop', function (e) {
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
                                let interval = setInterval(function () {
                                    fakeProgress += 10;
                                    progressBar.css('width', fakeProgress + '%');

                                    if (fakeProgress >= 100) {
                                        clearInterval(interval);

                                        // Preview image
                                        let reader = new FileReader();
                                        reader.onload = function (e) {
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
                        removeButton.on('click', function () {
                            uploadedImage.addClass('d-none');
                            input.val(''); // Clear the input
                        });
                    });

                    $(document).ready(function () {
                        let input = $('#product-image-main');
                        let uploadArea = $('#upload-area');
                        let progress = $('#upload-progress');
                        let progressBar = $('.progress-bar');
                        let uploadedImage = $('#uploaded-image');
                        let removeButton = $('#remove-image');

                        // Click on the upload area triggers the hidden input
                        uploadArea.on('click', function () {
                            input.click();
                        });

                        // Handle file selection
                        input.on('change', function (e) {
                            handleFiles(e.target.files);
                        });

                        // Handle Drag & Drop
                        uploadArea.on('dragover', function (e) {
                            e.preventDefault();
                            uploadArea.addClass('dragover');
                        });

                        uploadArea.on('dragleave', function (e) {
                            e.preventDefault();
                            uploadArea.removeClass('dragover');
                        });

                        uploadArea.on('drop', function (e) {
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
                                let interval = setInterval(function () {
                                    fakeProgress += 10;
                                    progressBar.css('width', fakeProgress + '%');

                                    if (fakeProgress >= 100) {
                                        clearInterval(interval);

                                        // Preview image
                                        let reader = new FileReader();
                                        reader.onload = function (e) {
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
                        removeButton.on('click', function () {
                            uploadedImage.addClass('d-none');
                            input.val(''); // Clear the input
                        });
                    });

                </script>
                <script>
                    handleAjaxFormSubmit("#updateStatisticsForm", {
                            successMessage: "Statistics updated successfully",
                            resetForm: false,
                        }
                    )
                </script>
                <script>
                    let selectedDesignId = null;

                    $(document).ready(function () {
                        // Handle live search
                        $('#design-search').on('keyup', function () {
                            const query = $(this).val();

                            if (query.length < 2) {
                                $('#search-suggestions').empty();
                                selectedDesignId = null;
                                return;
                            }

                            $.ajax({
                                url: '{{ route("templates.search") }}',
                                method: 'GET',
                                data: {search: query},
                                success: function (data) {
                                    let suggestions = '';
                                    data.data.forEach(function (item) {
                                        suggestions += `<a href="#" class="list-group-item list-group-item-action design-option" data-id="${item.id}" data-name="${item.name}">${item.name}</a>`;
                                    });

                                    $('#search-suggestions').html(suggestions).show();
                                },
                                error: function () {
                                    $('#search-suggestions').empty().hide();
                                }
                            });
                        });

                        // When suggestion clicked
                        $(document).on('click', '.design-option', function (e) {
                            e.preventDefault();
                            const name = $(this).data('name');
                            selectedDesignId = $(this).data('id');

                            $('#design-search').val(name);
                            $('#search-suggestions').empty().hide();
                        });

                        // Hide suggestions on click outside
                        $(document).on('click', function (e) {
                            if (!$(e.target).closest('#design-search, #search-suggestions').length) {
                                $('#search-suggestions').empty().hide();
                            }
                        });

                        // Submit on Add Design
                        $('.btn-primary:contains("Add Design")').on('click', function (e) {
                            e.preventDefault();

                            if (!selectedDesignId) {
                                Toastify({
                                    text: "Please select a design from suggestions.",
                                    backgroundColor: "#FF6B6B"
                                }).showToast();
                                return;
                            }

                            $.ajax({
                                url: '{{ route("templates.landing") }}',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    design_id: selectedDesignId
                                },
                                success: function (response) {
                                    Toastify({
                                        text: "Design added successfully!",
                                        backgroundColor: "#24B094"
                                    }).showToast();
                                    location.reload();
                                    $('#design-search').val('');
                                    selectedDesignId = null;
                                },
                                error: function (xhr) {
                                    let errorMessage = "Error adding design.";

                                    if (xhr.status === 422) {
                                        const errors = xhr.responseJSON.errors;
                                        if (errors) {
                                            errorMessage = Object.values(errors).flat().join('\n');
                                        }
                                    }

                                    Toastify({
                                        text: errorMessage,
                                        backgroundColor: "#FF6B6B",
                                        duration: 5000
                                    }).showToast();
                                }
                            });
                        });
                    });
                    $(document).on('click', '.remove-template', function (e) {
                        e.preventDefault();

                        const designId = $(this).data('id');

                        $.ajax({
                            url: '{{ route("templates.landing.remove") }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                design_id: designId
                            },
                            success: function (response) {
                                Toastify({
                                    text: "Design removed successfully.",
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
                    $(function () {
                        handleAjaxFormSubmit("#productSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('productSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('productSectionValue');
                            const form = document.getElementById('productSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit(); // Triggers the form submit event, which your AJAX listener handles
                        });
                    });
                </script>
                <script>
                    $(function () {
                        handleAjaxFormSubmit("#reviewsWithoutImagesSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('reviewsWithoutImagesSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('reviewsWithoutImagesSectionValue');
                            const form = document.getElementById('reviewsWithoutImagesSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit(); // Triggers the form submit event, which your AJAX listener handles
                        });
                    });
                </script>
                <script>
                    $(function () {
                        handleAjaxFormSubmit("#testimonialSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('testimonialSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('testimonialSectionValue');
                            const form = document.getElementById('testimonialSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit(); // Triggers the form submit event, which your AJAX listener handles
                        });
                    });
                </script>
                <script>
                    $(function () {
                        handleAjaxFormSubmit("#partnerSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('partnerSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('partnerSectionValue');
                            const form = document.getElementById('partnerSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit(); // Triggers the form submit event, which your AJAX listener handles
                        });
                    });
                </script>

                <script>
                    $(function () {
                        handleAjaxFormSubmit("#faqSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('faqSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('faqSectionValue');
                            const form = document.getElementById('faqSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit(); // Triggers the form submit event, which your AJAX listener handles
                        });
                    });
                </script>
                <script>
                    $(function () {
                        handleAjaxFormSubmit("#logoSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('logoSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('logoSectionValue');
                            const form = document.getElementById('logoSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit(); // Triggers the form submit event, which your AJAX listener handles
                        });
                    });
                </script>
                <script>
                    $(function () {
                        handleAjaxFormSubmit("#statisticsSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('statisticsSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('statisticsSectionValue');
                            const form = document.getElementById('statisticsSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit(); // Triggers the form submit event, which your AJAX listener handles
                        });
                    });
                </script>
                <script>
                    $(function () {
                        handleAjaxFormSubmit("#navbarSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('navbarSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('navbarSectionValue');
                            const form = document.getElementById('navbarSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit(); // Triggers the form submit event, which your AJAX listener handles
                        });
                    });
                </script>
                <script>
                    $(function () {
                        handleAjaxFormSubmit("#heroSectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('heroSectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('heroSectionValue');
                            const form = document.getElementById('heroSectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit();
                        });
                    });
                </script>
                <script>
                    $(function () {
                        handleAjaxFormSubmit("#categorySectionForm", {
                            successMessage: "Request completed Successfully",
                            resetForm: false,
                        });

                        document.getElementById('categorySectionToggle').addEventListener('change', function () {
                            const isChecked = this.checked;
                            const valueInput = document.getElementById('categorySectionValue');
                            const form = document.getElementById('categorySectionForm');

                            valueInput.value = isChecked ? 1 : 0;

                            form.requestSubmit();
                        });
                    });
                </script>
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
                        $('[data-repeater-item]').each(function () {
                            initCarouselDropzone($(this));
                        });
                        $('.invoice-repeater').repeater({
                            show: function () {
                                $(this).find('.uploaded-image').addClass('d-none').find('img').attr('src', '');

                                $(this).slideDown();
                                feather && feather.replace();
                                let $newItem = $(this);
                                setTimeout(function () {
                                    initCarouselDropzone($newItem);
                                }, 100);
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
                </script>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const savedTab = localStorage.getItem('activeTab');
                        const defaultTab = document.querySelector('.nav-link[href="#tab1"]');

                        // Remove 'active' and 'show' from tab1 and its pane
                        if (!savedTab && defaultTab) {
                            defaultTab.classList.remove('active');
                            const tab1Pane = document.querySelector('#tab1');
                            if (tab1Pane) {
                                tab1Pane.classList.remove('active', 'show');
                            }
                        }

                        // Restore the saved tab
                        if (savedTab) {
                            const trigger = document.querySelector('.nav-link[href="' + savedTab + '"]');
                            if (trigger) {
                                new bootstrap.Tab(trigger).show();
                            }
                        }

                        // Save active tab on change
                        document.querySelectorAll('.nav-link').forEach(function (tab) {
                            tab.addEventListener('shown.bs.tab', function (e) {
                                const href = e.target.getAttribute('href');
                                localStorage.setItem('activeTab', href);
                            });
                        });
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
