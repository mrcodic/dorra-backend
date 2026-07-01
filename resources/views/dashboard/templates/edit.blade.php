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
                            $selectedTableauSceneIds = $model->tableauScenes?->pluck('id')->map(fn ($id) => (string) $id)->values()->toArray() ?? [];

                            $existingScenePositions = $model->tableauScenes
                                ?->mapWithKeys(function ($scene) {
                                    $positions = $scene->pivot->positions ?? [];
                                    if (is_string($positions)) {
                                        $positions = json_decode($positions, true) ?: [];
                                    }
                                    return [(string) $scene->id => $positions];
                                })
                                ->toArray() ?? [];
                            $category = \App\Models\Category::find(request('product_without_category_id'));
                        @endphp

                        <form id="editTemplateForm" enctype="multipart/form-data" method="post"
                              action="{{ route('product-templates.update',$model->id) }}">
                            @csrf
                            @method("PUT")
                            <div class="flex-grow-1">
                                <div class="">
                                    <input type="hidden" name="use_front_as_back" id="useFrontAsBack"
                                           value="{{ $model->use_front_as_back }}">
                                    <input type="hidden" name="template_image_id" id="uploadedTemplateImage"
                                           value="{{ $model->getFirstMedia('template_model_image')?->id ?? '' }}">
                                    <input type="hidden" name="selected_scene_image_id" id="selectedSceneImageId" value="">
                                    <input type="hidden" name="selected_scene_image_url" id="selectedSceneImageUrl" value="">

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
                                        <label class="label-text mb-1">Language</label>
                                        <div class="row">
                                            @foreach(config("app.locales") as $locale)
                                                <div class="col-md-4 mb-1">
                                                    <label class="radio-box">
                                                        <input class="form-check-input " type="checkbox"
                                                               name="supported_languages[]"
                                                               value="{{ $locale }}"
                                                            @checked(in_array($locale, $model->supported_languages ??[], true))
                                                        >
                                                        <span>{{ $locale == 'en' ? 'English' : 'Arabic'}}</span>
                                                    </label>
                                                </div>
                                            @endforeach

                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="label-text mb-1">Template Type</label>
                                            <div class="row">
                                                @foreach(\App\Models\Type::when(
          ($category?->is_tableau),
           fn($q) => $q->whereValue(\App\Enums\Template\TypeEnum::FRONT)
       )->get(['id','value']) as $type)
                                                    <div class="col-md-4 mb-1">
                                                        <label class="radio-box">
                                                            <input class="form-check-input type-checkbox"
                                                                   type="checkbox"
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
                                        <div class="row">

                                            <div class="row" id="templateTypeDropzones">
                                                <input type="hidden" name="approach" value="{{ $model->approach }}">
                                                @if($model->approach == 'without_editor')
                                                    <!-- FRONT -->
                                                    <div class="form-group mb-2 col-md-6 d-none" id="dz-front">
                                                        <label class="label-text mb-1">{{ $model->use_front_as_back ? 'Upload Print File (Front,Back)' : 'Upload Print File (Front)' }}</label>
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
                                                        <label class="label-text mb-1">Upload Print File
                                                            (Back)</label>
                                                        <div id="back-template-dropzone"
                                                             class="dropzone border rounded p-3"
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
                                                    {{--                                                    <div class="form-group mb-2 col-md-6 d-none" id="dz-none">--}}
                                                    {{--                                                        <label class="label-text mb-1">Upload Print File--}}
                                                    {{--                                                            (General)</label>--}}
                                                    {{--                                                        <div id="none-template-dropzone"--}}
                                                    {{--                                                             class="dropzone border rounded p-3"--}}
                                                    {{--                                                             style="cursor:pointer; min-height:150px;">--}}
                                                    {{--                                                            <div class="dz-message">--}}
                                                    {{--                                                                <span>Drop general image here or click</span>--}}
                                                    {{--                                                            </div>--}}
                                                    {{--                                                            <input type="hidden" name="template_image_none_id"--}}
                                                    {{--                                                                   id="uploadedNoneTemplateImage">--}}
                                                    {{--                                                        </div>--}}
                                                    {{--                                                        <small class="form-text text-muted">--}}
                                                    {{--                                                            Allowed formats: PNG, JPG, JPEG, WEBP.--}}
                                                    {{--                                                            Maximum file size: 30 MB.--}}
                                                    {{--                                                            Minimum dimensions: 1000 × 1000 px.--}}
                                                    {{--                                                        </small>--}}
                                                    {{--                                                    </div>--}}
                                                @endif

                                                @if(($category && !$category->has_mockup) || !$category )
                                                    <!-- MODEL  -->
                                                    <div class="form-group mb-2 col-md-6 d-none" id="dz-model">
                                                        <label class="label-text mb-1">Template Model Image</label>
                                                        <div id="template-dropzone" class="dropzone border rounded p-3"
                                                             style="cursor:pointer; min-height:150px;">
                                                            <div class="dz-message" data-dz-message>
                                                                <span>Drop image here or click to upload</span>
                                                            </div>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            Upload an image with an 8:9 aspect ratio (for example, 618 × 700 px).
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>


                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <label for="templateNameEn" class="label-text mb-1">Name (EN)</label>
                                                <input type="text" id="templateNameEn" class="form-control"
                                                       name="name[en]"
                                                       value="{{ $model->getTranslation('name','en') }}"
                                                       placeholder="Template Name in English">
                                            </div>


                                            <div class="col-md-6">
                                                <label for="templateNameAr" class="label-text mb-1">Name (AR)</label>
                                                <input type="text" id="templateNameAr" class="form-control"
                                                       name="name[ar]"
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
                                        <div class="row mb-2">
                                            <div class="col-md-12">
                                                <label for="templatePrice" class="label-text mb-1">
                                                    Price</label>
                                                <input id="templatePrice" class="form-control" type="number"
                                                       name="price" placeholder="Template Price"
                                                       value="{{ $model->price }}"
                                                       step="0.01"
                                                       min="0"
                                                />
                                            </div>
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
                                                    <option value="{{ $category->id }}"
                                                            data-is-tableau="{{ $category->is_tableau ? '1' : '0' }}"
                                                        @selected($category->
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
                                                    <option value="{{ $product->id }}"
                                                            data-is-tableau="{{ $product->is_tableau ? '1' : '0' }}"
                                                        @selected($model->
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
                                                <option value="{{ $category->id }}"
                                                        data-is-tableau="{{ $category->is_tableau ? '1' : '0' }}"
                                                    @selected($model->
                                        categories->contains($category))>
                                                    {{ $category->getTranslation('name', app()->getLocale()) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="row mb-2 d-none" id="tableauSizeSpecWrapper">
                                        <div class="col-md-12 form-group mb-2">
                                            <label class="label-text mb-1">Tableau Size</label>

                                            <div id="tableauSizeSpecOptionsContainer"></div>

                                        </div>
                                    </div>
                                    {{-- TABLEAU SCENE --}}
                                    <div class="form-group mb-2 col-md-6 d-none" id="dz-tableau-scene">
                                        <label class="label-text mb-1">Tableau Scene</label>

                                        {{-- Choose existing scenes --}}
                                        <select name="tableau_scene_ids[]"
                                                id="tableauSceneSelect"
                                                class="form-select select2 mb-1"
                                                multiple>
                                            @foreach(\App\Models\TableauScene::where('is_active', true)->latest()->get() as $scene)
                                                <option value="{{ $scene->id }}"
                                                        data-image-id="{{ $scene->getFirstMedia('tableau_scene_image')?->id }}"
                                                        data-image-url="{{ $scene->getFirstMediaUrl('tableau_scene_image') }}"
                                                    @selected(in_array((string) $scene->id, $selectedTableauSceneIds, true))>
                                                    {{ $scene->getTranslation('name', app()->getLocale(), false) ?: 'Scene #' . $scene->id }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="text-center my-1 text-muted">or create new scene</div>

                                        {{-- New scene name --}}
                                        <div id="newTableauSceneFields">
                                            <div class="row mb-1">
                                                <div class="col-md-6">
                                                    <label class="label-text mb-1">Scene Name (EN)</label>
                                                    <input type="text"
                                                           id="newTableauSceneNameEn"
                                                           class="form-control"
                                                           placeholder="Example: Living Room Scene">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="label-text mb-1">Scene Name (AR)</label>
                                                    <input type="text"
                                                           id="newTableauSceneNameAr"
                                                           class="form-control"
                                                           placeholder="مثال: مشهد غرفة معيشة">
                                                </div>
                                            </div>

                                            {{-- New scene image --}}
                                            <div id="tableau-scene-dropzone"
                                                 class="dropzone border rounded p-3"
                                                 style="cursor:pointer; min-height:150px;">
                                                <div class="dz-message">
                                                    <span>Drop tableau scene image here or click</span>
                                                </div>

                                                <input type="hidden"
                                                       id="uploadedTableauSceneImage">
                                            </div>

                                            <div class="d-flex justify-content-end mt-1">
                                                <button type="button"
                                                        id="createTableauSceneBtn"
                                                        class="btn btn-outline-primary btn-sm"
                                                        data-create-url="{{ url('/tableau-scenes') }}">
                                                    Create Scene
                                                </button>
                                            </div>

                                            <small class="form-text text-muted">
                                                Upload the scene image, then click Create Scene. The created scene will be selected automatically.
                                            </small>
                                        </div>
                                    </div>

                                    {{-- SCENE POSITION EDITOR --}}
                                    <div class="form-group mb-2 col-md-12 d-none" id="dz-scene-position-editor">
                                        <div class="position-relative mt-3 text-center mb-2">
                                            <hr class="opacity-75" style="border: 1px solid #24B094;">
                                            <span class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                                  style="color: #24B094;">
            Scene Position Editor
        </span>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2">
                                            <div id="scenePosTabs" style="display:flex;gap:6px;flex-wrap:wrap;"></div>

                                            <button type="button"
                                                    id="saveScenePositionsBtn"
                                                    class="btn btn-primary btn-sm">
                                                Save Positions
                                            </button>
                                        </div>

                                        <div id="scenePosPanels"></div>

                                        <p class="text-muted mt-1" style="font-size:12px;">
                                            Drag the template overlay on each scene, then click Save Positions before saving the template.
                                        </p>

                                        <input type="hidden" name="tableau_scene_ids" id="tableauScenePositionsInput">
                                    </div>
                                    <div
                                        class="col-md-12 form-group mb-2 mockupWrapper">
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
                                                    <option value="{{ $industry->id }}" @selected($model->
                                                industries->contains($industry))>
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
                                                    name="industry_ids[]" multiple>
                                                @foreach($associatedData['sub_industries'] as $industry)
                                                    <option value="{{ $industry->id }}" @selected($model->
                                                industries->contains($industry))>
                                                        {{ $industry->getTranslation('name', 'en').
                                  "({$industry->getTranslation('name', 'ar')})" }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row align-items-end">
                                        <div class="col-md-9 form-group mb-2">
                                            <label for="tagsSelect" class="label-text mb-1">Tags</label>
                                            <select id="tagsSelect" class="form-select select2" name="tags[]" multiple>
                                                @foreach($associatedData['tags'] as $tag)
                                                    <option value="{{ $tag->id }}" @selected($model->tags->contains($tag))>
                                                        {{ $tag->getTranslation('name', app()->getLocale()) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @can("tags_create")
                                            <div class="col-3 col-md-3 mb-2 text-md-end">
                                                <a class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-1"
                                                   data-bs-toggle="modal" data-bs-target="#addTagModal">
                                                    <i data-feather="plus"></i>
                                                    Add New Tag
                                                </a>
                                            </div>
                                        @endcan
                                    </div>
                                    @if($model->approach == 'with_editor')
                                        <div class="position-relative mt-3 text-center">
                                            <hr class="opacity-75" style="border: 1px solid #24B094;">
                                            <span
                                                class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                                style="color: #24B094;">
                                        Design Specifications
                                    </span>
                                        </div>
                                        {{-- Persisted resources (used on submit / ajax) --}}
                                        <input type="hidden" name="dimension_resource_ids" id="dimensionResourceIds">
                                        <input type="hidden" name="dimension_resource_types"
                                               id="dimensionResourceTypes">
                                        <div class="form-group mb-2">
                                            <label for="orientation" class="label-text mb-1">Orientation</label>
                                            <select id="orientation" class="form-select" name="orientation">
                                                <option value="" disabled>
                                                    chooese orientation
                                                </option>
                                                @foreach(\App\Enums\OrientationEnum::cases() as $orientation)
                                                    <option value="{{ $orientation->value }}" @selected($orientation==$model->
                                            orientation) >
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
                                        <label class="label-text mb-1">Shape</label>
                                        <div class="row mb-2">
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <input type="hidden" name="has_corner" id="has_corner_hidden"
                                                           value="{{ old('has_corner', $model->has_corner ?? '') }}">

                                                    <div class="d-flex gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="has_corner"
                                                                   id="shape_circle"
                                                                   value="0" @checked($model->has_corner == 0)
                                                            >
                                                            <label class="form-check-label"
                                                                   for="shape_circle">Circle</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="has_corner"
                                                                   id="shape_other"
                                                                   value="1" @checked($model->has_corner == 1)

                                                            >
                                                            <label class="form-check-label"
                                                                   for="shape_other">Other</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-2 d-none" id="cornersBox">
                                                    <label for="cornersSelect" class="label-text mb-1">Corners</label>
                                                    <select id="cornersSelect" class="form-select select2"
                                                            name="border">
                                                        <option value="" selected>Choose Corner</option>
                                                        @foreach(\App\Enums\CornerEnum::cases() as $border)
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
                                                        <input class="form-check-input" type="checkbox"
                                                               id="hasSafetyArea"
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
                                                        <input class="form-check-input" type="checkbox"
                                                               id="hasCutMargin"
                                                               value="1" @checked((int) $model->cut_margin > 0) >
                                                        <label class="form-check-label" for="hasCutMargin">Enable Cut
                                                            Margin</label>
                                                    </div>

                                                    <div id="cutMarginBox"
                                                         class="{{ $model->cut_margin > 0 ? '' : 'd-none' }}">
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


    <!-- Back Design Modal -->
    <div class="modal fade" id="backDesignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Back Side Design</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-4">Do you want to use the same front design for the back side, or upload a different
                        one?</p>
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

    <input type="hidden" name="use_front_as_back" id="useFrontAsBack"
           value="{{ $model->use_front_as_back ? '1' : '0' }}">
    @include('modals.tags.add-tag')

@endsection


@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection


@section('page-script')
    <script>
        handleAjaxFormSubmit("#addTagForm", {
            successMessage: "Tag Added Successfully",
            onSuccess: function (response) {

                $('#addTagModal').modal('hide');

                var newTag = response.data;
                console.log(newTag.name['en'])
                var newOption = new Option(newTag.name['en'], newTag.id, true, true);
                $('#tagsSelect').append(newOption).trigger('change');

                // 3. Reset the form fields
                $('#addTagForm')[0].reset();
            }
        });

        @if($model->approach == 'without_editor')
        $(function () {
            let backModalShown = false;

            const alreadyUsingSame = "{{ $model->use_front_as_back ? '1' : '0' }}" === '1';
            if (alreadyUsingSame) {
                backModalShown = true;
                setTimeout(function () {
                    $('#dz-back').addClass('d-none');
                    $('#dz-front .label-text').text('Upload Print File (Front, Back)');
                }, 150);
            }

            $(document).on('change', '.type-checkbox', function () {
                const isBack   = $(this).data('type-name') === 'back';
                const isFront  = $(this).data('type-name') === 'front';
                const isChecked = $(this).is(':checked');

                const frontChecked = $('.type-checkbox[data-type-name="front"]').is(':checked');
                const backChecked  = $('.type-checkbox[data-type-name="back"]').is(':checked');

                // Show modal only if BOTH front and back are checked
                if ((isBack || isFront) && isChecked && frontChecked && backChecked && !backModalShown) {
                    backModalShown = true;
                    setTimeout(function () {
                        const modal = new bootstrap.Modal(document.getElementById('backDesignModal'));
                        modal.show();
                    }, 50);
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
        @endif
    </script>
    {{-- Replace the first <script> block inside @section('page-script') in your edit blade --}}

    <script>
        // Pre-selected mockup IDs from the server (already attached to this template)
        const attachedMockupIds = new Set(
            (@json(($model?->mockups?->pluck('id') ?? collect())->values())).map(String)
        );

        $(function () {
            const $cardsWrap = $('#mockupsCards');
            const $hiddenWrap = $('#mockupsHiddenInputs');
            const $withCat = $('#categoriesSelect');
            const $withoutCat = $('#productsWithoutCategoriesSelect');

            // ── Single source of truth — seeded with server-side attached IDs ──
            const selected = new Set([...attachedMockupIds]);

            // ── Sync hidden inputs (the ONLY thing that submits mockup_ids[]) ──
            function syncHiddenInputs() {
                $hiddenWrap.empty();
                [...selected].forEach(id => {
                    $hiddenWrap.append(`<input type="hidden" name="mockup_ids[]" value="${id}">`);
                });
            }
            const editUrlTemplate = "{{ config('services.editor_url') }}mokup/{id}?templateId={{ $model->id }}&is_has_category=0&product_id={categoryId}";
            // ── Render mockup cards ──────────────────────────────────────────────
            function renderMockupCards(items) {
                $cardsWrap.empty();

                if (!items.length) {
                    $cardsWrap.append(`<div class="col-12 text-muted py-2">No mockups found</div>`);
                    syncHiddenInputs();
                    return;
                }

                items.forEach(mockup => {
                    const id           = String(mockup.id);
                    const name         = mockup.name ?? ('Mockup #' + id);
                    const isHasCategory = parseInt(mockup.product?.is_has_category ?? 0);

                    const images   = mockup?.images || {};
                    const firstKey = Object.keys(images)[0];
                    const img      = (firstKey && images[firstKey]?.base_url)
                        || "{{ asset('images/placeholder.svg') }}";

                    const isSelected = selected.has(id);

                    // ── Build product_ids[] query string — mirrors your PHP logic ──
                    let productIdsQuery = '';
                    if (isHasCategory === 0) {
                        // send single category_id
                        const categoryId = mockup.product.id ?? '';
                        productIdsQuery = `&product_ids[]=${encodeURIComponent(categoryId)}`;
                    } else {
                        // send all product IDs
                        const productIds = mockup.products?.map(p => p.id) ?? [];
                        productIdsQuery = productIds
                            .map(pid => `&product_ids[]=${encodeURIComponent(pid)}`)
                            .join('');
                    }

                    const href = `{{ config('services.editor_url') }}mokup/${id}?templateId={{ $model->id }}&is_has_category=${isHasCategory}${productIdsQuery}`;

                    $cardsWrap.append(`
            <div class="col-12 col-md-4 col-lg-2">
                <div class="mockup-card${isSelected ? ' selected' : ''}" data-id="${id}">
                    <div class="card rounded-3 shadow-sm position-relative" style="border:1px solid #24B094;">

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
                            <button type="button"
                                    class="btn btn-sm btn-primary w-100 js-show-on-mockup"
                                    data-id="${id}"
                                    data-href="${href}">
                                Show on Mockup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
                });

                syncHiddenInputs();
            }
            // ── Toggle selection — shared by card click and checkbox click ───────
            function toggleMockup(id) {
                id = String(id);
                if (selected.has(id)) {
                    selected.delete(id);
                } else {
                    selected.add(id);
                }

                // Keep card border + checkbox visually in sync
                const $card = $(`.mockup-card[data-id="${id}"]`);
                $card.toggleClass('selected', selected.has(id));
                $card.find('.js-mockup-checkbox').prop('checked', selected.has(id));

                syncHiddenInputs();
            }

            // Card body click (ignore clicks on buttons/inputs inside)
            $(document).on('click', '.mockup-card', function (e) {
                if ($(e.target).closest('button, input, a').length) return;
                toggleMockup($(this).data('id'));
            });

            // Checkbox click — stop propagation so card handler doesn't double-fire
            $(document).on('click', '.js-mockup-checkbox', function (e) {
                e.stopPropagation();
                toggleMockup($(this).val());
            });

            // "Show on Mockup" button — select this mockup, then submit form
            $(document).on('click', '.js-show-on-mockup', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const id   = String($(this).data('id'));
                const href = $(this).data('href'); // ✅ read from the button

                if (!selected.has(id)) toggleMockup(id);

                $('#selectedMockupId').val(id);

                // ✅ open editor URL then submit form
                if (href) window.open(href, '_blank');

                $('#editTemplateForm').submit();
            });
            // ── Fetch available mockups from server ──────────────────────────────
            // ── Fetch available mockups from server ──────────────────────────────
            function getSelectValues(selector) {
                const value = $(selector).val();
                if (Array.isArray(value)) return value.map(String).filter(Boolean);
                return value ? [String(value)] : [];
            }

            function fetchMockups() {
                const productIdsWithCategory    = getSelectValues('#categoriesSelect');
                const productIdsWithoutCategory = getSelectValues('#productsWithoutCategoriesSelect');

                // Categories loaded from selected products with categories
                const categoryIds = getSelectValues('#productsSelect');

                /*
                 * IMPORTANT:
                 * - Products With Categories => send ONLY category_ids
                 * - Products Without Categories => send ONLY product_ids
                 */
                const productIdsToSend  = productIdsWithoutCategory;
                const categoryIdsToSend = productIdsWithCategory.length > 0 ? categoryIds : [];

                console.log('productIdsWithCategory',    productIdsWithCategory);
                console.log('productIdsWithoutCategory', productIdsWithoutCategory);
                console.log('categoryIdsToSend',         categoryIdsToSend);
                console.log('productIdsToSend',          productIdsToSend);

                if (!productIdsToSend.length && !categoryIdsToSend.length) {
                    $cardsWrap.empty();
                    $hiddenWrap.empty();
                    // Don't clear selected — preserve already-attached mockup IDs
                    syncHiddenInputs();
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
                        product_ids:  productIdsToSend,
                        category_ids: categoryIdsToSend,
                        types:        selectedTypes,
                        filter:       'both'
                    },
                    success(response) {
                        const items = response?.data?.data || response?.data || response || [];

                        // Remove from selected any IDs no longer in the result set
                        const validIds = new Set(items.map(x => String(x.id)));
                        [...selected].forEach(id => {
                            if (!validIds.has(id)) selected.delete(id);
                        });

                        renderMockupCards(items);
                    },
                    error(xhr) {
                        console.error("Error fetching mockups:", xhr.responseText);
                        $cardsWrap.empty().append(
                            `<div class="col-12 text-danger py-2">Failed to load mockups</div>`
                        );
                    }
                });
            }

            // ── Wire up external change triggers ────────────────────────────────
            // ── Wire up external change triggers ────────────────────────────────
            $withCat.on('change', function () {
                const selectedIds = $(this).val();
                const $right = $('#productsSelect');

                if (selectedIds && selectedIds.length > 0) {
                    $.ajax({
                        url: "{{ route('products.categories') }}",
                        type: "POST",
                        data: { _token: "{{ csrf_token() }}", category_ids: selectedIds },
                        success(response) {
                            $right.empty();
                            (response.data || []).forEach(cat => {
                                const opt = new Option(cat.name, cat.id, false, true);
                                $(opt).attr('data-has-mockup', cat.has_mockup ? '1' : '0');
                                $(opt).attr('data-is-tableau', cat.is_tableau ? '1' : '0');
                                $right.append(opt);
                            });
                            $right.trigger('change.select2');
                            if (typeof updateTemplateTypeDropzones === 'function') updateTemplateTypeDropzones();
                            fetchMockups(); // ✅ called AFTER #productsSelect is populated
                        },
                        error(xhr) {
                            console.error("Error fetching categories:", xhr.responseText);
                            fetchMockups();
                        }
                    });
                } else {
                    $right.empty().trigger('change.select2');
                    if (typeof updateTemplateTypeDropzones === 'function') updateTemplateTypeDropzones();
                    fetchMockups();
                }
            });

            $withoutCat.on('change', fetchMockups);
            $(document).on('change', '.type-checkbox', fetchMockups);

// Initial load — also need to pre-populate #productsSelect first
            (function initialLoad() {
                const selectedIds = $withCat.val();
                const $right = $('#productsSelect');

                if (selectedIds && selectedIds.length > 0) {
                    $.ajax({
                        url: "{{ route('products.categories') }}",
                        type: "POST",
                        data: { _token: "{{ csrf_token() }}", category_ids: selectedIds },
                        success(response) {
                            // preserve already-selected options (server-rendered)
                            const alreadySelected = $right.find('option:selected').map(function() {
                                return String($(this).val());
                            }).get();

                            $right.empty();
                            (response.data || []).forEach(cat => {
                                const isSelected = alreadySelected.includes(String(cat.id));
                                const opt = new Option(cat.name, cat.id, isSelected, isSelected);
                                $(opt).attr('data-has-mockup', cat.has_mockup ? '1' : '0');
                                $(opt).attr('data-is-tableau', cat.is_tableau ? '1' : '0');
                                $right.append(opt);
                            });
                            $right.trigger('change.select2');
                            if (typeof updateTemplateTypeDropzones === 'function') updateTemplateTypeDropzones();
                            fetchMockups(); // ✅ called AFTER #productsSelect is populated
                        },
                        error() {
                            fetchMockups();
                        }
                    });
                } else {
                    fetchMockups();
                }
            })();
        });
    </script>

    <script>
        function selectedProductIsTableau() {
            const selectedOptions = [
                ...document.querySelectorAll('#productsWithoutCategoriesSelect option:checked'),
                ...document.querySelectorAll('#productsSelect option:checked'),
                ...document.querySelectorAll('#categoriesSelect option:checked')
            ];

            return selectedOptions.some(option => option.dataset.isTableau === '1');
        }

        function syncTableauSceneFields() {
            const $select = $('#tableauSceneSelect');
            const value = $select.val();
            const hasExistingScene = Array.isArray(value) ? value.length > 0 : !!value;

            // $('#newTableauSceneFields').toggle(!hasExistingScene);

            if (hasExistingScene) {
                $('#newTableauSceneNameEn').val('');
                $('#newTableauSceneNameAr').val('');
                $('#uploadedTableauSceneImage').val('');
            }
        }

        function resetTableauSceneFields() {
            /*
             * Edit page safety:
             * Do NOT clear #tableauSceneSelect here and do NOT call removeAllFiles(true).
             * Clearing/removing on edit can detach/delete the saved tableau scene image.
             */
            $('#newTableauSceneNameEn').val('');
            $('#newTableauSceneNameAr').val('');
            $('#uploadedTableauSceneImage').val('');
            $('#newTableauSceneFields').show();

            window.newTableauSceneImageUrl = null;
            window.lastTableauSceneImageId = null;
        }

        function updateTemplateTypeDropzones() {
            const selectedTypes = Array.from(document.querySelectorAll('.type-checkbox'))
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            const dzFront = document.getElementById("dz-front");
            const dzBack = document.getElementById("dz-back");
            const dzNone = document.getElementById("dz-none");
            const dzModel = document.getElementById("dz-model");
            const dzTableauScene = document.getElementById("dz-tableau-scene");

            [dzFront, dzBack, dzNone, dzModel, dzTableauScene].forEach(dz => {
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

            @if($model->approach == 'with_editor')
            if (dzModel) {
                dzModel.classList.remove("d-none");
                if (!visibleDZ.includes(dzModel)) {
                    visibleDZ.push(dzModel);
                }
            }
            @endif

            if (selectedProductIsTableau() && dzTableauScene) {
                dzTableauScene.classList.remove("d-none");
                visibleDZ.push(dzTableauScene);
                syncTableauSceneFields();
            } else if (dzTableauScene) {
                resetTableauSceneFields();
            }

            // Re-apply "use same design" state after dropzones are toggled
            if (document.getElementById('useFrontAsBack')?.value === '1') {
                setTimeout(function () {
                    $('#dz-back').addClass('d-none');
                    $('#dz-front .label-text').text('Upload Print File (Front, Back)');
                }, 0);
            }

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

        // trigger on checkbox/product/scene change
        document.querySelectorAll('.type-checkbox').forEach(cb => {
            cb.addEventListener("change", updateTemplateTypeDropzones);
        });

        $(document).on('change', '#categoriesSelect, #productsSelect, #productsWithoutCategoriesSelect', function () {
            updateTemplateTypeDropzones();
        });

        $(document).on('change', '#tableauSceneSelect', function () {
            syncTableauSceneFields();
        });

        $(document).ready(function () {
            updateTemplateTypeDropzones();
            syncTableauSceneFields();
        });
    </script>
    <script>
        // Already-attached tableau_size option ids for this template (from pivot)
        window.preselectedTableauSizeOptionIds = @json(
        $model->specificationOptions->pluck('id')->map(fn ($id) => (string) $id)->values()
    );

        function getSelectedIdsForSpec(selector) {
            const value = $(selector).val();
            if (Array.isArray(value)) return value.filter(Boolean).map(String);
            return value ? [String(value)] : [];
        }

        function escapeHtmlForSpec(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;');
        }

        function renderTableauSizeSpecOptions(specs) {
            const $wrapper = $('#tableauSizeSpecWrapper');
            const $container = $('#tableauSizeSpecOptionsContainer');
            const preselected = window.preselectedTableauSizeOptionIds || [];

            $container.empty();

            if (!specs.length) {
                $wrapper.addClass('d-none');
                return;
            }

            specs.forEach(function (spec) {
                const specId = spec.id;
                const specLabel = spec.label || spec.name || ('Tableau Size #' + specId);
                const options = spec.options || [];

                if (!options.length) return;

                const selectId = 'tableau_size_options_' + specId;

                let html = `
                <div class="mb-2 border rounded p-1">
                    <input type="hidden" name="tableau_size_specification_ids[]" value="${escapeHtmlForSpec(specId)}">

                    <label class="form-label mb-1" for="${escapeHtmlForSpec(selectId)}">
                        ${escapeHtmlForSpec(specLabel)}
                    </label>

                    <select
                        id="${escapeHtmlForSpec(selectId)}"
                        name="tableau_size_options[${escapeHtmlForSpec(specId)}][]"
                        class="form-select tableau-size-options-select"
                        multiple>
            `;

                options.forEach(function (option) {
                    const optionLabel = option.label || option.name || option.value || ('Option #' + option.id);
                    const isSelected = preselected.includes(String(option.id));

                    html += `
                    <option
                        value="${escapeHtmlForSpec(option.id)}"
                        data-value="${escapeHtmlForSpec(option.value || '')}"
                        data-price="${escapeHtmlForSpec(option.price || '')}"
                        data-image-url="${escapeHtmlForSpec(option.image_url || '')}"
                        ${isSelected ? 'selected' : ''}>
                        ${escapeHtmlForSpec(optionLabel)}
                    </option>
                `;
                });

                html += `</select></div>`;

                $container.append(html);
            });

            if (!$container.children().length) {
                $wrapper.addClass('d-none');
                return;
            }

            $wrapper.removeClass('d-none');

            $('.tableau-size-options-select').each(function () {
                const $select = $(this);

                if ($select.data('select2')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    width: '100%',
                    placeholder: 'Choose Tableau Size Options',
                    closeOnSelect: false,
                    allowClear: true
                });
            });
        }

        function fetchTableauSizeSpecification() {
            const $wrapper = $('#tableauSizeSpecWrapper');

            if (typeof selectedProductIsTableau === 'function' && !selectedProductIsTableau()) {
                $('#tableauSizeSpecOptionsContainer').empty();
                $wrapper.addClass('d-none');
                return;
            }

            const productIds = getSelectedIdsForSpec('#productsSelect');
            const categoryIds = getSelectedIdsForSpec('#productsWithoutCategoriesSelect');

            if (!productIds.length && !categoryIds.length) {
                $('#tableauSizeSpecOptionsContainer').empty();
                $wrapper.addClass('d-none');
                return;
            }

            $.ajax({
                url: "{{ route('tableau.specifications.size') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    product_ids: productIds,
                    category_ids: categoryIds
                },
                success(response) {
                    const specs = response?.data?.data || response?.data || [];
                    renderTableauSizeSpecOptions(specs);
                },
                error(xhr) {
                    console.error('Failed to load tableau_size options:', xhr.responseText);
                    $('#tableauSizeSpecOptionsContainer').empty();
                    $wrapper.addClass('d-none');
                }
            });
        }

        $(document).on('change', '#categoriesSelect, #productsSelect, #productsWithoutCategoriesSelect', function () {
            fetchTableauSizeSpecification();
        });

        // Edit page loads #productsSelect asynchronously (via AJAX), so retry
        // until it has settled instead of firing once on a fixed timeout.
        $(document).ready(function () {
            let attempts = 0;

            function attemptFetch() {
                const hasAnySelection =
                    getSelectedIdsForSpec('#categoriesSelect').length ||
                    getSelectedIdsForSpec('#productsSelect').length ||
                    getSelectedIdsForSpec('#productsWithoutCategoriesSelect').length;

                if (hasAnySelection || attempts > 10) {
                    fetchTableauSizeSpecification();
                    return;
                }

                attempts++;
                setTimeout(attemptFetch, 200);
            }

            setTimeout(attemptFetch, 300);
        });
    </script>
    <script>
        Dropzone.autoDiscover = false;
        const templateDropzones = {
            front: null,
            back: null,
            none: null,
        };

        // 🔹 FRONT Dropzone
        templateDropzones.front = new Dropzone("#front-template-dropzone", {
            url: "{{ route('media.store') }}",
            paramName: "file",
            maxFiles: 30,
            acceptedFiles: "image/png,image/jpeg,image/webp",
            headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
            addRemoveLinks: true,
            init: function () {
                let dz = this;

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

                @if(!empty($media = $model->getFirstMedia('templates')) && $model->types->contains(\App\Enums\Template\TypeEnum::FRONT->value))
                let modelMockFile = {
                    name: "{{ $media->file_name }}",
                    size: {{ $media->size ?? 12345 }},
                    _hiddenInputId: "{{ $media->id }}",
                    _isMock: true,
                };
                document.getElementById("uploadedFrontTemplateImage").value = "{{ $media->id }}";

                dz.emit("addedfile", modelMockFile);
                dz.emit("thumbnail", modelMockFile, "{{ $media->getUrl() }}");
                dz.emit("complete", modelMockFile);
                dz.files.push(modelMockFile);
                @endif

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
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                    }
                });
            },
        });

        // 🔹 BACK Dropzone
        templateDropzones.back = new Dropzone("#back-template-dropzone", {
            url: "{{ route('media.store') }}",
            paramName: "file",
            maxFiles: 30,
            acceptedFiles: "image/png,image/jpeg,image/webp",
            headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
            addRemoveLinks: true,
            init: function () {
                let dz = this;

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

                @if(!empty($media = $model->getFirstMedia('back_templates')))
                let modelMockFile = {
                    name: "{{ $media->file_name }}",
                    size: {{ $media->size ?? 12345 }},
                    _hiddenInputId: "{{ $media->id }}",
                    _isMock: true,
                };
                document.getElementById("uploadedBackTemplateImage").value = "{{ $media->id }}";

                dz.emit("addedfile", modelMockFile);
                dz.emit("thumbnail", modelMockFile, "{{ $media->getUrl() }}");
                dz.emit("complete", modelMockFile);
                dz.files.push(modelMockFile);
                @endif

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
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                    }
                });
            },
        });

        // 🔹 NONE Dropzone
        {{--templateDropzones.none = new Dropzone("#none-template-dropzone", {--}}
        {{--    url: "{{ route('media.store') }}",--}}
        {{--    paramName: "file",--}}
        {{--    maxFiles: 30,--}}
        {{--    acceptedFiles: "image/png,image/jpeg,image/webp",--}}
        {{--    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},--}}
        {{--    addRemoveLinks: true,--}}
        {{--    init: function () {--}}
        {{--        let dz = this;--}}

        {{--        this.on("addedfile", function (file) {--}}
        {{--            if (file._isMock) return;--}}

        {{--            // ── Max file size check (30MB) ──--}}
        {{--            if (file.size > 30 * 1024 * 1024) {--}}
        {{--                this.removeFile(file);--}}
        {{--                Toastify({--}}
        {{--                    text: `File size must not exceed 30MB. Your file is ${(file.size / 1024 / 1024).toFixed(2)}MB.`,--}}
        {{--                    duration: 3000,--}}
        {{--                    gravity: "top",--}}
        {{--                    position: "right",--}}
        {{--                    backgroundColor: "#EA5455",--}}
        {{--                    close: true,--}}
        {{--                }).showToast();--}}
        {{--                return;--}}
        {{--            }--}}

        {{--            // ── Min dimensions check (1000×1000) ──--}}
        {{--            const reader = new FileReader();--}}
        {{--            const dzRef = this;--}}

        {{--            reader.onload = function (e) {--}}
        {{--                const img = new Image();--}}
        {{--                img.onload = function () {--}}
        {{--                    if (img.width < 1000 || img.height < 1000) {--}}
        {{--                        dzRef.removeFile(file);--}}
        {{--                        Toastify({--}}
        {{--                            text: `Image must be at least 1000×1000px. Your image is ${img.width}×${img.height}px.`,--}}
        {{--                            duration: 3000,--}}
        {{--                            gravity: "top",--}}
        {{--                            position: "right",--}}
        {{--                            backgroundColor: "#EA5455",--}}
        {{--                            close: true,--}}
        {{--                        }).showToast();--}}
        {{--                    }--}}
        {{--                };--}}
        {{--                img.src = e.target.result;--}}
        {{--            };--}}

        {{--            reader.readAsDataURL(file);--}}
        {{--        });--}}

        {{--        @if(!empty($media = $model->getFirstMedia('templates'))))--}}
        {{--        let modelMockFile = {--}}
        {{--            name: "{{ $media->file_name }}",--}}
        {{--            size: {{ $media->size ?? 12345 }},--}}
        {{--            _hiddenInputId: "{{ $media->id }}",--}}
        {{--            _isMock: true,--}}
        {{--        };--}}
        {{--        document.getElementById("uploadedNoneTemplateImage").value = "{{ $media->id }}";--}}

        {{--        dz.emit("addedfile", modelMockFile);--}}
        {{--        dz.emit("thumbnail", modelMockFile, "{{ $media->getUrl() }}");--}}
        {{--        dz.emit("complete", modelMockFile);--}}
        {{--        dz.files.push(modelMockFile);--}}
        {{--        @endif--}}

        {{--            this.on("success", function (file, response) {--}}
        {{--            if (response.success && response.data) {--}}
        {{--                file._hiddenInputId = response.data.id;--}}
        {{--                document.getElementById("uploadedNoneTemplateImage").value = response.data.id;--}}
        {{--            }--}}
        {{--        });--}}

        {{--        this.on("removedfile", function (file) {--}}
        {{--            document.getElementById("uploadedNoneTemplateImage").value = "";--}}
        {{--            if (file._hiddenInputId) {--}}
        {{--                fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {--}}
        {{--                    method: "DELETE",--}}
        {{--                    headers: {--}}
        {{--                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content--}}
        {{--                    }--}}
        {{--                });--}}
        {{--            }--}}
        {{--        });--}}
        {{--    },--}}
        {{--});--}}

        // ✅ لما المستخدم يغير الأنواع (front/back/none)
        function handleTypeChangeAndResetDZ() {
            if (typeof updateTemplateTypeDropzones === "function") {
                updateTemplateTypeDropzones();
            }
        }
        document.querySelectorAll('.type-checkbox').forEach(cb => {
            cb.addEventListener('change', handleTypeChangeAndResetDZ);
        });
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
                return `${item?.name_en ?? ''}(${item?.name_ar ?? ''})` || item?.title || `#${item?.id ?? ''}`;            };
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
                            console.log(it)
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
                            $(opt).attr('data-is-tableau', cat.is_tableau ? '1' : '0');
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
        // $('#sizesSelect').on('mousedown focus', function () {
        //     refreshSizes();
        // });

        // On load: sync & preselect saved size
        $(document).ready(function () {
            syncSelectedResourcesToHiddenInputs();
            const savedDimensionId = "{{ $model->dimension_id ?? '' }}";
            refreshSizes(savedDimensionId || null);
        });


    </script>

    <script>
        let modelDropzone = null;

        if (document.getElementById("template-dropzone")) {
            modelDropzone = new Dropzone("#template-dropzone", {
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

        }

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
        let modelMainDropzone = null;
        if (document.getElementById("template-main-dropzone")) {
            modelMainDropzone = new Dropzone("#template-main-dropzone", {
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
        }


    </script>


    <script !src="">
        $('#editTemplateForm').on('submit', function () {
            if (typeof window.syncTableauScenePositions === 'function') {
                window.syncTableauScenePositions();
            }
        });

        handleAjaxFormSubmit("#editTemplateForm", {
            successMessage: "Template updated successfully",
            onSuccess: function (response, $form) {


                if (response.data.editor_url) {
                    window.open(response.data.editor_url, '_blank');
                    return;

                }
                if (response.data.mockup_redirect_url) {
                    window.open(response.data.mockup_redirect_url);

                }
                // setTimeout(function () {
                //     const params = new URLSearchParams(window.location.search);
                //     window.location.href = '/product-templates?' + params.toString();
                // }, 1000);
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

            $('#tableauSceneSelect').select2({
                placeholder: "Choose Tableau Scenes",
                allowClear: true,
                width: '100%'
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
    <script>
        Dropzone.autoDiscover = false;

        let tableauSceneDropzone = null;

        if (document.getElementById("tableau-scene-dropzone")) {
            tableauSceneDropzone = new Dropzone("#tableau-scene-dropzone", {
                url: "{{ route('media.store') }}",
                paramName: "file",
                maxFiles: 1,
                maxFilesize: 30,
                acceptedFiles: "image/png,image/jpeg,image/webp",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                addRemoveLinks: true,
                dictDefaultMessage: "Drop tableau scene image here or click to upload",
                init: function () {
                    this.on("addedfile", function (file) {
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            file._fullDataUrl = e.target.result;
                            window.newTableauSceneImageUrl = e.target.result;
                        };

                        reader.readAsDataURL(file);
                    });

                    this.on("success", function (file, response) {
                        if (response.success && response.data) {
                            file._hiddenInputId = response.data.id;
                            window.lastTableauSceneImageId = response.data.id;
                            document.getElementById("uploadedTableauSceneImage").value = response.data.id;
                            // setTableauSceneImageUrl(file, response);
                        }
                    });

                    this.on("removedfile", function (file) {
                        document.getElementById("uploadedTableauSceneImage").value = "";
                        window.newTableauSceneImageUrl = null;
                        window.lastTableauSceneImageId = null;

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
        }
        $('#productsWithoutCategoriesSelect, #productsSelect, #categoriesSelect').on('change', function () {
            updateTemplateTypeDropzones();
        });

        $('#tableauSceneSelect').on('change', function () {
            const selectedScenes = $(this).val() || [];
            const hasExistingScene = Array.isArray(selectedScenes)
                ? selectedScenes.length > 0
                : !!selectedScenes;

            // $('#newTableauSceneFields').toggle(!hasExistingScene);

            if (hasExistingScene) {
                $('#newTableauSceneNameEn').val('');
                $('#newTableauSceneNameAr').val('');
            }
        });


        function notifyTableau(message, type = 'success') {
            if (typeof Toastify === 'function') {
                Toastify({
                    text: message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: type === 'success' ? "#28C76F" : "#EA5455",
                    close: true
                }).showToast();
                return;
            }

            if (type === 'success') {
                console.log(message);
            } else {
                alert(message);
            }
        }

        function getSceneTextFromResponse(response) {
            const data = response?.data || response?.scene || response || {};
            const name = data?.name || data?.translations?.name || {};

            return name?.['{{ app()->getLocale() }}'] ||
                name?.en ||
                data?.name_en ||
                data?.title ||
                data?.label ||
                data?.name ||
                ('Scene #' + (data?.id || ''));
        }

        function getSceneImageUrlFromResponse(response) {
            const data = response?.data || response?.scene || response || {};

            return data?.image_url ||
                data?.imageUrl ||
                data?.media_url ||
                data?.mediaUrl ||
                data?.full_url ||
                data?.fullUrl ||
                data?.url ||
                data?.base_url ||
                data?.baseUrl ||
                data?.media?.full_url ||
                data?.media?.url ||
                window.newTableauSceneImageUrl ||
                '';
        }

        function getSceneImageIdFromResponse(response, fallbackImageId = '') {
            const data = response?.data || response?.scene || response || {};

            return data?.image_id ||
                data?.media_id ||
                data?.new_tableau_scene_image_id ||
                data?.media?.id ||
                data?.image?.id ||
                fallbackImageId ||
                window.lastTableauSceneImageId ||
                '';
        }

        function addAndSelectTableauScene(sceneId, sceneText, imageUrl, imageId = '') {
            const $select = $('#tableauSceneSelect');

            if (!$select.length || !sceneId) return;

            let option = $select.find(`option[value="${sceneId}"]`)[0];

            if (!option) {
                option = new Option(sceneText || ('Scene #' + sceneId), sceneId, true, true);
                $select.append(option);
            }

            $(option)
                .attr('data-image-url', imageUrl || '')
                .attr('data-image-id', imageId || '')
                .prop('selected', true);

            $select.trigger('change');
        }

        $(document).on('click', '#createTableauSceneBtn', function () {
            const $btn = $(this);
            const url = $btn.data('create-url');
            const nameEn = String($('#newTableauSceneNameEn').val() || '').trim();
            const nameAr = String($('#newTableauSceneNameAr').val() || '').trim();
            const imageId = $('#uploadedTableauSceneImage').val();

            if (!nameEn && !nameAr) {
                notifyTableau('Please enter scene name first.', 'error');
                return;
            }

            if (!imageId) {
                notifyTableau('Please upload scene image first.', 'error');
                return;
            }

            const oldText = $btn.text();

            $btn
                .prop('disabled', true)
                .data('old-text', oldText)
                .text('Creating...');

            function resetCreateSceneButton() {
                $btn
                    .prop('disabled', false)
                    .text($btn.data('old-text') || 'Create Scene');
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: document.querySelector('meta[name="csrf-token"]').content,
                    'name[en]': nameEn,
                    'name[ar]': nameAr,
                    'new_tableau_scene_name[en]': nameEn,
                    'new_tableau_scene_name[ar]': nameAr,
                    image_id: imageId,
                    media_id: imageId,
                    new_tableau_scene_image_id: imageId,
                    is_active: 1
                },
                success(response) {
                    const data = response?.data || response?.scene || response || {};
                    const sceneId = data?.id || response?.id;

                    if (!sceneId) {
                        notifyTableau('Scene created, but response does not contain scene id.', 'error');
                        resetCreateSceneButton();
                        return;
                    }

                    addAndSelectTableauScene(
                        sceneId,
                        getSceneTextFromResponse(response),
                        getSceneImageUrlFromResponse(response),
                        getSceneImageIdFromResponse(response, imageId)
                    );

                    $('#newTableauSceneNameEn').val('');
                    $('#newTableauSceneNameAr').val('');
                    $('#uploadedTableauSceneImage').val('');
                    window.newTableauSceneImageUrl = null;

                    if (tableauSceneDropzone) {
                        tableauSceneDropzone.files.forEach(file => {
                            file._hiddenInputId = null;
                        });
                        tableauSceneDropzone.removeAllFiles(true);
                    }

                    notifyTableau('Scene created and selected successfully.');
                },
                error(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;

                        for (const key in errors) {
                            Toastify({
                                text: errors[key][0],
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#EA5455",
                                close: true,
                            }).showToast();
                        }
                    } else {
                        Toastify({
                            text: xhr.responseJSON?.message || 'Failed to create scene.',
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true,
                        }).showToast();
                    }

                    resetCreateSceneButton();

                    if (typeof options !== 'undefined' && options?.onError) {
                        options.onError(xhr, typeof $form !== 'undefined' ? $form : null);
                    }
                },
                complete() {
                    resetCreateSceneButton();
                }
            });
        });
    </script>

    <script>
        (function () {
            const positionState = {};
            let templateSrc = null;
            let templateAspect = 1;

            const DEFAULT_TOP = 35;
            const DEFAULT_LEFT = 35;
            const DEFAULT_OVERLAY_WIDTH = 28;
            const MIN_OVERLAY_WIDTH = 5;
            const MAX_OVERLAY_WIDTH = 95;

// Positions already saved in DB, keyed by scene id (string) — edit page only
            const existingScenePositions = @json($existingScenePositions ?? []);

            function safeId(sceneId) {
                return String(sceneId).replace(/[^a-zA-Z0-9_-]/g, '_');
            }

            function num(value, fallback = 0) {
                const n = Number(value);
                return Number.isFinite(n) ? n : fallback;
            }

            function pct(value) {
                return Math.min(100, Math.max(0, Math.round(num(value) * 10) / 10));
            }

            function getSelectedDimension() {
                const select = document.getElementById('sizesSelect');
                const option = select?.selectedOptions?.[0];

                let width = num(option?.dataset?.width, null);
                let height = num(option?.dataset?.height, null);

                if ((!width || !height) && option) {
                    const text = option.textContent || '';
                    const match = text.match(/([\d.]+)\s*[*×x]\s*([\d.]+)/i);

                    if (match) {
                        height = num(match[1], null);
                        width = num(match[2], null);
                    }
                }

                if (!width || !height) {
                    return {
                        width: 1,
                        height: 1,
                        aspect: templateAspect || 1
                    };
                }

                return {
                    width,
                    height,
                    aspect: width / height
                };
            }

            function getCanvasAspect(canvas) {
                const rect = canvas?.getBoundingClientRect();

                if (rect?.width && rect?.height) {
                    return rect.width / rect.height;
                }

                return 4 / 3;
            }

            function clampOverlayWidth(canvas, sceneId, width) {
                const dimension = getSelectedDimension();
                const canvasAspect = getCanvasAspect(canvas);
                const overlayAspect = dimension.aspect || templateAspect || 1;

                /*
                 * Width is stored as percentage of the scene canvas width.
                 * Height is calculated from aspect ratio, so max width must also
                 * keep the calculated height inside the scene.
                 */
                const maxByHeight = overlayAspect && canvasAspect
                    ? (100 * overlayAspect / canvasAspect)
                    : 100;

                const maxWidth = Math.max(
                    MIN_OVERLAY_WIDTH,
                    Math.min(MAX_OVERLAY_WIDTH, maxByHeight)
                );

                return pct(Math.min(maxWidth, Math.max(MIN_OVERLAY_WIDTH, num(width, DEFAULT_OVERLAY_WIDTH))));
            }

            function ensureState(sceneId) {
                if (!positionState[sceneId]) {
                    const saved = existingScenePositions[String(sceneId)];

                    if (saved && Number.isFinite(Number(saved.top)) && Number.isFinite(Number(saved.left))) {
                        positionState[sceneId] = {
                            top: num(saved.top, DEFAULT_TOP),
                            left: num(saved.left, DEFAULT_LEFT),
                            width: num(saved.width, DEFAULT_OVERLAY_WIDTH)
                        };
                    } else {
                        positionState[sceneId] = {
                            top: DEFAULT_TOP,
                            left: DEFAULT_LEFT,
                            width: DEFAULT_OVERLAY_WIDTH
                        };
                    }
                }

                return positionState[sceneId];
            }

            function getOverlaySize(canvas, sceneId) {
                const st = ensureState(sceneId);
                const dimension = getSelectedDimension();

                st.width = clampOverlayWidth(canvas, sceneId, st.width || DEFAULT_OVERLAY_WIDTH);

                const width = st.width;
                const canvasAspect = getCanvasAspect(canvas);
                const overlayAspect = dimension.aspect || templateAspect || 1;

                const height = pct(width * canvasAspect / overlayAspect);

                return {
                    width,
                    height,
                    aspect: overlayAspect
                };
            }

            function clampPosition(canvas, sceneId, top, left) {
                const size = getOverlaySize(canvas, sceneId);

                const maxTop = Math.max(0, 100 - size.height);
                const maxLeft = Math.max(0, 100 - size.width);

                return {
                    top: Math.min(maxTop, Math.max(0, top)),
                    left: Math.min(maxLeft, Math.max(0, left))
                };
            }

            function getBox(canvas, sceneId) {
                const st = ensureState(sceneId);
                const size = getOverlaySize(canvas, sceneId);
                const clamped = clampPosition(canvas, sceneId, st.top, st.left);

                st.top = clamped.top;
                st.left = clamped.left;

                return {
                    top: pct(st.top),
                    left: pct(st.left),
                    right: pct(100 - st.left - size.width),
                    bottom: pct(100 - st.top - size.height),
                    width: pct(size.width),
                    height: pct(size.height),
                    aspect: size.aspect
                };
            }

            function loadTemplateImage(url) {
                templateSrc = url;

                const img = new Image();

                img.onload = function () {
                    templateAspect = img.naturalWidth / img.naturalHeight || 1;
                    refreshAllDraggables();
                };

                img.onerror = function () {
                    templateSrc = null;
                    refreshAllDraggables();
                };

                img.src = url;
            }

            function refreshTemplateSrc() {
                if (window.tableauTemplateUrl) {
                    loadTemplateImage(window.tableauTemplateUrl);
                    return;
                }

                const preview = document.querySelector(
                    '#front-template-dropzone .dz-image img, ' +
                    '#front-template-dropzone .dz-preview img, ' +
                    '#template-dropzone .dz-image img, ' +
                    '#template-dropzone .dz-preview img, ' +
                    '#back-template-dropzone .dz-image img, ' +
                    '#back-template-dropzone .dz-preview img'
                );

                if (preview && preview.src && !preview.src.includes('placeholder')) {
                    loadTemplateImage(preview.src);
                } else {
                    templateSrc = null;
                    refreshAllDraggables();
                }
            }

            window.refreshTableauTemplatePreview = refreshTemplateSrc;

            [
                'front-template-dropzone',
                'template-dropzone',
                'back-template-dropzone',
            ].forEach(id => {
                const el = document.getElementById(id);

                if (el) {
                    new MutationObserver(refreshTemplateSrc).observe(el, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['src']
                    });
                }
            });

            function syncHiddenInput(sceneId) {
                const canvas = document.querySelector(`.spe-canvas[data-scene-id="${sceneId}"]`);

                if (!canvas) return;

                const box = getBox(canvas, sceneId);

                window.tableauScenePositions = window.tableauScenePositions || {};
                window.tableauScenePositions[sceneId] = {
                    positions: {
                        top: box.top,
                        right: box.right,
                        left: box.left,
                        bottom: box.bottom,
                        width: box.width,
                        height: box.height
                    }
                };

                writeTableauScenePositionsInput();
            }

            function writeTableauScenePositionsInput() {
                const input = document.getElementById('tableauScenePositionsInput');

                if (!input) return;

                input.value = JSON.stringify(window.tableauScenePositions || {});
            }

            function updateCoordsDisplay(sceneId) {
                const id = safeId(sceneId);
                const canvas = document.querySelector(`.spe-canvas[data-scene-id="${sceneId}"]`);

                if (!canvas) return;

                const box = getBox(canvas, sceneId);
                const dimension = getSelectedDimension();

                ['top', 'right', 'left', 'bottom', 'width', 'height'].forEach(axis => {
                    const textEl = document.getElementById(`spe-${axis}-${id}`);

                    if (textEl) {
                        textEl.textContent = box[axis] + '%';
                    }
                });

                const widthInput = document.getElementById(`spe-input-width-${id}`);

                if (widthInput && document.activeElement !== widthInput) {
                    widthInput.value = box.width;
                }

                const dimEl = document.getElementById(`spe-dim-${id}`);

                if (dimEl) {
                    dimEl.textContent = `Template canvas ratio: ${dimension.width} × ${dimension.height}`;
                }

                syncHiddenInput(sceneId);
            }

            function renderDraggable(canvas, sceneId) {
                canvas.querySelectorAll('.spe-dragger').forEach(el => el.remove());

                const box = getBox(canvas, sceneId);

                const dragger = document.createElement('div');
                dragger.className = 'spe-dragger';
                dragger.style.cssText = `
                            position:absolute;
                            width:${box.width}%;
                            aspect-ratio:${box.aspect};
                            top:${box.top}%;
                            left:${box.left}%;
                            cursor:grab;
                            touch-action:none;
                            border:2px dashed rgba(255,255,255,.9);
                            border-radius:4px;
                            box-sizing:border-box;
                            overflow:visible;
                            user-select:none;
                            z-index:5;
                        `;

                if (templateSrc) {
                    const img = document.createElement('img');
                    img.src = templateSrc;
                    img.draggable = false;
                    img.style.cssText = `
                            width:100%;
                            height:100%;
                            display:block;
                            object-fit:contain;
                            opacity:1;
                            filter:none;
                            pointer-events:none;
                            user-select:none;
                        `;
                    dragger.appendChild(img);
                } else {
                    dragger.textContent = 'Canvas';
                    dragger.style.background = 'rgba(36,176,148,.18)';
                    dragger.style.borderColor = '#24B094';
                    dragger.style.display = 'flex';
                    dragger.style.alignItems = 'center';
                    dragger.style.justifyContent = 'center';
                    dragger.style.fontSize = '11px';
                    dragger.style.color = '#24B094';
                }

                const resizeHandle = document.createElement('span');
                resizeHandle.className = 'spe-resize-handle';
                resizeHandle.title = 'Resize design';
                resizeHandle.style.cssText = `
                            position:absolute;
                            right:-8px;
                            bottom:-8px;
                            width:16px;
                            height:16px;
                            border-radius:50%;
                            background:#24B094;
                            border:2px solid #fff;
                            box-shadow:0 1px 4px rgba(0,0,0,.25);
                            cursor:nwse-resize;
                            touch-action:none;
                            z-index:10;
                        `;

                dragger.appendChild(resizeHandle);

                canvas.appendChild(dragger);
                attachDrag(dragger, canvas, sceneId);
                attachResize(resizeHandle, dragger, canvas, sceneId);
                updateCoordsDisplay(sceneId);
            }

            function attachResize(handle, el, canvas, sceneId) {
                let resizing = false;
                let startX = 0;
                let startY = 0;
                let startWidth = 0;

                function xy(e) {
                    return e.touches
                        ? {x: e.touches[0].clientX, y: e.touches[0].clientY}
                        : {x: e.clientX, y: e.clientY};
                }

                function onStart(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    resizing = true;

                    const point = xy(e);
                    const box = getBox(canvas, sceneId);

                    startX = point.x;
                    startY = point.y;
                    startWidth = box.width;

                    el.style.cursor = 'nwse-resize';
                }

                function onMove(e) {
                    if (!resizing) return;

                    e.preventDefault();

                    const point = xy(e);
                    const cRect = canvas.getBoundingClientRect();
                    const canvasAspect = getCanvasAspect(canvas);
                    const overlayAspect = getOverlaySize(canvas, sceneId).aspect || 1;

                    const deltaWidthFromX = ((point.x - startX) / cRect.width) * 100;
                    const deltaWidthFromY = ((point.y - startY) / cRect.height) * 100 * (overlayAspect / canvasAspect);
                    const deltaWidth = Math.abs(deltaWidthFromX) >= Math.abs(deltaWidthFromY)
                        ? deltaWidthFromX
                        : deltaWidthFromY;

                    const state = ensureState(sceneId);
                    state.width = clampOverlayWidth(canvas, sceneId, startWidth + deltaWidth);

                    const clamped = clampPosition(canvas, sceneId, state.top, state.left);
                    state.top = clamped.top;
                    state.left = clamped.left;

                    const box = getBox(canvas, sceneId);

                    el.style.width = box.width + '%';
                    el.style.aspectRatio = String(box.aspect);
                    el.style.top = box.top + '%';
                    el.style.left = box.left + '%';

                    updateCoordsDisplay(sceneId);
                }

                function onEnd() {
                    if (!resizing) return;

                    resizing = false;
                    el.style.cursor = 'grab';
                    updateCoordsDisplay(sceneId);
                }

                handle.addEventListener('mousedown', onStart);
                handle.addEventListener('touchstart', onStart, {passive: false});

                window.addEventListener('mousemove', onMove);
                window.addEventListener('touchmove', onMove, {passive: false});

                window.addEventListener('mouseup', onEnd);
                window.addEventListener('touchend', onEnd);
            }
            function refreshAllDraggables() {
                document.querySelectorAll('.spe-canvas').forEach(canvas => {
                    renderDraggable(canvas, canvas.dataset.sceneId);
                });
            }

            function attachDrag(el, canvas, sceneId) {
                let dragging = false;
                let startX = 0;
                let startY = 0;
                let startTop = 0;
                let startLeft = 0;

                function xy(e) {
                    return e.touches
                        ? {x: e.touches[0].clientX, y: e.touches[0].clientY}
                        : {x: e.clientX, y: e.clientY};
                }

                function onStart(e) {
                    if (e.target?.closest?.('.spe-resize-handle')) {
                        return;
                    }

                    e.preventDefault();

                    dragging = true;

                    const point = xy(e);
                    const cRect = canvas.getBoundingClientRect();
                    const elRect = el.getBoundingClientRect();

                    startX = point.x;
                    startY = point.y;
                    startTop = ((elRect.top - cRect.top) / cRect.height) * 100;
                    startLeft = ((elRect.left - cRect.left) / cRect.width) * 100;

                    el.style.cursor = 'grabbing';
                }

                function onMove(e) {
                    if (!dragging) return;

                    e.preventDefault();

                    const point = xy(e);
                    const cRect = canvas.getBoundingClientRect();

                    const newTop = startTop + ((point.y - startY) / cRect.height) * 100;
                    const newLeft = startLeft + ((point.x - startX) / cRect.width) * 100;
                    const clamped = clampPosition(canvas, sceneId, newTop, newLeft);

                    positionState[sceneId].top = clamped.top;
                    positionState[sceneId].left = clamped.left;

                    el.style.top = clamped.top + '%';
                    el.style.left = clamped.left + '%';

                    updateCoordsDisplay(sceneId);
                }

                function onEnd() {
                    dragging = false;
                    el.style.cursor = 'grab';
                }

                el.addEventListener('mousedown', onStart);
                el.addEventListener('touchstart', onStart, {passive: false});

                window.addEventListener('mousemove', onMove);
                window.addEventListener('touchmove', onMove, {passive: false});

                window.addEventListener('mouseup', onEnd);
                window.addEventListener('touchend', onEnd);
            }

            function applyManualInput(sceneId, axis) {
                const id = safeId(sceneId);
                const canvas = document.querySelector(`.spe-canvas[data-scene-id="${sceneId}"]`);

                if (!canvas) return;

                const state = ensureState(sceneId);
                const current = getBox(canvas, sceneId);

                if (axis === 'width') {
                    const width = num(document.getElementById(`spe-input-width-${id}`)?.value, current.width);
                    state.width = clampOverlayWidth(canvas, sceneId, width);

                    const clamped = clampPosition(canvas, sceneId, state.top, state.left);
                    state.top = clamped.top;
                    state.left = clamped.left;
                } else {
                    let top = num(document.getElementById(`spe-input-top-${id}`)?.value, current.top);
                    let left = num(document.getElementById(`spe-input-left-${id}`)?.value, current.left);
                    const right = num(document.getElementById(`spe-input-right-${id}`)?.value, current.right);
                    const bottom = num(document.getElementById(`spe-input-bottom-${id}`)?.value, current.bottom);

                    if (axis === 'right') {
                        left = 100 - right - current.width;
                    }

                    if (axis === 'bottom') {
                        top = 100 - bottom - current.height;
                    }

                    const clamped = clampPosition(canvas, sceneId, top, left);

                    state.top = clamped.top;
                    state.left = clamped.left;
                }

                const box = getBox(canvas, sceneId);
                const dragger = canvas.querySelector('.spe-dragger');

                if (dragger) {
                    dragger.style.top = box.top + '%';
                    dragger.style.left = box.left + '%';
                    dragger.style.width = box.width + '%';
                    dragger.style.aspectRatio = String(box.aspect);
                }

                updateCoordsDisplay(sceneId);
            }

            function buildPanel(sceneId, label, imageUrl) {
                const id = safeId(sceneId);
                console.log('buildPanel', sceneId, imageUrl);
                ensureState(sceneId);

                const panel = document.createElement('div');
                panel.className = 'spe-panel';
                panel.id = `spe-panel-${id}`;
                panel.style.display = 'none';

                const canvasWrap = document.createElement('div');
                canvasWrap.className = 'spe-canvas';
                canvasWrap.dataset.sceneId = sceneId;
                canvasWrap.style.cssText = `
                        position:relative;
                        width:100%;
                        max-width:100%;
                        aspect-ratio:4/3;
                        border-radius:8px;
                        overflow:hidden;
                        border:1px solid #dee2e6;
                        background:#fff;
                        user-select:none;
                    `;

                if (imageUrl) {
                    const bg = document.createElement('img');
                    bg.src = imageUrl;
                    bg.style.cssText = `
                            position:absolute;
                            inset:0;
                            width:100%;
                            height:100%;
                            object-fit:contain;
                            opacity:1;
                            filter:none;
                            image-rendering:auto;
                            pointer-events:none;
                        `;

                    bg.onload = function () {
                        if (bg.naturalWidth && bg.naturalHeight) {
                            canvasWrap.style.aspectRatio = `${bg.naturalWidth} / ${bg.naturalHeight}`;
                            refreshAllDraggables();
                        }
                    };

                    canvasWrap.appendChild(bg);
                } else {
                    const placeholder = document.createElement('div');
                    placeholder.style.cssText = `
                            position:absolute;
                            inset:0;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            color:#adb5bd;
                            font-size:13px;
                            flex-direction:column;
                            gap:6px;
                        `;
                    placeholder.innerHTML = `
                            <i data-feather="image" style="width:32px;height:32px;stroke:#adb5bd"></i>
                            <span>Scene image</span>
                        `;
                    canvasWrap.appendChild(placeholder);
                }

                const controls = document.createElement('div');
                controls.className = 'd-flex flex-wrap align-items-center gap-1 mt-2';
                controls.innerHTML = `
                        <span class="badge bg-light text-dark border">Top: <strong id="spe-top-${id}">0%</strong></span>
                        <span class="badge bg-light text-dark border">Right: <strong id="spe-right-${id}">0%</strong></span>
                        <span class="badge bg-light text-dark border">Left: <strong id="spe-left-${id}">0%</strong></span>
                        <span class="badge bg-light text-dark border">Bottom: <strong id="spe-bottom-${id}">0%</strong></span>
                        <span class="badge bg-light text-dark border">Width: <strong id="spe-width-${id}">0%</strong></span>
                        <span class="badge bg-light text-dark border">Height: <strong id="spe-height-${id}">0%</strong></span>
                        <label class="d-flex align-items-center gap-50 mb-0 ms-1" style="font-size:12px;">
                            Width %
                            <input type="number"
                                   min="${MIN_OVERLAY_WIDTH}"
                                   max="${MAX_OVERLAY_WIDTH}"
                                   step="0.1"
                                   id="spe-input-width-${id}"
                                   class="form-control form-control-sm"
                                   style="width:90px;">
                        </label>
                        <small class="text-muted ms-1" id="spe-dim-${id}"></small>
                    `;

                controls
                    .querySelector(`#spe-input-width-${id}`)
                    ?.addEventListener('input', () => applyManualInput(sceneId, 'width'));

                panel.appendChild(canvasWrap);
                panel.appendChild(controls);

                setTimeout(() => {
                    updateCoordsDisplay(sceneId);
                }, 0);

                return panel;
            }

            function buildTab(sceneId, label) {
                const id = safeId(sceneId);
                const btn = document.createElement('button');

                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-secondary spe-tab';
                btn.dataset.sceneId = String(sceneId);
                btn.dataset.panelId = id;
                btn.textContent = label;
                btn.addEventListener('click', () => activateTab(sceneId));

                return btn;
            }

            function activateTab(sceneId) {
                const id = safeId(sceneId);

                document.querySelectorAll('.spe-tab').forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.sceneId === String(sceneId));
                });

                document.querySelectorAll('.spe-panel').forEach(panel => {
                    panel.style.display = panel.id === `spe-panel-${id}` ? 'block' : 'none';
                });

                refreshAllDraggables();
            }

            function rebuildEditor(scenes) {
                const tabsEl = document.getElementById('scenePosTabs');
                const panelsEl = document.getElementById('scenePosPanels');
                const wrapper = document.getElementById('dz-scene-position-editor');

                if (!tabsEl || !panelsEl || !wrapper) return;

                tabsEl.innerHTML = '';
                panelsEl.innerHTML = '';

                if (!scenes.length) {
                    wrapper.classList.add('d-none');
                    return;
                }

                wrapper.classList.remove('d-none');

                scenes.forEach(scene => {
                    tabsEl.appendChild(buildTab(scene.id, scene.label));
                    panelsEl.appendChild(buildPanel(scene.id, scene.label, scene.imageUrl || ''));
                });

                refreshTemplateSrc();

                scenes.forEach(scene => {
                    const canvas = document.querySelector(`.spe-canvas[data-scene-id="${scene.id}"]`);

                    if (canvas) {
                        renderDraggable(canvas, scene.id);
                    }
                });

                activateTab(scenes[0].id);

                if (window.feather) {
                    feather.replace();
                }
            }

            function collectScenes() {
                const scenes = [];
                const $select = $('#tableauSceneSelect');

                ($select.val() || []).forEach(id => {
                    const $option = $select.find(`option[value="${id}"]`);

                    const imageUrl =
                        $option.attr('data-image-url') ||
                        $option.data('image-url') ||
                        '';

                    const imageId =
                        $option.attr('data-image-id') ||
                        $option.data('image-id') ||
                        '';

                    const label =
                        $option.attr('data-label') ||
                        $option.attr('data-name') ||
                        $option.text().trim() ||
                        `Scene #${id}`;

                    scenes.push({
                        id,
                        label,
                        imageUrl,
                        imageId
                    });
                });

                return scenes;
            }            function syncAllScenePositionInputs() {
                let count = 0;
                const activeIds = [];

                document.querySelectorAll('.spe-canvas[data-scene-id]').forEach(canvas => {
                    const sceneId = canvas.dataset.sceneId;
                    syncHiddenInput(sceneId);
                    activeIds.push(String(sceneId));
                    count++;
                });

                if (window.tableauScenePositions) {
                    Object.keys(window.tableauScenePositions).forEach(id => {
                        if (!activeIds.includes(String(id))) {
                            delete window.tableauScenePositions[id];
                        }
                    });
                }

                writeTableauScenePositionsInput();

                return count;
            }

            window.syncTableauScenePositions = syncAllScenePositionInputs;

            function ensureTemplateModelInput() {
                let input = document.getElementById('uploadedTemplateImage');

                if (input) {
                    input.name = 'template_image_id';
                    return input;
                }

                const form = document.getElementById('editTemplateForm');

                if (!form) {
                    return null;
                }

                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'template_image_id';
                input.id = 'uploadedTemplateImage';
                form.appendChild(input);

                return input;
            }

            function getLatestSceneCanvas() {
                const activeTab = document.querySelector('.spe-tab.active');

                if (activeTab?.dataset?.sceneId) {
                    const activeCanvas = document.querySelector(`.spe-canvas[data-scene-id="${activeTab.dataset.sceneId}"]`);

                    if (activeCanvas) {
                        return activeCanvas;
                    }
                }

                const canvases = Array.from(document.querySelectorAll('.spe-canvas[data-scene-id]'));

                return canvases.length ? canvases[canvases.length - 1] : null;
            }

            function loadImageForExport(src) {
                return new Promise((resolve, reject) => {
                    if (!src) {
                        reject(new Error('Missing image source.'));
                        return;
                    }

                    const img = new Image();

                    if (!src.startsWith('data:') && !src.startsWith('blob:')) {
                        img.crossOrigin = 'anonymous';
                    }

                    img.onload = () => resolve(img);
                    img.onerror = () => reject(new Error('Failed to load image for export.'));
                    img.src = src;
                });
            }

            function canvasToBlob(canvas, type = 'image/png', quality = 0.95) {
                return new Promise((resolve, reject) => {
                    canvas.toBlob(blob => {
                        if (blob) {
                            resolve(blob);
                        } else {
                            reject(new Error('Failed to create image blob.'));
                        }
                    }, type, quality);
                });
            }

            async function renderLatestSceneToBlob() {
                const editorCanvas = getLatestSceneCanvas();

                if (!editorCanvas) {
                    throw new Error('Please select or create a scene first.');
                }

                if (!templateSrc) {
                    refreshTemplateSrc();
                }

                const sceneImgEl = editorCanvas.querySelector('img:not(.spe-dragger)');
                const sceneSrc = sceneImgEl?.currentSrc || sceneImgEl?.src || '';
                const overlaySrc = templateSrc || document.querySelector('#front-template-dropzone .dz-image img, #front-template-dropzone .dz-preview img')?.src || '';

                if (!sceneSrc) {
                    throw new Error('Scene image is missing.');
                }

                if (!overlaySrc) {
                    throw new Error('Front template image is missing.');
                }

                const [sceneImg, overlayImg] = await Promise.all([
                    loadImageForExport(sceneSrc),
                    loadImageForExport(overlaySrc)
                ]);

                const maxSide = 1600;
                const naturalWidth = sceneImg.naturalWidth || 1200;
                const naturalHeight = sceneImg.naturalHeight || 900;
                const ratio = Math.min(1, maxSide / Math.max(naturalWidth, naturalHeight));
                const outputWidth = Math.max(1, Math.round(naturalWidth * ratio));
                const outputHeight = Math.max(1, Math.round(naturalHeight * ratio));

                const exportCanvas = document.createElement('canvas');
                exportCanvas.width = outputWidth;
                exportCanvas.height = outputHeight;

                const ctx = exportCanvas.getContext('2d');

                ctx.clearRect(0, 0, outputWidth, outputHeight);
                ctx.drawImage(sceneImg, 0, 0, outputWidth, outputHeight);

                const box = getBox(editorCanvas, editorCanvas.dataset.sceneId);
                const x = (box.left / 100) * outputWidth;
                const y = (box.top / 100) * outputHeight;
                const w = (box.width / 100) * outputWidth;
                const h = (box.height / 100) * outputHeight;

                ctx.drawImage(overlayImg, x, y, w, h);

                return canvasToBlob(exportCanvas, 'image/png');
            }

            async function uploadLatestSceneToTemplateModelImage() {
                const blob = await renderLatestSceneToBlob();

                const formData = new FormData();
                formData.append('file', new File([blob], `tableau-template-model-${Date.now()}.png`, {
                    type: 'image/png'
                }));

                const response = await fetch("{{ route('media.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}"
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                let json = {};

                try {
                    json = await response.json();
                } catch (error) {
                    // Keep json empty and use the generic error below.
                }

                const mediaId =
                    json?.data?.id ||
                    json?.id ||
                    json?.media?.id ||
                    json?.file?.id;

                if (!response.ok || !mediaId) {
                    throw new Error(json?.message || 'Failed to upload latest scene as template model image.');
                }

                const input = ensureTemplateModelInput();

                if (!input) {
                    throw new Error('template_image_id input was not found.');
                }

                input.value = mediaId;

                window.latestTableauTemplateModelMediaId = mediaId;

                return mediaId;
            }

            window.uploadLatestSceneToTemplateModelImage = uploadLatestSceneToTemplateModelImage;

            $(document).on('click', '#saveScenePositionsBtn', async function () {
                const button = this;
                const count = syncAllScenePositionInputs();

                if (!count) {
                    notifyTableau('Please select or create a scene first.', 'error');
                    return;
                }

                button.disabled = true;
                const oldText = button.textContent;
                button.textContent = 'Saving...';

                try {
                    const mediaId = await uploadLatestSceneToTemplateModelImage();

                    notifyTableau('Scene positions saved and latest scene uploaded as Template Model Image.');

                    console.log('Template model image media id:', mediaId);
                } catch (error) {
                    console.error(error);
                    notifyTableau(error.message || 'Failed to save latest scene image.', 'error');
                } finally {
                    button.disabled = false;
                    button.textContent = oldText;
                }
            });

            function triggerRebuild() {
                rebuildEditor(collectScenes());
            }

            window.triggerTableauScenePositionRebuild = triggerRebuild;

            $(document).on('change', '#tableauSceneSelect', triggerRebuild);

            $(document).on('change', '#sizesSelect', function () {
                refreshAllDraggables();
            });

            const tableauDzEl = document.getElementById('tableau-scene-dropzone');

            if (tableauDzEl && tableauDzEl.dropzone) {
                tableauDzEl.dropzone.on('success', () => setTimeout(triggerRebuild, 200));
                tableauDzEl.dropzone.on('removedfile', () => setTimeout(triggerRebuild, 200));
            }

            $(document).on('change', '#productsWithoutCategoriesSelect, #productsSelect, #categoriesSelect', function () {
                setTimeout(triggerRebuild, 300);
            });

            // Build immediately on page load from already-selected scenes,
            // retrying briefly until select2 has applied server-rendered selections
            $(document).ready(function () {
                let attempts = 0;

                function attemptRebuild() {
                    const hasSelection = ($('#tableauSceneSelect').val() || []).length > 0;

                    if (hasSelection || attempts > 10) {
                        triggerRebuild();
                        return;
                    }

                    attempts++;
                    setTimeout(attemptRebuild, 150);
                }

                setTimeout(attemptRebuild, 150);
            });

            // Re-sync positions right before the edit form submits
            $('#editTemplateForm').on('submit', function () {
                syncAllScenePositionInputs();
            });
        })();
    </script>


@endsection
