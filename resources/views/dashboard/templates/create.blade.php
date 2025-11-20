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

@section('content')
<section id="multiple-column-form ">
    <div class="row ">
        <div class="col-12 ">
            <div class="card">
                <div class="card-body ">
                    <form id="addTemplateForm" enctype="multipart/form-data" method="post"
                        action="{{ route('templates.redirect.store') }}">
                        @csrf
                        <div class="flex-grow-1">
                            <div class="">
                                <div class="form-group mb-2">
                                    <label class="label-text mb-1">Template Image</label>

                                    <!-- Dropzone Container -->
                                    <div id="main-template-dropzone" class="dropzone border rounded p-3"
                                        style="cursor:pointer; min-height:150px;">
                                        <div class="dz-message" data-dz-message>
                                            <span>Drop image here or click to upload</span>
                                        </div> <!-- Hidden input for uploaded file ID -->
                                        <input type="hidden" name="template_image_main_id"
                                            id="uploadedMainTemplateImage">
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="label-text mb-1">Template Model Image</label>

                                    <!-- Dropzone Container -->
                                    <div id="template-dropzone" class="dropzone border rounded p-3"
                                        style="cursor:pointer; min-height:150px;">
                                        <div class="dz-message" data-dz-message>
                                            <span>Drop image here or click to upload</span>
                                        </div> <!-- Hidden input for uploaded file ID -->
                                        <input type="hidden" name="template_image_id" id="uploadedTemplateImage">
                                    </div>
                                </div>

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
                                                    data-type-name="{{ strtolower($type->value->name) }}">
                                                <span>{{ $type->value->label() }}</span>
                                            </label>
                                        </div>
                                        @endforeach

                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label for="templateName" class="label-text mb-1">Name (AR)</label>
                                        <input type="text" id="templateName" class="form-control" name="name[ar]"
                                            placeholder="Template Name in Arabic">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="templateName" class="label-text mb-1">Name (EN)</label>
                                        <input type="text" id="templateName" class="form-control" name="name[en]"
                                            placeholder="Template Name in English">
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
                                            <option value="{{ $category->id }}">
                                                {{ $category->getTranslation('name', app()->getLocale()) }}
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

                                <div class="form-group mb-2">
                                    <label for="productsWithoutCategoriesSelect" class="label-text mb-1">Products
                                        Without Categories</label>
                                    <select id="productsWithoutCategoriesSelect" class="form-select select2"
                                        name="category_ids[]" multiple>
                                        @foreach($associatedData['product_without_categories'] as $category)
                                        <option value="{{ $category->id }}">
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
                                            <option value="{{ $industry->id }}">
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
                                        <option value="{{ $orientation->value }}">
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
                                <input type="hidden" name="dimension_resource_types" id="dimensionResourceTypes">
                                <label class="label-text mb-1">Shape</label>
                                <div class="row mb-2">
                                    {{-- Shape (col-6) --}}
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <input type="hidden" name="has_corner" id="has_corner_hidden"
                                                value="{{ old('has_corner') }}">
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="has_corner"
                                                        id="shape_circle" value="0">
                                                    <label class="form-check-label" for="shape_circle">Circle</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="has_corner"
                                                        id="shape_other" value="1">
                                                    <label class="form-check-label" for="shape_other">Other</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-2 d-none" id="cornersBox">
                                            <label for="cornersSelect" class="label-text mb-1">Corners</label>
                                            <select id="cornersSelect" class="form-select select2" name="border">
                                                <option value="" selected disabled>Choose Corner</option>
                                                @foreach(\App\Enums\BorderEnum::cases() as $border)
                                                <option value="{{ $border->value }}">{{$border->label()}}</option>
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
                                                <input class="form-check-input" type="checkbox" id="hasSafetyArea"
                                                    name="has_safety_area" value="1" {{ old('has_safety_area')
                                                    ? 'checked' : '' }}>
                                                <label class="form-check-label" for="hasSafetyArea">Enable Safety
                                                    Area</label>
                                            </div>

                                            <div id="safetyAreaBox"
                                                class="{{ old('has_safety_area') ? '' : 'd-none' }}">
                                                <label for="safetyAreaSelect" class="label-text mb-1">Safety
                                                    Area</label>
                                                <select id="safetyAreaSelect" class="form-select select2"
                                                    name="safety_area">
                                                    @foreach(\App\Enums\SafetyAreaEnum::cases() as $area)
                                                    <option value="{{ $area->value }}" {{
                                                        (string)old('safety_area')===(string)$area->value ? 'selected' :
                                                        '' }}>
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
                                                    value="1" {{ old('cut_margin') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="hasCutMargin">Enable Cut
                                                    Margin</label>
                                            </div>

                                            <div id="cutMarginBox" class="{{ old('cut_margin') ? '' : 'd-none' }}">
                                                <label for="cutMarginSelect" class="label-text mb-1">Cut
                                                    Margin</label>
                                                <select id="cutMarginSelect" class="form-select select2"
                                                    name="cut_margin">
                                                    @foreach(\App\Enums\SafetyAreaEnum::cases() as $area)
                                                    <option value="{{ $area->value }}" {{
                                                        (string)old('cut_margin')===(string)$area->value ? 'selected' :
                                                        '' }}>
                                                        {{ $area->label() }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                {{-- <small class="form-text text-muted">Padding inside the design--}}
                                                    {{-- area.</small>--}}
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-group mb-2 d-none" id="cornersBox">
                                        <label for="cornersSelect" class="label-text mb-1">Corners</label>
                                        <select id="cornersSelect" class="form-select select2" name="border">
                                            <option value="" selected disabled>Choose Corner</option>
                                            @foreach(\App\Enums\BorderEnum::cases() as $border)
                                            <option value="{{ $border->value }}">{{$border->label()}}</option>
                                            @endforeach
                                        </select>
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

                            </div>
                        </div>


                        <div class="d-flex flex-wrap-reverse gap-1 justify-content-between pt-2">
                            <button type="button" class="btn btn-outline-secondary" id="cancelButton">Cancel
                            </button>
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-outline-secondary fs-5 saveChangesButton"
                                    data-action="draft">
                                    <span>Add Template as Draft</span>
                                    <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                        role="status" aria-hidden="true"></span>
                                </button>
                                <button type="submit" class="btn btn-primary fs-5 saveChangesButton"
                                    data-action="editor">
                                    <span>Save & Go to Editor</span>
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
                                $right.append(new Option(cat.name, cat.id, false, false));
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
            // Make sure hidden inputs reflect latest UI, then read from them
            syncSelectedResourcesToHiddenInputs();
            const payload = buildDimensionPayloadFromHidden();

            if (!payload.resource_ids.length) {
                $('#sizesSelect').empty().trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('dimensions.index') }}",
                method: "POST",
                data: payload,                   // parallel arrays
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success(res) {
                    const $sizes = $('#sizesSelect');
                    const current = $sizes.val() || [];
                    $sizes.empty();

                    const items = res.data || res || [];
                    items.forEach(item => {
                        const text = dimensionLabelHWTop(item, {showUnit: true});  // "H * W Cm"
                        const id = item.id;
                        $sizes.append(new Option(text, id, false, false));
                    });

                    $sizes.val(current.filter(v => $sizes.find(`option[value="${v}"]`).length)).trigger('change');
                },
                error(xhr) {
                    console.error('Failed to load dimensions:', xhr.responseText);
                    $('#sizesSelect').empty().trigger('change');
                }
            });
        }
