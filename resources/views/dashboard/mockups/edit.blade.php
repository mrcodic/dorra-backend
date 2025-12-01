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
        .gradient-picker-trigger .pcr-button { display: none !important; }

        .selected-color-wrapper { width: 28px; height: 28px; }
        .selected-color-dot {
            width: 100%; height: 100%; padding: 1px;
            border-radius: 50%; border: 2px solid #ccc;
            box-sizing: border-box; background-clip: content-box;
        }
        .selected-color-inner { width: 100%; height: 100%; border-radius: 50%; }
        .remove-color-btn {
            position: absolute; top: -5px; right: -5px;
            background-color: #F4F6F6 !important;
            color: #424746 !important;
            border-radius: 5px; width: 16px; height: 16px;
            font-size: 16px; line-height: 1; padding: 1px;
            display: none;
        }
        .selected-color-wrapper:hover .remove-color-btn {
            display: flex; align-items: center; justify-content: center;
        }

        .gradient-edit-picker-trigger {
            width: 40px; height: 40px; border-radius: 50%;
            background-image: url('/images/AddColor.svg') !important;
            background-size: cover; background-position: center;
            background-repeat: no-repeat; border: 1px solid #ccc;
            cursor: pointer; position: relative;
        }
        .gradient-edit-picker-trigger .pcr-button { display: none !important; }
    </style>
@endsection

@section('page-style')
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
    <section class="">
        <div class="card">
            <div class="card-body">
                <form id="editMockupForm"
                      enctype="multipart/form-data"
                      method="POST"
                      action="{{ route('mockups.update', $model->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-body flex-grow-1">
                        <div class="">
                            {{-- Mockup name --}}
                            <div class="form-group mb-2">
                                <label for="templateName" class="label-text mb-1">Mockup Name</label>
                                <input type="text"
                                       id="templateName"
                                       class="form-control"
                                       name="name"
                                       value="{{ old('name', $model->name) }}"
                                       placeholder="Mockup Name">
                            </div>

                            {{-- Types --}}
                            <div class="form-group mb-2">
                                <label for="mockup-type" class="label-text mb-1">Mockup Type</label>
                                <div class="row">
                                    @foreach($associatedData['types'] as $type)
                                        <div class="col-md-4 mb-1">
                                            <label class="radio-box">
                                                <input class="form-check-input type-checkbox"
                                                       type="checkbox"
                                                       name="types[]"
                                                       value="{{ $type->value }}"
                                                       data-type-name="{{ strtolower($type->value->name) }}"
                                                    @checked($model->types->contains($type))>
                                                <span>{{ $type->value->label() }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Product --}}
                            <div class="form-group mb-2">
                                <label for="productsSelect" class="label-text mb-1">Product</label>
                                <select id="productsSelect" name="category_id" class="form-select">
                                    <option value="" disabled>Choose product</option>
                                    @foreach($associatedData['products'] as $product)
                                        <option value="{{ $product->id }}"
                                        @selected($product->id == $model->category_id)>
                                        {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Templates Repeater --}}
                            <div class="template-repeater row d-none" id="template-wrapper">
                                <div data-repeater-list="templates">

                                    {{-- لو فيه templates مرتبطة بالموديل اعرضها كـ initial items --}}
                                    @forelse($model->templates as $mockupTemplate)
                                        @php
                                            // هنا يفترض إن عندك علاقة على الـ template pivot بترجع الـ positions
                                            // مثال: $mockupTemplate->pivotPositions => Collection فيها [template_type, position_id]
                                            $pivotPositions = $mockupTemplate->pivot->positions ?? collect();
