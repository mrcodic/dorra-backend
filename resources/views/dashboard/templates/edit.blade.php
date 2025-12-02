@extends('layouts/contentLayoutMaster')
@section('title', 'Edit Templates')
@section('main-page', 'Templates')
@section('sub-page', 'Edit Templates')
@section('main-page-url', route("product-templates.index"))
@section('sub-page-url', route('product-templates.edit',$model->id))
@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<section id="multiple-column-form ">
    <div class="row ">
        <div class="col-12 ">
            <div class="card">
                <div class="card-body ">
                    @php
                    $preselectedIndustryIds = $model->industries->whereNull('parent_id')->pluck('id')->values();
                    $preselectedSubIndustryIds = $model->industries->whereNotNull('parent_id')->pluck('id')->values();
                    @endphp

                    <form id="editTemplateForm" enctype="multipart/form-data" method="post"
                        action="{{ route('product-templates.update',$model->id) }}">
                        @csrf
                        @method("PUT")
                        <div class="flex-grow-1">
                            <div class="">
                                <div class="row">
                                    @if($model->approach == 'without_editor')
                                    <div class="form-group mb-2 col-md-6">
                                        <label class="label-text mb-1">Template Image</label>

                                        <!-- Dropzone container -->
                                        <div id="template-main-dropzone" class="dropzone border rounded p-3"
                                            style="cursor:pointer; min-height:150px;">
                                            <div class="dz-message" data-dz-message>
                                                <span>Drop image here or click to upload</span>
                                            </div>
                                        </div>

                                        <!-- Hidden input for uploaded file id -->
                                        <input type="hidden" name="template_image_main_id"
                                            id="uploadedMainTemplateImage">
                                    </div>
                                    @endif
                                    <div class="form-group mb-2 col-md-6">
                                        <label class="label-text mb-1">Template Model Image</label>

                                        <!-- Dropzone container -->
                                        <div id="template-dropzone" class="dropzone border rounded p-3"
                                            style="cursor:pointer; min-height:150px;">
                                            <div class="dz-message" data-dz-message>
                                                <span>Drop image here or click to upload</span>
                                            </div>
                                        </div>

                                        <!-- Hidden input for uploaded file id -->
                                        <input type="hidden" name="template_image_id" id="uploadedTemplateImage">
                                    </div>
                                </div>
                                @php
                                $colors = collect($model->colors) ?? collect();
                                $hasColors = $colors->isNotEmpty();
                                @endphp
{{--                                @if($model->approach == 'without_editor')--}}
{{--                                <div class="col-md-12">--}}
{{--                                    <div class="mb-2">--}}
{{--                                        <label class="form-label label-text">Template Colors</label>--}}



{{--                                        <div class="color-repeater">--}}
{{--                                            <div data-repeater-list="colors" class="row d-flex flex-wrap">--}}
{{--                                                @forelse($colors as $color)--}}

{{--                                                <div data-repeater-item class="col-12 col-md-6 col-lg-3">--}}
{{--                                                    <div--}}
{{--                                                        class="border rounded-3 p-1 d-flex flex-column align-items-start mt-1">--}}

{{--                                                        --}}{{-- Color value --}}
{{--                                                        <div class="col-12">--}}
{{--                                                            <label class="form-label label-text">Color Value--}}
{{--                                                                <span--}}
{{--                                                                    style="color: red; font-size: 20px;">*</span></label>--}}
{{--                                                            <div class="d-flex gap-1 align-items-center">--}}
{{--                                                                --}}{{-- Color picker --}}
{{--                                                                <input type="color"--}}
{{--                                                                    class="form-control rounded-circle color-picker border border-0"--}}
{{--                                                                    style="max-width: 30px; padding: 0;"--}}
{{--                                                                    value="{{ $color ?? '#000000' }}" />--}}

