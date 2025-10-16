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
                    <form id="editTemplateForm" enctype="multipart/form-data" method="post"
                        action="{{ route('product-templates.update',$model->id) }}">
                        @csrf
                        @method("PUT")
                        <div class="flex-grow-1">
                            <div class="">
                                <div class="form-group mb-2">
                                    <label class="label-text mb-1">Template Image</label>

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


                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label for="templateNameAr" class="label-text mb-1">Name (AR)</label>
                                        <input type="text" id="templateNameAr" class="form-control" name="name[ar]"
                                            value="{{ $model->getTranslation('name','ar') }}"
                                            placeholder="Template Name in Arabic">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="templateNameEn" class="label-text mb-1">Name (EN)</label>
                                        <input type="text" id="templateNameEn" class="form-control" name="name[en]"
                                            value="{{ $model->getTranslation('name','en') }}"
                                            placeholder="Template Name in English">
                                    </div>
                                </div>


                                <div class="form-group mb-2">
                                    <label for="statusSelect" class="label-text mb-1">Status</label>
                                    <select id="statusSelect" name="status" class="form-select select2">
                                        <option value="" disabled selected>Choose status</option>
                                        @foreach(\App\Enums\Template\StatusEnum::cases() as $status)
                                        <option value="{{ $status->value }}" @selected($status==$model->status)>{{
                                            $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
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
                                <div class="form-group mb-2">
                                    <label for="orientation" class="label-text mb-1">Orientation</label>
                                    <select id="orientation" class="form-select" name="orientation">
                                        <option value="" selected disabled>
                                            chooese orientation
                                        </option>
                                        @foreach(\App\Enums\OrientationEnum::cases() as $orientation)
                                            <option value="{{ $orientation->value }}" @selected($orientation == $model->orientation)>
                                                {{$orientation->label()}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row mb-2">

                                    <div class="col-md-6 form-group mb-2">
                                        <label for="categoriesSelect" class="label-text mb-1">Products With Categories</label>
                                        <select id="categoriesSelect" class="form-select select2" name="product_with_category"
                                                multiple>
                                            @foreach($associatedData['product_with_categories'] as $category)
                                                <option value="{{ $category->id }}"
                                                    @selected($category->load('products')->products->intersect($model->products)->isNotEmpty())>
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
                                                <option value="{{ $product->id }}"  @selected($model->products->contains($product))>
                                                    {{ $product->getTranslation('name', app()->getLocale()) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="productsWithoutCategoriesSelect" class="label-text mb-1">Products Without Categories</label>
                                    <select id="productsWithoutCategoriesSelect" class="form-select select2" name="category_ids[]"
                                            multiple>
                                        @foreach($associatedData['product_without_categories'] as $category)
                                            <option value="{{ $category->id }}" @selected($model->categories->contains($category))>
                                                {{ $category->getTranslation('name', app()->getLocale()) }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                {{-- Persisted resources (used on submit / ajax) --}}
                                <input type="hidden" name="dimension_resource_ids"   id="dimensionResourceIds">
                                <input type="hidden" name="dimension_resource_types" id="dimensionResourceTypes">

                                <div class="form-group mb-2">
                                    <label for="sizesSelect" class="label-text mb-1">Sizes</label>
                                    <select id="sizesSelect" class="form-select" name="dimension_id">
                                        <option value="" disabled>Select Size</option>
                                    </select>
                                </div>


                            </div>
                        </div>





                        <div class="d-flex flex-wrap-reverse gap-1 justify-content-between pt-2">
                            <button type="reset" class="btn btn-outline-secondary" id="cancelButton">Cancel</button>
                            <div class="d-flex gap-1">
                                <a href="{{ config("services.editor_url")."templates/".$model->id.'?is_clear'}}"
                                    class="btn btn-outline-secondary fs-5 "
                                    target="_blank"
                                    >
                                    <i data-feather="edit-3"></i>
                                    <span>Edit Design</span>

                                </a>
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
        // Build parallel arrays from current UI selections
        function buildDimensionPayloadFromUI() {
            const categoryIds = ($('#productsSelect').val() || []).map(String);               // categories
            const productIds  = ($('#productsWithoutCategoriesSelect').val() || []).map(String); // products

            const resource_ids   = [];
            const resource_types = [];

            // ✅ categories → "category"
            categoryIds.forEach(id => { resource_ids.push(id); resource_types.push('category'); });

            // ✅ products → "product"
            productIds.forEach(id  => { resource_ids.push(id); resource_types.push('product'); });

            return { resource_ids, resource_types };
        }

        // Persist arrays into hidden inputs (so they submit with the form)
        function syncSelectedResourcesToHiddenInputs() {
            const { resource_ids, resource_types } = buildDimensionPayloadFromUI();
            $('#dimensionResourceIds').val(JSON.stringify(resource_ids));
            $('#dimensionResourceTypes').val(JSON.stringify(resource_types));
        }

        // Pretty number (single declaration!)
        const nf = new Intl.NumberFormat(undefined, { maximumFractionDigits: 3 });

        // "HEIGHT * WIDTH (Unit)"
        function dimensionLabelHWTop(item, { showUnit = true } = {}) {
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
            try { ids   = JSON.parse($('#dimensionResourceIds').val()   || '[]'); } catch {}
            try { types = JSON.parse($('#dimensionResourceTypes').val() || '[]'); } catch {}
            return { resource_ids: ids, resource_types: types };
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
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                success(res) {
                    $sizes.empty().append(new Option('Select Size', '', false, false));
                    const items = res.data || res || [];
                    items.forEach(item => {
                        $sizes.append(new Option(dimensionLabelHWTop(item, { showUnit: true }), item.id, false, false));
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
                    data: { _token: "{{ csrf_token() }}", category_ids: selectedIds },
                    success(response) {
                        const $right = $('#productsSelect').empty();
                        (response.data || []).forEach(p => $right.append(new Option(p.name, p.id, false, false)));
                        $right.val(prev).trigger('change');
                        refreshSizes(); // 🔔 fetch sizes
                    },
                    error(xhr) {
                        console.error("Error fetching categories:", xhr.responseText);
                        refreshSizes();
                    }
                });
            } else {
                $('#productsSelect').empty().trigger('change');
                refreshSizes();
            }
        });

        // Right: categories changed → refresh sizes
        $('#productsSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            refreshSizes();
        });

        // Bottom: products without categories changed → refresh sizes
        $('#productsWithoutCategoriesSelect').on('change', function () {
            syncSelectedResourcesToHiddenInputs();
            refreshSizes();
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


<script !src="">
    handleAjaxFormSubmit("#editTemplateForm", {
            successMessage: "Template updated successfully",
            onSuccess: function (response, $form) {
                setTimeout(function () {
                    window.location.href = '/product-templates';
                }, 1000);

                console.log('Form inputs updated with new data.');
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