//                                            dd($mockupTemplate->pivot->load('positions'));
                                            $positionForType = function($typeKey) use ($pivotPositions) {
                                                $row = $pivotPositions->firstWhere('template_type', $typeKey);
                                                return $row->position_id ?? null;
                                            };
                                        @endphp

                                        <div data-repeater-item class="row template-item">
                                            {{-- TEMPLATE SELECT --}}
                                            <div class="form-group mb-2 col-4">
                                                <label class="label-text mb-1">Template</label>
                                                <select name="template_id" class="form-select template-select">
                                                    <option value="" disabled>Choose template</option>
                                                    @foreach($model->templates  as $template)
                                                        <option value="{{ $template->id }}"
                                                            @selected($template->id == $mockupTemplate->id)>
                                                            {{ $template->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Positions per type --}}
                                            @foreach($associatedData['types'] as $type)
                                                @php
                                                    $typeKey = strtolower($type->value->name);
                                                    // template_type في جدولك ممكن تكون int/enum،
                                                    // عدّله حسب implementation عندك
                                                    $selectedPositionId = $positionForType($typeKey);
                                                @endphp

                                                <div class="form-group mb-2 col-4 position-wrapper d-none"
                                                     data-type="{{ $typeKey }}">
                                                    <label class="label-text mb-1">
                                                        {{ $type->value->label() }} Position
                                                    </label>
                                                    <select name="positions[{{ $typeKey }}]" class="form-select">
                                                        <option value="" disabled>Choose position</option>
                                                        @foreach($associatedData['positions'] ?? [] as $pos)
                                                            <option value="{{ $pos->id }}"
                                                                @selected($pos->id == $selectedPositionId)>
                                                                {{ $pos->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endforeach

                                            <div class="col-12 text-end">
                                                <button type="button"
                                                        data-repeater-delete
                                                        class="btn btn-sm btn-light-danger">
                                                    Remove Template
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        {{-- لو مفيش templates حالية، اعمل واحد فاضي --}}
                                        <div data-repeater-item class="row template-item">
                                            <div class="form-group mb-2 col-4">
                                                <label class="label-text mb-1">Template</label>
                                                <select name="template_id" class="form-select template-select">
                                                    <option value="" disabled selected>Choose template</option>
                                                    @foreach($associatedData['templates'] ?? [] as $template)
                                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @foreach($associatedData['types'] as $type)
                                                @php $typeKey = strtolower($type->value->name); @endphp
                                                <div class="form-group mb-2 col-4 position-wrapper d-none"
                                                     data-type="{{ $typeKey }}">
                                                    <label class="label-text mb-1">
                                                        {{ $type->value->label() }} Position
                                                    </label>
                                                    <select name="positions[{{ $typeKey }}]" class="form-select">
                                                        <option value="" disabled selected>Choose position</option>
                                                        @foreach($associatedData['positions'] ?? [] as $pos)
                                                            <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endforeach

                                            <div class="col-12 text-end">
                                                <button type="button"
                                                        data-repeater-delete
                                                        class="btn btn-sm btn-light-danger">
                                                    Remove Template
                                                </button>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="row mt-1">
                                    <div class="col-12">
                                        <button type="button"
                                                data-repeater-create
                                                class="w-100 rounded-3 p-1 text-dark"
                                                style="border: 2px dashed #CED5D4; background-color: #EBEFEF">
                                            <i data-feather="plus"></i> Add New Template
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- File inputs (per type) --}}
                        <div class="col-md-12">
                            <div id="fileInputsContainer" class="dynamic-upload-container mb-1"></div>
                        </div>
                    </div>

                    {{-- Colors --}}
                    <div class="mb-2">
                        <label class="label-text mb-1 d-block">Colors</label>
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            <button type="button" id="openColorPicker"
                                    class="gradient-picker-trigger border"></button>

                            <span id="selected-colors"
                                  class="d-flex gap-1 flex-wrap align-items-center"></span>
                        </div>
                        <div id="colorsInputContainer"></div>
                    </div>

                    <div class="modal-footer border-top-0">
                        <a href="{{ route('mockups.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span class="btn-text">Save Changes</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                  role="status" aria-hidden="true"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </section>
@endsection