{{--                                                                --}}{{-- Text hex input (submitted) --}}
{{--                                                                <input type="text" name="value"--}}
{{--                                                                    class="form-control color-hex-input"--}}
{{--                                                                    placeholder="#000000"--}}
{{--                                                                    value="{{ $color ?? '#000000' }}"--}}
{{--                                                                    pattern="^#([A-Fa-f0-9]{6})$" />--}}
{{--                                                            </div>--}}
{{--                                                            <small class="text-muted">--}}
{{--                                                                Pick a color or type hex (e.g. #FFAA00).--}}
{{--                                                            </small>--}}
{{--                                                        </div>--}}

{{--                                                        --}}{{-- Color image --}}
{{--                                                        <div class="col-12 mt-1">--}}
{{--                                                            <label class="form-label label-text">Color Image--}}
{{--                                                                <span style="color: red; font-size: 20px;">*</span>--}}
{{--                                                            </label>--}}
{{--                                                            @php--}}
{{--                                                            $mediaWithColor = $model--}}
{{--                                                            ->getMedia('color_templates')--}}
{{--                                                            ->first(fn ($media) =>--}}
{{--                                                            $media->getCustomProperty('color_hex') ==$color);--}}
{{--                                                            @endphp--}}

{{--                                                            <div class="dropzone color-dropzone border rounded p-2"--}}
{{--                                                                style="cursor:pointer; min-height:100px;"--}}
{{--                                                                data-existing-media='@json($mediaWithColor)'>--}}
{{--                                                                <div class="dz-message" data-dz-message>--}}
{{--                                                                    <span>Drop image or click</span>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}

{{--                                                            <input type="hidden" name="image_id"--}}
{{--                                                                class="color-image-hidden"--}}
{{--                                                                value="{{$mediaWithColor->id ?? ''}}">--}}
{{--                                                        </div>--}}

{{--                                                        --}}{{-- Delete row --}}
{{--                                                        <div class="col-12 text-center mt-1 ms-auto">--}}
{{--                                                            <button type="button" class="btn btn-outline-danger"--}}
{{--                                                                data-repeater-delete>--}}
{{--                                                                <i data-feather="x" class="me-25"></i>--}}
{{--                                                                Delete--}}
{{--                                                            </button>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                @empty--}}
{{--                                                <div data-repeater-item class="col-12 col-md-6 col-lg-3">--}}
{{--                                                    <div--}}
{{--                                                        class="border rounded-3 p-1 d-flex flex-column align-items-start mt-1">--}}

{{--                                                        <div class="col-12">--}}
{{--                                                            <label class="form-label label-text">Color Value--}}
{{--                                                                *</label>--}}
{{--                                                            <div class="d-flex gap-1 align-items-center">--}}
{{--                                                                <input type="color"--}}
{{--                                                                    class="form-control rounded-circle color-picker border-0"--}}
{{--                                                                    style="max-width: 30px; padding: 0;"--}}
{{--                                                                    value="#000000" />--}}
{{--                                                                <input type="text" name="value"--}}
{{--                                                                    class="form-control color-hex-input"--}}
{{--                                                                    placeholder="#000000" value="#000000"--}}
{{--                                                                    pattern="^#([A-Fa-f0-9]{6})$" />--}}
{{--                                                            </div>--}}
{{--                                                            <small class="text-muted">Pick a color or type--}}
{{--                                                                hex (e.g. #FFAA00).</small>--}}
{{--                                                        </div>--}}

{{--                                                        <div class="col-12 mt-1">--}}
{{--                                                            <label class="form-label label-text">Color Image--}}
{{--                                                                *--}}
{{--                                                            </label>--}}
{{--                                                            <div class="dropzone color-dropzone border rounded p-2"--}}
{{--                                                                style="cursor:pointer; min-height:100px;"--}}
{{--                                                                data-existing-media='null'>--}}
{{--                                                                <div class="dz-message" data-dz-message>--}}
{{--                                                                    <span>Drop image or click</span>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
{{--                                                            <input type="hidden" name="image_id"--}}
{{--                                                                class="color-image-hidden">--}}
{{--                                                        </div>--}}

{{--                                                        <div class="col-12 text-center mt-1 ms-auto">--}}
{{--                                                            <button type="button" class="btn btn-outline-danger"--}}
{{--                                                                data-repeater-delete>--}}
{{--                                                                <i data-feather="x" class="me-25"></i>--}}
{{--                                                                Delete--}}
{{--                                                            </button>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                @endforelse--}}
{{--                                            </div>--}}

{{--                                            <div class="row mt-1">--}}
{{--                                                <div class="col-12">--}}
{{--                                                    <button type="button" class="w-100 rounded-3 p-1 text-dark"--}}
{{--                                                        style="border: 2px dashed #CED5D4; background-color: #EBEFEF"--}}
{{--                                                        data-repeater-create>--}}
{{--                                                        <i data-feather="plus" class="me-25"></i>--}}
{{--                                                        <span>Add New Color</span>--}}
{{--                                                    </button>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                @endif--}}
                                <div class="position-relative mt-3 text-center">
                                    <hr class="opacity-75" style="border: 1px solid #24B094;">
                                    <span
                                        class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                        style="color: #24B094">
                                        Template Details
                                    </span>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="label-text mb-1">Template Type</label>
                                    <div class="row">
                                        @foreach(\App\Models\Type::all(['id','value']) as $type)
                                        <div class="col-md-4 mb-1">
                                            <label class="radio-box">
                                                <input class="form-check-input type-checkbox" type="checkbox"
                                                    name="types[]" value="{{ $type->value }}"
                                                    data-type-name="{{ strtolower($type->value->name) }}"
                                                    @checked($model->types->contains($type->id))

                                                >
                                                <span>{{ $type->value->label() }}</span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="templateNameEn" class="label-text mb-1">Name (EN)</label>
                                    <input type="text" id="templateNameEn" class="form-control" name="name[en]"
                                           value="{{ $model->getTranslation('name','en') }}"
                                           placeholder="Template Name in English">
                                </div>

                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label for="templateNameAr" class="label-text mb-1">Name (AR)</label>
                                        <input type="text" id="templateNameAr" class="form-control" name="name[ar]"
                                            value="{{ $model->getTranslation('name','ar') }}"
                                            placeholder="Template Name in Arabic">
                                    </div>


                                </div>


                                {{-- <div class="form-group mb-2">--}}
                                    {{-- <label for="statusSelect" class="label-text mb-1">Status</label>--}}
                                    {{-- <select id="statusSelect" name="status" class="form-select select2">--}}
                                        {{-- <option value="" disabled selected>Choose status</option>--}}
                                        {{-- @foreach(\App\Enums\Template\StatusEnum::cases() as $status)--}}
                                        {{-- <option value="{{ $status->value }}" @selected($status==$model->
                                            status)>{{--}}
                                            {{-- $status->label() }}</option>--}}
                                        {{-- @endforeach--}}
                                        {{-- </select>--}}
                                    {{-- </div>--}}
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label for="templateDescription" class="label-text mb-1">Description
                                            (AR)</label>
                                        <textarea id="templateDescription" class="form-control" rows="3"
                                            name="description[ar]"
                                            placeholder="Template Description in Arabic">{{ $model->getTranslation('description','ar') }}</textarea>
                                    </div>
                                    <div class="col-md-6 ">
                                        <label for="templateDescription" class="label-text mb-1">Description
                                            (EN)</label>
                                        <textarea id="templateDescription" class="form-control" rows="3"
                                            name="description[en]"
                                            placeholder="Template Description in English">{{ $model->getTranslation('description','en') }}</textarea>
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
                                            @foreach($associatedData['product_with_categories'] as $category)
                                            <option value="{{ $category->id }}" @selected($category->
                                                load('products')->products->intersect($model->products)->isNotEmpty())>
                                                {{ $category->getTranslation('name', app()->getLocale()) }}
                                            </option>

                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group mb-2">
                                        <label for="productsSelect" class="label-text mb-1">Categories</label>
                                        <select id="productsSelect" class="form-select select2" name="product_ids[]"
                                            multiple>
                                            @foreach($associatedData['products'] as $product)
                                            <option value="{{ $product->id }}" @selected($model->
                                                products->contains($product))>
                                                {{ $product->getTranslation('name', app()->getLocale()) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="productsWithoutCategoriesSelect" class="label-text mb-1">Products
                                        Without Categories</label>
                                    <select id="productsWithoutCategoriesSelect" class="form-select select2"
                                        name="category_ids[]" multiple>
                                        @foreach($associatedData['product_without_categories'] as $category)
                                        <option value="{{ $category->id }}" @selected($model->
                                            categories->contains($category))>
                                            {{ $category->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                        @endforeach
                                    </select>
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
                                        <select id="industriesSelect" class="form-select select2" name="industry_ids[]"
                                            multiple>
                                            @foreach($associatedData['industries'] as $industry)
                                            <option value="{{ $industry->id }}" @selected($model->
                                                industries->contains($industry))>
                                                {{ $industry->getTranslation('name', app()->getLocale()) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group mb-2">
                                        <label for="subIndustriesSelect" class="label-text mb-1">Sub
                                            Industries</label>
                                        <select id="subIndustriesSelect" class="form-select select2"
                                            name="industry_ids[]" multiple>
                                            @foreach($associatedData['sub_industries'] as $industry)
                                            <option value="{{ $industry->id }}" @selected($model->
                                                industries->contains($industry))>
                                                {{ $industry->getTranslation('name', app()->getLocale()) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="form-group mb-2">
                                    <label for="tagsSelect" class="label-text mb-1">Tags</label>
                                    <select id="tagsSelect" class="form-select select2" name="tags[]" multiple>
                                        @foreach($associatedData['tags'] as $tag)
                                        <option value="{{ $tag->id }}" @selected($model->tags->contains($tag))>
                                            {{ $tag->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

{{--                                <div class="position-relative mt-3 text-center">--}}
{{--                                    <hr class="opacity-75" style="border: 1px solid #24B094;">--}}
{{--                                    <span--}}
{{--                                        class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"--}}
{{--                                        style="color: #24B094;">--}}
{{--                                        Design Specifications--}}
{{--                                    </span>--}}
{{--                                </div>--}}
                                {{-- Persisted resources (used on submit / ajax) --}}
                                <input type="hidden" name="dimension_resource_ids" id="dimensionResourceIds">
                                <input type="hidden" name="dimension_resource_types" id="dimensionResourceTypes">
{{--                                <div class="form-group mb-2">--}}
{{--                                    <label for="orientation" class="label-text mb-1">Orientation</label>--}}
{{--                                    <select id="orientation" class="form-select" name="orientation">--}}
{{--                                        <option value="" disabled>--}}
{{--                                            chooese orientation--}}
{{--                                        </option>--}}
{{--                                        @foreach(\App\Enums\OrientationEnum::cases() as $orientation)--}}
{{--                                        <option value="{{ $orientation->value }}" @selected($orientation==$model->--}}
{{--                                            orientation) >--}}
{{--                                            {{$orientation->label()}}--}}
{{--                                        </option>--}}
{{--                                        @endforeach--}}
{{--                                    </select>--}}
{{--                                </div>--}}
                                @if($model->approach == 'with_editor')

                                <div class="position-relative mt-3 text-center">
                                    <hr class="opacity-75" style="border: 1px solid #24B094;">
                                    <span
                                        class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                        style="color: #24B094;">
                                        Guides Settings
                                    </span>
                                </div>
                                <label class="label-text mb-1">Shape</label>
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <input type="hidden" name="has_corner" id="has_corner_hidden"
                                                value="{{ old('has_corner', $model->has_corner ?? '') }}">

                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="has_corner"
                                                        id="shape_circle" value="0" @checked($model->has_corner == 0)
                                                    >
                                                    <label class="form-check-label" for="shape_circle">Circle</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="has_corner"
                                                        id="shape_other" value="1" @checked($model->has_corner == 1)

                                                    >
                                                    <label class="form-check-label" for="shape_other">Other</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-2 d-none" id="cornersBox">
                                            <label for="cornersSelect" class="label-text mb-1">Corners</label>
                                            <select id="cornersSelect" class="form-select select2" name="border">
                                                <option value="" selected>Choose Corner</option>
                                                @foreach(\App\Enums\BorderEnum::cases() as $border)
                                                <option value="{{ $border->value }}" @selected($border->value ==
                                                    $model->border)
                                                    >
                                                    {{$border->label()}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <div class="form-check mb-2">

                                                <input type="hidden" name="has_safety_area" value="0">
                                                <input class="form-check-input" type="checkbox" id="hasSafetyArea"
                                                    name="has_safety_area" value="1" {{ $model->has_safety_area ?
                                                'checked' : '' }} >
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
                                                    <option value="{{ $area->value }}" @selected($area->value ==
                                                        $model->safety_area) >
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
                                                <input class="form-check-input" type="checkbox" id="hasCutMargin"
                                                    value="1" @checked((int) $model->cut_margin > 0) >
                                                <label class="form-check-label" for="hasCutMargin">Enable Cut
                                                    Margin</label>
                                            </div>

                                            <div id="cutMarginBox" class="{{ $model->cut_margin > 0 ? '' : 'd-none' }}">
                                                <label for="cutMarginSelect" class="label-text mb-1">Cut
                                                    Margin</label>
                                                <select id="cutMarginSelect" class="form-select select2"
                                                    name="cut_margin">

                                                    @foreach(\App\Enums\SafetyAreaEnum::cases() as $area)
                                                    <option value="{{ $area->value }}" @selected($area->value ==
                                                        $model->cut_margin) >
                                                        {{ $area->label() }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                {{-- <small class="form-text text-muted">Padding inside the design--}}
                                                    {{-- area.</small>--}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="sizesSelect" class="label-text mb-1">Sizes</label>
                                        <select id="sizesSelect" class="form-select" name="dimension_id">
                                            <option value="" disabled>Select Size</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            If no size is selected, the default 650×650 will be applied.
                                        </small>
                                    </div>
                                </div>


                            </div>
                            @endif
                        </div>


                        <div class="d-flex flex-wrap-reverse gap-1 justify-content-between pt-2">
                            <button type="reset" class="btn btn-outline-secondary" id="cancelButton">Cancel</button>
                            <div class="d-flex gap-1">
                                {{-- default: don't go to editor --}}
                                <input type="hidden" name="go_to_editor" value="0" id="goToEditorFlag">

                                @if($model->approach == 'with_editor')
                                <button type="submit" class="btn btn-outline-secondary fs-5 js-go-editor">
                                    <i data-feather="edit-3"></i> <span>Save & Edit Design</span>
                                </button>
                                @endif
                                <button type="submit" class="btn btn-primary fs-5 saveChangesButton"
                                    id="SaveChangesButton">
                                    <span>Save Changes</span>
                                    <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                        role="status" aria-hidden="true"></span>
                                </button>
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
                let val = text.value.trim().toUpperCase();

                if (!val.startsWith("#")) val = "#" + val;
                text.value = val;

                if (/^#([0-9A-F]{6})$/.test(val)) {
                    picker.value = val;
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
            if (dropzoneElement.dropzone) return; // prevent double init

            const existingMedia = dropzoneElement.dataset.existingMedia
                ? JSON.parse(dropzoneElement.dataset.existingMedia)
                : null;

            const dz = new Dropzone(dropzoneElement, {
                url: "{{ route('media.store') }}",
                paramName: "file",
                maxFiles: 1,
                maxFilesize: 1, // MB
                acceptedFiles: "image/*",
                headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                addRemoveLinks: true,
                dictDefaultMessage: "Drop image or click",
                init: function () {
                    const dropzone = this;

                    if (existingMedia) {
                        const mockFile = {
                            name: existingMedia.file_name,
                            size: existingMedia.size,
                            _hiddenInputId: existingMedia.id
                        };

                        dropzone.emit("addedfile", mockFile);
                        dropzone.emit("thumbnail", mockFile, existingMedia.original_url);
                        dropzone.emit("complete", mockFile);
                        dropzone.files.push(mockFile);

                        hiddenInput.value = existingMedia.id;
                    }

                    // ✅ success upload
                    dropzone.on("success", function (file, response) {
                        if (response.success && response.data) {
                            file._hiddenInputId = response.data.id;
                            hiddenInput.value = response.data.id;
                        }
                    });

                    // ✅ removed file
                    dropzone.on("removedfile", function (file) {
                        if (hiddenInput.value == file._hiddenInputId) {
                            hiddenInput.value = "";
                        }

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

    document.addEventListener("DOMContentLoaded", function () {
        const $colorRepeater = $('.color-repeater');

        // 1) init Dropzone على العناصر الموجودة (ألوان قديمة)
        $colorRepeater.find('[data-repeater-item]').each(function () {
            initColorItem(this);
        });

        // 2) init jquery.repeater
        if (window.$ && $.fn.repeater) {
            $colorRepeater.repeater({
                initEmpty: {{ $colors->isEmpty() ? 'true' : 'false' }},
                show: function () {
                    $(this).addClass('col-12 col-md-6 col-lg-3').hide().slideDown();

                    const item = this;
                    const dropzoneElement = item.querySelector('.color-dropzone');
                    const hiddenInput = item.querySelector('.color-image-hidden');
                    const colorPicker = item.querySelector('.color-picker');
                    const hexInput = item.querySelector('.color-hex-input');

                    if (dropzoneElement) {
                        // 🧹 امسح أي DOM منسوخ من الصف القديم (previews, classes...)
                        dropzoneElement.innerHTML =
                            '<div class="dz-message" data-dz-message><span>Drop image or click</span></div>';

                        dropzoneElement.classList.remove('dz-started', 'dz-max-files-reached');
                        dropzoneElement.dataset.existingMedia = '';
                    }

                    // امسح قيمة الـ image_id
                    if (hiddenInput) {
                        hiddenInput.value = '';
                    }

                    // Reset للّون الافتراضي
                    if (colorPicker) colorPicker.value = '#000000';
                    if (hexInput) hexInput.value = '#000000';

                    // init Dropzone + events
                    initColorItem(item);

                    if (window.feather) feather.replace();
                },
                hide: function (deleteElement) {
                    const item = this;
                    const hiddenInput = item.querySelector('.color-image-hidden');
                    const mediaId = hiddenInput ? hiddenInput.value : null;

                    // 🔥 لو في صورة مرتبطة باللون ده امسحها من السيرفر
                    if (mediaId) {
                        fetch("{{ url('api/v1/media') }}/" + mediaId, {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).catch(function (err) {
                            console.error("Failed to delete media:", err);
                        });
                    }

                    // بعد الأنيميشن فعلياً شيل العنصر من الـ DOM ومن الـ array بتاعة repeater
                    $(item).slideUp(function () {
                        deleteElement(); // مهم جداً تستخدم deleteElement بدل $this.remove()
                    });
                }
            });

            @if($colors->isEmpty())
            const hasItems = $colorRepeater.find('[data-repeater-item]').length > 0;
            if (!hasItems) {
                $colorRepeater.find('[data-repeater-create]').first().trigger('click');
            }
            @endif
        }
    });

</script>

<script>
    (function () {
            const LOCALE = @json(app()->getLocale());
            const SUB_ROUTE = @json(route('sub-industries'));
            const PRESEL_PARENTS = @json($preselectedIndustryIds ?? []);
            const PRESEL_SUBS = @json($preselectedSubIndustryIds ?? []);

            const $inds = $('#industriesSelect');     // parents
            const $subs = $('#subIndustriesSelect');  // subs

            const pickName = (item) => {
                if (item?.name && typeof item.name === 'object' && item.name[LOCALE]) return item.name[LOCALE];
                return item?.name ?? item?.title ?? `#${item?.id ?? ''}`;
            };
            const unpack = (resp) => Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : []);

            async function refreshSubs({preserveSelection = true} = {}) {
                const selectedParents = $inds.val() || PRESEL_PARENTS || [];

                if (!selectedParents.length) {
                    $subs.empty().trigger('change');
                    return;
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

                $.ajax({
                    url: SUB_ROUTE,
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': token},
                    // if you prefer JSON, add: contentType: 'application/json', data: JSON.stringify({...})
                    data: {industry_ids: selectedParents}
                })
                    .done((resp) => {
                        const items = unpack(resp);
                        // keep current selection or fall back to preselected ones
                        const keep = preserveSelection ? (($subs.val() || PRESEL_SUBS || []).map(String)) : [];
                        $subs.empty();
                        items.forEach(it => {
                            if (it?.id == null) return;
                            const id = String(it.id);
                            const isSelected = keep.includes(id);
                            $subs.append(new Option(pickName(it), id, isSelected, isSelected));
                        });
                        $subs.trigger('change');
                    })
                    .fail((xhr) => {
                        console.error('Failed to load sub-industries:', xhr.responseText);
                        $subs.empty().trigger('change');
                    });
            }

            $inds.on('change', () => refreshSubs({preserveSelection: true}));

            $(document).ready(() => {
                if ($inds.data('select2')) $inds.trigger('change.select2');
                refreshSubs({preserveSelection: true});
            });
        })();
</script>



<script>
    $(function () {
            const $circle = $('#shape_circle'); // value="0"
            const $other = $('#shape_other');  // value="1"
            const $hidden = $('#has_corner_hidden');

            // NEW: refs for corners UI
            const $cornersBox = $('#cornersBox');
            const $cornersSelect = $('#cornersSelect');

            // (optional) init select2 for corners once
            if ($cornersSelect.length && !$cornersSelect.data('select2')) {
                $cornersSelect.select2({
                    placeholder: "Corners",
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });
            }

            function updateHidden() {
                if ($circle.is(':checked')) return $hidden.val('0');
                if ($other.is(':checked')) return $hidden.val('1');
                $hidden.val(''); // none selected
            }

            function syncState() {
                // Mutual exclusivity + disable other
                if ($circle.is(':checked') && !$other.is(':checked')) {
                    $other.prop('checked', false).prop('disabled', true);
                } else if ($other.is(':checked') && !$circle.is(':checked')) {
                    $circle.prop('checked', false).prop('disabled', true);
                } else {
                    $circle.prop('disabled', false);
                    $other.prop('disabled', false);
                }

                // NEW: show/hide corners when "Other" is selected
                if ($other.is(':checked')) {
                    $cornersBox.removeClass('d-none');
                } else {
                    $cornersBox.addClass('d-none');
                    // optional: clear border when hidden
                    $cornersSelect.val(null).trigger('change');
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

            // Safety: resync on submit
            $('#editTemplateForm').on('submit', function () {
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
    document.addEventListener('click', function (e) {
            const submitBtn = e.target.closest('button[type="submit"]');
            if (!submitBtn) return;

            const form = submitBtn.closest('form');
            const flag = form.querySelector('input[name="go_to_editor"]');

            // if the clicked submit has the "go editor" class → set 1, otherwise 0
            if (submitBtn.classList.contains('js-go-editor')) {
                flag.value = '1';
            } else {
                flag.value = '0';
            }
        });
</script>

<script>
    // Build parallel arrays from current UI selections
        function buildDimensionPayloadFromUI() {
            const categoryIds = ($('#productsSelect').val() || []).map(String);               // categories
            const productIds = ($('#productsWithoutCategoriesSelect').val() || []).map(String); // products

            const resource_ids = [];
            const resource_types = [];

            // ✅ categories → "category"
            categoryIds.forEach(id => {
                resource_ids.push(id);
                resource_types.push('product');
            });

            // ✅ products → "product"
            productIds.forEach(id => {
                resource_ids.push(id);
                resource_types.push('category');
            });

            return {resource_ids, resource_types};
        }

        // Persist arrays into hidden inputs (so they submit with the form)
        function syncSelectedResourcesToHiddenInputs() {
            const {resource_ids, resource_types} = buildDimensionPayloadFromUI();
            $('#dimensionResourceIds').val(JSON.stringify(resource_ids));
            $('#dimensionResourceTypes').val(JSON.stringify(resource_types));
        }

        // Pretty number (single declaration!)
        const nf = new Intl.NumberFormat(undefined, {maximumFractionDigits: 3});

        // "HEIGHT * WIDTH (Unit)"
        function dimensionLabelHWTop(item, {showUnit = true} = {}) {
            const src = item.attributes ? item.attributes : item;
            const h = Number(src.height);
            const w = Number(src.width);
            const unitObj = src.unit;
            const unitLabel = unitObj && typeof unitObj === 'object' ? (unitObj.label || '') : (unitObj || '');

            if (Number.isFinite(h) && Number.isFinite(w)) {
                const core = `${nf.format(h)} * ${nf.format(w)}`;
                return showUnit && unitLabel ? `${core} ${unitLabel}` : core;
            }
            return src.name || src.label || `#${item.id ?? ''}`.trim();
        }

        // Read payload back from hidden inputs
        function buildDimensionPayloadFromHidden() {
            let ids = [], types = [];
            // const has_corner = $('input[name="has_corner"]:checked').val() === '1' ? 1 : 0;

            try {
                ids = JSON.parse($('#dimensionResourceIds').val() || '[]');
            } catch {
            }
            try {
                types = JSON.parse($('#dimensionResourceTypes').val() || '[]');
            } catch {
            }
            // return { resource_ids: ids, resource_types: types ,has_corner: has_corner};
            return {resource_ids: ids, resource_types: types};

        }

        // Fetch & render sizes
        function refreshSizes(preselectId = null) {
            syncSelectedResourcesToHiddenInputs();
            const payload = buildDimensionPayloadFromHidden();

            const $sizes = $('#sizesSelect');
            const current = preselectId ?? ($sizes.val() || []);

            if (!payload.resource_ids.length) {
                $sizes.empty().append(new Option('Select Size', '', false, false)).trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('dimensions.index') }}",
                method: "POST",
                data: payload,
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                success(res) {
                    $sizes.empty().append(new Option('Select Size', '', false, false));
                    const items = res.data || res || [];
                    items.forEach(item => {
                        $sizes.append(new Option(dimensionLabelHWTop(item, {showUnit: true}), item.id, false, false));
                    });
                    const target = Array.isArray(current) ? current : [String(current)];
                    $sizes.val(target.filter(v => $sizes.find(`option[value="${v}"]`).length)).trigger('change');
                },
                error(xhr) {
                    console.error('Failed to load dimensions:', xhr.responseText);
                    $sizes.empty().append(new Option('Select Size', '', false, false)).trigger('change');
                }
            });
        }

</script>

<script>
    // Listen for change on "Products With Categories"
        // Left: Products With Categories → updates right list, then refresh
        $('#categoriesSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            const selectedIds = $(this).val();
            const prev = $('#productsSelect').val() || [];

            if (selectedIds && selectedIds.length) {
                $.ajax({
                    url: "{{ route('products.categories') }}",
                    type: "POST",
                    data: {_token: "{{ csrf_token() }}", category_ids: selectedIds},
                    success(response) {
                        const $right = $('#productsSelect').empty();
                        (response.data || []).forEach(p => $right.append(new Option(p.name, p.id, false, false)));
                        $right.val(prev).trigger('change');
                        refreshSizes(); // 🔔 fetch sizes
                    },
                    error(xhr) {
                        console.error("Error fetching categories:", xhr.responseText);
                        // refreshSizes();
                    }
                });
            } else {
                $('#productsSelect').empty().trigger('change');
                // refreshSizes();
            }
        });

        // Right: categories changed → refresh sizes
        $('#productsSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            // refreshSizes();
        });

        // Bottom: products without categories changed → refresh sizes
        $('#productsWithoutCategoriesSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            // refreshSizes();
        });

        // Also fetch when opening the sizes select
        $('#sizesSelect').on('mousedown focus', function () {
            refreshSizes();
        });

        // On load: sync & preselect saved size
        $(document).ready(function () {
            syncSelectedResourcesToHiddenInputs();
            const savedDimensionId = "{{ $model->dimension_id ?? '' }}";
            refreshSizes(savedDimensionId || null);
        });


</script>

<script>
    const modelDropzone = new Dropzone("#template-dropzone", {
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
                @if(!empty($media = $model->getFirstMedia('template_model_image')))
                let modelMockFile = {
                    name: "{{ $media->file_name }}",
                    size: {{ $media->size ?? 12345 }},
                    _hiddenInputId: "{{ $media->id }}"
                };
                document.getElementById("uploadedTemplateImage").value = "{{ $media->id }}";


                dz.emit("addedfile", modelMockFile);
                dz.emit("thumbnail", modelMockFile, "{{ $media->getUrl() }}");
                dz.emit("complete", modelMockFile);
                dz.files.push(modelMockFile);
                @endif

                dz.on("success", function (file, response) {
                    if (response?.data?.id) {
                        file._hiddenInputId = response.data.id;
                        document.getElementById("uploadedTemplateImage").value = response.data.id;
                    }
                });

                dz.on("removedfile", function (file) {
                    if (file._hiddenInputId) {
                        fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        let hiddenInput = document.getElementById("uploadedTemplateImage");
                        if (hiddenInput.value == file._hiddenInputId) {
                            hiddenInput.value = "";
                        }
                    }
                });
            }
        });


        // store initial values when page loads
        const originalProducts = $('#productsSelect').val();
        const originalTags = $('#tagsSelect').val();

        document.getElementById('cancelButton').addEventListener('click', function (e) {
            $('#productsSelect').val(originalProducts).trigger('change');
            $('#tagsSelect').val(originalTags).trigger('change');
        });


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
    const modelMainDropzone = new Dropzone("#template-main-dropzone", {
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
                @if(!empty($media = $model->getFirstMedia('templates')))
                let modelMockFile = {
                    name: "{{ $media->file_name }}",
                    size: {{ $media->size ?? 12345 }},
                    _hiddenInputId: "{{ $media->id }}"
                };
                document.getElementById("uploadedMainTemplateImage").value = "{{ $media->id }}";


                dz.emit("addedfile", modelMockFile);
                dz.emit("thumbnail", modelMockFile, "{{ $media->getUrl() }}");
                dz.emit("complete", modelMockFile);
                dz.files.push(modelMockFile);
                @endif

                dz.on("success", function (file, response) {
                    if (response?.data?.id) {
                        file._hiddenInputId = response.data.id;
                        document.getElementById("uploadedMainTemplateImage").value = response.data.id;
                    }
                });

                dz.on("removedfile", function (file) {
                    if (file._hiddenInputId) {
                        fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        let hiddenInput = document.getElementById("uploadedMainTemplateImage");
                        if (hiddenInput.value == file._hiddenInputId) {
                            hiddenInput.value = "";
                        }
                    }
                });
            }
        });


</script>


<script !src="">
    handleAjaxFormSubmit("#editTemplateForm", {
            successMessage: "Template updated successfully",
            onSuccess: function (response, $form) {
                setTimeout(function () {
                    window.location.href = '/product-templates';
                }, 1000);

                if (response.data.editor_url) {
                    window.open(response.data.editor_url, '_blank');

                }
            }
        });


</script>
<script !src="">
    $(document).ready(function () {
            const preselectedProductId = $('#productsSelect').val();
            if (preselectedProductId) {
                $('#productsSelect').trigger('change');
            }
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
            $('#flagsSelect').select2({
                placeholder: "Choose Flags",
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