</script>
<script>
    // After “Products With Categories (left)” changes we fetch its categories (right), then sync + refresh
        $('#categoriesSelect').on('change', function () {
            const selectedIds = $(this).val();
            if (selectedIds && selectedIds.length > 0) {
                $.ajax({
                    url: "{{ route('products.categories') }}",
                    type: "POST",
                    data: {_token: "{{ csrf_token() }}", category_ids: selectedIds},
                    success(response) {
                        const $right = $('#productsSelect');
                        const saved = $right.val() || [];
                        (response.data || []).forEach(cat => {
                            if ($right.find(`option[value="${cat.id}"]`).length === 0) {
                                $right.append(new Option(cat.name, cat.id, false, false));
                            }
                        });
                        $right.val(saved).trigger('change');

                        // sync + maybe refresh
                        syncSelectedResourcesToHiddenInputs();
                        // optional immediate refresh: refreshSizes();
                    },
                    error(xhr) {
                        console.error("Error fetching categories:", xhr.responseText);
                        syncSelectedResourcesToHiddenInputs();
                    }
                });
            } else {
                // Clear right select and sync
                $('#productsSelect').empty().trigger('change');
                syncSelectedResourcesToHiddenInputs();
            }
        });

        // Right (categories) changed
        $('#productsSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            // optional immediate refresh:
            // refreshSizes();
        });

        // Products without categories changed
        $('#productsWithoutCategoriesSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            // optional immediate refresh:
            // refreshSizes();
        });

        // When user opens/clicks Sizes, fetch fresh sizes
        // Works for click/focus; pick one or both
        $('#sizesSelect').on('mousedown focus', function () {
            refreshSizes();
        });

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
                $('.saveChangesButton').prop('disabled', false).find('.saveLoader').addClass('d-none');
                if (response.data.redirect_url) {
                    window.open(response.data.redirect_url, '_blank');
                } else {
                    setTimeout(function () {
                        window.location.href = '/product-templates';
                    }, 1000);
                }

            },
            onError: function () {
                // Re-enable buttons & hide all loaders on error too
                $('.saveChangesButton').prop('disabled', false).find('.saveLoader').addClass('d-none');
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
            url: "{{ route('media.store') }}", // your Laravel media upload route
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

        const mainTemplateDropzone = new Dropzone("#main-template-dropzone", {
            url: "{{ route('media.store') }}", // your Laravel media upload route
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
                        document.getElementById("uploadedMainTemplateImage").value = response.data.id;
                    }
                });

                this.on("removedfile", function (file) {
                    document.getElementById("uploadedMainTemplateImage").value = "";
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