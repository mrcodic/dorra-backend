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

    /* كل بلوك ياخد سطر كامل ويتكدس عموديًا */
    .type-block {
        display: block !important;
        width: 100% !important;
        box-sizing: border-box;
        margin-bottom: .75rem;
    }

    /* لو الـ inner d-flex موجود داخل البلوك فهو هعرض Base | Mask جنب بعض */
    .type-block>.d-flex {
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

    /* إعدادات عامة للانيميشن */
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
            <form id="editMockupForm" enctype="multipart/form-data" action="{{ route('mockups.update',$model->id) }}">
                @csrf
                @method('PUT')
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
                        <div class="form-group mb-2 col-md-12">
                            <label for="mockupName" class="label-text mb-1">Mockup Name</label>
                            <input type="text" id="templateName" class="form-control" name="name"
                                placeholder="Mockup Name" value="{{ $model->name }}">
                        </div>
                    </div>
                    <div class="form-group mb-2 col-12">
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
                    <!-- العمود اليمين: الـ editor / preview -->
                    <div class="row">
                        <div class="d-none col-lg-6 d-flex flex-column align-items-center mb-1" id="editorFrontWrapper">
                            <label class="label-text">Mockup Editor (Front)</label>
                            <canvas id="mockupCanvasFront" style="border:1px solid #ccc;" height="480"
                                width="480"></canvas>
                        </div>
                        <div class="d-none col-lg-6 d-flex flex-column align-items-center mb-1" id="editorBackWrapper">
                            <label class="label-text">Mockup Editor (Back)</label>
                            <canvas id="mockupCanvasBack" style="border:1px solid #ccc;" height="480px"
                                width="480px"></canvas>
                        </div>

                        <div class="d-none col-lg-6 d-flex flex-column align-items-center mb-1" id="editorNoneWrapper">
                            <label class="label-text">Mockup Editor (General)</label>
                            <canvas id="mockupCanvasNone" class="w-100" height="480" width="480"
                                style="border:1px solid #ccc;"></canvas>
                        </div>
                    </div>
                    <div class="form-group my-2 d-none" id="templatesCardsWrapper">
                        <label class="form-label mb-2">Choose Template</label>

                        {{-- هنا هتنضاف الكروت بالـ JS --}}
                        <div id="templatesCardsContainer" class="row g-1 p-1 bg-white border rounded-3 shadow-sm"></div>
                        <input type="hidden" name="template_id" id="selectedTemplateId" class="col-md-3">
                        <div id="templatesHiddenContainer"></div>

                    </div>



                </div>

                {{-- <div class="mb-2">
                    <label class="label-text mb-1 d-block">Colors</label>
                    <div class="d-flex flex-wrap align-items-center gap-1">
                        <button type="button" id="openColorPicker" class="gradient-picker-trigger border"></button>

                        <span id="selected-colors" class="d-flex gap-1 flex-wrap align-items-center"></span>
                    </div>
                    <div id="colorsInputContainer"></div>
                </div> --}}

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
<!-- Remove Color Modal -->
<div class="modal fade" id="removeColorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header text-white">
                <h5 class="modal-title">Remove Color from Mockups</h5>
                <button type="button" class="btn-close d-flex align-items-start justify-content-center"
                    data-bs-dismiss="modal" aria-label="Close" style="background-color: #24b094">x</button>
            </div>
            <div class="modal-body">
                <p class="mb-1">This color exists in other mockups using the same template. Do you want to remove it
                    from all of them?</p>

                <div id="relatedMockupsList" class="rounded p-1 bg-light d-flex flex-wrap gap-1"
                    style="max-height:300px; overflow-y: auto;">
                    <div class="text-center text-muted">Loading mockups...</div>
                    <div id="relatedMockupsList"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveColor">Yes, remove from all</button>
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

            function preloadFile(type, baseUrl, maskUrl) {
                const baseInput = document.getElementById(`${type}-base-input`);
                const maskInput = document.getElementById(`${type}-mask-input`);

                const block = document.getElementById(`${type}-file-block`);
                if (!block) return;

                const basePreview = block.querySelector(`.upload-area[data-input-id="${type}-base-input"] .preview`);
                const maskPreview = block.querySelector(`.upload-area[data-input-id="${type}-mask-input"] .preview`);

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
                    if (canvas) loadMaskImage(canvas, maskUrl);
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
                        "{{ $model->{ strtolower($type->value->name) . '_mask_image_url' } ?? '' }}"
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

        let pickrInstance  = null;
        let currentCard    = null; // card for current pickr session

        $(document).ready(function () {

            // Destroy previous instance if exists
            if (pickrInstance) pickrInstance.destroyAndRemove();

            // Dummy element for pickr
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

            // Handle save
            pickrInstance.on('save', (color) => {
                if (!currentCard) return;

                const hex = color.toHEXA().toString();

                if (!Array.isArray(currentCard.selectedColors)) {
                    currentCard.selectedColors = [];
                }

                // ADD ONLY (no replace)
                if (!currentCard.selectedColors.includes(hex)) {
                    currentCard.selectedColors.push(hex);
                }

                renderSelectedColors(currentCard);
                const templateIndex = currentCard.dataset.index;
                const templateId = currentCard.dataset.id;

                // buildTemplateColorInputs(currentCard, templateIndex,templateId);

                pickrInstance.hide();
            });

            // Handle clear
            pickrInstance.on('clear', () => {
                if (!currentCard) return;
                currentCard.selectedColors = [];
                renderSelectedColors(currentCard);
                pickrInstance.hide();
            });
        });

        // Open color picker
        $(document).on('click', '.openColorPicker', function () {
            const trigger = this;
            const card = trigger.closest('.template-card');
            //hydrateColorsForCard(card);
            currentCard = card;
            // Initialize selectedColors array if not exists
            if (!card.selectedColors) card.selectedColors = [];
            console.log(card.selectedColors)
            const rect = trigger.getBoundingClientRect();
            const modalScrollTop = document.querySelector('#templateModal .modal-body')?.scrollTop || 0;
            if (!card.selectedColors || !card.selectedColors.length) {
            }
            // hydrateColorsForCard(card); // يجيب المحفوظ ويرسمه مرةv واح-دة

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

        // Remove color from current card
        window.removeColor = function (hex) {
            console.log("sdf",currentCard.selectedColors)
            if (!currentCard || !currentCard.selectedColors) return;
            currentCard.selectedColors = currentCard.selectedColors.filter(c => c !== hex);
            renderSelectedColors(currentCard);
            const templateIndex = currentCard.dataset.index;
            const templateId = currentCard.dataset.id;
            buildTemplateColorInputs(currentCard, templateIndex,templateId);


        };

        // Render colors inside a card
        function renderSelectedColors(card) {
            const ul = card.querySelector('.selected-colors');
            const container = card.querySelector('.colorsInputContainer');

            if (!ul || !container) return;

            ul.innerHTML = '';
            container.innerHTML = '';

            (card.selectedColors || []).forEach(c => {
                const li = document.createElement('li');
                li.innerHTML = `
            <div class="selected-color-wrapper position-relative">
                <div class="selected-color-dot" style="background-color: #fff;">
                    <div class="selected-color-inner" style="background-color: ${c};"></div>
                </div>
                <button type="button" class="remove-color-btn" data-color="${c}">×</button>
            </div>
        `;
                ul.appendChild(li);
            });

            // Keep dataset synced
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
                // احذف اللون محليًا بدون مودال
                card.selectedColors = (card.selectedColors || []).filter(c => c !== hex);
                renderSelectedColors(card);
                buildHiddenTemplateInputs();


                return; // ❌ متفتحش المودال
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
                pendingColorData = null;
            },
            error: function() {
                alert('Failed to remove color.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Yes, remove from all');
            }
        });
    });

        const templatesData = @json($model->templates ?? []);

        // Map: template_id -> colors[]
        const savedColorsById = new Map(
            (templatesData || []).map(t => {
                let colors = t?.pivot?.colors ?? [];
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
                const pivotColors = safeJson(tpl?.pivot?.colors, []);
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
            <button type="button" class="btn btn-sm btn-outline-primary w-100 js-save-positions">Save Positions</button>
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
                        approach: "without_editor",
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
                const $prod = $('#productsSelect');
                if ($prod.length && $prod.val()) {
                    $prod.trigger('change');
                }
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


        function renderFileInputs() {
            if (!fileInputsContainer) return;

            let selectedTypes = [...checkboxes]
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.typeName);

            // -------------------------------
            // REMOVE blocks + hide canvas for unchecked types
            // -------------------------------
            ['front', 'back', 'none'].forEach(type => {
                if (!selectedTypes.includes(type)) {
                    const block = document.getElementById(`${type}-file-block`);
                    if (block) block.remove();

                    // ⬅ هنا نخبّي الكانفاس ونفضّيه ونصفّر الـ inputs
                    if (typeof hideCanvasForType === 'function') {
                        hideCanvasForType(type);
                    }
                }
            });

            // -------------------------------
            // ADD blocks for newly selected types
            // -------------------------------
            selectedTypes.forEach(type => {
                if (document.getElementById(`${type}-file-block`)) return; // already exists

                const typeLabel = type.charAt(0).toUpperCase() + type.slice(1);

                const block = document.createElement('div');
                block.className = 'col-md-6';
                block.id = `${type}-file-block`;

                block.innerHTML = `
                    <label for="mockupTypLabel" class="label-text">${typeLabel}</label>
                    <hr style="height: 2px; background-color: #CED5D4;"/>
                    <div class="mb-2">
                        <label class="form-label label-text">${typeLabel} Base Image</label>
                        <input type="file" name="${type}_base_image" id="${type}-base-input"
                            class="d-none" accept="image/*">

                        <div class="upload-card upload-area" data-input-id="${type}-base-input">
                            <div class="upload-content">
                                <i data-feather="upload" class="mb-2"></i>
                                <p>${typeLabel} Base Image: Drag file here or click to upload</p>
                                <div class="preview mt-1"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label label-text">${typeLabel} Mask Image</label>
                        <input type="file" name="${type}_mask_image" id="${type}-mask-input"
                            class="d-none" accept="image/*">

                        <div class="upload-card upload-area" data-input-id="${type}-mask-input">
                            <div class="upload-content">
                                <i data-feather="upload" class="mb-2"></i>
                                <p>${typeLabel} Mask Image: Drag file here or click to upload</p>
                                <div class="preview mt-1"></div>
                            </div>
                        </div>
                    </div>
        `;

                document.getElementById('fileInputsContainer').appendChild(block);
            });

            feather.replace();
            bindUploadAreas();
        }


        function bindUploadAreas() {
            document.querySelectorAll('.upload-area').forEach(area => {
                area.replaceWith(area.cloneNode(true));
            });

            document.querySelectorAll('.upload-area').forEach(area => {
                const input = document.getElementById(area.dataset.inputId);
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

                input?.addEventListener('change', e => handleFiles(e.target.files, input, preview));
            });
        }

        function handleFiles(files, input, preview) {
            if (!files.length) return;

            const reader = new FileReader();
            reader.onload = e => {
                const dataUrl = e.target.result;
                preview.innerHTML = `<img src="${dataUrl}" class="img-fluid rounded border" style="max-height:120px;">`;

                if (input.name.includes('_base_image')) {
                    if (input.id.startsWith('front')) {
                        loadBaseImage(window.canvasFront, dataUrl);
                        document.getElementById('editorFrontWrapper')?.classList.remove('d-none');
                    } else if (input.id.startsWith('back')) {
                        loadBaseImage(window.canvasBack, dataUrl);
                        document.getElementById('editorBackWrapper')?.classList.remove('d-none');
                    } else if (input.id.startsWith('none')) {
                        loadBaseImage(window.canvasNone, dataUrl);
                        document.getElementById('editorNoneWrapper')?.classList.remove('d-none');
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
    // =========================
        // MAIN IMAGE UPLOAD + FORM SUBMIT
        // =========================
        $(document).ready(function () {
            handleAjaxFormSubmit("#editMockupForm", {
                successMessage: "Mockup Updated Successfully",
                onSuccess: function () {
                    location.replace('/mockups');
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
                    saveAllTemplatePositions(); // sync canvas → DOM (if you still use it)
                }
                buildHiddenTemplateInputs();     // rebuild templates[..] payload
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
