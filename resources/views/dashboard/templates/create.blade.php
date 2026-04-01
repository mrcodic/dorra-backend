@extends('layouts/contentLayoutMaster')

@section('title', 'Templates')
@section('main-page', 'Templates')
@section('sub-page', 'Add New Template')
@section('main-page-url', route("product-templates.index"))
@section('sub-page-url', route("product-templates.create"))
@section('vendor-style')
    <!-- Vendor CSS Files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection
@php
$category = \App\Models\Category::find(request('category_id'));
$HasMockupCategory = \App\Models\Category::find(request('category_id'));
@endphp

@section('content')
    <section id="multiple-column-form ">
        <div class="row ">
            <div class="col-12 ">
                <div class="card">
                    <div class="card-body ">
                        <form id="addTemplateForm" enctype="multipart/form-data" method="post"
                              action="{{ route('templates.redirect.store') }}">
                            @csrf
                            <input type="hidden" name="approach"
                                   value="{{ request()->query('q') == 'without' ? 'without_editor' : 'with_editor' }}">
                            <div class="flex-grow-1">
                                <div class="">
                                    <input type="hidden" name="use_front_as_back" id="useFrontAsBack" value="0">

                                    {{-- @if(request()->query('q') == 'without')--}}
                                    {{--
                                    <!-- Template Colors -->--}}
                                    {{-- <div class="col-md-12">--}}
                                    {{-- <div class="mb-2">--}}
                                    {{-- <label class="form-label label-text">Template Colors</label>--}}

                                    {{-- <div class="color-repeater">--}}
                                    {{-- <div data-repeater-list="colors" class="row d-flex flex-wrap">--}}
                                    {{-- <div data-repeater-item class="col-12 col-md-6 col-lg-3">--}}
                                    {{-- <div--}} {{--
                                                        class="border rounded-3 p-1 d-flex flex-column align-items-start mt-1">
                                                        --}}

                                    {{-- <div class="col-12">--}}
                                    {{-- <label class="form-label label-text">Color Value
                                        <span--}} {{-- style="color: red; font-size: 20px;">
                                                                    *</span>
                                                            </label>--}}
                                    {{-- <div class="d-flex gap-1 align-items-center">--}}
                                    {{--
                                    <!-- Color picker -->--}}
                                    {{-- <input type="color" --}} {{--
                                                                    class="form-control rounded-circle color-picker border-0"
                                                                    --}} {{-- style="max-width: 30px; padding: 0;"
                                                                    value="#000" />--}}

                                    {{--
                                    <!-- Text hex input (this will actually submit the value) -->--}}
                                    {{-- <input type="text" name="value" --}} {{--
                                                                    class="form-control color-hex-input" --}} {{--
                                                                    placeholder="#000000" value="#000000" --}} {{--
                                                                    pattern="^#([A-Fa-f0-9]{6})$" />--}}
                                    {{--
                                </div>--}}
                                    {{-- <small class="text-muted">Pick a color or type hex
                                        (e.g.--}}
                                    {{-- #FFAA00).</small>--}}
                                    {{-- </div>--}}

                                    {{-- <div class="col-12 mt-1">--}}
                                    {{-- <label class="form-label label-text">Color Image
                                        <span--}} {{-- style="color: red; font-size: 20px;">
                                                                    *</span>
                                                            </label>--}}
                                    {{-- <div class="dropzone color-dropzone border rounded p-2"
                                        --}} {{-- style="cursor:pointer; min-height:100px;">--}}
                                    {{-- <div class="dz-message" data-dz-message>--}}
                                    {{-- <span>Drop image or click</span>--}}
                                    {{-- </div>--}}
                                    {{-- </div>--}}
                                    {{-- <input type="hidden" name="image_id" --}} {{--
                                                                class="color-image-hidden">--}}
                                    {{-- </div>--}}

                                    {{-- <div class="col-12 text-center mt-1 ms-auto">--}}
                                    {{-- <button type="button" class="btn btn-outline-danger"
                                        --}} {{-- data-repeater-delete>--}}
                                    {{-- <i data-feather="x" class="me-25"></i>--}}
                                    {{-- Delete--}}
                                    {{-- </button>--}}
                                    {{-- </div>--}}
                                    {{-- </div>--}}
                                    {{-- </div>--}}
                                    {{-- </div>--}}

                                    {{-- <div class="row mt-1">--}}
                                    {{-- <div class="col-12">--}}
                                    {{-- <button type="button" class="w-100 rounded-3 p-1 text-dark" --}}
                                    {{-- style="border: 2px dashed #CED5D4; background-color: #EBEFEF"
                                    --}} {{-- data-repeater-create>--}}
                                    {{-- <i data-feather="plus" class="me-25"></i>--}}
                                    {{-- <span>Add New Color</span>--}}
                                    {{-- </button>--}}
                                    {{-- </div>--}}
                                    {{-- </div>--}}
                                    {{-- </div>--}}
                                    {{-- </div>--}}
                                    {{--
                                </div>--}}
                                    {{-- @endif--}}
                                    <div class="position-relative mt-3 text-center">
                                        <hr class="opacity-75" style="border: 1px solid #24B094;">
                                        <span
                                            class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                            style="color: #24B094">
                                    Template Details
                                </span>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="label-text mb-1">Language</label>
                                        <div class="row">
                                            @foreach(config("app.locales") as $locale)
                                                <div class="col-md-4 mb-1">
                                                    <label class="radio-box">
                                                        <input class="form-check-input " type="checkbox"
                                                               name="supported_languages[]"
                                                               value="{{ $locale }}"
                                                               checked
                                                        >
                                                        <span>{{ $locale == 'en' ? 'English' : 'Arabic'}}</span>
                                                    </label>
                                                </div>
                                            @endforeach

                                        </div>
                                    <div class="form-group mb-2">
                                        <label class="label-text mb-1">Template Type</label>
                                        <div class="row">
                                            @foreach(\App\Models\Type::all(['id','value']) as $type)
                                                <div class="col-md-4 mb-1">
                                                    <label class="radio-box">
                                                        <input class="form-check-input type-checkbox" type="checkbox"
                                                               name="types[]"
                                                               value="{{ $type->value }}"
                                                               data-type-name="{{ strtolower($type->value->name) }}"
                                                            @checked(
   $HasMockupCategory?->has_mockup == false ? $type->value === \App\Enums\Template\TypeEnum::FRONT
           || $type->value === \App\Enums\Template\TypeEnum::BACK :$type->value === \App\Enums\Template\TypeEnum::FRONT
       )
                                                        >
                                                        <span>{{ $type->value->label() }}</span>
                                                    </label>
                                                </div>
                                            @endforeach

                                        </div>

                                    </div>
                                    <div class="row">

                                        <div class="row" id="templateTypeDropzones">
                                            @if(request()->query('q') == 'without')
                                                <!-- FRONT -->
                                                <div class="form-group mb-2 col-md-6 d-none" id="dz-front">
                                                    <label class="label-text mb-1">Upload Print File (Front)</label>
                                                    <div id="front-template-dropzone"
                                                         class="dropzone border rounded p-3"
                                                         style="cursor:pointer; min-height:150px;">
                                                        <div class="dz-message">
                                                            <span>Drop front image here or click</span>
                                                        </div>
                                                        <input type="hidden" name="template_image_front_id"
                                                               id="uploadedFrontTemplateImage">
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Allowed formats: PNG, JPG, JPEG, WEBP.
                                                        Maximum file size: 30 MB.
                                                        Minimum dimensions: 1000 × 1000 px.
                                                    </small>
                                                </div>

                                                <!-- BACK -->
                                                <div class="form-group mb-2 col-md-6 d-none" id="dz-back">
                                                    <label class="label-text mb-1">Upload Print File (Back)</label>
                                                    <div id="back-template-dropzone" class="dropzone border rounded p-3"
                                                         style="cursor:pointer; min-height:150px;">
                                                        <div class="dz-message">
                                                            <span>Drop back image here or click</span>
                                                        </div>
                                                        <input type="hidden" name="template_image_back_id"
                                                               id="uploadedBackTemplateImage">
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Allowed formats: PNG, JPG, JPEG, WEBP.
                                                        Maximum file size: 30 MB.
                                                        Minimum dimensions: 1000 × 1000 px.
                                                    </small>
                                                </div>


                                                <!-- NONE -->
                                                <div class="form-group mb-2 col-md-6 d-none" id="dz-none">
                                                    <label class="label-text mb-1">Upload Print File (General)</label>
                                                    <div id="none-template-dropzone" class="dropzone border rounded p-3"
                                                         style="cursor:pointer; min-height:150px;">
                                                        <div class="dz-message">
                                                            <span>Drop general image here or click</span>
                                                        </div>
                                                        <input type="hidden" name="template_image_none_id"
                                                               id="uploadedNoneTemplateImage">
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Allowed formats: PNG, JPG, JPEG, WEBP.
                                                        Maximum file size: 30 MB.
                                                        Minimum dimensions: 1000 × 1000 px.
                                                    </small>

                                                </div>
                                            @endif

                                                @if($HasMockupCategory && !$HasMockupCategory->has_mockup)
                                            <!-- MODEL  -->
                                            <div class="form-group mb-2 col-md-6 d-none" id="dz-model">
                                                <label class="label-text mb-1">Template Model Image</label>
                                                <div id="template-dropzone" class="dropzone border rounded p-3"
                                                     style="cursor:pointer; min-height:150px;">
                                                    <div class="dz-message" data-dz-message>
                                                        <span>Drop image here or click to upload</span>
                                                    </div>
                                                    <input type="hidden" name="template_image_id"
                                                           id="uploadedTemplateImage">
                                                </div>
                                                <small class="form-text text-muted">
                                                    If no size is selected, the default 618×700 will be applied.
                                                </small>
                                            </div>
                                                @endif

                                        </div>


                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <label for="templateName" class="label-text mb-1">Name (EN)</label>
                                            <input type="text" id="templateName" class="form-control" name="name[en]"
                                                   placeholder="Template Name in English"
                                                   value="{{ $category?->getTranslation('name','en') ?? 'Personal Card'}}">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="templateName" class="label-text mb-1">Name (AR)</label>
                                            <input type="text" id="templateName" class="form-control" name="name[ar]"
                                                   placeholder="Template Name in Arabic"
                                                   value="{{ $category?->getTranslation('name','ar') ?? 'كارت شخصى'}}">
                                        </div>


                                    </div>


                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <label for="templateDescription" class="label-text mb-1">Description
                                                (AR)</label>
                                            <textarea id="templateDescription" class="form-control" rows="3"
                                                      name="description[ar]"
                                                      placeholder="Template Description in Arabic"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="templateDescription" class="label-text mb-1">Description
                                                (EN)</label>
                                            <textarea id="templateDescription" class="form-control" rows="3"
                                                      name="description[en]"
                                                      placeholder="Template Description in English"></textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-12">
                                            <label for="templatePrice" class="label-text mb-1">
                                                Price</label>
                                            <input id="templatePrice" class="form-control" type="number"
                                                   name="price" placeholder="Template Price"
                                                   step="0.01"
                                                   min="0"/>
                                        </div>

                                        </div>

                                    <div class="position-relative mt-3 text-center">
                                        <hr class="opacity-75" style="border: 1px solid #24B094;">
                                        <span
                                            class="d-none d-md-flex position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4"
                                            style="color: #24B094;">
                                    Products & Categories
                                </span>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-md-6 form-group mb-2">
                                            <label for="categoriesSelect" class="label-text mb-1">Products With
                                                Categories</label>
                                            <select id="categoriesSelect" class="form-select select2"
                                                    name="product_with_category" multiple>
                                                @foreach($associatedData['product_with_categories'] as $cate)
                                                    <option value="{{ $cate->id }}"
                                                        @selected($cate->id === ($category?->id ?? 1))
                                                    >
                                                        {{ $cate->getTranslation('name', app()->getLocale()) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group mb-2">
                                            <label for="productsSelect" class="label-text mb-1">Categories</label>
                                            <select id="productsSelect" class="form-select select2" name="product_ids[]"
                                                    multiple>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-md-12 form-group mb-2">
                                            <label for="productsWithoutCategoriesSelect" class="label-text mb-1">Products
                                                Without Categories</label>
                                            <select id="productsWithoutCategoriesSelect" class="form-select select2"
                                                    name="category_ids[]" multiple>
                                                @foreach($associatedData['product_without_categories'] as $cate)
                                                    <option value="{{ $cate->id }}"
                                                            @selected($cate->id === ($category?->id ?? 1))

                                                            data-has-mockup="{{ $cate->has_mockup ? '1' : '0' }}">
                                                        {{ $cate->getTranslation('name', app()->getLocale()) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>


                                    </div>
                                    <div class="col-md-12 form-group mb-2 mockupWrapper d-none">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div>
                                                <h5 class="mb-0" style="color:#24B094;">Mockups</h5>
                                                <small class="text-muted">Select a mockup to show this template on
                                                    it.</small>
                                            </div>
                                            <span class="badge bg-light text-dark border">Optional</span>
                                        </div>
                                        <!-- where cards will render -->
                                        <div id="mockupsCards" class="row g-2"></div>
                                        <input type="hidden" name="mockup_id" id="selectedMockupId" value="">

                                        <!-- hidden inputs to submit selected ids -->
                                        <div id="mockupsHiddenInputs"></div>
                                    </div>

                                    <div class="position-relative mt-3 text-center">
                                        <hr class="opacity-75" style="border: 1px solid #24B094;">
                                        <span
                                            class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                            style="color: #24B094;">
                                    Industry
                                </span>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-md-6 form-group mb-2">
                                            <label for="industriesSelect" class="label-text mb-1">Industries</label>
                                            <select id="industriesSelect" class="form-select select2"
                                                    name="industry_ids[]"
                                                    multiple>
                                                @foreach($associatedData['industries'] as $industry)
                                                    <option value="{{ $industry->id }}">
                                                        {{ $industry->getTranslation('name', 'en').
                        "({$industry->getTranslation('name', 'ar')})" }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group mb-2">
                                            <label for="subIndustriesSelect" class="label-text mb-1">Sub
                                                Industries</label>
                                            <select id="subIndustriesSelect" class="form-select select2"
                                                    name="industry_ids[]"
                                                    multiple>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group mb-2">
                                        <label for="tagsSelect" class="label-text mb-1">Tags</label>
                                        <select id="tagsSelect" class="form-select select2" name="tags[]" multiple>
                                            @foreach($associatedData['tags'] as $tag)
                                                <option value="{{ $tag->id }}">
                                                    {{ $tag->getTranslation('name', app()->getLocale()) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(request()->query('q') == 'with')

                                        <div class="position-relative mt-3 text-center">
                                            <hr class="opacity-75" style="border: 1px solid #24B094;">
                                            <span
                                                class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                                style="color: #24B094;">
                                    Design Specifications
                                </span>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="orientation" class="label-text mb-1">Orientation</label>
                                            <select id="orientation" class="form-select" name="orientation">
                                                <option value="" selected disabled>
                                                    chooese orientation
                                                </option>
                                                @foreach(\App\Enums\OrientationEnum::cases() as $orientation)

                                                    <option value="{{ $orientation->value }}" selected>
                                                        {{$orientation->label()}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="position-relative mt-3 text-center">
                                            <hr class="opacity-75" style="border: 1px solid #24B094;">
                                            <span
                                                class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                                style="color: #24B094;">
                                    Guides Settings
                                </span>
                                        </div>
                                        {{-- Persisted resources (used on submit) --}}
                                        <input type="hidden" name="dimension_resource_ids" id="dimensionResourceIds">
                                        <input type="hidden" name="dimension_resource_types"
                                               id="dimensionResourceTypes">
                                        <label class="label-text mb-1">Shape</label>
                                        <div class="row mb-2">
                                            {{-- Shape (col-6) --}}
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <input type="hidden" name="has_corner" id="has_corner_hidden"
                                                           value="{{ old('has_corner') }}">
                                                    <div class="d-flex gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="has_corner"
                                                                   id="shape_circle" value="0">
                                                            <label class="form-check-label"
                                                                   for="shape_circle">Circle</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="has_corner"
                                                                   id="shape_other" value="1" checked>
                                                            <label class="form-check-label"
                                                                   for="shape_other">Other</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-2 d-none" id="cornersBox">
                                                    <label for="cornersSelect" class="label-text mb-1">Corners</label>
                                                    <select id="cornersSelect" class="form-select select2"
                                                            name="border">
                                                        <option value="" selected disabled>Choose
                                                            Corner
                                                        </option>
                                                        @foreach(\App\Enums\CornerEnum::cases() as $border)
                                                            <option
                                                                value="{{ $border->value }}"
                                                            selected
                                                            >{{$border->label()}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Safety Area (col-6) --}}
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <div class="form-check mb-2">
                                                        {{-- send 0 when unchecked --}}
                                                        <input type="hidden" name="has_safety_area" value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="hasSafetyArea"
                                                               name="has_safety_area" value="1" checked>
                                                        <label class="form-check-label" for="hasSafetyArea">Enable
                                                            Safety
                                                            Area</label>
                                                    </div>

                                                    <div id="safetyAreaBox"
                                                         class="{{ old('has_safety_area') ? '' : 'd-none' }}">
                                                        <label for="safetyAreaSelect" class="label-text mb-1">Safety
                                                            Area</label>
                                                        <select id="safetyAreaSelect" class="form-select select2"
                                                                name="safety_area">
                                                            @foreach(\App\Enums\SafetyAreaEnum::cases() as $area)
                                                                <option value="{{ $area }}" @selected($area === \App\Enums\SafetyAreaEnum::R15)>
                                                                    {{ $area->label() }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">Padding inside the design
                                                            area.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Cut Margin (col-6) --}}
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <div class="form-check mb-2">
                                                        {{-- send 0 when unchecked --}}
                                                        <input type="hidden" value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="hasCutMargin" value="1"
                                                               checked>
                                                        <label class="form-check-label" for="hasCutMargin">Enable Cut
                                                            Margin</label>
                                                    </div>

                                                    <div id="cutMarginBox"
                                                         class="{{ old('cut_margin') ? '' : 'd-none' }}">
                                                        <label for="cutMarginSelect" class="label-text mb-1">Cut
                                                            Margin</label>
                                                        <select id="cutMarginSelect" class="form-select select2"
                                                                name="cut_margin">
                                                            @foreach(\App\Enums\SafetyAreaEnum::cases() as $area)
                                                                <option value="{{ $area->value }}"
                                                                    @selected($area === \App\Enums\SafetyAreaEnum::R10)>

                                                                    {{ $area->label() }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        {{-- <small class="form-text text-muted">Padding inside the design--}}
                                                        {{-- area.</small>--}}
                                                    </div>
                                                </div>
                                            </div>


                                        </div>

                                        <div class="form-group mb-2">
                                            <label for="sizesSelect" class="label-text mb-1">Sizes</label>
                                            <select id="sizesSelect" class="form-select" name="dimension_id">
                                                <option value="" selected disabled>Select Size</option>
                                            </select>
                                            <small class="form-text text-muted">
                                                If no size is selected, the default 650×650 will be applied.
                                            </small>
                                        </div>

                                </div>
                                @endif
                            </div>


                            <div class="d-flex flex-wrap-reverse gap-1 justify-content-between pt-2">
                                <button type="button" class="btn btn-outline-secondary" id="cancelButton">Cancel
                                </button>
                                <div class="d-flex gap-1">
                                    @if(request()->query('q') == 'without')
                                        <button type="submit" class="btn btn-outline-secondary fs-5 saveChangesButton"
                                                data-action="draft">
                                            <span>Add Template as Draft</span>
                                            <span id="saveLoader"
                                                  class="spinner-border spinner-border-sm d-none saveLoader"
                                                  role="status" aria-hidden="true"></span>
                                        </button>
                                    @endif
                                    @if(request()->query('q') == 'with')
                                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton"
                                                data-action="editor">
                                            <span>Save & Go to Editor</span>
                                            <span id="saveLoader"
                                                  class="spinner-border spinner-border-sm d-none saveLoader"
                                                  role="status" aria-hidden="true"></span>
                                        </button>
                                    @endif

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Design Modal -->
        <div class="modal fade" id="backDesignModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Back Side Design</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-4">Do you want to use the same front design for the back side, or upload a different one?</p>
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="button" class="btn btn-outline-primary" id="useSameDesignBtn">
                                <i data-feather="copy" class="me-1"></i> Use Same Design
                            </button>
                            <button type="button" class="btn btn-primary" id="useDifferentDesignBtn">
                                <i data-feather="upload" class="me-1"></i> Upload Different
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
    @php
        // لو route محتاج parameter اسمه mockup
        $mockupUpdateUrlTemplate = route('mockups.edit', ['mockup' => '__ID__']);
    @endphp

@endsection
@section('vendor-script')

    <script>
        $(function () {
            let backModalShown = false;

            $(document).on('change', '.type-checkbox', function () {
                const isBack = $(this).data('type-name') === 'back';
                const isFront = $(this).data('type-name') === 'front';
                const isChecked = $(this).is(':checked');

                const frontChecked = $('.type-checkbox[data-type-name="front"]').is(':checked');
                const backChecked  = $('.type-checkbox[data-type-name="back"]').is(':checked');

                // Show modal only if BOTH front and back are checked
                if ((isBack || isFront) && isChecked && frontChecked && backChecked && !backModalShown) {
                    backModalShown = true;
                    const modal = new bootstrap.Modal(document.getElementById('backDesignModal'));
                    modal.show();
                }

                // Reset when back is unchecked
                if (isBack && !isChecked) {
                    backModalShown = false;
                    document.getElementById('uploadedBackTemplateImage').value = '';
                    document.getElementById('useFrontAsBack').value = '0';
                    $('#dz-front .label-text').text('Upload Print File (Front)');
                }

                // Reset when front is unchecked
                if (isFront && !isChecked) {
                    backModalShown = false;
                    document.getElementById('useFrontAsBack').value = '0';
                    $('#dz-front .label-text').text('Upload Print File (Front)');
                }
            });

            $('#useSameDesignBtn').on('click', function () {
                document.getElementById('useFrontAsBack').value = '1';
                $('#dz-back').addClass('d-none');
                $('#dz-front .label-text').text('Upload Print File (Front, Back)');
                document.getElementById('uploadedBackTemplateImage').value = '';
                bootstrap.Modal.getInstance(document.getElementById('backDesignModal')).hide();
            });

            $('#useDifferentDesignBtn').on('click', function () {
                document.getElementById('useFrontAsBack').value = '0';
                $('#dz-front .label-text').text('Upload Print File (Front)');
                bootstrap.Modal.getInstance(document.getElementById('backDesignModal')).hide();
            });
        });
        $(document).ready(function () {
            $('#categoriesSelect').trigger('change');
        });
    </script>

    {{-- Replace the entire mockup JS block in your blade file with this --}}

    <script>
        $(function () {
            const q = "{{ request()->query('q') }}";
            const isWithoutEditor = (q === 'without' || q === 'without_editor');
            const $cardsWrap    = $('#mockupsCards');
            const $mockupWrap   = $('.mockupWrapper');
            const $hiddenWrap   = $('#mockupsHiddenInputs');

            // ── Single source of truth ──────────────────────────────────────────
            const selected = new Set();

            function syncHiddenInputs() {
                $hiddenWrap.empty();
                [...selected].forEach(id => {
                    $hiddenWrap.append(`<input type="hidden" name="mockup_ids[]" value="${id}">`);
                });
            }

            // ── Card rendering ──────────────────────────────────────────────────
            function renderMockupCards(items) {
                $mockupWrap.removeClass('d-none');
                $cardsWrap.empty();

                if (!items.length) {
                    $cardsWrap.append(`<div class="col-12 text-muted py-2">No mockups found</div>`);
                    syncHiddenInputs();
                    return;
                }
                const urlParams = new URLSearchParams(window.location.search);
                const isWithEditor = urlParams.get('q') === 'with';
                items.forEach(mockup => {
                    const id      = String(mockup.id);
                    const name    = mockup.name ?? ('Mockup #' + id);
                    const images  = mockup?.images || {};
                    const firstKey = Object.keys(images)[0];
                    const img     = (firstKey && images[firstKey]?.base_url)
                        || "{{ asset('images/placeholder.svg') }}";

                    const isSelected = selected.has(id);

                    $cardsWrap.append(`
              <div class="col-12 col-md-4 col-lg-2">
                <div class="mockup-card${isSelected ? ' selected' : ''}" data-id="${id}">
                  <div class="card rounded-3 shadow-sm" style="border:1px solid #24B094;">

                    <!-- checkbox — NO name attribute, purely visual -->
                    <div class="position-absolute" style="top:10px;left:10px;z-index:20;">
                      <input
                        class="form-check-input js-mockup-checkbox"
                        type="checkbox"
                        value="${id}"
                        ${isSelected ? 'checked' : ''}
                      />
                    </div>

                    <div class="d-flex justify-content-center align-items-center"
                         style="background-color:#F4F6F6;height:160px;border-radius:12px;padding:10px;">
                      <img src="${img}" class="mx-auto d-block"
                           style="height:auto;width:auto;max-width:100%;max-height:100%;border-radius:8px;"
                           alt="${name}">
                    </div>

                    <div class="card-body py-2">
                      <h6 class="card-title mb-2 text-truncate">${name}</h6>

                ${ !isWithEditor ?`<button type="button"
                              class="btn btn-sm btn-primary w-100 js-show-on-mockup"
                              data-id="${id}">
                        Show on Mockup
                      </button>` :''}
                    </div>
                  </div>
                </div>
              </div>
            `);
                });

                syncHiddenInputs();
            }

            // ── Toggle selection (card click OR checkbox click — same handler) ──
            function toggleMockup(id) {
                id = String(id);
                if (selected.has(id)) {
                    selected.delete(id);
                } else {
                    selected.add(id);
                }

                // Keep card border + checkbox in sync
                const $card = $(`.mockup-card[data-id="${id}"]`);
                $card.toggleClass('selected', selected.has(id));
                $card.find('.js-mockup-checkbox').prop('checked', selected.has(id));

                syncHiddenInputs();
            }

            // Card body click (excluding button clicks)
            $(document).on('click', '.mockup-card', function (e) {
                if ($(e.target).closest('button').length) return; // ignore button clicks
                toggleMockup($(this).data('id'));
            });

            // Checkbox click (stop propagation so card handler doesn't also fire)
            $(document).on('click', '.js-mockup-checkbox', function (e) {
                e.stopPropagation();
                toggleMockup($(this).val());
            });

            // ── Fetch mockups ───────────────────────────────────────────────────
            function fetchMockups() {
                const idsWithCat    = $('#categoriesSelect').val() || [];
                const idsWithoutCat = $('#productsWithoutCategoriesSelect').val() || [];
                const allProductIds = [...idsWithCat, ...idsWithoutCat];

                if (!allProductIds.length) {
                    $cardsWrap.empty();
                    $hiddenWrap.empty();
                    selected.clear();
                    checkAllSelectedHaveMockup();
                    return;
                }

                const selectedTypes = $('.type-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();

                $.ajax({
                    url: "{{ route('mockups.index') }}",
                    type: "GET",
                    traditional: false,
                    data: {
                        'product_ids[]': allProductIds,
                        'types[]': selectedTypes,
                    },
                    success(response) {
                        const items = response?.data?.data || response?.data || response || [];

                        // Drop any selected IDs that are no longer in the results
                        const validIds = new Set(items.map(x => String(x.id)));
                        [...selected].forEach(id => {
                            if (!validIds.has(id)) selected.delete(id);
                        });

                        renderMockupCards(items);
                        checkAllSelectedHaveMockup();
                    },
                    error(xhr) {
                        console.error("Error fetching mockups:", xhr.responseText);
                        $cardsWrap.empty().append(
                            `<div class="col-12 text-danger py-2">Failed to load mockups</div>`
                        );
                    }
                });
            }

        // ── Mockup wrapper visibility ────────────────────────────────────────
        window.checkAllSelectedHaveMockup = function () {
            const allSelected = [
                ...$('#productsSelect').find('option:selected'),
                ...$('#productsWithoutCategoriesSelect').find('option:selected')
            ];
            if (!allSelected.length) {
                $mockupWrap.addClass('d-none');
            } else {
                $mockupWrap.removeClass('d-none');
            }
        };

        // ── "Show on Mockup" button ─────────────────────────────────────────
        $(document).on('click', '.js-show-on-mockup', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const id = String($(this).data('id'));
            $('#selectedMockupId').val(id);
            $('#addTemplateForm').submit();
        });

        // ── Wire up external change triggers ────────────────────────────────
        $('#categoriesSelect').on('change', fetchMockups);
        $('#productsWithoutCategoriesSelect').on('change', fetchMockups);
        $(document).on('change', '.type-checkbox', fetchMockups);

        // Initial load
        fetchMockups();
        });
    </script>

    <script>
        function updateTemplateTypeDropzones() {
            const selectedTypes = Array.from(document.querySelectorAll('.type-checkbox'))
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            const dzFront = document.getElementById("dz-front");
            const dzBack = document.getElementById("dz-back");
            const dzNone = document.getElementById("dz-none");
            const dzModel = document.getElementById("dz-model");


            [dzFront, dzBack, dzNone, dzModel].forEach(dz => {
                if (dz) {
                    dz.classList.add("d-none");
                    dz.classList.remove("col-md-4", "col-md-6", "col-md-12");
                }
            });

            const visibleDZ = [];

            if (selectedTypes.includes("front") && dzFront) {
                dzFront.classList.remove("d-none");
                visibleDZ.push(dzFront);
            }
            if (selectedTypes.includes("back") && dzBack) {
                dzBack.classList.remove("d-none");
                visibleDZ.push(dzBack);
            }
            if (selectedTypes.includes("none") && dzNone) {
                dzNone.classList.remove("d-none");
                visibleDZ.push(dzNone);
            }


            if (visibleDZ.length > 0 && dzModel) {
                dzModel.classList.remove("d-none");
                visibleDZ.push(dzModel);
            }
            @if(request()->query('q') == 'with')
            dzModel.classList.remove("d-none");

            @endif


            visibleDZ.forEach(dz => {
                if (visibleDZ.length === 1) {
                    dz.classList.add("col-md-12");
                } else if (visibleDZ.length === 2) {
                    dz.classList.add("col-md-6");
                } else {
                    dz.classList.add("col-md-4");
                }
            });
        }


        // trigger on checkbox change
        document.querySelectorAll('.type-checkbox').forEach(cb => {
            cb.addEventListener("change", updateTemplateTypeDropzones);
        });

        // initial run
        updateTemplateTypeDropzones();

    </script>
    <script>
        // keep color picker & text field in sync
        document.addEventListener("input", (e) => {
            if (e.target.classList.contains("color-picker")) {
                const picker = e.target;
                const text = picker.closest(".d-flex").querySelector(".color-hex-input");
                text.value = picker.value.toUpperCase();
            }

            if (e.target.classList.contains("color-hex-input")) {
                const text = e.target;
                const picker = text.closest(".d-flex").querySelector(".color-picker");
                const val = text.value.trim();

                // Only update if valid hex
                if (/^#([A-Fa-f0-9]{6})$/.test(val)) {
                    picker.value = val;
                }
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const $colorRepeater = $('.color-repeater');

            // لو في items جاهزة (في حالة edit مثلاً)
            $colorRepeater.find('[data-repeater-item]').each(function () {
                initColorItem(this);
            });

            if (window.$ && $.fn.repeater) {
                $colorRepeater.repeater({
                    initEmpty: true, // يستخدم الـ item الموجود كـ template
                    show: function () {
                        $(this).addClass('col-12 col-md-6 col-lg-3').hide().slideDown(200, function () {
                            // this = data-repeater-item الجديد
                            initColorItem(this);
                            if (window.feather) feather.replace();
                        });
                    },
                    hide: function (deleteElement) {
                        $(this).slideUp(function () {
                            $this.remove()
                        });
                    }
                });

                // نضيف أول صف تلقائيًا في صفحة الإنشاء
                const hasItems = $colorRepeater.find('[data-repeater-item]').length > 0;
                if (!hasItems) {
                    $colorRepeater.find('[data-repeater-create]').first().trigger('click');
                }
            }
        });
    </script>
    <script>
        Dropzone.autoDiscover = false;

        function initColorItem(item) {
            const dropzoneElement = item.querySelector('.color-dropzone');
            const hiddenInput = item.querySelector('.color-image-hidden');

            if (!dropzoneElement || !hiddenInput) return;


            if (dropzoneElement.dropzone) return;

            const dz = new Dropzone(dropzoneElement, {
                url: "{{ route('media.store') }}",
                paramName: "file",
                maxFiles: 1,
                maxFilesize: 1, // MB
                acceptedFiles: "image/*",
                headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                addRemoveLinks: true,
                init: function () {
                    this.on("success", function (file, response) {
                        if (response.success && response.data) {
                            file._hiddenInputId = response.data.id;
                            hiddenInput.value = response.data.id;
                        }
                    });

                    this.on("removedfile", function (file) {
                        hiddenInput.value = "";
                        if (file._hiddenInputId) {
                            fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                                method: "DELETE",
                                headers: {
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                        }
                    });
                }
            });

            const colorPicker = item.querySelector('.color-picker');
            const hexInput = item.querySelector('.color-hex-input');

            if (colorPicker && hexInput) {
                colorPicker.addEventListener('input', function () {
                    const hex = this.value.toUpperCase();
                    hexInput.value = hex;
                });

                hexInput.addEventListener('input', function () {
                    let v = this.value.toUpperCase();
                    if (!v.startsWith('#')) v = '#' + v;
                    this.value = v;

                    if (/^#([0-9A-F]{6})$/.test(v)) {
                        colorPicker.value = v;
                    }
                });
            }
        }
    </script>

    <script !src="">
        $('#industriesSelect').on('change', function () {
            const selectedIds = $(this).val();
            if (selectedIds && selectedIds.length > 0) {
                $.ajax({
                    url: "{{ route('sub-industries') }}",
                    type: "POST",
                    data: {_token: "{{ csrf_token() }}", industry_ids: selectedIds},
                    success(response) {
                        const $right = $('#subIndustriesSelect');
                        const saved = $right.val() || [];
                        (response.data || []).forEach(cat => {
                            if ($right.find(`option[value="${cat.id}"]`).length === 0) {
                                const text = `${cat.name_en} (${cat.name_ar})`;
                                $right.append(new Option(text, cat.id, false, false));
                            }
                        });
                        $right.val(saved).trigger('change');

                    },
                    error(xhr) {
                        console.error("Error fetching sub industries:", xhr.responseText);

                    }
                });
            } else {
                // Clear right select and sync
                $('#industriesSelect').empty().trigger('change');
            }
        });
    </script>
    <script>
        $(function () {
            const $circle = $('#shape_circle'); // value="0"
            const $other = $('#shape_other');  // value="1"
            const $hidden = $('#has_corner_hidden');

            function updateHidden() {
                if ($circle.is(':checked')) return $hidden.val('0');
                if ($other.is(':checked')) return $hidden.val('1');
                $hidden.val(''); // none selected
            }

            function syncState() {
                // If exactly one is checked, disable the other; otherwise enable both
                if ($circle.is(':checked') && !$other.is(':checked')) {
                    $other.prop('checked', false).prop('disabled', true);
                } else if ($other.is(':checked') && !$circle.is(':checked')) {
                    $circle.prop('checked', false).prop('disabled', true);
                } else {
                    // none or both -> allow user to choose; uncheck "both" case
                    if ($circle.is(':checked') && $other.is(':checked')) {
                        // If both became checked somehow, prefer the last clicked; we'll handle below
                    }
                    $circle.prop('disabled', false);
                    $other.prop('disabled', false);
                }
                updateHidden();
            }

            // When one is checked, uncheck the other then sync
            $circle.on('change', function () {
                if (this.checked) $other.prop('checked', false);
                syncState();
            });

            $other.on('change', function () {
                if (this.checked) $circle.prop('checked', false);
                syncState();
            });

            // Ensure consistent initial state based on server-rendered checks
            syncState();

            // Optional: just before submit, re-sync to be extra safe
            $('#addTemplateForm').on('submit', function () {
                syncState();
            });
        });
    </script>


    <script>
        $(function () {
            const $toggle = $('#hasSafetyArea');
            const $box = $('#safetyAreaBox');
            const $select = $('#safetyAreaSelect');

            function syncSafetyArea() {
                if ($toggle.is(':checked')) {
                    $box.removeClass('d-none');
                } else {
                    $box.addClass('d-none');
                    // Clear value when hidden (so backend gets null/empty)
                    $select.val(null).trigger('change');
                }
                // If sizes depend on safety area, refresh:
                // if (typeof refreshSizes === 'function') refreshSizes();
            }

            // init select2 if not already
            if ($select.length && !$select.data('select2')) {
                $select.select2({
                    placeholder: "Safety Area",
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });
            }

            $toggle.on('change', syncSafetyArea);
            syncSafetyArea(); // initial state
        });
    </script>
    <script>
        $(function () {
            const $toggle = $('#hasCutMargin');
            const $box = $('#cutMarginBox');
            const $select = $('#cutMarginSelect');

            function syncCutMargin() {
                if ($toggle.is(':checked')) {
                    $box.removeClass('d-none');
                } else {
                    $box.addClass('d-none');
                    // Clear value when hidden (so backend gets null/empty)
                    $select.val(null).trigger('change');
                }
                // If sizes depend on safety area, refresh:
                // if (typeof refreshSizes === 'function') refreshSizes();
            }

            // init select2 if not already
            if ($select.length && !$select.data('select2')) {
                $select.select2({
                    placeholder: "Cut Margin",
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });
            }

            $toggle.on('change', syncCutMargin);
            syncCutMargin(); // initial state
        });
    </script>

    <script>
        $(function () {
            const $cornersBox = $('#cornersBox');
            const $corners = $('#cornersSelect');
            const $radios = $('input[name="has_corner"]'); // 0 = circle, 1 = other

            function syncCornersVisibility() {
                const val = $radios.filter(':checked').val();
                if (val === '1') {
                    $cornersBox.removeClass('d-none');
                } else {
                    $cornersBox.addClass('d-none');
                    // clear selection when hidden
                    $corners.val(null).trigger('change');
                }
            }

            $radios.on('change', syncCornersVisibility);
            syncCornersVisibility(); // initial state on page load
        });
    </script>

    <script>
        // Build parallel arrays from current UI selections
        function buildDimensionPayloadFromUI() {
            // Right: CATEGORIES of "Products With Categories"
            const categoryIds = ($('#productsSelect').val() || []).map(String);

            // Bottom: PRODUCTS of "Products Without Categories"
            const productIds = ($('#productsWithoutCategoriesSelect').val() || []).map(String);

            const resource_ids = [];
            const resource_types = [];

            // ✅ categories → type=category
            categoryIds.forEach(id => {
                resource_ids.push(id);
                resource_types.push('product');
            });

            // ✅ products → type=product
            productIds.forEach(id => {
                resource_ids.push(id);
                resource_types.push('category');
            });

            return {resource_ids, resource_types};
        }

        // Save the arrays into the hidden inputs (as JSON)
        function syncSelectedResourcesToHiddenInputs() {
            const {resource_ids, resource_types} = buildDimensionPayloadFromUI();
            $('#dimensionResourceIds').val(JSON.stringify(resource_ids));
            $('#dimensionResourceTypes').val(JSON.stringify(resource_types));
        }
    </script>
    <script>
        // Safe numeric parser
        function asNum(x) {
            const n = Number(x);
            return Number.isFinite(n) ? n : null;
        }

        // Make "H * W" (optionally with unit)
        // Pretty number, trims float noise (e.g., 0.56)
        const nf = new Intl.NumberFormat(undefined, {maximumFractionDigits: 3});

        function dimensionLabelHWTop(item, {showUnit = true} = {}) {
            // Support both shapes: top-level or {attributes:{...}}
            const src = item.attributes ? item.attributes : item;

            const h = Number(src.height);
            const w = Number(src.width);
            const unitObj = src.unit; // { value, label } or string/null
            const unitLabel = unitObj && typeof unitObj === 'object' ? (unitObj.label || '') : (unitObj || '');

            if (Number.isFinite(h) && Number.isFinite(w)) {
                const core = `${nf.format(h)} * ${nf.format(w)}`;
                return showUnit && unitLabel ? `${core} ${unitLabel}` : core;
            }

            // Fallbacks
            return src.name || src.label || `#${item.id ?? ''}`.trim();
        }

    </script>

    <script>
        // Human-readable label for each dimension option
        function dimensionLabel(d) {
            const name = d.name ?? d.label ?? null;
            const width = d.width ?? d.w ?? null;
            const height = d.height ?? d.h ?? null;
            const unit = d.unit ?? d.u ?? null;

            if (name) return name;
            if (width && height && unit) return `${width}×${height} ${unit}`;
            if (width && height) return `${width}×${height}`;
            return `#${d.id}`;
        }

        // Build payload from hidden inputs (the same thing you'll submit)
        function buildDimensionPayloadFromHidden() {
            let ids = [];
            let types = [];
            // const has_corner = $('input[name="has_corner"]:checked').val() === '1' ? 1 : 0;
            try {
                ids = JSON.parse($('#dimensionResourceIds').val() || '[]');
            } catch {
            }
            try {
                types = JSON.parse($('#dimensionResourceTypes').val() || '[]');
            } catch {
            }


            // return { resource_ids: ids, resource_types: types,has_corner: has_corner };
            return {resource_ids: ids, resource_types: types};
        }
        function refreshSizes() {
            syncSelectedResourcesToHiddenInputs();
            const payload = buildDimensionPayloadFromHidden();

            const $sizes = $('#sizesSelect');

            if (!payload.resource_ids.length) {
                $sizes.empty().trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('dimensions.index') }}",
                method: "POST",
                data: payload,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success(res) {
                    // Remember current selection
                    const current = $sizes.val() || [];

                    $sizes.empty();

                    const items = res.data || res || [];
                    items.forEach((item, index) => {
                        const text = dimensionLabelHWTop(item, { showUnit: true });
                        const id = item.id;

                        // If user has no selection, auto-select the first option
                        const selected = current.length ? current.includes(String(id)) : index === 0;
                        $sizes.append(new Option(text, id, false, selected));
                    });

                    $sizes.trigger('change');
                },
                error(xhr) {
                    console.error('Failed to load dimensions:', xhr.responseText);
                    $sizes.empty().trigger('change');
                }
            });
        }
    </script>
    <script>
        // After “Products With Categories (left)” changes we fetch its categories (right), then sync + refresh
        $('#categoriesSelect').on('change', function () {
            const selectedIds = $(this).val();
            const $right = $('#productsSelect');
            if (selectedIds && selectedIds.length > 0) {
                $.ajax({
                    url: "{{ route('products.categories') }}",
                    type: "POST",
                    data: {_token: "{{ csrf_token() }}", category_ids: selectedIds},
                    success(response) {
                        const saved = $right.val() || [];

                        // Clear previous options if needed
                        $right.empty();

                        (response.data || []).forEach(cat => {
                            const opt = new Option(cat.name, cat.id, false, true);
                            $(opt).attr('data-has-mockup', cat.has_mockup ? '1' : '0');
                            $right.append(opt);
                        });

                        // If you want to preserve previously selected values
                        // $right.val(saved).trigger('change');

                        $right.trigger('change'); // refresh select2
                        syncSelectedResourcesToHiddenInputs();
                    },
                    error(xhr) {
                        console.error("Error fetching categories:", xhr.responseText);
                        syncSelectedResourcesToHiddenInputs();
                    }
                });
            } else {
                $right.empty().trigger('change');
                syncSelectedResourcesToHiddenInputs();
            }
        });
        // Right (categories) changed
        $('#productsSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            // optional immediate refresh:
            refreshSizes();
            window.checkAllSelectedHaveMockup();
        });

        // Products without categories changed
        $('#productsWithoutCategoriesSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            // optional immediate refresh:
            refreshSizes();
           window.checkAllSelectedHaveMockup();
        });

        // When user opens/clicks Sizes, fetch fresh sizes
        // Works for click/focus; pick one or both
        // $('#sizesSelect').on('mousedown focus', function () {
        //     refreshSizes();
        // });

        // Initial sync on page load
        $(document).ready(function () {
            syncSelectedResourcesToHiddenInputs();
        });
    </script>

    <script !src="">
        $(document).ready(function () {
            $('#cancelButton').on('click', function (e) {
                e.preventDefault();

                // Reset the form inputs to initial values
                $('#addTemplateForm')[0].reset();

                // Reset all select2 fields inside the form to their original values
                $('#addTemplateForm').find('.select2').each(function () {
                    var $select = $(this);
                    // Get the option with selected attribute from original HTML
                    var originalVal = $select.find('option[selected]').val() || '';
                    $select.val(originalVal).trigger('change');
                });
            });
            $(document).on('click', '#specsContainer .border', function (e) {
                if ($(e.target).is('input[type="checkbox"]')) {
                    return;
                }
                const checkbox = $(this).find('input[type="checkbox"]');
                checkbox.prop('checked', !checkbox.prop('checked'));
            });
            const preselectedProductId = $('#productsSelect').val();
            if (preselectedProductId) {
                $('#productsSelect').trigger('change');
            }
        });


        $('.saveChangesButton').on('click', function (e) {
            const $button = $(this);
            const action = $button.data('action');
            const $form = $('#addTemplateForm');

            // Set form action based on the button clicked
            if (action === 'draft') {
                $('#addTemplateForm').attr('action', "{{ route('templates.store') }}");
            } else if (action === 'editor') {
                $('#addTemplateForm').attr('action', "{{ route('templates.redirect.store') }}");
            }


            // Let `handleAjaxFormSubmit()` take care of the actual submission
        });

        handleAjaxFormSubmit("#addTemplateForm", {
            successMessage: "Template created successfully",
            onSuccess: function (response, $form) {

                // Re-enable buttons & hide all loaders
                $('.saveChangesButton')
                    .prop('disabled', false)
                    .find('.saveLoader')
                    .addClass('d-none');

                // ✅ 1) Redirect to mockup edit if returned
                const mockupUrl =
                    response?.data?.mockup_redirect_url ||
                    response?.mockup_redirect_url;

                if (mockupUrl) {
                    window.location.href = mockupUrl; // or window.open(mockupUrl, '_blank');
                    return;
                }

                // ✅ 2) Your existing editor redirect (if any)
                const editorUrl =
                    response?.data?.redirect_url ||
                    response?.redirect_url;

                if (editorUrl) {
                    window.open(editorUrl, '_blank');
                    return;
                }

                // ✅ 3) Default fallback
                setTimeout(function () {
                    const params = new URLSearchParams(window.location.search);
                    params.set('product_without_category_id', params.get('category_id'));
                    params.delete('category_id');
                    window.location.href = '/product-templates?' + params.toString();
                }, 1000);
            },
            onError: function () {
                $('.saveChangesButton')
                    .prop('disabled', false)
                    .find('.saveLoader')
                    .addClass('d-none');
            }
        });


    </script>

    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
    <script>
        Dropzone.autoDiscover = false;

        const templateDropzone = new Dropzone("#template-dropzone", {
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
                        document.getElementById("uploadedTemplateImage").value = response.data.id;
                    }
                });

                this.on("removedfile", function (file) {
                    document.getElementById("uploadedTemplateImage").value = "";
                    if (file._hiddenInputId) {
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

        const frontTemplateDropzone = new Dropzone("#front-template-dropzone", {
            url: "{{ route('media.store') }}",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 30,
            acceptedFiles: "image/png,image/jpeg,image/webp",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            addRemoveLinks: true,
            dictDefaultMessage: "Drop image here or click to upload",
            init: function () {

                this.on("addedfile", function (file) {
                    if (file._isMock) return;

                    // ── Max file size check (30MB) ──
                    if (file.size > 30 * 1024 * 1024) {
                        this.removeFile(file);
                        Toastify({
                            text: `File size must not exceed 30MB. Your file is ${(file.size / 1024 / 1024).toFixed(2)}MB.`,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true,
                        }).showToast();
                        return;
                    }

                    // ── Min dimensions check (1000×1000) ──
                    const reader = new FileReader();
                    const dzRef = this;

                    reader.onload = function (e) {
                        const img = new Image();
                        img.onload = function () {
                            if (img.width < 1000 || img.height < 1000) {
                                dzRef.removeFile(file);
                                Toastify({
                                    text: `Image must be at least 1000×1000px. Your image is ${img.width}×${img.height}px.`,
                                    duration: 3000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#EA5455",
                                    close: true,
                                }).showToast();
                            }
                        };
                        img.src = e.target.result;
                    };

                    reader.readAsDataURL(file);
                });

                this.on("success", function (file, response) {
                    if (response.success && response.data) {
                        file._hiddenInputId = response.data.id;
                        document.getElementById("uploadedFrontTemplateImage").value = response.data.id;
                    }
                });

                this.on("removedfile", function (file) {
                    document.getElementById("uploadedFrontTemplateImage").value = "";
                    if (file._hiddenInputId) {
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
        const backTemplateDropzone = new Dropzone("#back-template-dropzone", {
            url: "{{ route('media.store') }}",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 30, // MB
            acceptedFiles: "image/png,image/jpeg,image/webp",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            addRemoveLinks: true,
            dictDefaultMessage: "Drop image here or click to upload",
            init: function () {
                this.on("addedfile", function (file) {
                    if (file._isMock) return;

                    // ── Max file size check (30MB) ──
                    if (file.size > 30 * 1024 * 1024) {
                        this.removeFile(file);
                        Toastify({
                            text: `File size must not exceed 30MB. Your file is ${(file.size / 1024 / 1024).toFixed(2)}MB.`,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true,
                        }).showToast();
                        return;
                    }

                    // ── Min dimensions check (1000×1000) ──
                    const reader = new FileReader();
                    const dzRef = this;

                    reader.onload = function (e) {
                        const img = new Image();
                        img.onload = function () {
                            if (img.width < 1000 || img.height < 1000) {
                                dzRef.removeFile(file);
                                Toastify({
                                    text: `Image must be at least 1000×1000px. Your image is ${img.width}×${img.height}px.`,
                                    duration: 3000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#EA5455",
                                    close: true,
                                }).showToast();
                            }
                        };
                        img.src = e.target.result;
                    };

                    reader.readAsDataURL(file);
                });

                this.on("success", function (file, response) {
                    if (response.success && response.data) {
                        file._hiddenInputId = response.data.id;
                        document.getElementById("uploadedBackTemplateImage").value = response.data.id;
                    }
                });

                this.on("removedfile", function (file) {
                    document.getElementById("uploadedBackTemplateImage").value = "";
                    if (file._hiddenInputId) {
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
        const noneTemplateDropzone = new Dropzone("#none-template-dropzone", {
            url: "{{ route('media.store') }}",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 30, // MB
            acceptedFiles: "image/png,image/jpeg,image/webp",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            addRemoveLinks: true,
            dictDefaultMessage: "Drop image here or click to upload",
            init: function () {
                this.on("addedfile", function (file) {
                    if (file._isMock) return;

                    // ── Max file size check (30MB) ──
                    if (file.size > 30 * 1024 * 1024) {
                        this.removeFile(file);
                        Toastify({
                            text: `File size must not exceed 30MB. Your file is ${(file.size / 1024 / 1024).toFixed(2)}MB.`,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true,
                        }).showToast();
                        return;
                    }

                    // ── Min dimensions check (1000×1000) ──
                    const reader = new FileReader();
                    const dzRef = this;

                    reader.onload = function (e) {
                        const img = new Image();
                        img.onload = function () {
                            if (img.width < 1000 || img.height < 1000) {
                                dzRef.removeFile(file);
                                Toastify({
                                    text: `Image must be at least 1000×1000px. Your image is ${img.width}×${img.height}px.`,
                                    duration: 3000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#EA5455",
                                    close: true,
                                }).showToast();
                            }
                        };
                        img.src = e.target.result;
                    };

                    reader.readAsDataURL(file);
                });

                this.on("success", function (file, response) {
                    if (response.success && response.data) {
                        file._hiddenInputId = response.data.id;
                        document.getElementById("uploadedNoneTemplateImage").value = response.data.id;
                    }
                });

                this.on("removedfile", function (file) {
                    document.getElementById("uploadedNoneTemplateImage").value = "";
                    if (file._hiddenInputId) {
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
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.type-checkbox');

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

                    if (noneChecked && (type === 'front' || type === 'back')) {
                        checkbox.disabled = true;
                    } else if ((frontChecked || backChecked) && type === 'none') {
                        checkbox.disabled = true;
                    } else {
                        checkbox.disabled = false;
                    }
                });
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleCheckboxes);
            });

            // Initial state
            toggleCheckboxes();
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#industriesSelect').select2({
                placeholder: "Choose Industries",
                allowClear: true
            });
            $('#subIndustriesSelect').select2({
                placeholder: "Choose Sub Industries",
                allowClear: true
            });
            $('#productsSelect').select2({
                placeholder: "Choose Categories",
                allowClear: true
            });
            $('#productsWithoutCategoriesSelect').select2({
                placeholder: "Choose Products",
                allowClear: true
            });
            $('#categoriesSelect').select2({
                placeholder: "Choose Products",
                allowClear: true
            });
            $('#tagsSelect').select2({
                placeholder: "Choose Tags",
                allowClear: true
            });
            $('#colorsSelect').select2({
                placeholder: "Choose Colors",
                allowClear: true
            });

        });
    </script>
    <script !src="">
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
            repeaters: [{
                selector: '.inner-repeater',
                show: function () {
                    $(this).slideDown();
                    updateDeleteButtons($(this).closest('.outer-repeater'));
                    initializeImageUploaders(this);
                    feather.replace();
                },
                hide: function (deleteElement) {
                    $(this).slideUp(deleteElement);
                    updateDeleteButtons($(this).closest('.outer-repeater'));
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

        // Initialize on page load for already existing items
        $(document).ready(function () {
            updateDeleteButtons($('.outer-repeater'));
            initializeImageUploaders($('.outer-repeater'));
        });
    </script>

@endsection
