@extends('layouts/contentLayoutMaster')

@section('title', 'Add Products')
@section('main-page', 'Products')
@section('sub-page', 'Add New Product')
@section('main-page-url', route("categories.index"))
@section('sub-page-url', route("categories.create"))


@section('vendor-style')
    <!-- Vendor CSS Files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
    <section id="multiple-column-form ">
        <div class="row ">
            <div class="col-12">
                <div class="card">

                    <div class="card-body ">

                        <form id="category-form" class="form" action="{{ route('product-without-categories.store') }}"
                              method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            {{-- checkbox added --}}
                            <div class="px-2 row gap-2">
                                <div class="d-flex gap-2 rounded p-1 mb-2 col" style="border: 1px solid #CED5D4">
                                    <div>
                                        <!-- Hidden fallback -->
                                        <input type="hidden" name="show_add_cart_btn" value="0">

                                        <!-- Actual checkbox -->
                                        <input class="form-check-input mt-0 " type="checkbox" name="show_add_cart_btn"
                                               value="1" @checked(old('show_add_cart_btn', false))>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <h5 style="color: #121212">Show “Add to Cart” Button</h5>
                                        <p style="color: #424746">
                                            When the checkbox is selected, the product can be added directly to the cart
                                            without customization.
                                            If it’s not selected, the product must be customized before being added to
                                            cart.
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 rounded p-1 mb-2 col" style="border: 1px solid #CED5D4">
                                    <div>
                                        <!-- Hidden fallback -->
                                        <input type="hidden" name="show_customize_design_btn" value="0">

                                        <!-- Actual checkbox -->
                                        <input class="form-check-input mt-0 " type="checkbox"
                                               name="show_customize_design_btn" value="1"
                                            @checked(old('show_customize_design_btn', false))>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <h5 style="color: #121212">Show “Customize Design” Button</h5>
                                        <p style="color: #424746">
                                            When the checkbox is selected, the product can be customized before being
                                            added
                                            to cart.
                                        </p>
                                    </div>
                                </div>

                            </div>


                            <ul class="nav nav-tabs mb-2 w-100 d-flex justify-content-center" id="formTabs">
                                <li class="nav-item" style="width: 30%;">
                                    <a class="nav-link active" data-step="0" href="#" style="font-size: 14px;">Product
                                        Details</a>
                                </li>
                                <li class="nav-item" style="width: 30%;">
                                    <a class="nav-link" data-step="1" href="#" style="font-size: 14px;">Quantity &
                                        Price</a>
                                </li>
                                <li class="nav-item" style="width: 30%;">
                                    <a class="nav-link" data-step="2" href="#" style="font-size: 14px;">Product
                                        Specs</a>
                                </li>
                            </ul>


                            <div class="tab-content">
                                <!-- first tab content -->
                                <div class="tab-pane active" id="step1">
                                    <div class="row">
                                        <input type="hidden" name="is_has_category" value="0">
                                        <!-- Product Name EN/AR -->
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label label-text" for="product-name-en">Product Name
                                                    (EN) <span style="color: red; font-size: 20px;">*</span></label>
                                                <input type="text" id="product-name-en" class="form-control"
                                                       name="name[en]"
                                                       placeholder="Product Name (EN)"/>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label label-text" for="product-name-ar">Product Name
                                                    (AR) <span style="color: red; font-size: 20px;">*</span></label>
                                                <input type="text" id="product-name-ar" class="form-control"
                                                       name="name[ar]"
                                                       placeholder="Product Name (AR)"/>
                                            </div>
                                        </div>

                                        <!-- Description EN/AR -->
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label label-text" for="description-en">Product
                                                    Description (EN) <span
                                                        style="color: red; font-size: 20px;">*</span></label>
                                                <textarea name="description[en]" id="description-en"
                                                          class="form-control"
                                                          placeholder="Product Description (EN)"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label label-text" for="description-ar">Product
                                                    Description (AR) <span
                                                        style="color: red; font-size: 20px;">*</span></label>
                                                <textarea name="description[ar]" id="description-ar"
                                                          class="form-control"
                                                          placeholder="Product Description (AR)"></textarea>
                                            </div>
                                        </div>


                                        <!-- Main Image Upload -->
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="form-label label-text" for="product-image-main">
                                                    Product Image (main) <span
                                                        style="color: red; font-size: 20px;">*</span>
                                                </label>

                                                <!-- Dropzone Container -->
                                                <div id="product-main-dropzone" class="dropzone border rounded p-3"
                                                     style="cursor:pointer; min-height:150px;">
                                                    <div class="dz-message" data-dz-message>
                                                        <span>Drop image here or click to upload</span>
                                                    </div>
                                                </div>


                                                <span class="image-hint small text-end">
                                                Max size: 1MB | Dimensions: 512x512 px
                                            </span>
                                                <!-- ✅ Hidden input outside Dropzone -->
                                                <input type="hidden" name="image_id" id="uploadedImageMain">

                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="form-label label-text" for="product-image-main">
                                                    Product Model Image (main) <span
                                                        style="color: red; font-size: 20px;">*</span>
                                                </label>

                                                <!-- Dropzone Container -->
                                                <div id="product-model-dropzone" class="dropzone border rounded p-3"
                                                     style="cursor:pointer; min-height:150px;">
                                                    <div class="dz-message" data-dz-message>
                                                        <span>Drop image here or click to upload</span>
                                                    </div>
                                                </div>

                                                <!-- ✅ Hidden input outside Dropzone -->
                                                <input type="hidden" name="image_model_id" id="uploadedImageModel">

                                                <span class="image-hint small text-end">
                                                Max size: 1MB | Dimensions: 512x512 px
                                            </span>

                                                <!-- Uploaded Image Preview -->
                                                <div id="uploaded-image-model"
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
                                        <!-- Product Colors -->
                                        {{-- <div class="col-md-12">--}}
                                        {{-- <div class="mb-2">--}}
                                        {{-- <label class="form-label label-text">Product Colors</label>--}}

                                        {{-- <div class="color-repeater">--}}
                                        {{-- <div data-repeater-list="colors">--}}
                                        {{-- <div data-repeater-item>--}}
                                        {{-- <div class="row align-items-start mt-1">--}}


                                        {{-- <div class="col-md-12">--}}
                                        {{-- <label class="form-label label-text">Color Value
                                            *</label>--}}
                                        {{-- <div class="d-flex gap-1 align-items-center">--}}
                                        {{--
                                        <!-- Color picker -->--}}
                                        {{--
                                        <input--}} {{-- type="color" --}} {{--
                                                                        class="form-control rounded-circle color-picker border border-0  "
                                                                        --}} {{-- style="max-width: 30px; padding: 0;"
                                                                        --}} {{-- value="#000" --}} {{-- />--}}

                                        {{--
                                        <!-- Text hex input (this will actually submit the value) -->--}}
                                        {{--
                                        <input--}} {{-- type="text" --}} {{-- name="value"
                                                                        --}} {{-- class="form-control color-hex-input"
                                                                        --}} {{-- placeholder="#000000" --}} {{--
                                                                        value="#000000" --}} {{--
                                                                        pattern="^#([A-Fa-f0-9]{6})$" --}} {{-- />--}}
                                        {{--
                                    </div>--}}
                                        {{-- <small class="text-muted">Pick a color or type hex
                                            (e.g. #FFAA00).</small>--}}
                                        {{-- </div>--}}




                                        {{-- <div class="col-md-12 mt-1">--}}
                                        {{-- <label class="form-label label-text">Color Image
                                            *</label>--}}
                                        {{-- <div
                                            class="dropzone color-dropzone border rounded p-2"
                                            --}} {{-- style="cursor:pointer; min-height:100px;">
                                                                    --}}
                                        {{-- <div class="dz-message" data-dz-message>--}}
                                        {{-- <span>Drop image or click</span>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}
                                        {{-- <input type="hidden" name="image_id"
                                            class="color-image-hidden">--}}
                                        {{-- </div>--}}


                                        {{-- <div class="col-md-2 text-center mt-1  ms-auto">--}}
                                        {{-- <button type="button" --}} {{--
                                                                    class="btn btn-outline-danger" --}} {{--
                                                                    data-repeater-delete>--}}
                                        {{-- <i data-feather="x" class="me-25"></i>--}}
                                        {{-- Delete--}}
                                        {{-- </button>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}

                                        {{-- <div class="row mt-1">--}}
                                        {{-- <div class="col-12">--}}
                                        {{-- <button type="button" --}} {{--
                                                            class="w-100 rounded-3 p-1 text-dark" --}} {{--
                                                            style="border: 2px dashed #CED5D4; background-color: #EBEFEF"
                                                            --}} {{-- data-repeater-create>--}}
                                        {{-- <i data-feather="plus" class="me-25"></i>--}}
                                        {{-- <span>Add New Color</span>--}}
                                        {{-- </button>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}
                                        <!-- Multiple Images Upload -->
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="form-label label-text" for="product-images">Product
                                                    Images (optional)</label>

                                                <!-- Dropzone container -->
                                                <div id="multi-dropzone" class="dropzone border rounded p-3"
                                                     style="cursor:pointer; min-height:150px;">
                                                    <div class="dz-message" data-dz-message>
                                                        <i data-feather="upload" class="mb-2"></i>
                                                        <p>Drag images here or click to upload</p>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="images_ids[]" id="images_ids">
                                                <div id="multi-uploaded-images" class="mt-3 d-flex flex-wrap gap-2">
                                                </div>

                                                <span class="image-hint small text-end">
                                                Max size: 1MB | Dimensions: 512x512 px
                                            </span>
                                            </div>
                                        </div>

                                        <!-- Tags -->
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="form-label label-text" for="tags">Tags <span
                                                        style="color: red; font-size: 20px;">*</span></label>
                                                <select name="tags[]" id="tags" class="select2 form-select" multiple>
                                                    @foreach($associatedData['tags'] as $tag)
                                                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Dimensions -->

                                        <div class="col-md-12 mb-2">
                                            <div>
                                                <label class="form-label label-text">Product Size <span
                                                        style="color: red; font-size: 20px;">*</span></label>

                                                <!-- Standard Dimensions -->
                                                <div
                                                    class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start"
                                                    id="standard-dimensions-container">
                                                    @foreach($associatedData['dimensions'] as $dimension)
                                                        <label
                                                            class="form-check option-box rounded border py-1 d-flex justify-content-center align-items-center cursor-pointer"
                                                            for="dimension-checkbox-{{ $dimension['id'] }}"
                                                            style="width: 100px;">
                                                            <input class="form-check-input me-1" type="checkbox"
                                                                   name="dimensions[]"
                                                                   id="dimension-checkbox-{{ $dimension['id'] }}"
                                                                   value="{{ $dimension['id'] }}"
                                                                   style="pointer-events: none"/>
                                                            <span class="form-check-label mb-0">
                                                        {{ $dimension['name'] }}
                                                    </span>
                                                        </label>
                                                    @endforeach
                                                </div>

                                                <!-- Custom Dimensions -->
                                                <div class="d-flex gap-3 mt-2" id="custom-dimensions-container">
                                                    <!-- Custom dimensions from sessionStorage will be injected here -->
                                                </div>

                                                <!-- Add Custom Size Button -->
                                                <button type="button" class="upload-card w-100 mt-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#addSizeModal">
                                                    Add Custom Size
                                                </button>
                                            </div>
                                        </div>


                                        <!-- Has Mockup -->
                                        <div class="col-md-12">
                                            <div class="mb-2 d-flex align-items-center gap-2">
                                                <label class="form-label label-text ">Is this product has
                                                    Mockup?</label>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="has_mockup" value="0"/>
                                                    <input class="form-check-input" type="checkbox" id="has_mockup"
                                                           name="has_mockup" value="1"/>

                                                </div>
                                            </div>
                                        </div>


                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-primary next-tab">Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!--end of the first tab content -->
                                <!--second tab content -->
                                <div class="tab-pane d-none" id="step2">

                                    <!-- Price Option Toggle -->
                                    <div class="col-md-12">
                                        <div class="mb-2">
                                            <label class="form-label label-text d-block">Quantity & Price
                                                Options</label>
                                            <label class="form-label label-text mt-2">Quantity Type <span
                                                    style="color: red; font-size: 20px;">*</span></label>
                                            <div class="row gap-1 d-flex flex-column flex-md-row" style="margin: 2px;">
                                                <div class="col border rounded-3 p-1">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio"
                                                               name="has_custom_prices" id="customPrice" value="1">
                                                        <div>
                                                            <label class="form-check-label label-text d-block"
                                                                   for="customPrice">Add Quantity Manually</label>
                                                            <label class="form-check-label text-dark"
                                                                   for="customPrice">Custom
                                                                Prices</label>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col border rounded-3 p-1">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio"
                                                               name="has_custom_prices" id="defaultPrice" value="0">
                                                        <div>
                                                            <label class="form-check-label label-text d-block"
                                                                   for="defaultPrice">Default Quantity</label>
                                                            <label class="form-check-label text-dark"
                                                                   for="defaultPrice">Default
                                                                Price</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Default Price -->
                                    <div class="col-md-12" id="default-price-section" style="display: none;">
                                        <div class="mb-2">
                                            <label class="form-label label-text" for="base_price">Original Price</label>
                                            <input type="text" id="base_price" name="base_price" class="form-control"
                                                   placeholder="Original Price"/>
                                        </div>
                                    </div>
                                    <!-- Custom Prices -->
                                    <div class="col-md-12" id="custom-price-section" style="display: none;">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="invoice-repeater">
                                                    <div data-repeater-list="prices">
                                                        <div data-repeater-item>
                                                            <div class="row d-flex align-items-end">
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <label
                                                                            class="form-label label-text">Quantity</label>
                                                                        <input type="number" name="prices[][quantity]"
                                                                               class="form-control"
                                                                               placeholder="Add Quantity"/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <label class="form-label label-text">Price
                                                                            (EGP)</label>
                                                                        <input type="text" name="prices[][price]"
                                                                               class="form-control"
                                                                               placeholder="Add Price"/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <button type="button"
                                                                                class="btn btn-outline-danger text-nowrap px-1"
                                                                                data-repeater-delete>
                                                                            <i data-feather="x" class="me-25"></i>
                                                                            <span>Delete</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <button type="button"
                                                                    class="w-100  rounded-3 p-1 bg-white text-dark"
                                                                    style="border:2px dashed #CED5D4;"
                                                                    data-repeater-create>
                                                                <i data-feather="plus" class="me-25"></i> <span>Add New
                                                                Quantity</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-secondary prev-tab">Previous</button>
                                        <button type="button" class="btn btn-primary next-tab">Next</button>
                                    </div>

                                    <!--end of the second tab content -->

                                </div>


                                <!--third tab content -->
                                <div class="tab-pane d-none" id="step3">
                                    <!-- Specifications -->
                                    <div class="col-12">
                                        <div class="mb-2">
                                            <label class="form-label label-text">Product Specs</label>
                                            <div class="">
                                                <div>
                                                    <!-- Outer Repeater for Specifications -->
                                                    <div class="outer-repeater">
                                                        <div data-repeater-list="specifications">
                                                            <div data-repeater-item>
                                                                <!-- Specification Fields -->
                                                                <div class="row mt-1">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-1">
                                                                            <label class="form-label label-text">Name
                                                                                (EN) <span
                                                                                    style="color: red; font-size: 20px;">*</span></label>
                                                                            <input type="text" name="name_en"
                                                                                   class="form-control"
                                                                                   placeholder="Specification Name (EN)"/>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-2">
                                                                            <label class="form-label label-text">Name
                                                                                (AR) <span
                                                                                    style="color: red; font-size: 20px;">*</span></label>
                                                                            <input type="text" name="name_ar"
                                                                                   class="form-control"
                                                                                   placeholder="Specification Name (AR)"/>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Inner Repeater for Specification Options -->
                                                                    <div class="inner-repeater">
                                                                        <div data-repeater-list="specification_options">
                                                                            <div data-repeater-item>
                                                                                <div
                                                                                    class="row d-flex flex-column flex-md-row gap-1 gap-md-0 mt-2">
                                                                                    <!-- Option Name (EN) -->
                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Value
                                                                                            (EN) <span
                                                                                                style="color: red; font-size: 20px;">*</span></label>
                                                                                        <input type="text"
                                                                                               name="value_en"
                                                                                               class="form-control"
                                                                                               placeholder="Option (EN)"/>
                                                                                    </div>

                                                                                    <!-- Option Name (AR) -->
                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Value
                                                                                            (AR) <span
                                                                                                style="color: red; font-size: 20px;">*</span></label>
                                                                                        <input type="text"
                                                                                               name="value_ar"
                                                                                               class="form-control"
                                                                                               placeholder="Option (AR)"/>
                                                                                    </div>

                                                                                    <!-- Option Price -->
                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Price
                                                                                            (EGP) (Optional)</label>
                                                                                        <input type="text" name="price"
                                                                                               class="form-control"
                                                                                               placeholder="Price"/>
                                                                                    </div>
                                                                                </div>
                                                                                <div
                                                                                    class="row d-flex align-items-end mt-2">
                                                                                    <div class="col-md-12">
                                                                                        <label
                                                                                            class="form-label label-text">Option
                                                                                            Image</label>

                                                                                        <!-- Dropzone container -->
                                                                                        <div
                                                                                            class="dropzone option-dropzone border rounded p-3"
                                                                                            style="cursor:pointer; min-height:120px;">
                                                                                            <div class="dz-message"
                                                                                                 data-dz-message>
                                                                                            <span>Drop image here or
                                                                                                click to upload</span>
                                                                                            </div>
                                                                                        </div>
                                                                                        <span
                                                                                            class="image-hint small text-end">
                                                Max size: 1MB | Dimensions: 200x200 px
                                            </span>

                                                                                        <!-- Hidden input to store uploaded file id / path -->
                                                                                        <input type="hidden"
                                                                                               name="option_image"
                                                                                               class="option-image-hidden">
                                                                                    </div>
                                                                                    <!-- ❌ Delete Option Button -->
                                                                                    <div class="row mt-2">
                                                                                        <div class="col-12 text-end">
                                                                                            <button type="button"
                                                                                                    class="btn btn-outline-danger"
                                                                                                    data-repeater-delete>
                                                                                                <i data-feather="x"
                                                                                                   class="me-25"></i>
                                                                                                Delete Value
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Add Option Button -->
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <button type="button"
                                                                                        class="btn primary-text-color bg-white mt-2"
                                                                                        data-repeater-create>
                                                                                    <i data-feather="plus"></i> <span> Add
                                                                                    New
                                                                                    Value</span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- End of Inner Repeater -->

                                                                    <!-- Delete Specification Button -->
                                                                    <div class="col-12 text-end mt-1 mb-2">
                                                                        <button type="button"
                                                                                class="btn btn-outline-danger"
                                                                                data-repeater-delete>
                                                                            <i data-feather="x" class="me-25"></i>
                                                                            Delete
                                                                            Spec
                                                                        </button>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Add New Specification Button -->
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <button type="button"
                                                                        class="w-100 rounded-3 p-1 text-dark"
                                                                        style="border: 2px dashed #CED5D4; background-color: #EBEFEF"
                                                                        data-repeater-create>
                                                                    <i data-feather="plus" class="me-25"></i> <span>Add New
                                                                    Spec</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Free Shipping -->
                                        {{-- <div class="col-md-12 col-12 mb-2">--}}
                                        {{-- <div class="form-check form-switch">--}}
                                        {{-- <input type="hidden" name="is_free_shipping" value="0">--}}
                                        {{-- <input type="checkbox" class="form-check-input" id="free-shipping"
                                            name="is_free_shipping" value="1">--}}
                                        {{-- <label class="form-check-label" for="free-shipping">Product available
                                            for
                                            free shipping</label>--}}
                                        {{-- </div>--}}
                                        {{-- </div>--}}
                                        {{--
                                    </div>--}}

                                        <!-- Submit -->
                                        <div class="col-12 d-flex justify-content-end gap-1">
                                            <button type="button" class="btn btn-secondary prev-tab">Previous</button>
                                            <button type="submit" class="btn btn-primary me-1 saveChangesButton"
                                                    id="SaveChangesButton">
                                                <span class="btn-text">Add Product</span>
                                                <span id="saveLoader"
                                                      class="spinner-border spinner-border-sm d-none saveLoader"
                                                      role="status" aria-hidden="true"></span>
                                            </button>

                                        </div>
                                    </div>
                                </div>
                                <!--third tab content end -->


                            </div>

                        </form>
        @include("modals.products.add-size")
    </section>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')

    {{--     <script>--}}
    {{--           document.addEventListener("DOMContentLoaded", function () {--}}
    {{--         const $colorRepeater = $('.color-repeater');--}}
    {{--        document.addEventListener("DOMContentLoaded", function () {--}}
    {{--            const $colorRepeater = $('.color-repeater');--}}


    {{--            $colorRepeater.find('[data-repeater-item]').each(function () {--}}
    {{--                initColorItem(this);--}}
    {{--            });--}}

    {{--            if (window.$ && $.fn.repeater) {--}}
    {{--                $colorRepeater.repeater({--}}
    {{--                    initEmpty: true,--}}
    {{--                    show: function () {--}}
    {{--                        $(this).slideDown();--}}

    {{--                        initColorItem(this);--}}
    {{--                        if (window.feather) feather.replace();--}}
    {{--                    },--}}
    {{--                    hide: function (deleteElement) {--}}
    {{--                        $(this).slideUp(deleteElement);--}}
    {{--                    }--}}
    {{--                });--}}

    {{--                --}}
    {{--                const hasItems = $colorRepeater.find('[data-repeater-item]').length > 0;--}}
    {{--                if (!hasItems) {--}}
    {{--                    $colorRepeater.find('[data-repeater-create]').first().trigger('click');--}}
    {{--                }--}}
    {{--            }--}}
    {{--        });--}}
    {{--
    </script>--}}

    {{-- <script>
        --}}
    {{--        Dropzone.autoDiscover = false;--}}

    {{--        function initColorItem(item) {--}}
    {{--            const dropzoneElement = item.querySelector('.color-dropzone');--}}
    {{--            const hiddenInput = item.querySelector('.color-image-hidden');--}}

    {{--            if (!dropzoneElement || !hiddenInput) return;--}}


    {{--            if (dropzoneElement.dropzone) return;--}}

    {{--            const dz = new Dropzone(dropzoneElement, {--}}
    {{--                url: "{{ route('media.store') }}",--}}
    {{--                paramName: "file",--}}
    {{--                maxFiles: 1,--}}
    {{--                maxFilesize: 1, // MB--}}
    {{--                acceptedFiles: "image/*",--}}
    {{--                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },--}}
    {{--                addRemoveLinks: true,--}}
    {{--                init: function () {--}}
    {{--                    this.on("success", function (file, response) {--}}
    {{--                        if (response.success && response.data) {--}}
    {{--                            file._hiddenInputId = response.data.id;--}}
    {{--                            hiddenInput.value = response.data.id;--}}
    {{--                        }--}}
    {{--                    });--}}

    {{--                    this.on("removedfile", function (file) {--}}
    {{--                        hiddenInput.value = "";--}}
    {{--                        if (file._hiddenInputId) {--}}
    {{--                            fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {--}}
    {{--                                method: "DELETE",--}}
    {{--                                headers: {--}}
    {{--                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content--}}
    {{--                                }--}}
    {{--                            });--}}
    {{--                        }--}}
    {{--                    });--}}
    {{--                }--}}
    {{--            });--}}

    {{--            const colorPicker = item.querySelector('.color-picker');--}}
    {{--            const hexInput = item.querySelector('.color-hex-input');--}}

    {{--            if (colorPicker && hexInput) {--}}
    {{--                colorPicker.addEventListener('input', function () {--}}
    {{--                    const hex = this.value.toUpperCase();--}}
    {{--                    hexInput.value = hex;--}}
    {{--                });--}}

    {{--                hexInput.addEventListener('input', function () {--}}
    {{--                    let v = this.value.toUpperCase();--}}
    {{--                    if (!v.startsWith('#')) v = '#' + v;--}}
    {{--                    this.value = v;--}}

    {{--                    if (/^#([0-9A-F]{6})$/.test(v)) {--}}
    {{--                        colorPicker.value = v;--}}
    {{--                    }--}}
    {{--                });--}}
    {{--            }--}}
    {{--        }--}}
    {{--
    </script>--}}
    <script !src="">
        Dropzone.autoDiscover = false;

        const categoryDropzone = new Dropzone("#product-model-dropzone", {
            url: "{{ route('media.store') }}",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 1, // MB
            acceptedFiles: "image/*",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            addRemoveLinks: true,
            dictDefaultMessage: "Drop image here or click to upload",
            init: function () {
                this.on("success", function (file, response) {
                    if (response.success && response.data) {
                        file._hiddenInputId = response.data.id;

                        // ✅ This will now set the hidden field correctly
                        document.getElementById("uploadedImageModel").value = response.data.id;
                        file._hiddenInputId = response.data.id;
                    }

                });

                this.on("removedfile", function (file) {
                    document.getElementById("uploadedImageModel").value = "";
                    if (file._hiddenInputId) {
                        fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                    }

                    // hide preview
                    document.getElementById("uploaded-image").classList.add("d-none");
                });
            }
        });

        // Handle remove button manually
        document.getElementById("remove-image").addEventListener("click", function () {
            categoryDropzone.removeAllFiles(true);
            document.getElementById("uploadedImage").value = "";
            document.getElementById("uploaded-image").classList.add("d-none");
        });
    </script>
    <script>
        Dropzone.autoDiscover = false; // prevent auto init

        function initOptionDropzones() {
            document.querySelectorAll(".option-dropzone").forEach((element) => {
                if (element.dropzone) return; // prevent duplicate init

                let hiddenInput = element.closest(".col-md-12").querySelector(".option-image-hidden");

                new Dropzone(element, {
                    url: "{{ route('media.store') }}", // your backend upload route
                    paramName: "file",
                    maxFiles: 1,
                    maxFilesize: 1, // MB
                    acceptedFiles: "image/*",
                    addRemoveLinks: true,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    init: function () {
                        this.on("success", function (file, response) {
                            // Assuming backend returns { id: ..., path: ... }
                            hiddenInput.value = response.data.id;
                            file._hiddenInputId = response.data.id;
                        });

                        this.on("removedfile", function (file) {
                            hiddenInput.value = "";
                            if (file._hiddenInputId) {
                                fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                                    method: "DELETE",
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                                        "Accept": "application/json"
                                    }
                                })
                                    .then(res => res.json())
                                    .then(data => {
                                        console.log("Deleted from server:", data);
                                    })
                                    .catch(err => {
                                        console.error("Delete failed:", err);
                                    });
                            }
                        });
                    }
                });
            });
        }

        // Run once on page load
        initOptionDropzones();

        // Run again whenever repeater adds a new option row
        $(document).on("click", "[data-repeater-create]", function () {
            setTimeout(() => {
                initOptionDropzones();
            }, 200); // slight delay to ensure DOM updated
        });
    </script>

    <script>
        Dropzone.autoDiscover = false;

        const multiDropzone = new Dropzone("#multi-dropzone", {
            url: "{{ route('media.store') }}",   // backend route for image upload
            paramName: "file",
            maxFiles: 10,              // allow up to 10 images
            maxFilesize: 1,            // MB
            acceptedFiles: "image/*",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            addRemoveLinks: true,
            dictDefaultMessage: "Drag images here or click to upload",
            init: function () {
                this.on("success", function (file, response) {
                    if (response.success && response.data) {
                        // save file id on the file object
                        file._hiddenInputId = response.data.id;

                        // append hidden input for each uploaded file
                        let hiddenInput = document.createElement("input");
                        hiddenInput.type = "hidden";
                        hiddenInput.name = "images_ids[]";
                        hiddenInput.value = response.data.id;
                        hiddenInput.id = "hidden-image-" + response.data.id;

                        document.querySelector("#multi-uploaded-images").appendChild(hiddenInput);


                    }
                });

                this.on("removedfile", function (file) {
                    if (file._hiddenInputId) {
                        // remove hidden input
                        let hiddenInput = document.getElementById("hidden-image-" + file._hiddenInputId);
                        if (hiddenInput) hiddenInput.remove();

                        // remove preview
                        let previewImg = document.getElementById("preview-image-" + file._hiddenInputId);
                        if (previewImg) previewImg.remove();

                        // delete from server
                        fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                    }
                });
            }
        });
    </script>

    <script>
        Dropzone.autoDiscover = false;

        const mainDropzone = new Dropzone("#product-main-dropzone", {
            url: "{{ route('media.store') }}",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 1,
            acceptedFiles: "image/*",
            headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
            addRemoveLinks: true,
            init: function () {
                this.on("success", function (file, response) {
                    if (response.success && response.data) {
                        file._hiddenInputId = response.data.id;
                        document.getElementById("uploadedImageMain").value = response.data.id;

                    }
                });

                this.on("removedfile", function (file) {
                    document.getElementById("uploadedImageMain").value = "";
                    if (file._hiddenInputId) {
                        fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                            method: "DELETE",
                            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
                        });
                    }
                    document.getElementById("uploaded-image-main").classList.add("d-none");
                });
            }
        });

        // Manual remove
        document.getElementById("remove-image-main").addEventListener("click", function () {
            mainDropzone.removeAllFiles(true);
            document.getElementById("uploadedImageMain").value = "";
            document.getElementById("uploaded-image-main").classList.add("d-none");
        });

    </script>
    <script>
        console.log(jQuery.fn.jquery);

        $(document).ready(function () {
            const form = $("#category-form");
            let currentStep = 0;

            const steps = [
                $("#step1"),
                $("#step2"),
                $("#step3")
            ];

            const navTabs = $("#formTabs .nav-link");

            // jQuery Validation
            form.validate({
                ignore: [],
                errorClass: "is-invalid",
                validClass: "is-valid",
                errorElement: "div",
                errorPlacement: function (error, element) {
                    error.addClass("invalid-feedback");
                    if (element.closest('.input-group').length) {
                        error.insertAfter(element.closest('.input-group'));
                    } else {
                        error.insertAfter(element);
                    }
                },
                rules: {
                    "name[en]": "required",
                    "name[ar]": "required",
                    "image": "required",
                    "category_id": "required",
                    "dimensions[]": {
                        required: true,
                        minlength: 1
                    }
                },
                messages: {
                    "name[en]": "Please enter the category name in English.",
                    "name[ar]": "Please enter the category name in Arabic.",
                    "image": "Please upload a category image.",
                    "category_id": "Please select a product.",
                    "dimensions[]": "Please select at least one size."
                }
            });

            // Step UI control
            function goToStep(index) {
                $(".tab-pane").removeClass("active d-block").addClass("d-none");
                steps[index].removeClass("d-none").addClass("active d-block");

                navTabs.removeClass("active");
                navTabs.eq(index).addClass("active");

                currentStep = index;
            }

            $(".next-tab").on("click", function () {
                // Validate only visible fields
                const currentFields = steps[currentStep].find("input, select, textarea").filter(":visible");
                if (currentFields.length === 0 || currentFields.toArray().every(field => $(field).valid())) {
                    if (currentStep < steps.length - 1) {
                        goToStep(currentStep + 1);
                    }
                }
            });

            $(".prev-tab").on("click", function () {
                if (currentStep > 0) {
                    goToStep(currentStep - 1);
                }
            });

            // Optional: disable manual tab clicks forward
            navTabs.on("click", function (e) {
                e.preventDefault();
                const step = parseInt($(this).data("step"));

                if (step <= currentStep) {
                    goToStep(step);
                }
            });

            // Start on first step
            goToStep(0);
        });
    </script>

    <script>
        $(document).ready(function () {
            // Optional if you want to clear when user reloads
            window.addEventListener("beforeunload", function () {
                sessionStorage.removeItem("custom_dimensions");
            });

        });
    </script>






    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Toggle pricing
            $('input[name="has_custom_prices"]').on('change', function () {
                const isCustom = $(this).val() === '1';
                $('#custom-price-section').toggle(isCustom).find('input').prop('disabled', !isCustom);
                $('#default-price-section').toggle(!isCustom).find('input').prop('disabled', isCustom).val('');
            });

            // Repeater
            $('.invoice-repeater').repeater({
                show: function () {
                    $(this).slideDown();
                    feather && feather.replace();

                    // Recalculate delete button visibility when an item is shown
                    var items = $(this).closest('.invoice-repeater').find('[data-repeater-item]');
                    items.each(function (index) {
                        // Hide delete button for the first item (index 0) and show for others
                        if (index === 0) {
                            $(this).find('[data-repeater-delete]').hide(); // Hide the delete button for the first item
                        } else {
                            $(this).find('[data-repeater-delete]').show(); // Show delete button for others
                        }
                    });
                },
                hide: function (deleteElement) {
                    $(this).slideUp(deleteElement);

                    // Recalculate delete button visibility after an item is removed
                    var items = $(this).closest('.invoice-repeater').find('[data-repeater-item]');
                    items.each(function (index) {
                        // Hide delete button for the first item (index 0) and show for others
                        if (index === 0) {
                            $(this).find('[data-repeater-delete]').hide(); // Hide the delete button for the first item
                        } else {
                            $(this).find('[data-repeater-delete]').show(); // Show delete button for others
                        }
                    });
                }
            });


            function updateDeleteButtons(containerSelector) {
                $(containerSelector).find('[data-repeater-list]').each(function () {
                    var items = $(this).find('[data-repeater-item]');
                    items.each(function () {
                        $(this).find('[data-repeater-delete]').show();
                        feather.replace();
                    });
                });
            }

            function initializeImageUploaders(context) {
                $(context).find('.option-upload-area').each(function () {
                    const uploadArea = $(this);
                    const input = uploadArea.closest('.col-md-12').find('.option-image-input');
                    const previewContainer = uploadArea.closest('.col-md-12').find('.option-uploaded-image');
                    const imagePreview = previewContainer.find('.option-image-preview');
                    const fileNameLabel = previewContainer.find('.option-file-name');
                    const fileSizeLabel = previewContainer.find('.option-file-size');
                    const removeButton = previewContainer.find('.option-remove-image');

                    uploadArea.off('click').on('click', function () {
                        input.trigger('click');
                    });

                    input.off('change').on('change', function () {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                imagePreview.attr('src', e.target.result);
                                fileNameLabel.text(file.name);
                                fileSizeLabel.text((file.size / 1024).toFixed(1) + ' KB');
                                previewContainer.removeClass('d-none');
                            };
                            reader.readAsDataURL(file);
                        }
                    });

                    removeButton.off('click').on('click', function () {
                        input.val('');
                        previewContainer.addClass('d-none');
                    });
                });
            }

            $('.outer-repeater').repeater({
                initEmpty: true,
                repeaters: [{
                    selector: '.inner-repeater',
                    initEmpty: true,
                    show: function () {
                        $(this).slideDown();
                        updateDeleteButtons($(this).closest('.inner-repeater'));
                        initializeImageUploaders(this);
                        feather.replace();
                    },
                    hide: function (deleteElement) {
                        $(this).slideUp(deleteElement);
                        updateDeleteButtons($(this).closest('.inner-repeater'));
                    },
                    afterAdd: function () {
                        updateDeleteButtons($(this).closest('.inner-repeater'));
                        initializeImageUploaders(this);
                        feather.replace();
                    },
                    afterDelete: function () {
                        updateDeleteButtons($(this).closest('.inner-repeater'));
                    },
                    nestedInputName: 'specification_options'
                }],
                show: function () {
                    $(this).slideDown();
                    updateDeleteButtons($('.outer-repeater'));
                    initializeImageUploaders(this);
                    feather.replace();
                },
                hide: function (deleteElement) {
                    $(this).slideUp(deleteElement);
                    updateDeleteButtons($('.outer-repeater'));
                },
                afterAdd: function () {
                    updateDeleteButtons($('.outer-repeater'));
                    initializeImageUploaders($('.outer-repeater'));
                    feather.replace();
                },
                afterDelete: function () {
                    updateDeleteButtons($('.outer-repeater'));
                }
            });

// Initialize on page load
            $(document).ready(function () {
                updateDeleteButtons($('.outer-repeater'));
                updateDeleteButtons($('.inner-repeater')); // <-- important
                initializeImageUploaders($('.outer-repeater'));
            });


            $('.select2').select2();

            // Product -> Subcategory
            $('.category-select').on('change', function () {
                const categoryId = $(this).val();
                const $subProductSelect = $('.sub-category-select');
                $.ajax({
                    url: `${$subProductSelect.data('sub-category-url')}?filter[parent_id]=${categoryId}`,
                    method: "GET",
                    success: function (res) {
                        $subProductSelect.empty().append('<option value="">Select subproduct</option>');
                        $.each(res.data, (i, s) => $subProductSelect.append(`<option value="${s.id}">${s.name}</option>`));
                    },
                    error: function () {
                        $subProductSelect.empty().append('<option value="">Error loading Subcategories</option>');
                    }
                });
            });

            // Form submit
            $('#category-form').on('submit', function (e) {
                e.preventDefault();

                const saveButton = $('.saveChangesButton');
                const saveLoader = $('.saveLoader');
                const saveButtonText = $('.saveChangesButton .btn-text');

                saveButton.prop('disabled', true);
                saveLoader.removeClass('d-none');
                saveButtonText.addClass('d-none');

                const formEl = this;
                const formData = new FormData(formEl);

                // (Optional) also inject as hidden inputs for full safety
                formEl.querySelectorAll('input[data-custom-dim="1"]').forEach(n => n.remove());

                const raw = sessionStorage.getItem('custom_dimensions');
                if (raw) {
                    try {
                        const dims = JSON.parse(raw);
                        console.log('[custom_dimensions] parsed:', dims);

                        dims.forEach((dim, i) => {
                            const isCustom = dim.is_custom ? 1 : 0;

                            // -> FormData (server receives via $_POST)
                            formData.append(`custom_dimensions[${i}][width]`, dim.width);
                            formData.append(`custom_dimensions[${i}][height]`, dim.height);
                            formData.append(`custom_dimensions[${i}][unit]`, dim.unit);
                            formData.append(`custom_dimensions[${i}][name]`, dim.name);
                            formData.append(`custom_dimensions[${i}][is_custom]`, isCustom);

                            // -> Hidden inputs (belt & suspenders)
                            [['width', dim.width], ['height', dim.height], ['unit', dim.unit], ['name', dim.name], ['is_custom', isCustom]]
                                .forEach(([k, v]) => {
                                    const inp = document.createElement('input');
                                    inp.type = 'hidden';
                                    inp.name = `custom_dimensions[${i}][${k}]`;
                                    inp.value = v;
                                    inp.setAttribute('data-custom-dim', '1');
                                    formEl.appendChild(inp);
                                });
                        });
                    } catch (e) {
                        console.warn('[custom_dimensions] JSON parse error:', e, 'raw:', raw);
                    }
                } else {
                    console.log('[custom_dimensions] none in sessionStorage');
                }

                $.ajax({
                    url: formEl.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        window.location.replace('/categories');
                        if (res.success) {
                            Toastify({
                                text: "Product created successfully!",
                                duration: 2000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#28C76F",
                                close: true,
                            }).showToast();
                        } else {
                            // backend returned success:false
                            saveButton.prop('disabled', false);
                            saveLoader.addClass('d-none');
                            saveButtonText.removeClass('d-none');
                        }
                    },
                    error: function (xhr) {
                        const errs = (xhr.responseJSON && xhr.responseJSON.errors) || {};
                        Object.keys(errs).forEach(k => {
                            Toastify({
                                text: errs[k][0],
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#EA5455",
                                close: true
                            }).showToast();
                        });
                        saveButton.prop('disabled', false);
                        saveLoader.addClass('d-none');
                        saveButtonText.removeClass('d-none');
                    }
                });
            });

        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const steps = ["step1", "step2", "step3"];
            let currentStep = 0;

            const showStep = (index) => {
                currentStep = index;

                // Update tab panes
                steps.forEach((step, i) => {
                    const tab = document.getElementById(step);
                    tab.classList.toggle("active", i === index);
                    tab.classList.toggle("d-none", i !== index);
                });

                // Update tab headers
                document.querySelectorAll("#formTabs .nav-link").forEach((link, i) => {
                    link.classList.toggle("active", i === index);
                });
            };

            // Header tab clicks
            document.querySelectorAll("#formTabs .nav-link").forEach(link => {
                link.addEventListener("click", function (e) {
                    e.preventDefault();
                    const stepIndex = parseInt(this.dataset.step);
                    showStep(stepIndex);
                });
            });

            // Next buttons
            document.querySelectorAll(".next-tab").forEach(btn => {
                btn.addEventListener("click", () => {
                    if (currentStep < steps.length - 1) {
                        showStep(currentStep + 1);
                    }
                });
            });

            // Prev buttons
            document.querySelectorAll(".prev-tab").forEach(btn => {
                btn.addEventListener("click", () => {
                    if (currentStep > 0) {
                        showStep(currentStep - 1);
                    }
                });
            });

            // Initial load
            showStep(currentStep);
        });
    </script>

@endsection
