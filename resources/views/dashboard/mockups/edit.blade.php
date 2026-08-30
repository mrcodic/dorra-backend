@extends('layouts/contentLayoutMaster')

@section('title', 'Edit Mockup')
@section('main-page', 'Edit Mockup')

@section('vendor-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
    <style>
        .small-badge {
            font-size: 10px;
            font-weight: 500;
            padding: 3px 6px;
            border-radius: 999px;
            line-height: 1.2;
        }
    </style>
    <style>
        .gradient-picker-trigger {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-image: url('/images/AddColor.svg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border: 1px solid #ccc;
            cursor: pointer;
            position: relative;
        }

        .gradient-picker-trigger .pcr-button {
            display: none !important;
        }

        .selected-color-wrapper {
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

        .gradient-edit-picker-trigger {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-image: url('/images/AddColor.svg') !important;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border: 1px solid #ccc;
            cursor: pointer;
            position: relative;
        }

        .gradient-edit-picker-trigger .pcr-button {
            display: none !important;
        }


        .color-settings-wrap {
            background: #fff;
            border-radius: 16px;
            padding: 18px 16px 14px;
            margin-top: 8px;
        }

        .color-settings-title {
            font-size: 20px;
            font-weight: 600;
            color: #1d1d1f;
            margin-bottom: 6px;
        }

        .color-settings-description {
            font-size: 12px;
            color: #686868;
            margin-bottom: 16px;
        }

        .color-settings-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .palette-card {
            min-height: 198px;
            border-radius: 22px;
            padding: 16px 20px 14px;
            display: flex;
            flex-direction: column;
        }

        .palette-card--base {
            border: 1px solid #24B094;
            background: #fbfffe;
        }

        .palette-card--across {
            border: 1px solid #6f3aa6;
            background: #fffafe;
        }

        .palette-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .palette-card-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
        }

        .palette-card-icon {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            line-height: 1;
        }

        .palette-card--base .palette-card-icon {
            color: #0a9e83;
            background: #e3f7f2;
        }

        .palette-card--across .palette-card-icon {
            color: #643091;
            background: #f2e5f7;
        }

        .palette-card-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e1e1e;
            margin: 2px 0 4px;
        }

        .palette-card-copy {
            font-size: 11px;
            color: #626262;
            margin: 0;
            line-height: 1.45;
        }

        .palette-badge {
            flex: 0 0 auto;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }

        .palette-card--base .palette-badge {
            color: #24B094;
            background: #e2f6f1;
        }

        .palette-card--across .palette-badge {
            color: #6f3aa6;
            background: #f2e4f6;
        }

        .palette-note {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            margin-top: 14px;
            font-size: 10px;
            line-height: 1.2;
        }

        .palette-card--base .palette-note {
            background: #e5f7f3;
            color: #128b75;
        }

        .palette-card--across .palette-note {
            background: #f3e8f7;
            color: #66308d;
        }

        .palette-note-check {
            font-size: 16px;
            font-weight: 700;
        }

        .palette-colors-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 18px;
            min-height: 30px;
        }

        .palette-card .selected-color-wrapper {
            width: 24px;
            height: 24px;
        }

        .palette-card .selected-color-dot {
            padding: 0;
            border: 0;
            background: transparent !important;
        }

        .palette-card .selected-color-inner {
            border: 0;
        }

        .palette-card .gradient-picker-trigger {
            width: 34px;
            height: 34px;
            min-width: 24px;

            box-shadow: none;
        }

        .palette-source-color {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 0;
            padding: 0;
            cursor: pointer;
            position: relative;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }

        .palette-source-color:hover {
            transform: translateY(-1px);
        }

        .palette-source-color.is-selected {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #6f3aa6;
            opacity: 1;
        }

        .palette-source-color:not(.is-selected) {
            opacity: .7;
        }

        .palette-source-color-check {
            position: absolute;
            right: -4px;
            top: -6px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: #6f3aa6;
            color: #fff;
            font-size: 9px;
            line-height: 13px;
            text-align: center;
            font-weight: 700;
        }

        .palette-footer {
            margin-top: auto;
            padding-top: 13px;
            font-size: 9px;
            color: #a0a0a0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .palette-footer-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex: 0 0 7px;
        }

        .palette-card--base .palette-footer-dot {
            background: #24B094;
        }

        .palette-card--across .palette-footer-dot {
            background: #6f3aa6;
        }

        #selected-colors,
        #mockupColorsAcrossOptions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        #selected-colors-across-templates {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        @media (max-width: 991.98px) {
            .color-settings-grid {
                grid-template-columns: 1fr;
            }
        }

        /* كل بلوك ياخد سطر كامل ويتكدس عموديًا */
        .type-block {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box;
            margin-bottom: .75rem;
        }

        /* لو الـ inner d-flex موجود داخل البلوك فهو هعرض Base | Mask جنب بعض */
        .type-block > .d-flex {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        /* تأكد أن الحاوية اليسرى عمودية */
        #left-column {
            display: flex !important;
            flex-direction: column !important;
            gap: .75rem;
        }

        /* لو محتاج تجاويف داخل البلوكات */
        .upload-card {
            box-sizing: border-box;
        }

        /* show more button animation */
        :root {
            --anim-duration: 300ms;
            --anim-ease: cubic-bezier(.2, .9, .3, 1);
        }

        .show-more:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: transform 180ms var(--anim-ease), box-shadow 180ms var(--anim-ease);
        }
    </style>
    <style>

        #generationProgressModal .modal-dialog {
            max-width: 440px;
            padding: 18px;
        }

        #generationProgressModal .modal-content {
            border: 0;
            border-radius: 28px !important;
            overflow: hidden;
            box-shadow: 0 22px 70px rgba(15, 23, 42, .22) !important;
        }

        .generation-progress-card {
            position: relative;
            padding: 24px 24px 20px;
            text-align: center;
            background: #fff;
        }

        .generation-minimize-btn {
            position: absolute;
            top: 17px;
            right: 17px;
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 50%;
            background: #f7f8fa;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
        }

        .generation-spinner-shell {
            width: 58px;
            height: 58px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #e9f9f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .generation-spinner-ring {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(36, 176, 148, .25);
            border-top-color: #24b094;
            border-radius: 50%;
            animation: generationSpin .8s linear infinite;
        }

        @keyframes generationSpin {
            to { transform: rotate(360deg); }
        }

        .generation-title {
            margin: 0;
            color: #172033;
            font-size: 21px;
            font-weight: 700;
        }

        .generation-copy {
            max-width: 330px;
            margin: 10px auto 24px;
            color: #7b8497;
            font-size: 13px;
            line-height: 1.65;
        }

        .generation-progress-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #596273;
            font-size: 13px;
            font-weight: 600;
        }

        .generation-progress-track {
            height: 10px;
            overflow: hidden;
            border-radius: 999px;
            background: #f1f3f6;
        }

        #generationProgressBar {
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: #24b094;
            transition: width .35s ease;
        }

        .generation-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 22px;
        }

        .generation-stat {
            border-radius: 16px;
            padding: 15px 8px 12px;
            background: #f8f9fb;
        }

        .generation-stat.done { background: #eafaf4; }
        .generation-stat.failed { background: #fff0f0; }

        .generation-stat-value {
            display: block;
            color: #172033;
            font-size: 19px;
            font-weight: 700;
            line-height: 1;
        }

        .generation-stat.done .generation-stat-value { color: #0c9d79; }
        .generation-stat.failed .generation-stat-value { color: #ef4444; }

        .generation-stat-label {
            display: block;
            margin-top: 7px;
            color: #a0a8b7;
            font-size: 10px;
            font-weight: 600;
        }

        .generation-remaining {
            min-height: 18px;
            margin: 14px 0 4px;
            color: #8b94a5;
            font-size: 11px;
        }

        .generation-action-btn {
            width: 100%;
            min-height: 44px;
            border: 0;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .generation-background-btn {
            margin-top: 16px;
            background: #24b094;
            color: #fff;
        }

        .generation-background-btn:hover {
            background: #1d9f86;
            color: #fff;
        }

        .generation-cancel-btn {
            margin-top: 10px;
            border: 1px solid #ffcaca;
            background: #fff4f4;
            color: #ef4444;
        }

        .generation-cancel-btn:hover {
            background: #ffeaea;
            color: #dc2626;
        }

        .generation-close-btn {
            margin-top: 16px;
            background: #172033;
            color: #fff;
        }

        .generation-hint {
            margin: 12px 0 0;
            color: #a4acba;
            font-size: 10px;
            font-weight: 500;
        }

        #generationProgressError {
            margin-top: 14px;
            border: 0;
            border-radius: 12px;
            font-size: 11px;
        }

        #generationFloatingStatus {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1055;
            border: 0;
            border-radius: 999px;
            padding: 11px 16px;
            background: #172033;
            color: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .2);
            font-size: 12px;
            font-weight: 700;
        }

        #generationFloatingStatus .floating-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 7px;
            border-radius: 50%;
            background: #24b094;
        }
    </style>
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection
@php
    $mediaCollection = $model->getMedia('mockups');

    $existingMedia = [
        'front' => [
            'base_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'base')->first()?->getFullUrl(),
            'mask_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'mask')->first()?->getFullUrl(),
            'shadow_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'shadow')->first()?->getFullUrl(),
            'displacement_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'displacement')->first()?->getFullUrl(),
            'light_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'light')->first()?->getFullUrl(),
        ],
        'back' => [
            'base_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'base')->first()?->getFullUrl(),
            'mask_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'mask')->first()?->getFullUrl(),
            'shadow_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'shadow')->first()?->getFullUrl(),
            'displacement_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'displacement')->first()?->getFullUrl(),
            'light_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'light')->first()?->getFullUrl(),
        ],
        'none' => [
            'base_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'base')->first()?->getFullUrl(),
            'mask_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'mask')->first()?->getFullUrl(),
            'shadow_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'shadow')->first()?->getFullUrl(),
            'displacement_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'displacement')->first()?->getFullUrl(),
            'light_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'light')->first()?->getFullUrl(),
        ],
    ];

    $existingMediaIds = [
        'front' => [
            'base_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'base')->first()?->id,
            'mask_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'mask')->first()?->id,
            'shadow_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'shadow')->first()?->id,
            'displacement_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'displacement')->first()?->id,
            'light_image' => $mediaCollection->where('custom_properties.side', 'front')->where('custom_properties.role', 'light')->first()?->id,
        ],
        'back' => [
            'base_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'base')->first()?->id,
            'mask_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'mask')->first()?->id,
            'shadow_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'shadow')->first()?->id,
            'displacement_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'displacement')->first()?->id,
            'light_image' => $mediaCollection->where('custom_properties.side', 'back')->where('custom_properties.role', 'light')->first()?->id,
        ],
        'none' => [
            'base_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'base')->first()?->id,
            'mask_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'mask')->first()?->id,
            'shadow_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'shadow')->first()?->id,
            'displacement_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'displacement')->first()?->id,
            'light_image' => $mediaCollection->where('custom_properties.side', 'none')->where('custom_properties.role', 'light')->first()?->id,
        ],
    ];

    $existingWarpPoints = [
        'front' => $model->sideSettings->firstWhere('side', 'front')?->warp_points ?? null,
        'back'  => $model->sideSettings->firstWhere('side', 'back')?->warp_points  ?? null,
        'none'  => $model->sideSettings->firstWhere('side', 'none')?->warp_points  ?? null,
    ];
@endphp
@section('content')
    <!-- users list start -->
    <section class="">
        <div class="card">
            <div class="card-body">
                <form id="editMockupForm" enctype="multipart/form-data" action="{{ route('mockups.update',$model->id) }}">
                    @csrf
                    @method('PUT')
                    {{--                <input type="hidden" name="approach" value="{{ $model->approach }}">--}}
                    <div class="modal-body flex-grow-1">
                        <div class="position-relative text-center mb-2">
                            <hr class="opacity-75" style="border: 1px solid #24B094;">
                            <span
                                class="position-absolute top-50 start-50 translate-middle px-1 bg-white fs-4 d-none d-md-flex"
                                style="color: #24B094">
                            Mockup Details
                        </span>
                        </div>
                        <div class="row">
                            <div class="row">
                                <div class="form-group mb-2 col-md-12">
                                    <label for="mockupName" class="label-text mb-1">Mockup Name</label>
                                    <input type="text" id="templateName" class="form-control" name="name"
                                           placeholder="Mockup Name" value="{{ $model->name }}">
                                </div>
                            </div>
                            <div class="row">

                                <div class="form-group mb-2 col-6">
                                    <label for="productsSelect" class="label-text mb-1">Product</label>
                                    <select id="productsSelect" name="category_id" class="form-select">
                                        <option value="" disabled selected>Choose product</option>
                                        @foreach($associatedData['products'] as $product)
                                            <option value="{{ $product->id }}" @selected($product->id == $model->category_id)>
                                                {{ $product->getTranslation('name', app()->getLocale()) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2 col-6">
                                    <label for="categoriesSelect" class="label-text mb-1">Categories</label>
                                    <select id="categoriesSelect" name="product_ids[]" class="form-select" multiple>
                                        <option value="" disabled selected>Choose category</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group mb-2 col-md-3">
                                    <label for="fillRatio" class="label-text mb-1">Fill Ratio</label>

                                    <input
                                        type="number"
                                        id="fillRatio"
                                        class="form-control"
                                        name="fill_ratio"
                                        placeholder="ex: 70"
                                        value="{{ old('fill_ratio', $model->fill_ratio) }}"
                                    >
                                    <small class="form-text text-muted">
                                        ex:  t-shirt: 70
                                    </small>
                                </div>

                                <div class="form-group mb-2 col-md-3">
                                    <label for="light_strength" class="label-text mb-1">Light Strength</label>

                                    <input
                                        type="number"
                                        id="light_strength"
                                        class="form-control"
                                        name="light_strength"
                                        placeholder="ex: 40"
                                        value="{{ old('light_strength', $model->light_strength) }}"
                                    >
                                    <small class="form-text text-muted">
                                        ex:  t-shirt: 35 ,scarf: 35-45
                                    </small>
                                </div>

                                <div class="form-group mb-2 col-md-3">
                                    <label for="shadow_strength" class="label-text mb-1">Shadow Strength</label>

                                    <input
                                        type="number"
                                        id="shadow_strength"
                                        class="form-control"
                                        name="shadow_strength"
                                        placeholder="ex: 60"
                                        value="{{ old('shadow_strength', $model->shadow_strength) }}"
                                    >
                                    <small class="form-text text-muted">
                                        ex:  t-shirt: 45% , scarf: 55-65%
                                    </small>
                                </div>

                                <div class="form-group mb-2 col-md-3">
                                    <label for="displacement_scale" class="label-text mb-1">Displacement Scale</label>

                                    <input
                                        type="number"
                                        id="displacement_scale"
                                        class="form-control"
                                        name="displacement_scale"
                                        placeholder="ex: 15"
                                        value="{{ old('displacement_scale', $model->displacement_scale) }}"
                                    >
                                    <small class="form-text text-muted">
                                        ex:  t-shirt: 8-10 ,scarf: 12-18
                                    </small>
                                </div>
                            </div>
                            <div class="form-group mb-2 col-md-12">
                                <div class="row">
                                    @foreach($associatedData['types'] as $type)
                                        <div class="col-md-4 mb-1">
                                            <label class="radio-box">
                                                <input class="form-check-input type-checkbox" type="checkbox" name="types[]"
                                                       value="{{ $type->value }}" @checked($model->types->contains($type))
                                                       data-type-name="{{ strtolower($type->value->name) }}">
                                                <span>{{ $type->value->label() }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            {{-- mockup Canvas --}}
                            <!-- العمود الشمال: يحتوي fixed-block + fileInputsContainer (البلوكات تتحط هنا) -->
                            <div class="row">
                                <div id="left-column" class="col-md-12">
                                    <!-- fixed-block يبقى مكان الإشارة لفانكشنك -->
                                    <div id="fixed-block"></div>

                                    <!-- الحاوية اللى بتضيف لها الفانكشن البلوكات (لو مش موجودة بالفعل) -->
                                    <div id="fileInputsContainer" class="row g-1"></div>
                                </div>
                            </div>
                        </div>


                        <div class="color-settings-wrap mb-2">
                            <div class="color-settings-title">Color settings</div>
                            <div class="color-settings-description">Keep the colors used automatically by the mockup separate from the optional colors used across templates.</div>
                            <div class="color-settings-grid">
                                <div class="palette-card palette-card--base">
                                    <div class="palette-card-header">
                                        <div class="palette-card-heading">
                                            <div class="palette-card-icon">&#128274;</div>
                                            <div>
                                                <div class="palette-card-name">Mockup Base Palette</div>
                                                <p class="palette-card-copy">Fixed colors used automatically across all generated mockups.</p>
                                            </div>
                                        </div>
                                        <span class="palette-badge">APPLIES TO ALL</span>
                                    </div>
                                    <div class="palette-note"><span class="palette-note-check">&#10003;</span><span>Customers don't choose these colors — they are part of the mockup setup.</span></div>
                                    <div class="palette-colors-row">
                                        <span id="selected-colors" class="selected-colors"></span>
                                        <button type="button" id="openColorPicker" class="gradient-picker-trigger openColorPicker" data-color-target="pre_fill_colors" title="Add mockup color"></button>
                                    </div>
                                    <div id="colorsInputContainer"></div>
                                    <div class="palette-footer"><span class="palette-footer-dot"></span><span>Automatic · Used in every mockup render</span></div>
                                </div>
                                <div class="palette-card palette-card--across">
                                    <div class="palette-card-header">
                                        <div class="palette-card-heading">
                                            <div class="palette-card-icon">&#127912;</div>
                                            <div>
                                                <div class="palette-card-name">Colors Across Templates</div>
                                                <p class="palette-card-copy">Select from Base Palette or add custom colors to use across templates.</p>
                                            </div>
                                        </div>
                                        <span class="palette-badge">OPTIONAL</span>
                                    </div>
                                    <div class="palette-note"><span class="palette-note-check">&#10003;</span><span>Select any base color below, or add a custom color.</span></div>
                                    <div class="palette-colors-row">
                                        <span id="selected-colors-across-templates"></span>
                                        <button type="button" id="openAcrossTemplatesColorPicker" class="gradient-picker-trigger openColorPicker" data-color-target="colors_across_templates" title="Pick a custom color"></button>
                                    </div>
                                    <div id="colorsAcrossTemplatesInputContainer"></div>
                                    <button type="button" id="toggleBasePaletteSelect"
                                            class="btn btn-link btn-sm p-0 mt-1 ms-auto d-block text-decoration-underline"
                                            data-bs-toggle="modal" data-bs-target="#basePaletteModal"
                                            title="Select from Mockup Base Palette">
                                        Select from Base Palette
                                    </button>
                                    <div class="palette-footer"><span class="palette-footer-dot"></span><span>Optional · Reuses colors already defined in the Base Palette</span></div>
                                    <button type="button" class="btn btn-secondary btn-sm mt-2 px-2 py-1" id="generateTemplateMockupFiles" data-mockup-id="{{ $model->id }}" style="font-size:13px;white-space:nowrap;">
                                        <span class="btn-text">Generate Mockups</span>
                                        <span class="spinner-border spinner-border-sm d-none ms-1" id="generateTemplateMockupFilesLoader" role="status"></span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                    {{--                @endif--}}

                    <div class="modal-footer border-top-0">
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span class="btn-text">Save Changes</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                                  aria-hidden="true"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
        @include("modals.templates.template-modal")
    </section>

    {{-- Select-from-Mockup-Base-Palette popup --}}
    <div class="modal fade" id="basePaletteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select from Mockup Base Palette</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body">
                    <div id="mockupColorsAcrossOptions"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary me-auto" id="selectAllBasePaletteColors">Select All</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="confirmColorDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Color?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to delete this color?</p>
                    <div class="d-flex align-items-center gap-1" id="confirmDeleteColorPreviewWrap">
                        <span id="confirmDeleteColorPreview" style="width:28px;height:28px;border-radius:50%;border:1px solid #ddd;display:inline-block;"></span>
                        <strong id="confirmDeleteColorValue"></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteColorAction">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="generationProgressModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="generation-progress-card">
                    <button type="button" class="generation-minimize-btn" id="generationMinimizeButton" aria-label="Continue in background">↙</button>
                    <div class="generation-spinner-shell" id="generationSpinnerShell">
                        <div class="generation-spinner-ring"></div>
                    </div>
                    <h3 class="generation-title">Generating mockups</h3>
                    <p class="generation-copy">You can safely minimize this window. Generation will continue in the background and you can reopen the progress from the bottom-right status button.</p>
                    <span id="generationProgressStatus" class="d-none">Preparing...</span>
                    <div class="generation-progress-meta">
                        <span id="generationProgressCount">0 / 0 processed</span>
                        <strong id="generationProgressPercent">0%</strong>
                    </div>
                    <div class="generation-progress-track">
                        <div id="generationProgressBar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
                    </div>
                    <div class="generation-stats">
                        <div class="generation-stat">
                            <span class="generation-stat-value" id="generationTotalCount">0</span>
                            <span class="generation-stat-label">Total</span>
                        </div>
                        <div class="generation-stat done">
                            <span class="generation-stat-value" id="generationDoneCount">0</span>
                            <span class="generation-stat-label">Done</span>
                        </div>
                        <div class="generation-stat failed">
                            <span class="generation-stat-value" id="generationFailedCount">0</span>
                            <span class="generation-stat-label">Failed</span>
                        </div>
                    </div>
                    <div class="generation-remaining" id="generationProgressRemaining">Calculating...</div>
                    <div id="generationProgressError" class="alert alert-danger d-none mb-0"></div>
                    <button type="button" class="generation-action-btn generation-background-btn" id="generationContinueBackground">↙ &nbsp; Continue in background</button>
                    <button type="button" class="generation-action-btn generation-cancel-btn" id="generationCancelButton">⊗ &nbsp; Cancel generation</button>
                    <button type="button" class="generation-action-btn generation-close-btn d-none" id="generationProgressClose" data-bs-dismiss="modal">Close</button>
                    <p class="generation-hint">Closing this window will not stop the generation.</p>
                </div>
            </div>
        </div>
    </div>
    <button type="button" id="generationFloatingStatus" class="d-none"><span class="floating-dot"></span><span id="generationFloatingText">Generating mockups</span> · <span id="generationFloatingPercent">0%</span></button>
    <!-- Remove Color Modal -->
    <div class="modal fade" id="removeColorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header text-white">
                    <h5 class="modal-title">Delete Color from Mockups?</h5>
                    <button type="button" class="btn-close d-flex align-items-start justify-content-center"
                            data-bs-dismiss="modal" aria-label="Close" style="background-color: #24b094">x</button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Are you sure you want to delete this color? It exists in other mockups using the same template and will be removed
                        from all of them.</p>

                    <div id="relatedMockupsList" class="rounded p-1 bg-light d-flex flex-wrap gap-1"
                         style="max-height:300px; overflow-y: auto;">
                        <div class="text-center text-muted">Loading mockups...</div>
                        <div id="relatedMockupsList"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveColor">Yes, Delete Color</button>
                </div>
            </div>
        </div>
    </div>

    <!-- users list ends -->
@endsection

@section('vendor-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.2.4/fabric.min.js"></script>

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
    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
@endsection

@section('page-script')

    <script>
        $('#productsSelect').select2({
            placeholder: 'Choose product',
            allowClear: true,
            width: '100%',
        });

        $('#categoriesSelect').select2({
            placeholder: 'Choose category',
            allowClear: true,
            width: '100%',
        });

        const selectedCategoryIdsOnLoad = @json(
        old('product_ids', isset($model) && method_exists($model, 'products')
            ? $model->products->pluck('id')->map(fn($id) => (string) $id)->values()
            : []
        )
    );

        function loadCategoriesBySelectedProducts(preselectedIds = []) {
            const selectedIds = $('#productsSelect').val();

            const ids = Array.isArray(selectedIds)
                ? selectedIds
                : (selectedIds ? [selectedIds] : []);

            const $right = $('#categoriesSelect');

            if (!ids.length) {
                $right.empty().trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('products.categories') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    category_ids: ids
                },
                success(response) {
                    $right.empty();

                    (response.data || []).forEach(cat => {
                        const catId = String(cat.id);

                        const isSelected = preselectedIds
                            .map(String)
                            .includes(catId);

                        const opt = new Option(cat.name, cat.id, isSelected, isSelected);

                        $(opt).attr('data-has-mockup', cat.has_mockup ? '1' : '0');

                        $right.append(opt);
                    });

                    $right.trigger('change');
                },
                error(xhr) {
                    console.error("Error fetching categories:", xhr.responseText);
                }
            });
        }

        $('#productsSelect').on('change', function () {
            loadCategoriesBySelectedProducts([]);
        });

        $(document).ready(function () {
            if ($('#productsSelect').val()) {
                loadCategoriesBySelectedProducts(selectedCategoryIdsOnLoad);
            }
        });
        @php
            $preFillColors = old('pre_fill_colors', $model->pre_fill_colors ?? []);
            if (is_string($preFillColors)) {
                $preFillColors = json_decode($preFillColors, true) ?? [];
            }
            if (!is_array($preFillColors)) {
                $preFillColors = [];
            }

            $colorsAcrossTemplates = old('colors_across_templates', $model->colors_across_templates ?? []);
            if (is_string($colorsAcrossTemplates)) {
                $colorsAcrossTemplates = json_decode($colorsAcrossTemplates, true) ?? [];
            }
            if (!is_array($colorsAcrossTemplates)) {
                $colorsAcrossTemplates = [];
            }
        @endphp
        const existingGlobalMockupColors = @json(array_values($preFillColors));
        const existingAcrossTemplateColors = @json(array_values($colorsAcrossTemplates));
        const mockupIdForColorSync = "{{ $model->id }}";
    </script>
    <script>
        const attachedTemplateIdsRaw = @json(($model?->templates?->pluck('id') ?? collect())->values());
        const attachedTemplateIds = new Set((attachedTemplateIdsRaw || []).map(id => String(id)));
    </script>


    <script>
        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        document.addEventListener('DOMContentLoaded', function () {

            function capitalize(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            function preloadFile(type, baseUrl, maskUrl, shadowUrl, displacementUrl, lightUrl) {
                const baseInput = document.getElementById(`${type}-base-input`);
                const maskInput = document.getElementById(`${type}-mask-input`);
                const shadowInput = document.getElementById(`${type}-shadow-input`);
                const displacementInput = document.getElementById(`${type}-displacement-input`);
                const lightInput = document.getElementById(`${type}-light-input`);

                const block = document.getElementById(`${type}-file-block`);
                if (!block) return;

                const basePreview = block.querySelector(`.upload-area[data-input-id="${type}-base-input"] .preview`);
                const maskPreview = block.querySelector(`.upload-area[data-input-id="${type}-mask-input"] .preview`);
                const shadowPreview = block.querySelector(`.upload-area[data-input-id="${type}-shadow-input"] .preview`);
                const displacementPreview = block.querySelector(`.upload-area[data-input-id="${type}-displacement-input"] .preview`);
                const lightPreview = block.querySelector(`.upload-area[data-input-id="${type}-light-input"] .preview`);

                const canvas = window[`canvas${capitalize(type)}`];
                const wrapperId = `editor${capitalize(type)}Wrapper`;

                // -----------------------------
                // Base image
                // -----------------------------
                if (baseUrl && basePreview) {
                    basePreview.innerHTML = `<img src="${baseUrl}" class="img-fluid rounded border" style="max-height:120px;">`;
                    if (canvas) loadBaseImage(canvas, baseUrl);
                    document.getElementById(wrapperId)?.classList.remove('d-none');

                    // set file input value (optional, if you want form submission)
                    if (baseInput) {
                        fetch(baseUrl)
                            .then(res => res.blob())
                            .then(blob => {
                                const dt = new DataTransfer();
                                dt.items.add(new File([blob], 'base.png', { type: blob.type }));
                                baseInput.files = dt.files;
                            });
                    }
                }

                // -----------------------------
                // Mask image
                // -----------------------------
                if (maskUrl && maskPreview) {
                    maskPreview.innerHTML = `<img src="${maskUrl}" class="img-fluid rounded border" style="max-height:120px;">`;
                    // if (canvas) loadMaskImage(canvas, maskUrl);
                    document.getElementById(wrapperId)?.classList.remove('d-none');

                    // set file input value
                    if (maskInput) {
                        fetch(maskUrl)
                            .then(res => res.blob())
                            .then(blob => {
                                const dt = new DataTransfer();
                                dt.items.add(new File([blob], 'mask.png', { type: blob.type }));
                                maskInput.files = dt.files;
                            });
                    }
                }

                // -----------------------------
                // Shadow image
                // -----------------------------
                if (shadowUrl && shadowPreview) {
                    console.log("shadow",shadowUrl)
                    shadowPreview.innerHTML = `<img src="${shadowUrl}" class="img-fluid rounded border" style="max-height:120px;">`;
                    document.getElementById(wrapperId)?.classList.remove('d-none');

                    // set file input value
                    if (shadowInput) {
                        fetch(shadowUrl)
                            .then(res => res.blob())
                            .then(blob => {
                                const dt = new DataTransfer();
                                dt.items.add(new File([blob], 'shadow.png', { type: blob.type }));
                                shadowInput.files = dt.files;
                            });
                    }
                }

                // -----------------------------
                // Displacement image
                // -----------------------------
                if (displacementUrl && displacementPreview) {
                    displacementPreview.innerHTML = `<img src="${displacementUrl}" class="img-fluid rounded border" style="max-height:120px;">`;
                    document.getElementById(wrapperId)?.classList.remove('d-none');

                    if (displacementInput) {
                        fetch(displacementUrl)
                            .then(res => res.blob())
                            .then(blob => {
                                const dt = new DataTransfer();
                                dt.items.add(new File([blob], 'displacement.png', { type: blob.type }));
                                displacementInput.files = dt.files;
                            });
                    }
                }

                // -----------------------------
                // Highlight image
                // -----------------------------
                if (lightUrl && lightPreview) {
                    lightPreview.innerHTML = `<img src="${lightUrl}" class="img-fluid rounded border" style="max-height:120px;">`;
                    document.getElementById(wrapperId)?.classList.remove('d-none');

                    if (lightInput) {
                        fetch(lightUrl)
                            .then(res => res.blob())
                            .then(blob => {
                                const dt = new DataTransfer();
                                dt.items.add(new File([blob], 'light.png', { type: blob.type }));
                                lightInput.files = dt.files;
                            });
                    }
                }
            }


            @if($model)
                @foreach($model->types as $type)
            (function () {
                const typeName = "{{ strtolower($type->value->name) }}";
                const checkbox = document.querySelector(`.type-checkbox[data-type-name="${typeName}"]`);

                if (checkbox && !checkbox.checked) {
                    checkbox.checked = true;
                }
                // Call toggleCheckboxes to render the block
                toggleCheckboxes();
                // Wait a tick to ensure the block exists in DOM
                setTimeout(() => {
                    preloadFile(
                        "{{ strtolower($type->value->name) }}",
                        "{{ $model->{ strtolower($type->value->name) . '_base_image_url' } ?? '' }}",
                        "{{ $model->{ strtolower($type->value->name) . '_mask_image_url' } ?? '' }}",
                        "{{ $model->{ strtolower($type->value->name) . '_shadow_image_url' } ?? '' }}",
                        "{{ $existingMedia[strtolower($type->value->name)]['displacement_image'] ?? '' }}",
                        "{{ $existingMedia[strtolower($type->value->name)]['light_image'] ?? '' }}"
                    );
                }, 50); // 50ms delay usually enough
            })();
            @endforeach
            @endif
        });

    </script>
    <script>
        // =========================
        // COLOR PICKER
        // =========================
        let pickrInstance = null;
        let currentCard = null;
        let currentGlobalColorTarget = 'pre_fill_colors';

        let pendingConfirmColorDelete = null;

        function requestColorDeleteConfirmation(hex, callback, label = null) {
            pendingConfirmColorDelete = callback;
            const value = label || String(hex || '');
            const hasColor = !!hex;
            $('#confirmDeleteColorValue').text(value);
            $('#confirmDeleteColorPreviewWrap').toggleClass('d-none', !hasColor && !label);
            $('#confirmDeleteColorPreview').toggleClass('d-none', !hasColor).css('background-color', hasColor ? hex : 'transparent');
            $('#confirmColorDeleteModal').modal('show');
        }

        $(document).on('click', '#confirmDeleteColorAction', function () {
            if (typeof pendingConfirmColorDelete !== 'function') return;
            const action = pendingConfirmColorDelete;
            pendingConfirmColorDelete = null;
            $('#confirmColorDeleteModal').modal('hide');
            setTimeout(action, 120);
        });

        function getGlobalColorConfig(target) {
            if (target === 'colors_across_templates') {
                return {
                    selectedId: 'selected-colors-across-templates',
                    inputContainerId: 'colorsAcrossTemplatesInputContainer',
                    inputName: 'colors_across_templates[]'
                };
            }

            return {
                selectedId: 'selected-colors',
                inputContainerId: 'colorsInputContainer',
                inputName: 'pre_fill_colors[]'
            };
        }

        function getGlobalColors(target) {
            const config = getGlobalColorConfig(target);
            const container = document.getElementById(config.inputContainerId);
            if (!container) return [];

            return [...container.querySelectorAll(`input[name="${config.inputName}"]`)]
                .map(input => String(input.value).toLowerCase());
        }

        function addGlobalColor(hex, target) {
            hex = String(hex || '').toLowerCase();
            if (!hex) return false;

            const config = getGlobalColorConfig(target);
            const selectedColors = document.getElementById(config.selectedId);
            const inputContainer = document.getElementById(config.inputContainerId);
            if (!selectedColors || !inputContainer || getGlobalColors(target).includes(hex)) return false;

            const li = document.createElement('li');
            li.style.listStyle = 'none';
            li.dataset.hex = hex;
            li.innerHTML = `
                <div class="selected-color-wrapper position-relative">
                    <div class="selected-color-dot" style="background-color:#fff;">
                        <div class="selected-color-inner" style="background-color:${hex};"></div>
                    </div>
                    <button type="button" onclick="removeGlobalColor('${hex}', this, '${target}')" class="remove-color-btn">×</button>
                </div>`;
            selectedColors.appendChild(li);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = config.inputName;
            input.value = hex;
            inputContainer.appendChild(input);

            syncAcrossTemplateSourceColors();
            return true;
        }

        function removeGlobalColorByHex(hex, target) {
            hex = String(hex || '').toLowerCase();
            const config = getGlobalColorConfig(target);
            const selectedColors = document.getElementById(config.selectedId);
            const inputContainer = document.getElementById(config.inputContainerId);

            if (selectedColors) {
                [...selectedColors.querySelectorAll('li')].forEach(li => {
                    if (String(li.dataset.hex || '').toLowerCase() === hex) li.remove();
                });
            }

            if (inputContainer) {
                [...inputContainer.querySelectorAll(`input[name="${config.inputName}"]`)]
                    .filter(input => String(input.value).toLowerCase() === hex)
                    .forEach(input => input.remove());
            }

            syncAcrossTemplateSourceColors();
        }

        function notifyAcrossColorRemoved(hex, templateId = null) {
            hex = String(hex || '').replace('#', '').toLowerCase();

            if (!hex || !mockupIdForColorSync) return;

            const urlTemplate = @json(route('mockups.colors-across-templates.remove', ['mockup' => '__MOCKUP_ID__', 'hex' => '__HEX__']));

            const url = urlTemplate
                .replace('__MOCKUP_ID__', mockupIdForColorSync)
                .replace('__HEX__', hex);

            $.ajax({
                url,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                data: {
                    template_id: templateId
                },
                success(response) {
                    Toastify({
                        text: response?.message || 'Color removed successfully',
                        duration: 2500,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#28a745',
                        close: true
                    }).showToast();
                },
                error(xhr) {
                    console.error(xhr.responseJSON || xhr.responseText);

                    Toastify({
                        text: xhr.responseJSON?.message || 'Failed to remove color',
                        duration: 3000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#dc3545',
                        close: true
                    }).showToast();
                }
            });
        }
        window.removeGlobalColor = function (hex, btn, target = 'pre_fill_colors') {
            requestColorDeleteConfirmation(hex, function () {
                const li = btn.closest('li');
                if (li) li.remove();
                const config = getGlobalColorConfig(target);
                const inputContainer = document.getElementById(config.inputContainerId);
                if (inputContainer) {
                    [...inputContainer.querySelectorAll(`input[name="${config.inputName}"]`)]
                        .filter(input => String(input.value).toLowerCase() === String(hex).toLowerCase())
                        .forEach(input => input.remove());
                }
                syncAcrossTemplateSourceColors();
                if (target === 'colors_across_templates') notifyAcrossColorRemoved(hex);
            });
        };

        // Colors Across Templates can reuse colors from the Mockup Base Palette
        // and can also include custom colors added by its own picker.
        // The modal below only manages selecting/deselecting base-palette colors.
        function updateBasePaletteSelectAllButton() {
            const button = document.getElementById('selectAllBasePaletteColors');
            if (!button) return;

            const baseColors = [...new Set(getGlobalColors('pre_fill_colors'))];
            const acrossColors = new Set(getGlobalColors('colors_across_templates'));
            const allSelected = baseColors.length > 0 && baseColors.every(hex => acrossColors.has(hex));

            button.disabled = baseColors.length === 0;
            button.textContent = allSelected ? 'Deselect All' : 'Select All';
        }

        function syncAcrossTemplateSourceColors() {
            const optionsContainer = document.getElementById('mockupColorsAcrossOptions');
            if (!optionsContainer) return;

            const mockupColors = [...new Set(getGlobalColors('pre_fill_colors'))];
            const acrossSet = new Set(getGlobalColors('colors_across_templates'));
            optionsContainer.innerHTML = '';

            if (!mockupColors.length) {
                const text = document.createElement('small');
                text.className = 'text-muted';
                text.textContent = 'Add Mockup Colors first to select them here';
                optionsContainer.appendChild(text);
                updateBasePaletteSelectAllButton();
                return;
            }

            mockupColors.forEach(hex => {
                const selected = acrossSet.has(hex);
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `js-toggle-across-color palette-source-color${selected ? ' is-selected' : ''}`;
                button.style.backgroundColor = hex;
                button.dataset.hex = hex;
                button.title = selected ? 'Remove from Colors Across Templates' : 'Add to Colors Across Templates';
                if (selected) button.innerHTML = '<span class="palette-source-color-check">✓</span>';
                optionsContainer.appendChild(button);
            });

            updateBasePaletteSelectAllButton();
        }

        $(document).on('click', '.js-toggle-across-color', function () {
            const hex = String(this.dataset.hex || '').toLowerCase();
            if (!hex) return;
            if (getGlobalColors('colors_across_templates').includes(hex)) {
                requestColorDeleteConfirmation(hex, function () {
                    removeGlobalColorByHex(hex, 'colors_across_templates');
                    notifyAcrossColorRemoved(hex);
                });
            } else {
                addGlobalColor(hex, 'colors_across_templates');
            }
        });

        // Refresh the base-palette swatches every time the popup opens (colors may have
        // changed since the panel was last built).
        $(document).on('show.bs.modal', '#basePaletteModal', function () {
            syncAcrossTemplateSourceColors();
        });

        $(document).on('click', '#selectAllBasePaletteColors', function () {
            const baseColors = [...new Set(getGlobalColors('pre_fill_colors'))];
            if (!baseColors.length) return;
            const acrossColors = new Set(getGlobalColors('colors_across_templates'));
            const allSelected = baseColors.every(hex => acrossColors.has(hex));
            const applyChange = function () {
                baseColors.forEach(hex => {
                    if (allSelected) {
                        if (getGlobalColors('colors_across_templates').includes(hex)) {
                            removeGlobalColorByHex(hex, 'colors_across_templates');
                            notifyAcrossColorRemoved(hex);
                        }
                    } else {
                        addGlobalColor(hex, 'colors_across_templates');
                    }
                });
                syncAcrossTemplateSourceColors();
            };
            if (allSelected) requestColorDeleteConfirmation(null, applyChange, 'All selected base colors');
            else applyChange();
        });

        $(document).ready(function () {
            existingGlobalMockupColors.forEach(hex => addGlobalColor(hex, 'pre_fill_colors'));
            existingAcrossTemplateColors.forEach(hex => addGlobalColor(hex, 'colors_across_templates'));
            syncAcrossTemplateSourceColors();

            if (pickrInstance) pickrInstance.destroyAndRemove();

            const dummyElement = document.createElement('div');
            document.body.appendChild(dummyElement);

            pickrInstance = Pickr.create({
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

            pickrInstance.on('save', color => {
                if (!color) {
                    pickrInstance.hide();
                    return;
                }

                const hex = color.toHEXA().toString().toLowerCase();

                if (!currentCard) {
                    addGlobalColor(hex, currentGlobalColorTarget);
                    pickrInstance.hide();
                    return;
                }

                if (!Array.isArray(currentCard.selectedColors)) currentCard.selectedColors = [];
                if (!currentCard.selectedColors.includes(hex)) currentCard.selectedColors.push(hex);

                renderSelectedColors(currentCard);
                buildHiddenTemplateInputs();
                pickrInstance.hide();
            });

            pickrInstance.on('clear', () => {
                if (!currentCard) {
                    const config = getGlobalColorConfig(currentGlobalColorTarget);
                    const selectedColors = document.getElementById(config.selectedId);
                    const inputContainer = document.getElementById(config.inputContainerId);
                    requestColorDeleteConfirmation(null, function () {
                        if (selectedColors) selectedColors.innerHTML = '';
                        if (inputContainer) inputContainer.innerHTML = '';
                        syncAcrossTemplateSourceColors();
                    }, 'All colors');
                    pickrInstance.hide();
                    return;
                }
                const card = currentCard;
                requestColorDeleteConfirmation(null, function () {
                    card.selectedColors = [];
                    renderSelectedColors(card);
                    buildHiddenTemplateInputs();
                }, 'All template colors');
                pickrInstance.hide();
            });
        });

        // This handler supports the Mockup Base Palette picker, the Colors Across Templates
        // picker, and the per-template-card pickers.
        $(document).on('click', '.openColorPicker', function () {
            const trigger = this;
            const globalTarget = trigger.dataset.colorTarget;

            if (globalTarget) {
                currentCard = null;
                currentGlobalColorTarget = globalTarget;
                pickrInstance.show();

                setTimeout(() => {
                    const pickerPanel = document.querySelector('.pcr-app.visible');
                    if (pickerPanel) {
                        const rect = trigger.getBoundingClientRect();
                        pickerPanel.style.position = 'fixed';
                        pickerPanel.style.left = `${rect.left}px`;
                        pickerPanel.style.top = `${rect.bottom + 5}px`;
                        pickerPanel.style.zIndex = 9999;
                    }
                }, 0);
                return;
            }

            const card = trigger.closest('.template-card');
            if (!card) return;

            currentCard = card;
            if (!Array.isArray(card.selectedColors)) card.selectedColors = [];

            const rect = trigger.getBoundingClientRect();
            const modalScrollTop = document.querySelector('#templateModal .modal-body')?.scrollTop || 0;
            pickrInstance.show();

            setTimeout(() => {
                const pickerPanel = document.querySelector('.pcr-app.visible');
                if (pickerPanel) {
                    pickerPanel.style.position = 'absolute';
                    pickerPanel.style.left = `${rect.left + window.scrollX}px`;
                    pickerPanel.style.top = `${rect.bottom + window.scrollY + modalScrollTop + 5}px`;
                    pickerPanel.style.zIndex = 9999;
                }
            }, 0);
        });

        window.removeColor = function (hex) {
            if (!currentCard || !Array.isArray(currentCard.selectedColors)) return;
            requestColorDeleteConfirmation(hex, function () {
                currentCard.selectedColors = currentCard.selectedColors.filter(c => String(c).toLowerCase() !== String(hex).toLowerCase());
                renderSelectedColors(currentCard);
                buildHiddenTemplateInputs();
            });
        };

        function renderSelectedColors(card) {
            const ul = card.querySelector('.selected-colors');
            const container = card.querySelector('.colorsInputContainer');
            if (!ul || !container) return;

            ul.innerHTML = '';
            container.innerHTML = '';
            ul.classList.add('list-unstyled', 'm-0', 'p-0');

            (card.selectedColors || []).forEach(c => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <div class="selected-color-wrapper position-relative">
                        <div class="selected-color-dot" style="background-color:#fff;">
                            <div class="selected-color-inner" style="background-color:${c};"></div>
                        </div>
                        <button type="button" class="remove-color-btn" data-color="${c}">×</button>
                    </div>`;
                ul.appendChild(li);
            });

            card.dataset.colors = JSON.stringify(card.selectedColors || []);
        }

        let pendingColorData = null;

        $(document).on('click', '.remove-color-btn', function () {
            const card = this.closest('.template-card');
            const hex = this.dataset.color;
            if (!card || !hex) return;

            const templateId = card.dataset.id;
            const savedColors = savedColorsById.get(String(templateId)) || [];

            // 🔹 لو اللون مش من الألوان القديمة (يعني لسه المستخدم ضافه)
            if (!savedColors.includes(hex)) {
                requestColorDeleteConfirmation(hex, function () {
                    card.selectedColors = (card.selectedColors || []).filter(c => c !== hex);
                    renderSelectedColors(card);
                    buildHiddenTemplateInputs();
                });
                return;
            }

            // 🔸 اللون قديم → افتح المودال
            const mockupId = $('#mockupId').val() || '{{ $model->id ?? "" }}';
            const categoryId = '{{ $model->category->id ?? "" }}';
            pendingColorData = { card, hex, templateId, mockupId };
            $('#removeColorModal').modal('show');

            const $list = $('#relatedMockupsList');
            $list.html('<div class="text-center text-muted py-3">Loading mockups...</div>');

            $.ajax({
                url: `/mockups`,
                type: 'GET',
                data: {
                    template_id: templateId,
                    category_id: categoryId,
                    mockup_id: mockupId,
                    color: hex,
                },
                success: function (res) {
                    const mockups = res?.data?.data || [];

                    if (!mockups.length) {
                        $list.html('<div class="text-center text-muted py-3">No other mockups found for this template.</div>');
                        return;
                    }

                    const html = mockups.map(m => {
                        const img = m.images?.front?.base_url || m.images?.back?.base_url || "{{ asset('images/placeholder.svg') }}";
                        const colors = (m.colors || []).map(c => `
                    <span class="d-inline-block me-1"
                          style="width:18px;height:18px;border-radius:50%;background:${c};border:1px solid #ccc"></span>
                `).join('');

                        const types = (m.types || []).map(t => `<span class="badge me-1" style="background:#24b094;">${t.label}</span>`).join('');

                        return `
                            <div class="d-flex gap-1 rounded" style="width: 120px; border:1px solid #24b094;">
                                <div class="d-flex flex-column gap-1 align-items-center">
                                    <img src="${img}" alt="${m.name}" class="rounded" style="width:115px;height:100px;">
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                        <div class="fw-bold">${m.name || 'Untitled Mockup'}</div>
                                        <div class="text-muted small mb-1">${types}</div>
                                    </div>
                                </div>
                            </div>
                `;
                    }).join('');

                    $list.html(html);
                },
                error: function () {
                    $list.html('<div class="text-danger text-center py-3">Failed to load mockups.</div>');
                }
            });
        });


        // عند تأكيد الحذف
        $('#confirmRemoveColor').on('click', function () {

            if (!pendingColorData) return;
            const { card, hex, templateId } = pendingColorData;
            console.log(templateId)
            const categoryId = '{{ $model->category->id ?? "" }}';
            const $btn = $(this);

            $btn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: "{{ route('mockups.remove-color') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    category_id: categoryId,
                    template_id: templateId,
                    color: hex,
                },
                success: function(res) {
                    // ✅ بعد نجاح السيرفر: احذف محليًا
                    card.selectedColors = (card.selectedColors || []).filter(c => c !== hex);
                    renderSelectedColors(card);
                    buildHiddenTemplateInputs();

                    $('#removeColorModal').modal('hide');
                    Toastify({
                        text: "Color removed Successfully.",
                        duration: 1000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                    }).showToast();
                    pendingColorData = null;
                },
                error: function() {
                    Toastify({
                        text: "Failed to remove color.",
                        duration: 1000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                    }).showToast();
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Yes, Delete Color');
                }
            });
        });

        const templatesData = @json($model->templates ?? []);

        // Map: template_id -> colors[]
        const savedColorsById = new Map(
            (templatesData || []).map(t => {
                let colors = t?.pivot?.pre_fill_colors ?? [];
                if (typeof colors === 'string') {
                    try { colors = JSON.parse(colors); } catch(e) { colors = []; }
                }
                if (!Array.isArray(colors)) colors = [];
                return [String(t.id), colors]; // <-- important
            })
        );


    </script>
    <script>
        function calculateObjectPercents(obj, meta) {
            const center = obj.getCenterPoint();
            const wReal = obj.width * obj.scaleX;
            const hReal = obj.height * obj.scaleY;

            return {
                xPct: ((center.x - meta.offsetLeft) / meta.scaledWidth).toFixed(6),
                yPct: ((center.y - meta.offsetTop) / meta.scaledHeight).toFixed(6),
                wPct: (wReal / meta.scaledWidth).toFixed(6),
                hPct: (hReal / meta.scaledHeight).toFixed(6),
                angle: obj.angle || 0
            };
        }

        function buildHiddenTemplateInputs() {
            const container = document.getElementById("templatesHiddenContainer");
            if (!container) return;

            container.innerHTML = "";

            const previousTemplates = @json($model->templates ?? []);
            const selectedTemplateIdRaw = $('#selectedTemplateId').val();
            const selectedTemplateId = selectedTemplateIdRaw ? String(selectedTemplateIdRaw) : "";

            const safeJson = (v, fallback = {}) => {
                if (v == null) return fallback;
                if (typeof v === "object") return v;
                if (typeof v === "string") {
                    try { return JSON.parse(v) || fallback; } catch (e) { return fallback; }
                }
                return fallback;
            };

            const getCanvas = (side) => window['canvas' + capitalize(side)];

            const findObj = (side, templateId) => {
                const canvas = getCanvas(side);
                if (!canvas) return null;

                const tid = String(templateId);

                // ✅ match more than one possible property name
                return canvas.getObjects()?.find(o => {
                    const sameId = String(o.templateId ?? o.tplId ?? o.template_id ?? "") === tid;
                    const sameSide =
                        (o.templateType === side) ||
                        (o.templateSide === side) ||
                        (o.side === side) ||
                        (o.mockupSide === side);

                    return sameId && sameSide;
                }) || null;
            };

            const readPivot = (tpl, side) => {
                const pos = safeJson(tpl?.pivot?.positions, {});
                return {
                    x: pos[`${side}_x`] ?? null,
                    y: pos[`${side}_y`] ?? null,
                    w: pos[`${side}_width`] ?? null,
                    h: pos[`${side}_height`] ?? null,
                    angle: pos[`${side}_angle`] ?? null,
                };
            };

            // ✅ colors: read from card.selectedColors OR from DOM OR pivot
            const getSelectedColors = (templateId, tpl) => {
                const card = document.querySelector(`.template-card[data-id="${templateId}"]`);

                if (card) {
                    // Try to read current selectedColors array
                    if (Array.isArray(card.selectedColors)) return card.selectedColors;

                    // Fallback: dataset (in case of rebuild)
                    try {
                        const colors = JSON.parse(card.dataset.colors || "[]");
                        if (Array.isArray(colors)) return colors;
                    } catch (e) {}

                    // Or legacy UI (selected swatches)
                    const nodes = card.querySelectorAll('[data-color].selected, .color-swatch.selected');
                    return Array.from(nodes).map(n => n.dataset.color).filter(Boolean);
                }

                // Fallback to pivot data if UI not available
                const pivotColors = safeJson(tpl?.pivot?.pre_fill_colors, []);
                return Array.isArray(pivotColors) ? pivotColors : [];
            };

            const getPercents = (tpl, side, templateId) => {
                // 1) لو هو selected template: حاول من canvas
                if (selectedTemplateId && String(templateId) === selectedTemplateId) {
                    const canvas = getCanvas(side);
                    const obj = findObj(side, templateId) || canvas?.getActiveObject?.();
                    const meta = canvas?.__mockupMeta;

                    if (obj && meta) {
                        const res = calculateObjectPercents(obj, meta) || {};
                        const x = res.xPct, y = res.yPct, w = res.wPct, h = res.hPct, angle = res.angle;
                        if ([x, y, w, h].every(v => v !== undefined && v !== null && v !== "")) {
                            return {
                                x: parseFloat(x),
                                y: parseFloat(y),
                                w: parseFloat(w),
                                h: parseFloat(h),
                                angle: parseFloat(angle ?? 0),
                            };
                        }
                    }
                }

                // 2) fallback: pivot (مع parse لو string)
                const pv = readPivot(tpl, side);
                if (pv.x !== null) {
                    return {
                        x: parseFloat(pv.x),
                        y: parseFloat(pv.y),
                        w: parseFloat(pv.w),
                        h: parseFloat(pv.h),
                        angle: parseFloat(pv.angle ?? 0),
                    };
                }

                // 3) لو new template ومفيش obj/meta: ابعت defaults عشان backend مايبقاش فاضي
                if (selectedTemplateId && String(templateId) === selectedTemplateId) {
                    return { x: 0.5, y: 0.5, w: 0.4, h: 0.4, angle: 0 };
                }

                return null;
            };

            const writeSideInputs = (htmlArr, index, side, p) => {
                if (!p) return;
                htmlArr.push(`<input type="hidden" name="templates[${index}][${side}_x]" value="${p.x}">`);
                htmlArr.push(`<input type="hidden" name="templates[${index}][${side}_y]" value="${p.y}">`);
                htmlArr.push(`<input type="hidden" name="templates[${index}][${side}_width]" value="${p.w}">`);
                htmlArr.push(`<input type="hidden" name="templates[${index}][${side}_height]" value="${p.h}">`);
                htmlArr.push(`<input type="hidden" name="templates[${index}][${side}_angle]" value="${p.angle ?? 0}">`);
            };

            const html = [];

            // 1️⃣ include all previous templates (preserve old pivot + override selected from canvas)
            previousTemplates.forEach((tpl, index) => {
                const currentId = tpl.id;

                html.push(`<input type="hidden" name="templates[${index}][template_id]" value="${currentId}">`);

                ['front', 'back', 'none'].forEach(side => {
                    const p = getPercents(tpl, side, currentId);
                    writeSideInputs(html, index, side, p);
                });

                // ✅ ADD THIS BACK:
                const colors = getSelectedColors(currentId, tpl);
                colors.forEach(c => {
                    html.push(
                        `<input type="hidden" name="templates[${index}][colors][]" value="${String(c).toLowerCase()}">`
                    );
                });
            });


            // 2️⃣ if selected template is new → add it (always send defaults if canvas not ready)
            const existsInPrevious = selectedTemplateId
                ? previousTemplates.some(t => String(t.id) === String(selectedTemplateId))
                : false;

            if (selectedTemplateId && !existsInPrevious) {
                const index = previousTemplates.length;

                html.push(`<input type="hidden" name="templates[${index}][template_id]" value="${selectedTemplateId}">`);

                ['front', 'back', 'none'].forEach(side => {
                    const p = getPercents({}, side, selectedTemplateId);
                    writeSideInputs(html, index, side, p);
                });

                const colors = getSelectedColors(selectedTemplateId, {});
                colors.forEach(c => {
                    html.push(
                        `<input type="hidden" name="templates[${index}][colors][]" value="${String(c).toLowerCase()}">`
                    );
                });
            }

            container.innerHTML = html.join("");
        }

        // قبل حفظ الفورم:
        // $('form').on('submit', function () {
        //     buildHiddenTemplateInputs();
        // });



        document.addEventListener('DOMContentLoaded', function () {
            const $productSelect = $('#productsSelect');
            const $templatesWrapper = $('#templatesCardsWrapper');
            const $templatesCardsContainer = $('#templatesCardsContainer');
            const $selectedTemplateId = $('#selectedTemplateId');

            const $modal = $('#templateModal');
            const $modalContainer = $('#templates-modal-container');
            const $modalPagination = $('#templates-modal-pagination');

            const locale = "{{ app()->getLocale() }}";

            // حالة التمبليتس
            let firstPageTemplates = [];
            let nextPageUrl = null;
            let currentProductId = null;

            // =========================
            // Helpers
            // =========================
            function resetTemplatesUI() {
                $templatesCardsContainer.empty();
                $templatesWrapper.addClass('d-none');
                $selectedTemplateId.val('');
                firstPageTemplates = [];
                nextPageUrl = null;

                $modalContainer.empty();
                $modalPagination.empty();
            }


            function buildTemplateInnerCard(tpl, index = 0) {
                const id = String(tpl.id);
                const isAttached = attachedTemplateIds.has(id);

                const name = typeof tpl.name === 'object'
                    ? (tpl.name[locale] ?? Object.values(tpl.name)[0])
                    : (tpl.name || ('Template #' + id));

                const hasType3 = tpl.types?.some(t => t.value === 3);

                let front = '', none = '';
                if (hasType3) { none = tpl.source_design_svg || ''; }
                else { front = tpl.source_design_svg || ''; }

                const back = tpl.back_base64_preview_image || '';
                const img = front || back || none || "{{ asset('images/placeholder.svg') }}";

                const editorBaseUrl = "{{ rtrim(config('services.editor_url'), '/') }}/mokup/";
                const mockupId = "{{ $model->id }}";
                const productId = $('#productsSelect').val() || "{{ $model->category_id }}";

                const editorUrl = `${editorBaseUrl}${mockupId}?${new URLSearchParams({
                    templateId: id,
                    is_has_category: '0',
                    product_id: String(productId || '')
                }).toString()}`;

                return `
                <div class="template-card h-100 position-relative"
                    data-id="${id}"
                    data-index="${index}"
                    data-front="${front}"
                    data-back="${back}"
                    data-none="${none}">

                    ${isAttached ? `
                    <span class="badge bg-success position-absolute"
                            style="top:10px;left:10px;z-index:10;">
                        Attached
                    </span>
                    ` : ``}

                    <div class="card rounded-3 shadow-sm" style="border:1px solid #24B094;">
                    <div class="d-flex justify-content-center align-items-center"
                        style="background-color:#F4F6F6;height:200px;border-radius:12px;padding:10px;">
                        <img src="${img}" class="mx-auto d-block"
                            style="height:auto;width:auto;max-width:100%;max-height:100%;border-radius:5px;"
                            alt="${name}">
                    </div>

                    <div class="card-body py-2">
                        <h6 class="card-title mb-0 text-truncate fs-5">${name}</h6>
                    </div>

                    <div class="d-flex gap-1 px-1 pb-2">
                        <button type="button" class="btn btn-sm btn-primary w-100 js-show-on-mockup">Show on Mockup</button>

                        <a href="${editorUrl}"
                        target="_blank"
                        class="btn btn-sm btn-primary w-100">
                            Show with Editor
                        </a>

                        <button type="button" class="btn btn-sm btn-outline-primary w-100 js-save-positions">
                            Save Positions
                        </button>
                    </div>

                    <div class="mb-2" style="padding-left:10px">
                        <label class="label-text mb-1 d-block">Colors</label>
                        <div class="d-flex flex-wrap align-items-center gap-1">
                        <button type="button" class="openColorPicker gradient-picker-trigger border"></button>
                        <span class="selected-colors d-flex gap-1 flex-wrap align-items-center"></span>
                        </div>
                        <div class="colorsInputContainer"></div>
                    </div>
                    </div>
                </div>
                `;
            }

            // =========================
            // Cards (أول 3 بس)
            // =========================
            function renderTemplateCards(templates) {
                $templatesCardsContainer.empty();

                if (!templates.length) {
                    $templatesCardsContainer.append(`
                    <div class="col-12 text-center text-muted py-2">
                        No templates found
                    </div>
                `);
                    return;
                }

                const maxInline = 3;
                const visible = templates.slice(0, maxInline);

                visible.forEach(function (tpl, index) {
                    const cardHtml = `
                    <div class="col-12 col-md-4">
                        ${buildTemplateInnerCard(tpl, index)}
                    </div>
                `;
                    $templatesCardsContainer.append(cardHtml);
                });

                // لو عندنا أكتر من 3 → زر Show Remaining
                if (templates.length > maxInline) {
                    const showMoreHtml = `
                     <div class="template-card cursor-pointer d-flex justify-content-center justify-content-md-end">
                        <span class="template-card cursor-pointer show-more rounded-2 py-1 px-2 shadow-sm show-more-card js-open-templates-modal" tabindex="0" style="border:1px solid #24B094;">
                            Show more Templates</span>
                    </div>
                `;
                    $templatesCardsContainer.append(showMoreHtml);
                }

                $templatesWrapper.removeClass('d-none');
// بعد ما تبني كل الكروت
                setTimeout(() => {
                    document.querySelectorAll('.template-card').forEach(card => {
                        hydrateColorsForCard(card);
                    });
                }, 50);

            }

            // =========================
            // Modal render
            // =========================
            function renderModalTemplates(templates, append = false) {
                if (!append) {
                    $modalContainer.empty();
                }

                if (!templates.length && !append) {
                    $modalContainer.html(`
                    <div class="col-12 text-center text-muted py-3">
                        No templates found
                    </div>
                `);
                    return;
                }

                templates.forEach(function (tpl , index) {

                    const cardHtml = `
                    <div class="col-12 col-md-6 col-lg-4">
                        ${buildTemplateInnerCard(tpl , index)}
                    </div>
                `;
                    $modalContainer.append(cardHtml);
                });
                setTimeout(() => {
                    $modalContainer.find('.template-card').each(function () {
                        hydrateColorsForCard(this);
                    });
                }, 50);
            }

            function renderModalPagination() {
                $modalPagination.empty();

                if (nextPageUrl) {
                    $modalPagination.html(`
                    <button
                        id="templates-modal-load-more"
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                    >
                        Load More
                    </button>
                `);
                }
            }
            function hydrateColorsForCard(cardEl) {
                if (!cardEl) return;

                // ✅ hydrate مرة واحدة فقط
                if (cardEl.__colorsHydrated) return;
                cardEl.__colorsHydrated = true;

                const id = String(cardEl.getAttribute('data-id'));
                const saved = savedColorsById.get(id) || [];

                // لو فيه ألوان موجودة بالفعل (اختيارات UI) دمجها مع المحفوظ
                const existing = Array.isArray(cardEl.selectedColors) ? cardEl.selectedColors : [];
                const merged = [...new Set([...saved, ...existing].map(c => String(c).toLowerCase()))];

                cardEl.selectedColors = merged;
                renderSelectedColors(cardEl);
            }


            // =========================
            // Fetch templates (API)
            // =========================
            function getSelectedTypesForRequest() {
                const typeMap = {front: 1, back: 2, none: 3};

                return $('.type-checkbox:checked')
                    .map(function () {
                        const typeName = $(this).data('typeName'); // front / back / none
                        return typeMap[typeName];
                    })
                    .get(); // → [1, 2] مثلاً
            }

            function fetchTemplatesForProduct(productId) {
                if (!productId) {
                    resetTemplatesUI();
                    return;
                }

                resetTemplatesUI();
                currentProductId = productId;

                $templatesCardsContainer.html(`
                <div class="col-12 text-center py-2">
                    Loading templates...
                </div>
            `);
                $templatesWrapper.removeClass('d-none');

                $.ajax({
                    url: "{{ route('product-templates.index') }}",
                    method: "GET",
                    data: {
                        product_without_category_id: productId,
                        request_type: "api",
                        // approach: "without_editor",
                        paginate: true,
                        // has_not_mockups: false,
                        {{--                        mockup_id: "{{ $model->id }}",--}}
                        per_page: 12,
                        types: getSelectedTypesForRequest(),
                    },

                    success: function (response) {
                        const data = response.data ?? {};
                        const items = data.data ?? [];
                        const links = data.links ?? {};

                        firstPageTemplates = items;
                        nextPageUrl = links.next || null;

                        renderTemplateCards(firstPageTemplates);
                    },
                    error: function (xhr) {
                        console.error("Error loading templates", xhr);
                        resetTemplatesUI();
                    }
                });
            }

            // =========================
            // Events: Product change
            // =========================
            $productSelect.on('change', function () {
                const productId = $(this).val();
                fetchTemplatesForProduct(productId);
            });

            // حالة edit: لو فيه value جاهزة
            if ($productSelect.val()) {
                fetchTemplatesForProduct($productSelect.val());
            }

            // =========================
            // Show Remaining → افتح المودال
            // =========================
            $templatesCardsContainer.on('click', '.js-open-templates-modal', function () {
                // ✅ لو المودال متبني بالفعل (وفيه عناصر) افتحه بس
                if ($modalContainer.children().length === 0) {
                    const remaining = firstPageTemplates.slice(3);
                    renderModalTemplates(remaining, false);
                    renderModalPagination();
                }

                $modal.modal('show');
            });

            // =========================
            // Modal: Load More
            // =========================
            $(document).on('click', '#templates-modal-load-more', function () {
                const $btn = $(this);
                if (!nextPageUrl) return;

                $btn.prop('disabled', true).text('Loading...');

                $.ajax({
                    url: nextPageUrl,
                    method: "GET",
                    success: function (res) {
                        const data = res.data ?? {};
                        const items = data.data ?? [];
                        const links = data.links ?? {};

                        if (items.length) {
                            renderModalTemplates(items, true);
                        }

                        nextPageUrl = links.next || null;

                        if (nextPageUrl) {
                            $btn.prop('disabled', false).text('Load More');
                        } else {
                            $btn.remove();
                        }
                    },
                    error: function (xhr) {
                        console.error("Error loading more templates", xhr);
                        $btn.prop('disabled', false).text('Load More');
                    }
                });
            });

            // =========================
            // Show on Mockup (cards + modal)
            // =========================

            $(document).on('click', '.js-show-on-mockup', function () {
                const $cardWrapper = $(this).closest('.template-card');
                const idStr = String($cardWrapper.data('id'));
                const front = $cardWrapper.data('front');
                const back  = $cardWrapper.data('back');
                const none  = $cardWrapper.data('none');

                // highlight selected card
                $('#templatesCardsContainer').find('.template-card .card')
                    .removeClass('border-primary shadow-lg')
                    .css('border-color', '#24B094');

                $cardWrapper.find('.card')
                    .addClass('border-primary shadow-lg')
                    .css('border-color', '#0d6efd');

                // store template_id
                $('#selectedTemplateId').val(idStr);

                // find saved template positions from $model->templates
                const savedTemplate = templatesData.find(t => String(t.id) === idStr);
                const savedPositions = savedTemplate ? savedTemplate.pivot.positions : null;

                // FRONT
                if (front) {
                    loadAndBind(
                        window.canvasFront,
                        front,
                        'front',
                        savedPositions,
                        idStr
                    );
                    document.getElementById('editorFrontWrapper')?.classList.remove('d-none');
                }

                // BACK
                if (back) {
                    loadAndBind(
                        window.canvasBack,
                        back,
                        'back',
                        savedPositions,
                        idStr
                    );
                    document.getElementById('editorBackWrapper')?.classList.remove('d-none');
                }

                // NONE
                if (none) {
                    loadAndBind(
                        window.canvasNone,
                        none,
                        'none',
                        savedPositions,
                        idStr
                    );
                    document.getElementById('editorNoneWrapper')?.classList.remove('d-none');
                }

                // close modal if inside
                if ($(this).closest('#templateModal').length) {
                    const $mainContainer  = $('#templatesCardsContainer');
                    const $modalContainer = $('#templates-modal-container');

                    const $modalCard = $(this).closest('.template-card');
                    const $modalCol  = $modalCard.closest('[class*="col-"]');

                    // placeholder مكان كارت المودال
                    const $ph = $('<div class="__swap_ph__"></div>');
                    $modalCol.before($ph);

                    // آخر كارت من التلاتة اللي برا (بدون show-more)
                    const $mainCards = $mainContainer.find('.template-card').not('.show-more');
                    if (!$mainCards.length) return;

                    const $lastMainCard = $mainCards.last();
                    const $lastMainCol  = $lastMainCard.closest('[class*="col-"]');

                    // 1) دخل آخر كارت برا إلى نفس مكان كارت المودال
                    $lastMainCol.detach()
                        .removeClass('col-12 col-md-4 col-lg-3')
                        .addClass('col-6 col-md-4 mb-2');

                    $ph.replaceWith($lastMainCol); // ✅ هنا اتأكدنا انه اتحط مكانه فعلاً

                    // 2) خرج كارت المودال وادخله أول التلاتة برا
                    $modalCol.detach()
                        .removeClass('col-6 col-md-4 mb-2')
                        .addClass('col-12 col-md-4 col-lg-3');

                    $mainContainer.prepend($modalCol);

                    // (اختياري) اقفل المودال
                    $('#templateModal').modal('hide');
                }

            });

            // =========================
            // Save Positions (cards + modal)
            // =========================

            $(document).on('click', '.js-save-positions', function () {
                if (typeof saveAllTemplatePositions === 'function') {
                    saveAllTemplatePositions();
                }

                buildHiddenTemplateInputs();

                if (window.Toastify) {
                    Toastify({
                        text: "Positions saved successfully",
                        duration: 1500,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                    }).showToast();
                } else {
                    alert('Positions saved successfully');
                }

                // 🔴 لو الزر جوّه المودال → اقفل المودال
                if ($(this).closest('#templateModal').length) {
                    $('#templateModal').modal('hide');
                }
            });

        });
    </script>


    <script>
        // =========================
        // CANVAS HELPER FUNCTIONS
        // =========================
        window.canvasFront = new fabric.Canvas('mockupCanvasFront');
        window.canvasBack = new fabric.Canvas('mockupCanvasBack');
        window.canvasNone = new fabric.Canvas('mockupCanvasNone');

        function loadBaseImage(canvas, baseUrl) {
            fabric.Image.fromURL(baseUrl, function (img) {
                img.set({selectable: false, evented: false});

                const canvasW = canvas.getWidth();
                const canvasH = canvas.getHeight();

                const scale = Math.min(canvasW / img.width, canvasH / img.height);
                const scaledW = img.width * scale;
                const scaledH = img.height * scale;

                const left = (canvasW - scaledW) / 2;
                const top = (canvasH - scaledH) / 2;

                canvas.__mockupMeta = {
                    originalWidth: img.width,
                    originalHeight: img.height,
                    scaledWidth: scaledW,
                    scaledHeight: scaledH,
                    offsetLeft: left,
                    offsetTop: top
                };

                canvas.setBackgroundImage(
                    img,
                    canvas.renderAll.bind(canvas),
                    {
                        scaleX: scale,
                        scaleY: scale,
                        left: left,
                        top: top,
                        originX: 'left',
                        originY: 'top'
                    }
                );
            });
        }

        function clearTemplateDesigns(canvas, type) {
            const objects = canvas.getObjects();
            objects.forEach(obj => {
                if (obj.templateType === type) {
                    canvas.remove(obj);
                }
            });
            canvas.renderAll();
        }

        function syncTemplateInputs(obj, type) {
            const wrapper = document.getElementById('templatesCardsWrapper');
            if (!wrapper) return;

            const canvas = obj.canvas;
            const meta = canvas && canvas.__mockupMeta;
            if (!meta) return;

            const xInput = wrapper.querySelector(`.template_x.${type}`);
            const yInput = wrapper.querySelector(`.template_y.${type}`);
            const widthInput = wrapper.querySelector(`.template_width.${type}`);
            const heightInput = wrapper.querySelector(`.template_height.${type}`);
            const angleInput = wrapper.querySelector(`.template_angle.${type}`);

            if (!xInput || !yInput || !widthInput || !heightInput || !angleInput) return;

            const center = obj.getCenterPoint();
            const wReal = obj.width * obj.scaleX;
            const hReal = obj.height * obj.scaleY;

            const xPct = (center.x - meta.offsetLeft) / meta.scaledWidth;
            const yPct = (center.y - meta.offsetTop) / meta.scaledHeight;
            const wPct = wReal / meta.scaledWidth;
            const hPct = hReal / meta.scaledHeight;

            xInput.value = xPct.toFixed(6);
            yInput.value = yPct.toFixed(6);
            widthInput.value = wPct.toFixed(6);
            heightInput.value = hPct.toFixed(6);
            angleInput.value = obj.angle || 0;
        }

        function clearTemplateInputsForObject(type) {
            const wrapper = document.getElementById('templatesCardsWrapper');
            if (!wrapper) return;

            const xInput = wrapper.querySelector(`.template_x.${type}`);
            const yInput = wrapper.querySelector(`.template_y.${type}`);
            const widthInput = wrapper.querySelector(`.template_width.${type}`);
            const heightInput = wrapper.querySelector(`.template_height.${type}`);
            const angleInput = wrapper.querySelector(`.template_angle.${type}`);

            [xInput, yInput, widthInput, heightInput, angleInput].forEach(inp => {
                if (inp) inp.value = '';
            });
        }

        function renderDeleteIcon(ctx, left, top) {
            const size = 18;

            ctx.save();
            ctx.beginPath();
            ctx.arc(left, top, size / 2, 0, Math.PI * 2, false);
            ctx.fillStyle = "#ff4d4f";
            ctx.fill();

            ctx.strokeStyle = "#ffffff";
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(left - 4, top - 4);
            ctx.lineTo(left + 4, top + 4);
            ctx.moveTo(left + 4, top - 4);
            ctx.lineTo(left - 4, top + 4);
            ctx.stroke();

            ctx.restore();
        }

        function addDeleteControl(obj, type) {
            obj.controls.deleteControl = new fabric.Control({
                x: 0.5,
                y: -0.5,
                offsetX: 0,
                offsetY: 0,
                cursorStyle: 'pointer',
                cornerSize: 24,
                mouseUpHandler: function (eventData, transform) {
                    const target = transform.target;
                    const canvas = target.canvas;

                    clearTemplateInputsForObject(type);

                    canvas.remove(target);
                    canvas.requestRenderAll();

                    return true;
                },
                render: renderDeleteIcon
            });
        }

        function applyDefaultPlacement(img, canvas, meta) {
            const defaultWidthRatio = 0.35;


            if (meta) {
                const targetW = meta.scaledWidth * defaultWidthRatio;
                const scale = targetW / img.width;

                img.scaleX = img.scaleY = scale;

                img.left = meta.offsetLeft + meta.scaledWidth / 2;
                img.top = meta.offsetTop + meta.scaledHeight * 0.35;
            } else {
                const canvasW = canvas.getWidth();
                const canvasH = canvas.getHeight();
                const targetW = canvasW * defaultWidthRatio;
                const scale = targetW / img.width;

                img.scaleX = img.scaleY = scale;
                img.left = canvasW / 2;
                img.top = canvasH / 2;
            }
        }

        function loadAndBind(canvas, designUrl, type, savedPositions, templateId) {
            clearTemplateDesigns(canvas, type);

            fabric.Image.fromURL(designUrl, function (img) {
                img.set({
                    originX: 'center',
                    originY: 'center',
                    transparentCorners: false
                });

                img.templateType = type;
                img.templateId   = templateId;

                const meta = canvas.__mockupMeta;

                if (savedPositions && meta) {
                    const prefix = type + '_';
                    const xPct  = parseFloat(savedPositions[prefix + 'x']      ?? 0.5);
                    const yPct  = parseFloat(savedPositions[prefix + 'y']      ?? 0.5);
                    const wPct  = parseFloat(savedPositions[prefix + 'width']  ?? 0.4);
                    const hPct  = parseFloat(savedPositions[prefix + 'height'] ?? 0.4);
                    const angle = parseFloat(savedPositions[prefix + 'angle']  ?? 0);

                    img.left   = meta.offsetLeft + meta.scaledWidth  * xPct;
                    img.top    = meta.offsetTop  + meta.scaledHeight * yPct;

                    const scaleX = (wPct * meta.scaledWidth)  / img.width;
                    const scaleY = (hPct * meta.scaledHeight) / img.height;
                    img.scaleX = img.scaleY = Math.min(scaleX, scaleY);

                    img.angle = angle;
                } else {
                    applyDefaultPlacement(img, canvas, meta);
                }

                addDeleteControl(img, type);
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();

                syncTemplateInputs(img, type);
            });
        }

        function saveAllTemplatePositions() {
            if (window.canvasFront) {
                window.canvasFront.getObjects().forEach(obj => {
                    if (obj.templateType === 'front') {
                        syncTemplateInputs(obj, 'front');
                    }
                });
            }

            if (window.canvasBack) {
                window.canvasBack.getObjects().forEach(obj => {
                    if (obj.templateType === 'back') {
                        syncTemplateInputs(obj, 'back');
                    }
                });
            }

            if (window.canvasNone) {
                window.canvasNone.getObjects().forEach(obj => {
                    if (obj.templateType === 'none') {
                        syncTemplateInputs(obj, 'none');
                    }
                });
            }
        }

        function bindCanvasUpdates(canvas, type) {
            canvas.on('object:modified', function (e) {
                const obj = e.target;
                syncTemplateInputs(obj, type);
            });
        }

        bindCanvasUpdates(window.canvasFront, "front");
        bindCanvasUpdates(window.canvasBack, "back");
        bindCanvasUpdates(window.canvasNone, "none");
    </script>

    <script>
        // =========================
        // TYPE CHECKBOXES + UPLOAD AREAS
        // =========================
        const checkboxes = document.querySelectorAll('.type-checkbox');
        const fileInputsContainer = document.getElementById('fileInputsContainer');

        function removeCanvasByType(type) {
            let canvas = null;
            let wrapperId = "";

            if (type === "front") {
                canvas = window.canvasFront;
                wrapperId = "editorFrontWrapper";
            } else if (type === "back") {
                canvas = window.canvasBack;
                wrapperId = "editorBackWrapper";
            } else if (type === "none") {
                canvas = window.canvasNone;
                wrapperId = "editorNoneWrapper";
            }

            if (!canvas) return;

            // 1️⃣ Remove all template objects of this type
            canvas.getObjects().forEach(o => {
                if (o.templateType === type) canvas.remove(o);
            });

            // 2️⃣ Remove background image
            canvas.setBackgroundImage(null, canvas.renderAll.bind(canvas));

            // 3️⃣ Clear saved input fields
            clearTemplateInputsForObject(type);

            // 4️⃣ Hide canvas wrapper
            const wrapper = document.getElementById(wrapperId);
            if (wrapper) wrapper.classList.add("d-none");

            canvas.renderAll();
        }

        function toggleCheckboxes() {
            let selectedTypes = [...checkboxes]
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            checkboxes.forEach(cb => {
                const type = cb.dataset.typeName;

                cb.disabled =
                    (selectedTypes.includes('none') && (type === 'front' || type === 'back')) ||
                    ((selectedTypes.includes('front') || selectedTypes.includes('back')) && type === 'none');
            });

            renderFileInputs();
            if (window.jQuery) {
                // const $prod = $('#productsSelect');
                // if ($prod.length && $prod.val()) {
                //     $prod.trigger('change');
                // }
            }
        }

        function hideCanvasForType(type) {
            const wrapperIdMap = {
                front: 'editorFrontWrapper',
                back: 'editorBackWrapper',
                none: 'editorNoneWrapper',
            };

            const canvasMap = {
                front: window.canvasFront,
                back: window.canvasBack,
                none: window.canvasNone,
            };

            // أخفي الـ wrapper
            const wrapper = document.getElementById(wrapperIdMap[type]);
            if (wrapper) {
                wrapper.classList.add('d-none');
            }

            // امسح الكانفاس (الخلفية + الأوبجكتس)
            const canvas = canvasMap[type];
            if (canvas) {
                canvas.clear();
                canvas.renderAll();
                delete canvas.__mockupMeta; // ننسى الـ meta بتاعة الموكاب
            }

            // صفّر الـ hidden inputs بتاعة النوع ده
            if (typeof clearTemplateInputsForObject === 'function') {
                clearTemplateInputsForObject(type);
            }
        }
        // =========================
        // WARP POINTS EDITOR
        // =========================
        function normalizeWarpPoints(saved) {
            if (!saved) return null;

            if (typeof saved === 'string') {
                try {
                    saved = JSON.parse(saved);
                } catch (e) {
                    return null;
                }
            }

            // لو جاي array بالفعل
            if (Array.isArray(saved) && saved.length === 4) {
                return saved.map((p) => ({
                    x: Number(p.x),
                    y: Number(p.y),
                }));
            }

            // لو جاي object بالشكل tl,tr,br,bl
            if (
                saved.tl && saved.tr &&
                saved.br && saved.bl
            ) {
                return [
                    { x: Number(saved.tl.x), y: Number(saved.tl.y) },
                    { x: Number(saved.tr.x), y: Number(saved.tr.y) },
                    { x: Number(saved.br.x), y: Number(saved.br.y) },
                    { x: Number(saved.bl.x), y: Number(saved.bl.y) },
                ];
            }

            return null;
        }
        const warpState = {};

        // ✅ Pre-load existing warp points from DB (per side)
        const existingWarpPoints = @json($existingWarpPoints);
        console.log(existingWarpPoints)
        function initWarpEditor(side, imageUrl) {
            const wrapper = document.getElementById(`warp-editor-${side}`);
            const img     = document.getElementById(`warp-preview-${side}`);
            const canvas  = document.getElementById(`warp-canvas-${side}`);
            if (!wrapper || !img || !canvas) return;

            // ✅ Use saved warp points if available, else default corners
            const saved = normalizeWarpPoints(existingWarpPoints[side]);

            warpState[side] = {
                points: saved ?? [
                    { x: 0.1, y: 0.1 },
                    { x: 0.9, y: 0.1 },
                    { x: 0.9, y: 0.9 },
                    { x: 0.1, y: 0.9 },
                ],
                dragging: null,
            };

            img.src = imageUrl;
            wrapper.classList.remove('d-none');

            const LABELS = ['TL', 'TR', 'BR', 'BL'];
            const RADIUS  = 10;

            function pxOf(p) {
                return { x: p.x * canvas.width, y: p.y * canvas.height };
            }

            function draw() {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const pts = warpState[side].points;

                ctx.beginPath();
                const f = pxOf(pts[0]);
                ctx.moveTo(f.x, f.y);
                pts.slice(1).forEach(p => { const px = pxOf(p); ctx.lineTo(px.x, px.y); });
                ctx.closePath();
                ctx.fillStyle = 'rgba(36,176,148,0.08)';
                ctx.fill();
                ctx.strokeStyle = 'rgba(36,176,148,0.85)';
                ctx.lineWidth   = 1.5;
                ctx.setLineDash([6, 4]);
                ctx.stroke();
                ctx.setLineDash([]);

                ctx.beginPath();
                const tl = pxOf(pts[0]), br = pxOf(pts[2]);
                const tr = pxOf(pts[1]), bl = pxOf(pts[3]);
                ctx.moveTo(tl.x, tl.y); ctx.lineTo(br.x, br.y);
                ctx.moveTo(tr.x, tr.y); ctx.lineTo(bl.x, bl.y);
                ctx.strokeStyle = 'rgba(36,176,148,0.25)';
                ctx.lineWidth   = 0.8;
                ctx.stroke();

                pts.forEach((p, i) => {
                    const px = pxOf(p);
                    ctx.beginPath();
                    ctx.arc(px.x, px.y, RADIUS, 0, Math.PI * 2);
                    ctx.fillStyle   = '#24B094';
                    ctx.fill();
                    ctx.strokeStyle = '#fff';
                    ctx.lineWidth   = 2;
                    ctx.stroke();
                    ctx.fillStyle    = '#fff';
                    ctx.font         = 'bold 10px sans-serif';
                    ctx.textAlign    = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(LABELS[i], px.x, px.y);
                });
            }

            function resize() {
                canvas.width  = img.clientWidth  || img.naturalWidth;
                canvas.height = img.clientHeight || img.naturalHeight;
                draw();
            }

            function nearestHandle(mx, my) {
                for (let i = 0; i < warpState[side].points.length; i++) {
                    const p = pxOf(warpState[side].points[i]);
                    if (Math.hypot(p.x - mx, p.y - my) < RADIUS + 5) return i;
                }
                return null;
            }

            canvas.addEventListener('pointerdown', e => {
                const rect = canvas.getBoundingClientRect();
                warpState[side].dragging = nearestHandle(e.clientX - rect.left, e.clientY - rect.top);
                if (warpState[side].dragging !== null) canvas.setPointerCapture(e.pointerId);
            });

            canvas.addEventListener('pointermove', e => {
                if (warpState[side].dragging === null) return;
                const rect = canvas.getBoundingClientRect();
                warpState[side].points[warpState[side].dragging] = {
                    x: Math.min(1, Math.max(0, (e.clientX - rect.left)  / canvas.width)),
                    y: Math.min(1, Math.max(0, (e.clientY - rect.top)   / canvas.height)),
                };
                draw();
                syncWarpInput(side);
            });

            canvas.addEventListener('pointerup', () => {
                warpState[side].dragging = null;
            });

            img.addEventListener('load', resize);
            new ResizeObserver(resize).observe(img);
            if (img.complete) resize();

            syncWarpInput(side);
        }

        function syncWarpInput(side) {
            if (!warpState[side]) return;

            const form = document.getElementById('editMockupForm');
            const [tl, tr, br, bl] = warpState[side].points;

            const points = { tl, tr, br, bl };

            // ✅ Remove old warp inputs for this side
            form.querySelectorAll(`[data-warp-side="${side}"]`).forEach(el => el.remove());

            // ✅ Write flat inputs — no JSON.stringify, no escaping
            Object.entries(points).forEach(([corner, coords]) => {
                ['x', 'y'].forEach(axis => {
                    const input       = document.createElement('input');
                    input.type        = 'hidden';
                    input.name        = `warp_points[${side}][${corner}][${axis}]`;
                    input.value       = coords[axis];
                    input.dataset.warpSide = side;   // for cleanup on re-sync
                    form.appendChild(input);
                });
            });
        }



        function resetWarp(side) {
            if (!warpState[side]) return;
            warpState[side].points = [
                { x: 0.1, y: 0.1 },   // tl
                { x: 0.9, y: 0.1 },   // tr
                { x: 0.9, y: 0.9 },   // br
                { x: 0.1, y: 0.9 },   // bl
            ];

            // re-init editor with current image
            const imgEl = document.getElementById(`warp-preview-${side}`);
            if (imgEl?.src) initWarpEditor(side, imgEl.src);

            syncWarpInput(side); // ✅ now sends {tl, tr, br, bl}
        }

        $(document).on('click', '.js-reset-warp', function () {
            resetWarp($(this).data('side'));
            Toastify({ text: 'Reset to default corners', backgroundColor: '#6c757d', duration: 1200 }).showToast();
        });


        // Disable Dropzone auto-discovery
        Dropzone.autoDiscover = false;
        const dropzoneInstances = {};

        function renderFileInputs() {
            if (!fileInputsContainer) return;

            let selectedTypes = [...checkboxes]
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            // Remove blocks + destroy dropzones for unchecked types
            ['front', 'back', 'none'].forEach(type => {
                if (!selectedTypes.includes(type)) {
                    const block = document.getElementById(`${type}-file-block`);
                    if (block) block.remove();

                    ['base_image', 'mask_image', 'shadow_image', 'displacement_image', 'light_image'].forEach(part => {
                        const key = `${type}-${part}`;
                        if (dropzoneInstances[key]) {
                            dropzoneInstances[key].destroy();
                            delete dropzoneInstances[key];
                        }
                    });

                    if (typeof hideCanvasForType === 'function') {
                        hideCanvasForType(type);
                    }
                }
            });

            // Add blocks for newly selected types
            selectedTypes.forEach(type => {
                if (document.getElementById(`${type}-file-block`)) return;

                const typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
                const block     = document.createElement('div');
                block.className = 'col-md-6';
                block.id        = `${type}-file-block`;

                block.innerHTML = `
            <label class="label-text">${typeLabel}</label>
            <hr style="height:2px;background-color:#CED5D4;"/>

            <div class="mb-2">
                <label class="form-label label-text">${typeLabel} Base Image</label>
                <div id="dz-${type}-base_image" class="dropzone dropzone-area">
                    <div class="dz-message">
                        <i data-feather="upload-cloud" style="width:28px;height:28px;stroke:#24B094;"></i>
                        <p class="mt-1 mb-0">Drag &amp; drop or <u>click to upload</u></p>
                        <small class="text-muted">PNG only</small>
                    </div>
                </div>
    <small class="form-text text-muted">
                                                            Upload an image with 1600w x 1800h [8:9].
                                                        </small>
            </div>

            <div class="mb-2">
                <label class="form-label label-text">${typeLabel} Mask Image</label>
                <div id="dz-${type}-mask_image" class="dropzone dropzone-area">
                    <div class="dz-message">
                        <i data-feather="upload-cloud" style="width:28px;height:28px;stroke:#24B094;"></i>
                        <p class="mt-1 mb-0">Drag &amp; drop or <u>click to upload</u></p>
                        <small class="text-muted">PNG only</small>
                    </div>
                </div>
    <small class="form-text text-muted">
                                                            Upload an image with 1600w x 1800h [8:9].
                                                        </small>
            </div>

            <div class="mb-2">
                <label class="form-label label-text">${typeLabel} Shadow Image</label>
                <div id="dz-${type}-shadow_image" class="dropzone dropzone-area">
                    <div class="dz-message">
                        <i data-feather="upload-cloud" style="width:28px;height:28px;stroke:#24B094;"></i>
                        <p class="mt-1 mb-0">Drag &amp; drop or <u>click to upload</u></p>
                        <small class="text-muted">PNG only</small>
                    </div>
                </div>
    <small class="form-text text-muted">
                                                            Upload an image with 1600w x 1800h [8:9].
                                                        </small>
            </div>

            <div class="mb-2">
                <label class="form-label label-text">${typeLabel} Displacement Image</label>
                <div id="dz-${type}-displacement_image" class="dropzone dropzone-area">
                    <div class="dz-message">
                        <i data-feather="upload-cloud" style="width:28px;height:28px;stroke:#24B094;"></i>
                        <p class="mt-1 mb-0">Drag &amp; drop or <u>click to upload</u></p>
                        <small class="text-muted">PNG only</small>
                    </div>
                </div>
    <small class="form-text text-muted">
                                                            Upload an image with 1600w x 1800h [8:9].
                                                        </small>
            </div>

            <div class="mb-2">
                <label class="form-label label-text">${typeLabel} Highlight Image</label>
                <div id="dz-${type}-light_image" class="dropzone dropzone-area">
                    <div class="dz-message">
                        <i data-feather="upload-cloud" style="width:28px;height:28px;stroke:#24B094;"></i>
                        <p class="mt-1 mb-0">Drag &amp; drop or <u>click to upload</u></p>
                        <small class="text-muted">PNG only</small>
                    </div>
                </div>
    <small class="form-text text-muted">
                                                            Upload an image with 1600w x 1800h [8:9].
                                                        </small>
            </div>


        `;

                document.getElementById('fileInputsContainer').appendChild(block);
                feather.replace();

                setTimeout(() => {
                    initDropzone(type, 'base_image');
                    initDropzone(type, 'mask_image');
                    initDropzone(type, 'shadow_image');
                    initDropzone(type, 'displacement_image');
                    initDropzone(type, 'light_image');
                }, 50);
            });
        }

        function initDropzone(type, part) {
            const key       = `${type}-${part}`;
            const elId      = `dz-${type}-${part}`;
            const el        = document.getElementById(elId);
            const inputName = `${type}_${part}`;           // e.g. front_base_image

            if (!el || dropzoneInstances[key]) return;

            // Hidden input to store uploaded media ID
            let hiddenInput = document.querySelector(`input[name="${inputName}_id"]`);
            if (!hiddenInput) {
                hiddenInput        = document.createElement('input');
                hiddenInput.type   = 'hidden';
                hiddenInput.name   = `${inputName}_id`;
                document.getElementById('editMockupForm').appendChild(hiddenInput);
            }

            const dz = new Dropzone(`#${elId}`, {
                url:           "{{ route('media.store') }}",
                paramName:     "file",
                maxFiles:      1,
                maxFilesize:   12,
                acceptedFiles: "image/png",
                headers:       { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                addRemoveLinks: true,
                dictRemoveFile: '✕ Remove',
                dictDefaultMessage: '',
                dictInvalidFileType: "Only PNG files are allowed.",

                params: {
                    "customProperties[role]": part.replace('_image', ''),  // base | mask | shadow | displacement | light
                    "customProperties[side]": type,                         // front | back | none
                },

                init: function () {
                    const dzInstance = this;

                    // Only one file at a time
                    dzInstance.on('addedfile', function () {
                        if (dzInstance.files.length > 1) {
                            dzInstance.removeFile(dzInstance.files[0]);
                        }
                    });

                    // Upload success → store media ID
                    dzInstance.on('success', function (file, response) {
                        if (response.success && response.data) {
                            file._mediaId     = response.data.id;
                            hiddenInput.value = response.data.id;

                            if (part === 'base_image' && response.data.url) {
                                const canvasMap  = { front: window.canvasFront, back: window.canvasBack, none: window.canvasNone };
                                const wrapperMap = { front: 'editorFrontWrapper', back: 'editorBackWrapper', none: 'editorNoneWrapper' };

                                loadBaseImage(canvasMap[type], response.data.url);
                                document.getElementById(wrapperMap[type])?.classList.remove('d-none');

                                // ✅ show warp editor for newly uploaded base image
                                initWarpEditor(type, response.data.url);
                            }
                        }
                    });

                    dzInstance.on('error', function (file, message) {
                        const msg = typeof message === 'object' ? (message.message ?? 'Upload failed') : message;
                        console.error(`Dropzone [${key}] error:`, msg);
                    });

                    // Remove → delete from server + clear hidden input
                    dzInstance.on('removedfile', function (file) {
                        hiddenInput.value = '';

                        if (file._mediaId) {
                            fetch("{{ url('api/v1/media') }}/" + file._mediaId, {
                                method:  'DELETE',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                            }).catch(err => console.error('Media delete failed:', err));
                        }

                        if (part === 'base_image' && typeof hideCanvasForType === 'function') {
                            hideCanvasForType(type);
                        }
                    });

                    // ✅ Preload existing file if available
                    const existingUrl = getExistingMediaUrl(type, part);
                    if (existingUrl) {
                        preloadDropzoneFile(dzInstance, existingUrl, type, part, hiddenInput);
                    }
                }
            });

            dropzoneInstances[key] = dz;
        }

        // ─── Preload existing media from server ──────────────────────────────────────
        // Pass existing URLs from blade to JS
        const existingMedia   = @json($existingMedia);
        const existingMediaIds = @json($existingMediaIds);

        function getExistingMediaUrl(type, part) {
            return (existingMedia[type] && existingMedia[type][part]) || null;
        }

        function preloadDropzoneFile(dz, url, type, part, hiddenInput) {
            const mediaId = existingMediaIds[type]?.[part] ?? null;
            if (mediaId) {
                hiddenInput.value  = mediaId;
                hiddenInput.dataset.existingId = mediaId;
            }

            const fileName = url.split('/').pop();
            const mockFile = { name: fileName, size: 0, _mediaId: mediaId, _isExisting: true };

            dz.emit('addedfile', mockFile);
            dz.emit('thumbnail', mockFile, url);
            dz.emit('complete', mockFile);
            dz.files.push(mockFile);

            if (part === 'base_image') {
                const canvasMap  = { front: window.canvasFront, back: window.canvasBack, none: window.canvasNone };
                const wrapperMap = { front: 'editorFrontWrapper', back: 'editorBackWrapper', none: 'editorNoneWrapper' };

                loadBaseImage(canvasMap[type], url);
                document.getElementById(wrapperMap[type])?.classList.remove('d-none');

                // ✅ show warp editor with existing base image
                // Delay slightly to let the block render in DOM first
                setTimeout(() => initWarpEditor(type, url), 100);
            }
        }

        checkboxes.forEach(cb => cb.addEventListener('change', toggleCheckboxes));
    </script>
    <script>
        let generateMockupsAfterSave = false;

        const generateMockupsUrl = @json(route('mockups.generate-template-files', ['mockup' => $model->id]));

        function resetGenerateButton() {
            const $button = $('#generateTemplateMockupFiles');
            $button.prop('disabled', false);
            $('#generateTemplateMockupFilesLoader').addClass('d-none');
            $button.find('.btn-text').text('Generate Mockups');
        }


        let generationPollTimer = null;
        let generationReloadOnClose = false;
        let generationCurrentJobId = null;
        let generationBackgroundMode = false;
        let generationTerminal = false;
        const bulkJobStatusUrlTemplate = @json(route('bulk-jobs.status', ['__JOB_ID__']));
        const bulkJobCancelUrlTemplate = @json(\Illuminate\Support\Facades\Route::has('bulk-jobs.cancel') ? route('bulk-jobs.cancel', ['__JOB_ID__']) : null);

        function extractBulkJobId(response) {
            return response?.data?.data?.bulk_job_id ?? response?.data?.bulk_job_id ?? response?.bulk_job_id ?? response?.data?.data?.id ?? response?.data?.id ?? null;
        }

        function formatRemainingSeconds(seconds) {
            seconds = Math.max(0, parseInt(seconds || 0, 10));
            if (!seconds) return 'Finishing...';
            if (seconds < 60) return `${seconds}s remaining`;
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${minutes}m ${secs}s remaining`;
        }

        function resetGenerationProgressModal() {
            generationTerminal = false;
            $('#generationProgressStatus').text('Preparing...');
            $('#generationProgressPercent').text('0%');
            $('#generationProgressBar').css('width', '0%').attr('aria-valuenow', 0);
            $('#generationProgressCount').text('0 / 0 processed');
            $('#generationTotalCount').text('0');
            $('#generationDoneCount').text('0');
            $('#generationFailedCount').text('0');
            $('#generationProgressRemaining').text('Calculating...');
            $('#generationProgressError').addClass('d-none').text('');
            $('#generationProgressClose').addClass('d-none');
            $('#generationContinueBackground, #generationMinimizeButton').removeClass('d-none');
            $('#generationCancelButton').toggleClass('d-none', !bulkJobCancelUrlTemplate).prop('disabled', false).html('⊗ &nbsp; Cancel generation');
            $('#generationSpinnerShell').removeClass('d-none');
            $('#generationFloatingStatus').addClass('d-none');
            $('#generationFloatingText').text('Generating mockups');
            $('#generationFloatingPercent').text('0%');
        }

        function startGenerationProgress(jobId, reloadOnClose = false) {
            if (!jobId) return false;
            clearTimeout(generationPollTimer);
            generationCurrentJobId = jobId;
            generationReloadOnClose = reloadOnClose;
            generationBackgroundMode = false;
            resetGenerationProgressModal();
            $('#generationProgressModal').modal('show');
            const statusUrl = bulkJobStatusUrlTemplate.replace('__JOB_ID__', jobId);

            const poll = function () {
                $.ajax({
                    url: statusUrl,
                    type: 'GET',
                    headers: {'Accept': 'application/json'},
                    success: function (response) {
                        const job = response?.data?.data ?? response?.data ?? response ?? {};
                        const status = String(job.status || 'processing');
                        const percent = Math.max(0, Math.min(100, Number(job.percent) || 0));
                        const completed = Number(job.completed_count) || 0;
                        const failed = Number(job.failed_count) || 0;
                        const total = Number(job.total_count) || 0;
                        const terminal = ['completed', 'completed_with_errors', 'failed', 'cancelled'].includes(status);

                        $('#generationProgressPercent').text(`${percent.toFixed(1)}%`);
                        $('#generationProgressBar').css('width', `${percent}%`).attr('aria-valuenow', percent);
                        $('#generationProgressCount').text(`${completed} / ${total} processed`);
                        $('#generationTotalCount').text(total);
                        $('#generationDoneCount').text(completed);
                        $('#generationFailedCount').text(failed);
                        $('#generationProgressRemaining').text(terminal ? 'Finished' : formatRemainingSeconds(job.estimated_remaining_seconds));
                        $('#generationProgressStatus').text(status.replaceAll('_', ' '));
                        $('#generationFloatingPercent').text(`${Math.round(percent)}%`);

                        if (terminal) {
                            generationTerminal = true;
                            $('#generationSpinnerShell').addClass('d-none');
                            $('#generationContinueBackground, #generationMinimizeButton, #generationCancelButton').addClass('d-none');
                            $('#generationProgressClose').removeClass('d-none');
                            $('#generationFloatingText').text(status === 'completed' ? 'Generation complete' : status.replaceAll('_', ' '));
                            if (status === 'failed' || status === 'cancelled') {
                                $('#generationProgressError').removeClass('d-none').text(status === 'failed' ? 'Mockup generation failed.' : 'Mockup generation was cancelled.');
                            } else if (status === 'completed_with_errors') {
                                $('#generationProgressError').removeClass('d-none').text('Generation completed with some failed items.');
                            }
                            if (typeof resetGenerateButton === 'function') resetGenerateButton();
                            return;
                        }

                        generationPollTimer = setTimeout(poll, 1000);
                    },
                    error: function (xhr) {
                        $('#generationProgressError').removeClass('d-none').text(xhr.responseJSON?.message || 'Unable to read generation progress. Retrying...');
                        generationPollTimer = setTimeout(poll, 2000);
                    }
                });
            };

            poll();
            return true;
        }

        $(document).on('click', '#generationContinueBackground, #generationMinimizeButton', function () {
            if (!generationCurrentJobId || generationTerminal) return;
            generationBackgroundMode = true;
            $('#generationFloatingStatus').removeClass('d-none');
            $('#generationProgressModal').modal('hide');
        });

        $(document).on('click', '#generationFloatingStatus', function () {
            generationBackgroundMode = false;
            $(this).addClass('d-none');
            $('#generationProgressModal').modal('show');
        });

        $(document).on('click', '#generationCancelButton', function () {
            if (!bulkJobCancelUrlTemplate || !generationCurrentJobId || generationTerminal) return;
            const $button = $(this);
            $button.prop('disabled', true).text('Cancelling...');
            $.ajax({
                url: bulkJobCancelUrlTemplate.replace('__JOB_ID__', generationCurrentJobId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                error: function (xhr) {
                    $button.prop('disabled', false).html('⊗ &nbsp; Cancel generation');
                    $('#generationProgressError').removeClass('d-none').text(xhr.responseJSON?.message || 'Failed to cancel generation.');
                }
            });
        });

        $(document).on('hidden.bs.modal', '#generationProgressModal', function () {
            if (generationBackgroundMode) return;
            if (!generationTerminal) return;
            clearTimeout(generationPollTimer);
            generationCurrentJobId = null;
            $('#generationFloatingStatus').addClass('d-none');
            if (generationReloadOnClose) location.reload();
        });

        function generateTemplateMockupFiles() {
            $.ajax({
                url: generateMockupsUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function (response) {
                    const jobId = extractBulkJobId(response);
                    Toastify({
                        text: response.message || 'Mockup generation started successfully',
                        duration: 2500,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#28a745',
                        close: true
                    }).showToast();
                    if (!startGenerationProgress(jobId, true)) {
                        Toastify({
                            text: 'Generation started but no bulk job id was returned.',
                            duration: 3000,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#dc3545',
                            close: true
                        }).showToast();
                        resetGenerateButton();
                    }
                },
                error: function (xhr) {
                    Toastify({
                        text: xhr.responseJSON?.message || 'Failed to generate mockups',
                        duration: 3000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#dc3545',
                        close: true
                    }).showToast();
                    resetGenerateButton();
                },
                complete: function () {
                    generateMockupsAfterSave = false;
                }
            });
        }

        $(document).on('click', '#generateTemplateMockupFiles', function () {
            const $button = $(this);
            generateMockupsAfterSave = true;
            $button.prop('disabled', true);
            $('#generateTemplateMockupFilesLoader').removeClass('d-none');
            $button.find('.btn-text').text('Saving...');
            if (typeof buildHiddenTemplateInputs === 'function') buildHiddenTemplateInputs();
            $('#editMockupForm').trigger('submit');
        });

        $(document).ready(function () {
            handleAjaxFormSubmit('#editMockupForm', {
                successMessage: 'Mockup Updated Successfully',
                onSuccess: function () {
                    if (generateMockupsAfterSave) {
                        $('#generateTemplateMockupFiles').find('.btn-text').text('Generating...');
                        generateTemplateMockupFiles();
                        return;
                    }
                    location.reload();
                }
            });
        });
    </script>
    <script>
        // =========================
        // MAIN IMAGE UPLOAD + FORM SUBMIT
        // =========================

        $(document).ready(function () {
            let input = $('#product-image-main');
            let uploadArea = $('#upload-area');
            let progress = $('#upload-progress');
            let progressBar = $('.progress-bar');
            let uploadedImage = $('#uploaded-image');
            let removeButton = $('#remove-image');

            uploadArea.on('click', function () {
                input.click();
            });

            input.on('change', function (e) {
                handleMainImageFiles(e.target.files);
            });

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
                handleMainImageFiles(e.originalEvent.dataTransfer.files);
            });

            function handleMainImageFiles(files) {
                if (files.length > 0) {
                    let file = files[0];
                    let dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input[0].files = dataTransfer.files;

                    progress.removeClass('d-none');
                    progressBar.css('width', '0%');

                    let fakeProgress = 0;
                    let interval = setInterval(function () {
                        fakeProgress += 10;
                        progressBar.css('width', fakeProgress + '%');

                        if (fakeProgress >= 100) {
                            clearInterval(interval);

                            let reader = new FileReader();
                            reader.onload = function (e) {
                                uploadedImage.find('img').attr('src', e.target.result);
                                uploadedImage.removeClass('d-none');
                                progress.addClass('d-none');

                                $('#file-details .file-name').text(file.name);
                                $('#file-details .file-size').text((file.size / 1024).toFixed(2) + ' KB');
                            }
                            reader.readAsDataURL(file);
                        }
                    }, 100);
                }
            }

            removeButton.on('click', function () {
                uploadedImage.addClass('d-none');
                input.val('');
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editMockupForm');
            if (!form) return;

            form.addEventListener('submit', function () {
                if (typeof saveAllTemplatePositions === 'function') {
                    saveAllTemplatePositions();
                }

                // ✅ sync warp points for all active sides before submit
                ['front', 'back', 'none'].forEach(side => syncWarpInput(side));

                buildHiddenTemplateInputs();
            });

            const params = new URLSearchParams(window.location.search);
            const templateId = params.get('template_id');
            if (!templateId) return;

            // 🕒 نحاول نلاقي الكارد كل نصف ثانية لمدة 10 ثواني
            let attempts = 0;
            const interval = setInterval(() => {
                const card = document.querySelector(`.template-card[data-id="${templateId}"] .js-show-on-mockup`);
                attempts++;

                if (card) {
                    clearInterval(interval);
                    console.log('✅ Auto-loading template', templateId);
                    card.click();
                } else if (attempts > 20) { // 20 محاولة × 500ms = 10 ثواني
                    clearInterval(interval);
                    console.warn('⚠️ Template card not found for ID:', templateId);
                }
            }, 500);
        });
    </script>

@endsection