@section('vendor-script')
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
        const locale = "{{ app()->getLocale() }}";
        // ألوان الموكاب الحالية من الـ DB
        let selectedColors = @json($model->colors ?? []);

        // ===== Templates loader =====
        window.loadTemplates = function () {
            let productId = document.getElementById('productsSelect')?.value;

            let selectedTypes = Array.from(document.querySelectorAll('.type-checkbox'))
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            if (!productId || selectedTypes.length === 0) return;

            $.ajax({
                url: "{{ route('product-templates.index') }}",
                method: "GET",
                data: {
                    product_without_category_id: productId,
                    request_type: "api",
                    approach: "without_editor",
                },
                success: function (response) {
                    const templates = Array.isArray(response)
                        ? response
                        : (response.data ?? []);

                    let templateSelects = document.querySelectorAll('.template-select');

                    templateSelects.forEach(select => {
                        const prevValue = select.value;

                        select.innerHTML = `<option value="" disabled>Choose template</option>`;

                        templates.forEach(t => {
                            let label = t.name;
                            if (t.name && typeof t.name === 'object') {
                                label = t.name[locale] ?? Object.values(t.name)[0] ?? '';
                            }
                            const option = document.createElement('option');
                            option.value = t.id;
                            option.textContent = label;
                            select.appendChild(option);
                        });

                        if (prevValue) {
                            const hasPrev = Array.from(select.options).some(opt => opt.value == prevValue);
                            if (hasPrev) select.value = prevValue;
                        }
                    });
                },
                error: function (xhr) {
                    console.error('Error loading templates', xhr);
                }
            });
        };

        // ===== Visibility for template wrapper / positions =====
        window.updateTemplateVisibility = function () {
            const productSelect = document.getElementById('productsSelect');
            const templateWrapper = document.getElementById('template-wrapper');
            const typeCheckboxes = document.querySelectorAll('.type-checkbox');

            const productSelected = productSelect?.value;
            const selectedTypes = [...typeCheckboxes]
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            templateWrapper.classList.add('d-none');

            document.querySelectorAll('.template-item').forEach(item => {
                item.querySelectorAll('.position-wrapper').forEach(p => p.classList.add('d-none'));
            });

            if (!productSelected || selectedTypes.length === 0) return;

            templateWrapper.classList.remove('d-none');

            document.querySelectorAll('.template-item').forEach(item => {
                selectedTypes.forEach(type => {
                    const row = item.querySelector(`.position-wrapper[data-type="${type}"]`);
                    if (row) row.classList.remove('d-none');
                });
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            const productSelect = document.getElementById('productsSelect');
            const typeCheckboxes = document.querySelectorAll('.type-checkbox');

            productSelect?.addEventListener('change', function () {
                window.updateTemplateVisibility();
                setTimeout(window.loadTemplates, 150);
            });

            typeCheckboxes.forEach(cb => cb.addEventListener('change', function () {
                window.updateTemplateVisibility();
                setTimeout(window.loadTemplates, 150);
            }));

            window.updateTemplateVisibility();
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const $templateRepeater = $('.template-repeater');

            if (!$templateRepeater.length) return;

            if ($.fn.repeater) {
                $templateRepeater.repeater({
                    initEmpty: false, // مهم في الـ edit عشان يستخدم الـ markup الموجود
                    show: function () {
                        $(this).slideDown();
                        if (window.feather) feather.replace();

                        if (window.updateTemplateVisibility) window.updateTemplateVisibility();
                        // if (window.loadTemplates) window.loadTemplates();
                    },
                    hide: function (deleteElement) {
                        $(this).slideUp(deleteElement);
                    }
                });

                if (window.updateTemplateVisibility) window.updateTemplateVisibility();
                // if (window.loadTemplates) window.loadTemplates();
            } else {
                console.error("Repeater not loaded — include jquery.repeater.min.js");
            }
        });
    </script>

    <script>
        // ========== Type Checkbox & File Inputs ==========
        const checkboxes = document.querySelectorAll('.type-checkbox');
        const fileInputsContainer = document.getElementById('fileInputsContainer');

        function renderFileInputs() {
            if (!fileInputsContainer) return;
            fileInputsContainer.innerHTML = '';

            let selectedTypes = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            selectedTypes.forEach(type => {
                const typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
                const block = document.createElement('div');
                block.classList.add('mb-3');

                block.innerHTML = `
                    <label class="form-label label-text">${typeLabel} Base Image</label>
                    <input type="file" name="${type}_base_image" id="${type}-base-input" class="d-none" accept="image/*">
                    <div class="upload-card upload-area" data-input-id="${type}-base-input">
                        <div class="upload-content">
                            <i data-feather="upload" class="mb-2"></i>
                            <p>${typeLabel} Base Image: Drag file here or click to upload</p>
                            <div class="preview mt-1"></div>
                        </div>
                    </div>

                    <label class="form-label label-text mt-2">${typeLabel} Mask Image</label>
                    <input type="file" name="${type}_mask_image" id="${type}-mask-input" class="d-none" accept="image/*">
                    <div class="upload-card upload-area" data-input-id="${type}-mask-input">
                        <div class="upload-content">
                            <i data-feather="upload" class="mb-2"></i>
                            <p>${typeLabel} Mask Image: Drag file here or click to upload</p>
                            <div class="preview mt-1"></div>
                        </div>
                    </div>
                `;

                fileInputsContainer.appendChild(block);
            });

            feather.replace();
            bindUploadAreas();
        }

        function bindUploadAreas() {
            document.querySelectorAll('.upload-area').forEach(area => {
                const inputId = area.dataset.inputId;
                const input = document.getElementById(inputId);
                const preview = area.querySelector('.preview');

                area.addEventListener('click', () => input?.click());

                area.addEventListener('dragover', e => {
                    e.preventDefault();
                    area.classList.add('dragover');
                });

                area.addEventListener('dragleave', e => {
                    e.preventDefault();
                    area.classList.remove('dragover');
                });

                area.addEventListener('drop', e => {
                    e.preventDefault();
                    area.classList.remove('dragover');
                    handleFiles(e.dataTransfer.files, input, preview);
                });

                input?.addEventListener('change', e => {
                    handleFiles(e.target.files, input, preview);
                });
            });
        }

        function handleFiles(files, input, preview) {
            if (!files.length) return;
            const file = files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid rounded border" style="max-height: 120px;">`;
            };
            reader.readAsDataURL(file);

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
        }

        function toggleCheckboxes() {
            let frontChecked = false, backChecked = false, noneChecked = false;

            checkboxes.forEach(cb => {
                const type = cb.dataset.typeName;
                if (type === 'front' && cb.checked) frontChecked = true;
                if (type === 'back' && cb.checked) backChecked = true;
                if (type === 'none' && cb.checked) noneChecked = true;
            });

            checkboxes.forEach(cb => {
                const type = cb.dataset.typeName;
                cb.disabled = (
                    (noneChecked && (type === 'front' || type === 'back')) ||
                    ((frontChecked || backChecked) && type === 'none')
                );
            });

            renderFileInputs();
        }

        checkboxes.forEach(cb => cb.addEventListener('change', toggleCheckboxes));
        // أول مرة
        toggleCheckboxes();
    </script>

    <script>
        $(document).ready(function () {
            handleAjaxFormSubmit("#editMockupForm", {
                successMessage: "Mockup Updated Successfully",
                onSuccess: function () {
                    window.location.href = "{{ route('mockups.index') }}";
                }
            });
        });
    </script>

    <script>
        let pickrInstance = null;

        $(document).ready(function () {
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

            pickrInstance.on('save', (color) => {
                const hex = color.toHEXA().toString();
                if (!selectedColors.includes(hex)) {
                    selectedColors.push(hex);
                    renderSelectedColors();
                }
                pickrInstance.hide();
            });

            // إظهار الألوان الحالية
            renderSelectedColors();
        });

        $('#openColorPicker').on('click', function () {
            const trigger = document.getElementById('openColorPicker');
            const rect = trigger.getBoundingClientRect();

            if (pickrInstance) {
                pickrInstance.show();

                setTimeout(() => {
                    const pickerPanel = document.querySelector('.pcr-app.visible');
                    if (pickerPanel) {
                        pickerPanel.style.position = 'absolute';
                        pickerPanel.style.left = `${rect.left + window.scrollX}px`;
                        pickerPanel.style.top = `${rect.bottom + window.scrollY + 5}px`;
                        pickerPanel.style.zIndex = 9999;
                    }
                }, 0);
            }
        });

        window.removeColor = function (hex) {
            selectedColors = selectedColors.filter(c => c !== hex);
            renderSelectedColors();
        };

        function renderSelectedColors() {
            const ul = document.getElementById('selected-colors');
            ul.innerHTML = '';

            const container = document.getElementById('colorsInputContainer');
            container.innerHTML = '';

            selectedColors.forEach(c => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <div class="selected-color-wrapper position-relative">
                        <div class="selected-color-dot" style="background-color: #fff;">
                            <div class="selected-color-inner" style="background-color: ${c};"></div>
                        </div>
                        <button type="button" onclick="removeColor('${c}')" class="remove-color-btn">×</button>
                    </div>
                `;
                ul.appendChild(li);

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'colors[]';
                hiddenInput.value = c;
                container.appendChild(hiddenInput);
            });
        }
    </script>
@endsection
