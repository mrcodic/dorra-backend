@extends('layouts/contentLayoutMaster')
@php
$dimensions = \App\Models\Dimension::query()
->where('is_custom', false)
->orWhereHas('products', function ($q) use ($model) {
$q->where('product_id', $model->id);
})
->get(['id', 'name']);


@endphp
@section('title', 'Edit Categories')
@section('main-page', 'Categories')
@section('sub-page', 'Edit Category')
@section('main-page-url', route("products.index"))
@section('sub-page-url', route("products.edit",$model->id))
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
                    <ul class="nav nav-tabs mb-4 w-100 d-flex justify-content-center" id="formTabs">
                        <li class="nav-item" style="width: 30%;">
                            <a class="nav-link active" data-step="0" href="#" style="font-size: 14px;">Category
                                Details</a>
                        </li>
                        <li class="nav-item" style="width: 30%;">
                            <a class="nav-link" data-step="1" href="#" style="font-size: 14px;">Quantity & Price</a>
                        </li>
                        <li class="nav-item" style="width: 30%;">
                            <a class="nav-link" data-step="2" href="#" style="font-size: 14px;">Category Specs</a>
                        </li>
                    </ul>

                    <form id="product-form" class="form" action="{{ route('products.update',$model->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method("PUT")

                        <div class="tab-content">
                            <!-- first tab content -->
                            <div class="tab-pane active" id="step1">
                                <div class="row">
                                    <!-- Category Name EN/AR -->
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label label-text" for="product-name-en">Category Name
                                                (EN)</label>
                                            <input type="text" id="product-name-en"
                                                value="{{ $model->getTranslation('name','en') }}" class="form-control"
                                                name="name[en]" placeholder="Category Name (EN)" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label label-text" for="product-name-ar">Category Name
                                                (AR)</label>
                                            <input type="text" id="product-name-ar"
                                                value="{{ $model->getTranslation('name','ar') }}" class="form-control"
                                                name="name[ar]" placeholder="Category Name (AR)" />
                                        </div>
                                    </div>

                                    <!-- Description EN/AR -->
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label label-text" for="description-en">Category
                                                Description
                                                (EN)</label>
                                            <textarea name="description[en]" id="description-en" class="form-control"
                                                placeholder="Category Description (EN)">{{ $model->getTranslation('description','en') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label label-text" for="description-ar">Category
                                                Description
                                                (AR)</label>
                                            <textarea name="description[ar]" id="description-ar" class="form-control"
                                                placeholder="Category Description (AR)">{{ $model->getTranslation('description','ar') }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Main Image Upload -->
                                    <div class="col-md-12">
                                        <div class="mb-2">
                                            <label class="form-label label-text" for="product-image-main">
                                                Category Image (main)
                                            </label>

                                            <!-- Dropzone Container -->
                                            <div id="product-main-dropzone" class="dropzone border rounded p-3"
                                                style="cursor:pointer; min-height:150px;">
                                                <div class="dz-message" data-dz-message>
                                                    <span>Drop image here or click to upload</span>
                                                </div>
                                            </div>

                                            <!-- ✅ Hidden input outside Dropzone -->
                                            <input type="hidden" name="image_id" id="uploadedImage">

                                            <span class="image-hint small text-end">
                                                Max size: 1MB | Dimensions: 512x512 px
                                            </span>


                                        </div>
                                    </div>
                                    <!-- Multiple Images Upload -->
                                    <div class="col-md-12">
                                        <div class="mb-2">
                                            <label class="form-label label-text" for="product-images">Category
                                                Images</label>

                                            <!-- Dropzone container -->
                                            <div id="multi-dropzone" class="dropzone border rounded p-3"
                                                style="cursor:pointer; min-height:150px;">
                                                <div class="dz-message" data-dz-message>
                                                    <i data-feather="upload" class="mb-2"></i>
                                                    <p>Drag images here or click to upload</p>
                                                </div>
                                            </div>
                                            <input type="hidden" name="images_ids[]" id="images_ids">
                                            <div id="multi-uploaded-images" class="mt-3 d-flex flex-wrap gap-2"></div>

                                            <span class="image-hint small text-end">
                                                Max size: 1MB | Dimensions: 512x512 px </span>
                                            <!-- Uploaded Images Preview Area -->
                                            <div id="multi-uploaded-images" class=" mt-3"></div>
                                        </div>
                                    </div>

                                    <!-- Category & Subcategory -->
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label label-text" for="category">Product</label>
                                            <select name="category_id" id="category"
                                                class="form-control category-select">
                                                <option value="" disabled selected>Select product</option>
                                                @foreach($associatedData['categories'] as $category)
                                                <option value="{{ $category->id }}" @selected($category->id ==
                                                    $model->category?->id)>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Subcategory -->
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label label-text" for="sub_category">SubProduct</label>
                                            <select name="sub_category_id" id="sub_category"
                                                class="form-control sub-category-select"
                                                data-sub-category-url="{{ route('sub-categories') }}">
                                                <option value=""  disabled selected>Select subproduct</option>
                                                @php
                                                $subCategories =
                                                App\Models\Category::whereParentId($model->category_id)->get();
                                                @endphp
                                                @if(old('category_id', $model->category_id))
                                                @foreach($subCategories as $subCategory)
                                                <option value="{{ $subCategory->id }}" @selected($subCategory->id ==
                                                    $model->sub_category_id)>{{ $subCategory->name }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>


                                    <!-- Tags -->
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label class="form-label label-text" for="tags">Tags</label>
                                            <select name="tags[]" id="tags" class="select2 form-select" multiple>
                                                @foreach($associatedData['tags'] as $tag)
                                                <option value="{{ $tag->id }}" @if(in_array($tag->id,
                                                    $model->tags->pluck('id')->toArray())) selected @endif >{{
                                                    $tag->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Has Mockup -->


                                        <!-- Has Mockup -->
                                        <div class="col-md-12">
                                            <div class="mb-2 d-flex align-items-center gap-2">
                                                <label class="form-label label-text ">Is this category has
                                                    Mockup?</label>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="has_mockup" value="0" />
                                                    <input class="form-check-input" type="checkbox" id="has_mockup"
                                                        name="has_mockup" value="1" @checked($model->has_mockup == 1)

                                                    />

                                                </div>
                                            </div>
                                        </div>

                                        <!-- Dimensions -->

                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label label-text">Category Size</label>
                                                <!-- Standard Dimensions -->
                                                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2"
                                                    id="standard-dimensions-container">
                                                    @foreach($dimensions as $dimension)
                                                    <div class="form-check option-box rounded border py-1 d-flex justify-content-center align-items-center"
                                                        style="width: 100px">
                                                        <input class="form-check-input me-1" type="checkbox"
                                                            name="dimensions[]"
                                                            id="dimension-checkbox-{{ $dimension['id'] }}"
                                                            value="{{ $dimension['id'] }}"
                                                            @checked($model->dimensions->contains($dimension->id))
                                                        />
                                                        <label class="form-check-label mb-0"
                                                            for="dimension-checkbox-{{ $dimension['id'] }}">
                                                            {{ $dimension['name'] }}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>

                                                <!-- Custom Dimensions -->
                                                <div class="d-flex gap-3 mt-2 mb-2" id="custom-dimensions-container">
                                                    <!-- Custom dimensions from sessionStorage will be injected here -->
                                                </div>


                                            </div>
                                            <button type="button" class="upload-card w-100 mt-1" data-bs-toggle="modal"
                                                data-bs-target="#addSizeModal">Add Custom Size
                                            </button>

                                        </div>

                                    </div>

                                    <div class="d-flex justify-content-end ">
                                        <button type="button" class="btn btn-primary next-tab mt-2">Next</button>
                                    </div>
                                </div>
                                <!-- first tab content end -->

                                <!--second tab content -->
                                <div class="tab-pane d-none" id="step2">
                                    <!-- Price Option Toggle -->
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label class="form-label label-text d-block">Quantity & Price
                                                Options</label>
                                            <label class="form-label label-text mt-2">Quantity Type</label>
                                            <div class="row gap-1 d-flex flex-column flex-md-row" style="margin: 2px;">
                                                <div class="col border rounded-3 p-1">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio"
                                                            name="has_custom_prices" id="customPrice" value="1"
                                                            @checked($model->has_custom_prices == 1) @disabled($model->has_custom_prices == 0)>
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
                                                        <input class="form-check-input " type="radio"
                                                            name="has_custom_prices" id="defaultPrice" value="0"
                                                            @checked($model->has_custom_prices == 0)@disabled($model->has_custom_prices == 1)>
                                                        <div>
                                                            <label class="form-check-label label-text d-block"
                                                                for="customPrice">Default Quantity</label>
                                                            <label class="form-check-label text-dark"
                                                                for="defaultPrice">Default
                                                                Price</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Custom Prices -->
                                    <div class="col-md-12" id="custom-price-section"
                                        style="{{  $model->has_custom_prices == 1 ? '': 'display: none;' }}">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="invoice-repeater">
                                                    <div data-repeater-list="prices">
                                                        @forelse($model->prices as $price)
                                                        <div data-repeater-item>
                                                            <div class="row d-flex align-items-end">
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <label
                                                                            class="form-label label-text">Quantity</label>
                                                                        <input type="number" name="quantity"
                                                                            value="{{ $price->quantity }}"
                                                                            class="form-control"
                                                                            placeholder="Add Quantity" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <label class="form-label label-text">Price
                                                                            (EGP)</label>
                                                                        <input type="text" name="price"
                                                                            value="{{ $price->price }}"
                                                                            class="form-control"
                                                                            placeholder="Add Price" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <button type="button"
                                                                            class="btn btn-outline-danger text-nowrap px-1"
                                                                            style="display: none" data-repeater-delete>
                                                                            <i data-feather="x" class="me-25"></i>
                                                                            <span>Delete</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        @empty
                                                        <div data-repeater-item>
                                                            <div class="row d-flex align-items-end">
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <label
                                                                            class="form-label label-text">Quantity</label>
                                                                        <input type="number" name="quantity"
                                                                            class="form-control"
                                                                            placeholder="Add Quantity" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <label class="form-label label-text">Price
                                                                            (EGP)</label>
                                                                        <input type="text" name="price"
                                                                            class="form-control"
                                                                            placeholder="Add Price" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <button type="button"
                                                                            class="btn btn-outline-danger text-nowrap px-1"
                                                                            style="display: none" data-repeater-delete>
                                                                            <i data-feather="x" class="me-25"></i>
                                                                            <span>Delete</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        @endforelse
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <button type="button"
                                                                class="w-100  rounded-3 p-1 bg-white text-dark"
                                                                style="border:2px dashed #CED5D4;" data-repeater-create>
                                                                <i data-feather="plus" class="me-25"></i> <span>Add New
                                                                    Quantity</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Default Price -->
                                    <div class="col-md-12" id="default-price-section"
                                        style="{{ $model->has_custom_prices == 0 ? '' : 'display:none;' }}">
                                        <div class="mb-1">
                                            <label class="form-label label-text" for="base_price">Original Price (EGP)
                                                (Per
                                                Item)</label>
                                            <input type="text" id="base_price" name="base_price"
                                                value="{{ $model->base_price }}" class="form-control"
                                                placeholder="Original Price" />
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-secondary prev-tab">Previous</button>
                                        <button type="button" class="btn btn-primary next-tab">Next</button>
                                    </div>
                                </div>
                                <!--second tab content end -->

                                <!--third tab content -->
                                <div class="tab-pane d-none" id="step3">
                                    <!-- Specifications -->
                                    <div class="col-12">
                                        <div class="mb-1">
                                            <label class="form-label label-text">Category Specs</label>
                                            <div class="">
                                                <div>
                                                    @php
                                                    $hasSpecs = $model->specifications->isNotEmpty();
                                                    @endphp
                                                    <!-- Outer Repeater for Specifications -->
                                                    <div class="outer-repeater">
                                                        <div class="{{ $hasSpecs ? '' :'d-none' }}"
                                                            data-repeater-list="specifications">
                                                            @forelse($model->specifications as $specification)
                                                            <div data-repeater-item>
                                                                <!-- Specification Fields -->
                                                                <div class="row mt-1">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-1">
                                                                            <label class="form-label label-text">Name
                                                                                (EN)</label>
                                                                            <input type="text" name="name_en"
                                                                                value="{{ $specification->getTranslation('name','en') }}"
                                                                                class="form-control"
                                                                                placeholder="Specification Name (EN)" />
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="mb-1">
                                                                            <label class="form-label label-text">Name
                                                                                (AR)</label>
                                                                            <input type="text" name="name_ar"
                                                                                value="{{ $specification->getTranslation('name','ar') }}"
                                                                                class="form-control"
                                                                                placeholder="Specification Name (AR)" />
                                                                        </div>
                                                                    </div>

                                                                    <!-- Inner Repeater for Specification Options -->
                                                                    <div class="inner-repeater">
                                                                        <div data-repeater-list="specification_options">
                                                                            @foreach($specification->options as $option)
                                                                            <div data-repeater-item>
                                                                                <div
                                                                                    class="row d-flex flex-column flex-md-row gap-1 gap-md-0 mt-2">
                                                                                    <!-- Option Name (EN) -->
                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Value
                                                                                            (EN)</label>
                                                                                        <input type="text"
                                                                                            name="value_en"
                                                                                            value="{{ $option->getTranslation('value','en') }}"
                                                                                            class="form-control"
                                                                                            placeholder="Option (EN)" />
                                                                                    </div>

                                                                                    <!-- Option Name (AR) -->
                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Value
                                                                                            (AR)</label>
                                                                                        <input type="text"
                                                                                            name="value_ar"
                                                                                            value="{{ $option->getTranslation('value','ar') }}"
                                                                                            class="form-control"
                                                                                            placeholder="Option (AR)" />
                                                                                    </div>

                                                                                    <!-- Option Price -->
                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Price
                                                                                            (EGP)
                                                                                            (Optional)</label>
                                                                                        <input type="text"
                                                                                            value="{{ $option->price }}"
                                                                                            name="price"
                                                                                            class="form-control"
                                                                                            placeholder="Price" />
                                                                                    </div>
                                                                                </div>
                                                                                <div
                                                                                    class="row d-flex align-items-end mt-2">
                                                                                    <!-- Option Image -->
                                                                                    <div class="col-md-12">
                                                                                        <label
                                                                                            class="form-label label-text">Option
                                                                                            Image</label>

                                                                                        <!-- Hidden real input -->
                                                                                        <!-- Hidden real input -->
                                                                                        <input type="file" name="image"
                                                                                            class="form-control d-none option-image-input"
                                                                                            accept="image/*">

                                                                                        <!-- Custom Upload Card -->
                                                                                        <div
                                                                                            class="upload-card option-upload-area">
                                                                                            <div
                                                                                                class="d-flex justify-content-center align-items-center gap-1">
                                                                                                <i data-feather="upload"
                                                                                                    class="mb-2"></i>
                                                                                                <p>Drag image
                                                                                                    here
                                                                                                    to
                                                                                                    upload</p>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Progress Bar -->
                                                                                        <div
                                                                                            class="progress mt-2 d-none w-50 option-upload-progress">
                                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                                                                style="width: 0%"></div>
                                                                                        </div>

                                                                                        <!-- Uploaded Image Preview -->
                                                                                        @php $image = $option->image;
                                                                                        @endphp
                                                                                        <div
                                                                                            class="uploaded-image {{ $option && $image  ? '' : 'd-none'  }} position-relative mt-1 d-flex align-items-center gap-2 option-uploaded-image">
                                                                                            <img src="{{ $image?->getUrl() }}"
                                                                                                alt="Uploaded"
                                                                                                class="img-fluid rounded option-image-preview"
                                                                                                style="width: 50px; height: 50px; object-fit: cover;">
                                                                                            <div class="file-details">
                                                                                                <div
                                                                                                    class="file-name fw-bold option-file-name">
                                                                                                    {{
                                                                                                    $image?->file_name
                                                                                                    }}
                                                                                                </div>
                                                                                                <div
                                                                                                    class="file-size text-muted small option-file-size">
                                                                                                    {{
                                                                                                    number_format($image?->size
                                                                                                    / 1024, 1) }}
                                                                                                    KB
                                                                                                </div>
                                                                                            </div>
                                                                                            {{-- <button type="button"
                                                                                                --}} {{--
                                                                                                class="btn btn-sm position-absolute text-danger option-remove-image"
                                                                                                --}} {{--
                                                                                                style="top: 5px; right: 5px; background-color: #FFEEED">--}}
                                                                                                {{-- <i
                                                                                                    data-feather="trash"></i>--}}
                                                                                                {{-- </button>--}}
                                                                                        </div>

                                                                                        <div
                                                                                            class="col-12 text-end mt-1 mb-2">
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

                                                                            @endforeach
                                                                        </div>

                                                                        <!-- Add Option Button -->
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <button type="button"
                                                                                    class="btn primary-text-color bg-white mt-2"
                                                                                    data-repeater-create>
                                                                                    <i data-feather="plus"></i>
                                                                                    <span> Add
                                                                                        New Value</span>
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
                                                                            Delete Spec
                                                                        </button>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            @empty
                                                            <div data-repeater-item>
                                                                <div class="row mt-1">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-1">
                                                                            <label class="form-label label-text">Name
                                                                                (EN)</label>
                                                                            <input type="text" name="name_en"
                                                                                class="form-control"
                                                                                placeholder="Specification Name (EN)" />
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="mb-1">
                                                                            <label class="form-label label-text">Name
                                                                                (AR)</label>
                                                                            <input type="text" name="name_ar"
                                                                                class="form-control"
                                                                                placeholder="Specification Name (AR)" />
                                                                        </div>
                                                                    </div>

                                                                    <!-- Inner Repeater empty structure -->
                                                                    <div class="inner-repeater">
                                                                        <div data-repeater-list="specification_options">
                                                                            <div data-repeater-item>
                                                                                <div
                                                                                    class="row d-flex align-items-end mt-2">
                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Value
                                                                                            (EN)</label>
                                                                                        <input type="text"
                                                                                            name="value_en"
                                                                                            class="form-control"
                                                                                            placeholder="Option (EN)" />
                                                                                    </div>

                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Value
                                                                                            (AR)</label>
                                                                                        <input type="text"
                                                                                            name="value_ar"
                                                                                            class="form-control"
                                                                                            placeholder="Option (AR)" />
                                                                                    </div>

                                                                                    <div class="col">
                                                                                        <label
                                                                                            class="form-label label-text">Price
                                                                                            (EGP) (Optional)</label>
                                                                                        <input type="text" name="price"
                                                                                            class="form-control"
                                                                                            placeholder="Price" />
                                                                                    </div>
                                                                                </div>

                                                                                <div
                                                                                    class="row d-flex align-items-end mt-2">
                                                                                    <div class="col-md-12">
                                                                                        <label
                                                                                            class="form-label label-text">Option
                                                                                            Image</label>
                                                                                        <input type="file" name="image"
                                                                                            class="form-control d-none option-image-input"
                                                                                            accept="image/*">
                                                                                        <div
                                                                                            class="upload-card option-upload-area">
                                                                                            <div
                                                                                                class="d-flex justify-content-center align-items-center gap-1">
                                                                                                <i data-feather="upload"
                                                                                                    class="mb-2"></i>
                                                                                                <p>Drag image here
                                                                                                    to upload
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div
                                                                                            class="progress mt-2 d-none w-50 option-upload-progress">
                                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                                                                style="width: 0%"></div>
                                                                                        </div>
                                                                                        <div
                                                                                            class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2 option-uploaded-image">
                                                                                            <img src="" alt="Uploaded"
                                                                                                class="img-fluid rounded option-image-preview"
                                                                                                style="width: 50px; height: 50px; object-fit: cover;">
                                                                                            <div class="file-details">
                                                                                                <div
                                                                                                    class="file-name fw-bold option-file-name">
                                                                                                </div>
                                                                                                <div
                                                                                                    class="file-size text-muted small option-file-size">
                                                                                                    0 KB
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div
                                                                                            class="col-12 text-end mt-1 mb-2">
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
                                                                                    <i data-feather="plus"></i>
                                                                                    <span>Add
                                                                                        New Value</span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>

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
                                                            @endforelse
                                                        </div>

                                                        <!-- Add New Specification Button -->
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <button type="button"
                                                                    class="w-100 rounded-3 p-1 text-dark"
                                                                    style="border: 2px dashed #CED5D4; background-color: #EBEFEF"
                                                                    data-repeater-create>
                                                                    <i data-feather="plus" class="me-25"></i> <span>Add
                                                                        New
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
                                                {{-- <label class="form-check-label" for="free-shipping">Category
                                                    available
                                                    for free shipping</label>--}}
                                                {{-- </div>--}}
                                            {{-- </div>--}}
                                        {{--
                                    </div>--}}

                                    <!-- Submit -->
                                    <div class="col-12 d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-secondary prev-tab">Back</button>
                                        <button type="submit" class="btn btn-primary me-1 saveChangesButton"
                                            id="SaveChangesButton">
                                            <span class="btn-text">Edit Category</span>
                                            <span id="saveLoader"
                                                class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                                                aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!--third tab content end -->
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>
    @include("modals.products.add-size")

</section>
@endsection

@section('vendor-script')
<script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
<script>
    Dropzone.autoDiscover = false;

        const multiDropzone = new Dropzone("#multi-dropzone", {
            url: "{{ route('media.store') }}",   // backend route for image upload
            paramName: "file",
            maxFiles: 10,
            maxFilesize: 1, // MB
            acceptedFiles: "image/*",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            addRemoveLinks: true,
            dictDefaultMessage: "Drag images here or click to upload",
            init: function () {
                let dz = this;

                // ✅ Show existing images if editing
                    @if($model->getMedia('product_extra_images')->isNotEmpty())
                    @foreach($model->getMedia('product_extra_images') as $media)
                {
                    const multiMockFile = {
                        name: "{{ $media->file_name }}",
                        size: {{ $media->size ?? 12345 }},
                        _hiddenInputId: "{{ $media->id }}"
                    };

                    dz.emit("addedfile", multiMockFile);
                    dz.emit("thumbnail", multiMockFile, "{{ $media->getUrl() }}");
                    dz.emit("success", multiMockFile);
                    dz.emit("complete", multiMockFile);
                    dz.files.push(multiMockFile);

                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "images_ids[]";
                    input.value = "{{ $media->id }}";
                    input.id = "hidden-image-{{ $media->id }}";
                    document.querySelector("#multi-uploaded-images").appendChild(input);
                }
                @endforeach
                @endif


                // ✅ On upload success
                dz.on("success", function (file, response) {
                    if (response.success && response.data) {
                        file._hiddenInputId = response.data.id;

                        let hiddenInput = document.createElement("input");
                        hiddenInput.type = "hidden";
                        hiddenInput.name = "images_ids[]";
                        hiddenInput.value = response.data.id;
                        hiddenInput.id = "hidden-image-" + response.data.id;
                        document.querySelector("#multi-uploaded-images").appendChild(hiddenInput);
                    }
                });

                // ✅ On remove
                dz.on("removedfile", function (file) {
                    if (file._hiddenInputId) {
                        let hiddenInput = document.getElementById("hidden-image-" + file._hiddenInputId);
                        if (hiddenInput) hiddenInput.remove();

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

        const categoryDropzone = new Dropzone("#product-main-dropzone", {
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
                let dz = this;

                // ✅ Show existing image if editing
                @if(!empty($media = $model->getFirstMedia('product_main_image')))
                // In categoryDropzone (single image)
                let mainMockFile = {
                    name: "{{ $media->file_name }}",
                    size: {{ $media->size ?? 12345 }},
                    _hiddenInputId: "{{ $media->id }}"
                };
                dz.emit("addedfile", mainMockFile);
                dz.emit("thumbnail", mainMockFile, "{{ $media->getUrl() }}");
                dz.emit("complete", mainMockFile);
                dz.files.push(mainMockFile);

                // also set hidden input
                document.getElementById("uploadedImage").value = "{{ $media->id }}";
                document.getElementById("uploaded-image").classList.remove("d-none");
                @endif

                // ✅ On success
                dz.on("success", function (file, response) {
                    if (response.success && response.data) {
                        file._hiddenInputId = response.data.id;
                        document.getElementById("uploadedImage").value = response.data.id;
                    }
                });

                // ✅ On remove
                dz.on("removedfile", function (file) {
                    console.log("Fs")

                    document.getElementById("uploadedImage").value = "";
                    if (file._hiddenInputId) {
                        fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                    }
                    document.getElementById("uploaded-image").classList.add("d-none");
                });
            }
        });

        // ✅ Manual remove
        document.getElementById("remove-image").addEventListener("click", function () {
            categoryDropzone.removeAllFiles(true);
            document.getElementById("uploadedImage").value = "";
            document.getElementById("uploaded-image").classList.add("d-none");
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
    $(document).ready(function () {
            let input = $('#product-images');
            let uploadArea = $('#multi-upload-area');
            let uploadedImages = $('#multi-uploaded-images');

            // Click to open file input
            uploadArea.on('click', function () {
                input.click();
            });

            // Handle input change
            input.on('change', function (e) {
                handleFiles(e.target.files);
            });

            // Drag and Drop
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
                let interval = setInterval(function () {
                    progress += 10;
                    progressBar.css('width', progress + '%');

                    if (progress >= 100) {
                        clearInterval(interval);

                        // Display the image after "upload" finishes
                        let reader = new FileReader();
                        reader.onload = function (e) {
                            imgTag.attr('src', e.target.result).fadeIn();
                            wrapper.find('.progress').remove();
                        }
                        reader.readAsDataURL(file);
                    }
                }, 100);

                // Remove button
                wrapper.find('.remove-btn').on('click', function () {
                    wrapper.remove();
                });

                // Re-render feather icons
                feather.replace();
            }
        });
</script>
<script>
    $(document).ready(function () {
            let optionInput = $('#option-image-input');
            let optionUploadArea = $('#option-upload-area');
            let optionProgress = $('#option-upload-progress');
            let optionProgressBar = $('#option-upload-progress .progress-bar');
            let optionUploadedImage = $('#option-uploaded-image');
            let optionRemoveButton = $('#option-remove-image');

            // Click to open file input
            optionUploadArea.on('click', function () {
                optionInput.click();
            });

            // Handle input change
            optionInput.on('change', function (e) {
                handleOptionFiles(e.target.files);
            });

            // Drag and Drop
            optionUploadArea.on('dragover', function (e) {
                e.preventDefault();
                optionUploadArea.addClass('dragover');
            });

            optionUploadArea.on('dragleave', function (e) {
                e.preventDefault();
                optionUploadArea.removeClass('dragover');
            });

            optionUploadArea.on('drop', function (e) {
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
                    let interval = setInterval(function () {
                        fakeProgress += 10;
                        optionProgressBar.css('width', fakeProgress + '%');

                        if (fakeProgress >= 100) {
                            clearInterval(interval);

                            let reader = new FileReader();
                            reader.onload = function (e) {
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
            optionRemoveButton.on('click', function () {
                optionUploadedImage.addClass('d-none');
                optionInput.val('');

                // Also clear file name and size
                $('#option-uploaded-image .file-name').text('');
                $('#option-uploaded-image .file-size').text('');
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

                // Show/hide the respective sections
                $('#custom-price-section')
                    .toggle(isCustom)
                    .find('input, select, textarea, button') // disable all form fields in repeater
                    .prop('disabled', !isCustom);

                $('#default-price-section')
                    .toggle(!isCustom)
                    .find('input, select, textarea, button')
                    .prop('disabled', isCustom)
                    .val('');

                // Reset inputs when toggling
                if (!isCustom) {
                    $('#custom-price-section').find('input').val('');
                } else {
                    $('#default-price-section').find('input').val('');
                }

                // OPTIONAL: Completely clear repeater rows if needed
                if (!isCustom) {
                    $('#custom-price-section .repeater-item').remove(); // adjust selector as needed
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

            $(document).ready(function () {
                initializeImageUploaders($('.outer-repeater')); // This ensures the first one is bound properly
            });
            const $specList = $('.outer-repeater').find('[data-repeater-list="specifications"]');

            $('.outer-repeater').repeater({
                repeaters: [{
                    selector: '.inner-repeater',
                    show: function () {
                        $(this).slideDown();
                        updateDeleteButtons($(this).closest('.outer-repeater'));
                        initializeImageUploaders(this);
                        // Reset image preview in newly added inner repeater items
                        $(this).find('.option-uploaded-image').addClass('d-none');
                        $(this).find('.option-image-preview').attr('src', '');
                        $(this).find('.option-file-name').text('');
                        $(this).find('.option-file-size').text('');
                        $(this).find('.option-upload-progress .progress-bar').css('width', '0%');
                        $(this).find('.option-upload-progress').addClass('d-none');

                        // Optionally clear the hidden input too
                        $(this).find('.option-image-input').val('');
                        feather.replace();
                    },
                    hide: function (deleteElement) {
                        $(this).slideUp(deleteElement);
                        updateDeleteButtons($(this).closest('.outer-repeater'));
                    },
                    nestedInputName: 'specification_options'
                }],
                show: function () {
                    if ($specList.hasClass('d-none')) {
                        $specList.removeClass('d-none');
                    }
                    $(this).slideDown();
                    updateDeleteButtons($('.outer-repeater'));
                    initializeImageUploaders(this);
                    // Reset image preview in newly added inner repeater items
                    $(this).find('.option-uploaded-image').addClass('d-none');
                    $(this).find('.option-image-preview').attr('src', '');
                    $(this).find('.option-file-name').text('');
                    $(this).find('.option-file-size').text('');
                    $(this).find('.option-upload-progress .progress-bar').css('width', '0%');
                    $(this).find('.option-upload-progress').addClass('d-none');

                    // Optionally clear the hidden input too
                    $(this).find('.option-image-input').val('');
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


            $('.select2').select2();

            // Category -> Subcategory
            $('.category-select').on('change', function () {
                const categoryId = $(this).val();
                const $subCategorySelect = $('.sub-category-select');
                $.ajax({
                    url: `${$subCategorySelect.data('sub-category-url')}?filter[parent_id]=${categoryId}`,
                    method: "GET",
                    success: function (res) {
                        $subCategorySelect.empty().append('<option value="">Select subproduct</option>');
                        $.each(res.data, (i, s) => $subCategorySelect.append(`<option value="${s.id}">${s.name}</option>`));
                    },
                    error: function () {
                        $subCategorySelect.empty().append('<option value="">Error loading Subcategories</option>');
                    }
                });
            });

            // Form submit
            $('#product-form').on('submit', function (e) {
                e.preventDefault();
                const isCustom = $('input[name="has_custom_prices"]:checked').val() === '1';

                if (!isCustom) {
                    // Disable all cu   stom price inputs so they're not submitted
                    $('[name^="prices["]').prop('disabled', true);
                }
                const saveButton = $('.saveChangesButton');
                const saveLoader = $('.saveLoader');
                const saveButtonText = $('.saveChangesButton .btn-text');
                saveButton.prop('disabled', true);
                saveLoader.removeClass('d-none');
                saveButtonText.addClass('d-none');
                const formData = new FormData(this);
                const customDimensions = sessionStorage.getItem('custom_dimensions');
                if (customDimensions) {
                    const parsedDimensions = JSON.parse(customDimensions);
                    parsedDimensions.forEach((dim, index) => {
                        formData.append(`custom_dimensions[${index}][width]`, dim.width);
                        formData.append(`custom_dimensions[${index}][height]`, dim.height);
                        formData.append(`custom_dimensions[${index}][unit]`, dim.unit);
                        formData.append(`custom_dimensions[${index}][name]`, dim.name);
                        formData.append(`custom_dimensions[${index}][is_custom]`, dim.is_custom);
                    });
                }
                $.ajax({
                    url: this.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            sessionStorage.setItem('product_updated', 'true');
                            window.location.href = '/products';
                        }
                    },
                    error: function (xhr) {
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
                        saveButton.prop('disabled', false);
                        saveLoader.addClass('d-none');
                        saveButtonText.removeClass('d-none');
                    }
                });
            });
            $(document).on('click', '.remove-old-image', function () {
                let btn = $(this);
                let imageId = btn.data('image-id');

                // Optionally, add a hidden input to track removed images
                $('<input>').attr({
                    type: 'hidden',
                    name: 'deleted_old_images[]',
                    value: imageId
                }).appendTo('form');

                // remove preview
                btn.closest('.uploaded-image').remove();
            });

            //Remove Extra Image
            {{--$('.remove-old-image').on('click', function(e) {--}}
            {{--    e.preventDefault();--}}
            {{--    var button = $(this);--}}
            {{--    var imageId = button.data('image-id');--}}
            {{--    var imageElement = button.closest('.uploaded-image');--}}
            {{--    $.ajax({--}}
            {{--        url: '{{ url("api/media") }}/' + imageId,--}}
            {{--        method: "DELETE",--}}
            {{--        success: function(response) {--}}
            {{--            imageElement.remove();--}}
            {{--            Toastify({--}}
            {{--                text: "Image Removed Successfully",--}}
            {{--                duration: 4000,--}}
            {{--                gravity: "top",--}}
            {{--                position: "right",--}}
            {{--                backgroundColor: "#28a745",--}}
            {{--                close: true--}}
            {{--            }).showToast();--}}
            {{--        },--}}
            {{--        error: function(xhr) {--}}
            {{--            console.log(xhr.responseJson.errors)--}}
            {{--        }--}}
            {{--    })--}}

            {{--});--}}
        });
</script>
<script>
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
