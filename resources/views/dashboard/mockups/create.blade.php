@extends('layouts/contentLayoutMaster')

@section('title', 'Create Mockup')
@section('main-page', 'Create Mockup')

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
    </style>
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
    <!-- users list start -->
    <section class="">
        <div class="card">
            <div class="card-body">
                <form id="addMockupForm" enctype="multipart/form-data" action="{{ route('mockups.store') }}">
                    @csrf
                    <div class="modal-body flex-grow-1">
                        <div class="">
                            <div class="form-group mb-2">
                                <label for="templateName" class="label-text mb-1">Mockup Name</label>
                                <input type="text" id="templateName" class="form-control" name="name"
                                       placeholder="Mockup Name">
                            </div>

                            <div class="form-group mb-2">
                                <label for="mockup-type" class="label-text mb-1">Mockup Type</label>
                                <div class="row">
                                    @foreach($associatedData['types'] as $type)
                                        <div class="col-md-4 mb-1">
                                            <label class="radio-box">
                                                <input class="form-check-input type-checkbox" type="checkbox"
                                                       name="types[]"
                                                       value="{{ $type->value }}"
                                                       data-type-name="{{ strtolower($type->value->name) }}">
                                                <span>{{ $type->value->label() }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label for="productsSelect" class="label-text mb-1">Product</label>
                                <select id="productsSelect" name="category_id" class="form-select">
                                    <option value="" disabled selected>Choose product</option>
                                    @foreach($associatedData['products'] as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="template-repeater row d-none" id="template-wrapper">
                                <div data-repeater-list="templates">
                                    <div data-repeater-item class="row template-item align-items-end">
                                        <!-- TEMPLATE SELECT -->
                                        <div class="form-group mb-2 col-8">
                                            <label class="label-text mb-1">Template</label>
                                            <select class="template-select" name="template_id" data-page="1"></select>


                                            <div class="template-preview mt-25">
                                                <img class="front-preview rounded-circle" style="width:40px;height:40px;display:none;">
                                                <img class="back-preview rounded-circle" style="width:40px;height:40px;display:none;">
                                            </div>
                                        </div>

                                        <!-- SHOW ON CANVAS BUTTON -->
                                        <div class="form-group mb-2 col-4">
                                            <button type="button" class="btn btn-primary w-100 show-template-canvas">
                                                Show on Canvas
                                            </button>
                                        </div>

                                        <!-- Hidden inputs for this template -->
                                        <!-- FRONT -->
                                        <input type="hidden" name="templates[][front_x]" class="template_x front">
                                        <input type="hidden" name="templates[][front_y]" class="template_y front">
                                        <input type="hidden" name="templates[][front_width]" class="template_width front">
                                        <input type="hidden" name="templates[][front_height]" class="template_height front">
                                        <input type="hidden" name="templates[][front_angle]" class="template_angle front">

                                        <!-- BACK -->
                                        <input type="hidden" name="templates[][back_x]" class="template_x back">
                                        <input type="hidden" name="templates[][back_y]" class="template_y back">
                                        <input type="hidden" name="templates[][back_width]" class="template_width back">
                                        <input type="hidden" name="templates[][back_height]" class="template_height back">
                                        <input type="hidden" name="templates[][back_angle]" class="template_angle back">

                                        <!-- NONE -->
                                        <input type="hidden" name="templates[][none_x]" class="template_x none">
                                        <input type="hidden" name="templates[][none_y]" class="template_y none">
                                        <input type="hidden" name="templates[][none_width]" class="template_width none">
                                        <input type="hidden" name="templates[][none_height]" class="template_height none">
                                        <input type="hidden" name="templates[][none_angle]" class="template_angle none">

                                        <!-- DELETE BUTTON -->
                                        <div class="col-12 text-end mt-2">
                                            <button type="button" data-repeater-delete class="btn btn-sm btn-light-danger">
                                                Remove Template
                                            </button>
                                        </div>
                                    </div>

                                </div>

                                <!-- ADD BUTTON -->
                                <div class="row mt-1">
                                    <div class="col-12">
                                        <button type="button" data-repeater-create class="w-100 rounded-3 p-1 text-dark"
                                                style="border: 2px dashed #CED5D4; background-color: #EBEFEF">
                                            <i data-feather="plus"></i> Add New Template
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-12">
                            <div id="fileInputsContainer" class="dynamic-upload-container mb-1"></div>
                        </div>
                    </div>

                    <div class="mt-2 d-none" id="editorFrontWrapper">
                        <label class="label-text">Mockup Editor (Front)</label>
                        <canvas id="mockupCanvasFront" width="800" height="800" style="border:1px solid #ccc;"></canvas>
                    </div>

                    <div class="mt-2 d-none" id="editorBackWrapper">
                        <label class="label-text">Mockup Editor (Back)</label>
                        <canvas id="mockupCanvasBack" width="800" height="800" style="border:1px solid #ccc;"></canvas>
                    </div>

                    <div class="mt-2 d-none" id="editorNoneWrapper">
                        <label class="label-text">Mockup Editor (General)</label>
                        <canvas id="mockupCanvasNone" width="800" height="800" style="border:1px solid #ccc;"></canvas>
                    </div>
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span class="btn-text">Create</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                  role="status" aria-hidden="true"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
        @include("modals.templates.template-modal")
    </section>
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
    @push('scripts')
        <script>
            $(function () {
                let nextPageUrl = null; // from pagination.next_page_url

                // When modal is about to open
                $('#templateModal').on('show.bs.modal', function () {
                    // 1) Close any open Select2
                    try {
                        $('.select2-hidden-accessible').each(function () {
                            if ($(this).data('select2')) {
                                $(this).select2('close');
                            }
                        });
                    } catch (e) {
                        console.warn('Select2 close error:', e);
                    }

                    // 2) Load first page of remaining templates
                    loadTemplatesFirstPage();
                });

                function getFilters() {
                    // TODO: replace with your own logic if different
                    const productId     = document.getElementById('productsSelect') || null;       // example
                    const selectedTypes = $('.types').val() || [];              // example (multi-select)

                    return { productId, selectedTypes };
                }

                function loadTemplatesFirstPage() {
                    const { productId, selectedTypes } = getFilters();

                    $('#templates-modal-container').html(
                        '<div class="col-12 text-center py-3">Loading...</div>'
                    );
                    $('#templates-modal-pagination').empty();
                    nextPageUrl = null;

                    $.ajax({
                        url: "{{ route('product-templates.index') }}",
                        method: "GET",
                        data: {
                            product_without_category_id: productId,
                            request_type: "api",
                            approach: "without_editor",
                            paginate: true,
                            per_page: 3,
                            limit: 3,
                            types: selectedTypes,
                            page: 1,
                        },
                        success: function (res) {
                            renderTemplatesResponse(res, false);
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText || xhr.statusText);
                            $('#templates-modal-container').html(
                                '<div class="col-12 text-danger text-center py-3">Error loading templates</div>'
                            );
                        }
                    });
                }

                // Handle "Load more" click inside modal (delegated)
                $(document).on('click', '#templates-modal-load-more', function () {
                    if (!nextPageUrl) return;

                    const $btn = $(this);
                    $btn.prop('disabled', true).text('Loading...');

                    $.ajax({
                        url: nextPageUrl,
                        method: "GET",
                        success: function (res) {
                            renderTemplatesResponse(res, true);
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText || xhr.statusText);
                            $btn.prop('disabled', false).text('Load More');
                        }
                    });
                });

                function renderTemplatesResponse(res, append) {
                    // Expecting structure like the one you pasted:
                    // {
                    //   status: 200,
                    //   success: true,
                    //   data: [ ...templates... ],
                    //   pagination: { next_page_url, ... }
                    //   ...other fields (source_design_svg, orientation, etc.)
                    // }

                    const templates  = res.data || [];
                    const pagination = res.pagination || {};

                    if (!append) {
                        $('#templates-modal-container').empty();
                    }

                    if (!templates.length && !append) {
                        $('#templates-modal-container').html(
                            '<div class="col-12 text-center text-muted py-3">No templates found</div>'
                        );
                    } else {
                        templates.forEach(function (tpl) {
                            const img = tpl.product_model_image || tpl.template_model_image || '';

                            const html = `
                        <div class="col-6 col-md-4 mb-2">
                            <button
                                type="button"
                                class="btn w-100 p-0 border-0 template-item-modal"
                                data-id="${tpl.id}"
                                data-name="${tpl.name || ''}"
                                data-image="${img}"
                            >
                                <div class="card h-100">
                                    ${img
                                ? `<img src="${img}" class="card-img-top" style="height:140px;object-fit:cover;" alt="${tpl.name || ''}">`
                                : `<div class="d-flex align-items-center justify-content-center bg-light" style="height:140px;">
                                              <span class="text-muted small">No image</span>
                                           </div>`
                            }
                                    <div class="card-body py-2 px-2">
                                        <div class="small fw-semibold text-truncate mb-1">
                                            ${tpl.name || ''}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center small text-muted">
                                            <span>${tpl.type || ''}</span>
                                            ${tpl.rating
                                ? `<span>${'★'.repeat(tpl.rating)}</span>`
                                : ''
                            }
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    `;

                            $('#templates-modal-container').append(html);
                        });
                    }

                    // Pagination
                    nextPageUrl = pagination.next_page_url || null;

                    if (nextPageUrl) {
                        $('#templates-modal-pagination').html(`
                    <button
                        id="templates-modal-load-more"
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                    >
                        Load More
                    </button>
                `);
                    } else {
                        if (!append) {
                            $('#templates-modal-pagination').empty();
                        } else {
                            $('#templates-modal-pagination').html(`
                        <div class="text-muted small">No more templates</div>
                    `);
                        }
                    }
                }

                // Optional: handle click on template in modal -> set value somewhere + close modal
                $(document).on('click', '.template-item-modal', function () {
                    const id   = $(this).data('id');
                    const name = $(this).data('name');
                    const img  = $(this).data('image');

                    // Example: set hidden input & preview (adjust to your needs)
                    $('#template_id').val(id);
                    $('#template_preview_name').text(name);

                    if (img) {
                        $('#template_preview_img').attr('src', img).show();
                    } else {
                        $('#template_preview_img').hide();
                    }

                    const modalEl = document.getElementById('templateModal');
                    const modal   = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                });
            });
        </script>
    @endpush

    <script>
        // =========================
        // SELECT2 FORMATTER
        // =========================
        function formatTemplateOption(option) {
            if (!option.id) return option.text;

            const $option = $(option.element);
            const front = $option.data("image");
            const back  = $option.data("back-image");

            return $(`
            <div style="display:flex;align-items:center;">
                ${front ? `<img src="${front}" style="width:24px;height:24px;border-radius:50%;margin-right:5px;">` : ""}
                ${back  ? `<img src="${back}"  style="width:24px;height:24px;border-radius:50%;margin-right:5px;">` : ""}
                <span>${option.text}</span>
            </div>
        `);
        }
        function injectLoadMoreButton($select) {
            let dropdown = $(".select2-results");

            if (dropdown.find(".load-all-btn").length) return;

            dropdown.append(`
        <div class="text-center py-1 border-top">
            <button type="button" class="btn btn-sm btn-outline-primary load-all-btn" data-bs-toggle="modal" data-bs-target="#templateModal">
                Show Remaining Templates
            </button>
        </div>
    `);

            $(".load-all-btn").on("click", function (e) {
                e.preventDefault();
                loadAllTemplates($select);
            });
        }

        // =========================
        // INIT SELECT2
        // =========================
        function initTemplateSelect(select) {
            const $select = $(select);

            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2("destroy");
            }

            $select.select2({
                templateResult: formatTemplateOption,
                templateSelection: formatTemplateOption,
                minimumResultsForSearch: -1
            });
            $select.on("select2:open", function () {
                injectLoadMoreButton($select);
            });
        }

        // =========================
        // CANVAS HELPER FUNCTIONS
        // =========================
        let canvasFront = new fabric.Canvas('mockupCanvasFront');
        let canvasBack  = new fabric.Canvas('mockupCanvasBack');
        let canvasNone  = new fabric.Canvas('mockupCanvasNone');

        function loadBaseImage(canvas, baseUrl) {
            fabric.Image.fromURL(baseUrl, function(img) {
                img.set({ selectable: false });
                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                    scaleX: canvas.width / img.width,
                    scaleY: canvas.height / img.height
                });
            });
        }

        function loadAndBind(canvas, designUrl, type, templateItem) {
            fabric.Image.fromURL(designUrl, function(img) {
                img.set({
                    left: 150,
                    top: 150,
                    scaleX: 0.5,
                    scaleY: 0.5,
                    cornerStyle: "circle",
                    transparentCorners: false
                });
                img.templateItem = templateItem;
                img.templateType = type;
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
            });
        }

        function bindCanvasUpdates(canvas, type) {
            canvas.on('object:modified', function(e) {
                const obj = e.target;
                if (!obj?.templateItem) return;
                const row = obj.templateItem;
                row.querySelector(`.template_x.${type}`).value      = obj.left;
                row.querySelector(`.template_y.${type}`).value      = obj.top;
                row.querySelector(`.template_width.${type}`).value  = obj.width * obj.scaleX;
                row.querySelector(`.template_height.${type}`).value = obj.height * obj.scaleY;
                row.querySelector(`.template_angle.${type}`).value  = obj.angle;
            });
        }

        bindCanvasUpdates(canvasFront, "front");
        bindCanvasUpdates(canvasBack,  "back");
        bindCanvasUpdates(canvasNone,  "none");
    </script>
    <script !src="">
        // When clicking "SHOW ON CANVAS"
        document.addEventListener('click', function(e) {
            if (!e.target.matches('.show-template-canvas')) return;

            const row = e.target.closest(".template-item");
            const select = row.querySelector(".template-select");
            const option = select.selectedOptions[0];
            if (!option) return;

            const front = option.dataset.image;
            const back  = option.dataset.backImage;

            // Load images on respective canvases
            if (front) loadAndBind(canvasFront, front, "front", row);
            if (back)  loadAndBind(canvasBack,  back,  "back",  row);
        });

    </script>
    <script>
        const locale = "{{ app()->getLocale() }}";

        window.updateTemplateVisibility = function () {
            const productSelect = document.getElementById('productsSelect');
            const templateWrapper = document.getElementById('template-wrapper');

            const selectedTypes = [...document.querySelectorAll('.type-checkbox')]
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            templateWrapper.classList.add("d-none");
            if (!productSelect.value || selectedTypes.length === 0) return;
            templateWrapper.classList.remove("d-none");
        };

        window.loadTemplates = function () {
            let productId = document.getElementById('productsSelect')?.value;
            let typeMap = { front: 1, back: 2, none: 3 };
            let selectedTypes = [...document.querySelectorAll('.type-checkbox')]
                .filter(cb => cb.checked)
                .map(cb => typeMap[cb.dataset.typeName]);

            if (!productId || selectedTypes.length === 0) return;

            $.ajax({
                url: "{{ route('product-templates.index') }}",
                method: "GET",
                data: {
                    product_without_category_id: productId,
                    request_type: "api",
                    approach: "without_editor",
                    paginate: true,
                    per_page: 3,
                    limit: 3,
                    types: selectedTypes,
                },
                success: function (response) {
                    const templates = Array.isArray(response) ? response : (response.data ?? []);
                    document.querySelectorAll('.template-select').forEach(select => {
                        if ($(select).data('select2')) $(select).off().select2("destroy");
                        select.innerHTML = `<option value="" disabled selected>Choose template</option>`;
                        templates.forEach(t => {
                            const option = document.createElement("option");
                            const label = typeof t.name === "object" ? (t.name[locale] ?? Object.values(t.name)[0]) : t.name;
                            option.value = t.id;
                            option.textContent = label;
                            option.dataset.image = t.source_design_svg;
                            option.dataset.backImage = t.back_base64_preview_image;
                            select.appendChild(option);
                        });
                        initTemplateSelect(select);
                    });
                },
                error: function (xhr) { console.error("Error loading templates", xhr); }
            });
        };
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const productSelect = document.getElementById("productsSelect");
            productSelect?.addEventListener("change", () => { updateTemplateVisibility(); loadTemplates(); });

            document.querySelectorAll(".type-checkbox").forEach(cb =>
                cb.addEventListener("change", () => { updateTemplateVisibility(); loadTemplates(); })
            );

            updateTemplateVisibility();
            loadTemplates();

            // Repeater
            const $templateRepeater = $('.template-repeater');
            if ($templateRepeater.length && $.fn.repeater) {
                $templateRepeater.repeater({
                    initEmpty: true,
                    show: function () {
                        $(this).slideDown();
                        if (window.feather) feather.replace();
                        window.updateTemplateVisibility();
                        window.loadTemplates();
                    },
                    hide: function (deleteElement) { $(this).slideUp(deleteElement); }
                });
                $templateRepeater.find('[data-repeater-create]').first().click();
            }
        });

        // Repeater new row click: initialize Select2
        document.addEventListener("click", function(e){
            if (!e.target.matches("[data-repeater-create]")) return;
            setTimeout(() => {
                document.querySelectorAll(".template-select").forEach(select => {
                    if (!$(select).hasClass("select2-hidden-accessible")) {
                        initTemplateSelect(select);
                    }
                });
            }, 50);
        });
    </script>
    <script>
        const checkboxes = document.querySelectorAll('.type-checkbox');
        const fileInputsContainer = document.getElementById('fileInputsContainer');

        function toggleCheckboxes() {
            let frontChecked = false, backChecked = false, noneChecked = false;
            checkboxes.forEach(cb => {
                if (cb.dataset.typeName === 'front' && cb.checked) frontChecked = true;
                if (cb.dataset.typeName === 'back' && cb.checked) backChecked = true;
                if (cb.dataset.typeName === 'none' && cb.checked) noneChecked = true;
            });

            checkboxes.forEach(cb => {
                const type = cb.dataset.typeName;
                cb.disabled = (noneChecked && (type === 'front' || type === 'back')) || ((frontChecked || backChecked) && type === 'none');
            });

            renderFileInputs();
        }

        function renderFileInputs() {
            if (!fileInputsContainer) return;

            let selectedTypes = [...checkboxes].filter(cb => cb.checked).map(cb => cb.dataset.typeName);

            selectedTypes.forEach(type => {
                if (document.getElementById(`${type}-base-input`)) return;

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
                const input = document.getElementById(area.dataset.inputId);
                const preview = area.querySelector('.preview');

                area.addEventListener('click', () => input?.click());
                area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('dragover'); });
                area.addEventListener('dragleave', e => { e.preventDefault(); area.classList.remove('dragover'); });
                area.addEventListener('drop', e => { e.preventDefault(); area.classList.remove('dragover'); handleFiles(e.dataTransfer.files, input, preview); });
                input?.addEventListener('change', e => handleFiles(e.target.files, input, preview));
            });
        }

        function handleFiles(files, input, preview) {
            if (!files.length) return;

            const reader = new FileReader();
            reader.onload = e => {
                const dataUrl = e.target.result;
                preview.innerHTML = `<img src="${dataUrl}" class="img-fluid rounded border" style="max-height:120px;">`;

                // Load base image into the canvas
                if (input.name.includes('_base_image')) {
                    if (input.id.startsWith('front')) {
                        loadBaseImage(canvasFront, dataUrl);
                        document.getElementById('editorFrontWrapper').classList.remove('d-none');
                    } else if (input.id.startsWith('back')) {
                        loadBaseImage(canvasBack, dataUrl);
                        document.getElementById('editorBackWrapper').classList.remove('d-none');
                    } else if (input.id.startsWith('none')) {
                        loadBaseImage(canvasNone, dataUrl);
                        document.getElementById('editorNoneWrapper').classList.remove('d-none');
                    }
                }
            };
            reader.readAsDataURL(files[0]);

            const dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
        }

        checkboxes.forEach(cb => cb.addEventListener('change', toggleCheckboxes));
    </script>

    <script>
        $(document).ready(function () {
            handleAjaxFormSubmit("#addMockupForm", {
                successMessage: "Mockup Created Successfully",
                onSuccess: function () {
                    $('#addMockupModal').modal('hide');
                    location.reload();
                }
            });
        });

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
                handleFiles(e.target.files);
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
                handleFiles(e.originalEvent.dataTransfer.files);
            });

            function handleFiles(files) {
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

        let selectedColors = [];
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
        });

        $('#openColorPicker').on('click', function () {
            const trigger = document.getElementById('openColorPicker');
            const rect = trigger.getBoundingClientRect();
            const modalScrollTop = document.querySelector('#addMockupModal .modal-body')?.scrollTop || 0;

            if (pickrInstance) {
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
