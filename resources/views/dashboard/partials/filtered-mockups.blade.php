@forelse ($data as $mockup)
@php
$images = collect($mockup->getMedia('mockups'))
->groupBy(fn($media) => $media->getCustomProperty('side'))
->mapWithKeys(function ($group, $side) {
$side = strtolower($side);
$base = $group->first(fn($m) => $m->getCustomProperty('role') === 'base');
return [
$side => [
'base_url' => $base?->getFullUrl(),
]
];
});
@endphp

<div class="col-md-6 col-lg-4 col-xxl-4 custom-4-per-row" data-template-id="{{ $mockup->id }}">
    <div class="position-relative border rounded-3" style="box-shadow: 0px 4px 6px 0px #4247460F;">
        <!-- Checkbox -->
        <input type="checkbox" class="form-check-input position-absolute top-0 start-0 m-1 category-checkbox"
            value="{{ $mockup->id }}" name="selected_mockups[]">

        <div style="background-color: #F4F6F6;height:200px">
            <!-- Top Image --> <img src="{{  $mockup->getFirstMediaUrl('mockups') ?: asset(" images/default-photo.png")
                }}" class="mx-auto d-block rounded-top" style="height:100%; width:auto;max-width: 100%; "
                alt="Template Image">
        </div> <!-- Template Info -->
        <div class="card-body text-start p-2">
            <div>
                <h6 class="fw-bold mb-1 text-black fs-3"
                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 300px; height: 54px;">
                    {{ $mockup->name }}
                </h6>

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div
                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;height:29px">
                        {{ $mockup->product?->name }}
                    </div>

                    <div>
                        @foreach( $mockup->types as $type)
                        <span class="badge text-light p-75  template-status-label" style="background-color: #222245">
                            {{ $type->value->label() }}
                        </span>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="d-flex flex-wrap w-100 mt-1" style="gap:5px">
                <button type="button" class="btn btn-outline-secondary flex-fill show-mockup-btn"
                    data-images="{{ json_encode($images) }}" data-colors="{{ json_encode($mockup->colors) }}"
                    data-bs-toggle="modal" data-bs-target="#showMockupModal">Show
                </button>

                <button type="button" class="btn btn-outline-secondary flex-fill edit-mockup-btn" data-bs-toggle="modal"
                    data-bs-target="#editMockupModal" data-id="{{ $mockup->id }}" data-name="{{ $mockup->name }}"
                    data-types="{{ $mockup->types?->pluck(" id") }}" data-product-id="{{ $mockup->product?->id }}"
                    data-colors="{{ json_encode($mockup?->colors) }}"
                    data-images="{{ json_encode($mockup->getMedia('mockups')) }}">Edit
                </button>

                <button class="btn btn-outline-danger flex-fill open-delete-mockup-modal" data-id="{{ $mockup->id }}"
                    data-bs-toggle="modal" data-bs-target="#deleteMockupModal">
                    <i data-feather="trash-2"></i> Delete
                </button>
            </div>


        </div>
    </div>
</div>

@include('modals.mockups.edit-mockup',['mockup' => $mockup, 'associatedData' => $associatedData])

@empty
<div class="d-flex flex-column justify-content-center align-items-center text-center py-5 w-100"
    style="min-height:65vh;">
    <!-- Empty Image -->
    <img src="{{ asset('images/Empty.png') }}" alt="No Templates" style="max-width: 200px;" class="mb-2">

    <!-- Empty Message -->
    <p class="mb-2 text-secondary">Nothing to show yet.</p>
</div>
@endforelse
<script !src="">
    function renderAllColors() {
        const container = document.getElementById('edit-selected-colors');
        container.innerHTML = '';

        editPreviousColors.forEach(color => {
            const item = document.createElement('span');
            item.innerHTML = `
            <div class="selected-color-wrapper position-relative">
                <div class="selected-color-dot" style="background-color: #fff;">
                    <div class="selected-color-inner" style="background-color: ${color};"></div>
                </div>
                <button type="button" class="remove-color-btn" onclick="removePreviousColor('${color}')">×</button>
            </div>
        `;
            container.appendChild(item);
        });
        handleAjaxFormSubmit('.change-status-form', {
            successMessage: '✅ Status updated successfully!',
            onSuccess: function (response, $form) {
                console.log('Success:', response);

                const templateId = response.data.id;
                const status = response.data.status.value; // assuming this contains status enum value
                const statusLabel = response.data.status.label;
                const designData = response.data.design_data; // assuming design_data returned from server

                // Update status label text
                const $statusLabel = $('.template-status-label[data-template-id="' + templateId + '"]');
                if ($statusLabel.length) {
                    $statusLabel.text(statusLabel);
                }

                // Find the template card div by data-template-id
                const $mockupCard = $('[data-template-id="' + templateId + '"]');

                if ($mockupCard.length) {
                    // Find publish button
                    const $publishBtn = $mockupCard.find('form.change-status-form input[name="status"][value="{{ \App\Enums\Template\StatusEnum::PUBLISHED }}"]').siblings('button');
                    // Find draft button
                    const $draftBtn = $mockupCard.find('form.change-status-form input[name="status"][value="{{ \App\Enums\Template\StatusEnum::DRAFTED }}"]').siblings('button');
                    // Find live button
                    const $liveBtn = $mockupCard.find('form.change-status-form input[name="status"][value="{{ \App\Enums\Template\StatusEnum::LIVE }}"]').siblings('button');

                    // Logic to enable/disable buttons, example (adjust according to your rules):

                    // Enable Publish if design_data exists and status not published
                    if (designData && status !== {{ \App\Enums\Template\StatusEnum::PUBLISHED->value }}) {
                        $publishBtn.removeClass('disabled').prop('disabled', false);
                    } else {
                        $publishBtn.addClass('disabled').prop('disabled', true);
                    }

                    // Enable Draft if design_data exists and status not drafted
                    if (designData && status !== {{ \App\Enums\Template\StatusEnum::DRAFTED->value }}) {
                        $draftBtn.removeClass('disabled').prop('disabled', false);
                    } else {
                        $draftBtn.addClass('disabled').prop('disabled', true);
                    }

                    // Enable Live if design_data exists and status not live (adjust this condition if needed)
                    if (designData && status !== {{ \App\Enums\Template\StatusEnum::LIVE->value }}) {
                        $liveBtn.removeClass('disabled').prop('disabled', false);
                    } else {
                        $liveBtn.addClass('disabled').prop('disabled', true);
                    }
                }
            },

            onError: function (xhr, $form) {
                console.error('Error:', xhr);
            },
            resetForm: false,
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (window.feather) {
            feather.replace();
        }
    });

    // Also run after AJAX updates
    $(document).ajaxComplete(function () {
        if (window.feather) {
            feather.replace();
        }
    });
    $(document).ready(function () {


        $(document).on("click", ".show-mockup-btn", function () {
            let colorsJson = $(this).attr("data-colors");
            let imagesJson = $(this).attr("data-images");
            let colors = [];
            try {
                colors = JSON.parse(colorsJson);
                if (colors.length === 1 && colors[0].includes(',')) {
                    colors = colors[0].split(',');
                }
            } catch (e) {
                colors = [];
            }

            let images = {};
            try {
                images = JSON.parse(imagesJson);
            } catch (e) {
                images = {};
            }

            // Render Colors
            const colorsContainer = $("#showMockupModal .colors-container");
            colorsContainer.empty();
            colors.forEach(color => {
                colorsContainer.append(`
            <div style="
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: ${color};
                border: 1px solid #ccc;
            "></div>
        `);
            });

            // Render Images
            const imageContainer = $("#showMockupModal .mockup-images-container");
            imageContainer.empty();

            // Group front/back in same row
            const sidesInRow = ['front', 'back'];
            let row = $('<div class="d-flex justify-content-center gap-3 mb-3"></div>');

            sidesInRow.forEach(side => {
                if (images[side]) {
                    const sideLabel = side.charAt(0).toUpperCase() + side.slice(1);
                    const baseUrl = images[side].base_url;

                    row.append(`
                <div class="text-center">
                    ${baseUrl ? `<img src="${baseUrl}" alt="${sideLabel} Base" style="height: auto; width: 300px;">` : `<p>No base image</p>`}
                </div>
            `);
                }
            });

            // Append row if it has content
            if (row.children().length) {
                imageContainer.append(row);
            }

            // Handle any remaining sides (e.g., "none", etc.)
            for (const side in images) {
                if (!sidesInRow.includes(side)) {
                    const sideLabel = side.charAt(0).toUpperCase() + side.slice(1);
                    const baseUrl = images[side].base_url;

                    imageContainer.append(`
                <div class="text-center mb-3">
                    <h6 class="text-uppercase mb-2">${sideLabel} Side</h6>
                    ${baseUrl ? `<img src="${baseUrl}" alt="${sideLabel} Base" style="max-height: 300px; max-width: 100%;">` : `<p>No base image</p>`}
                </div>
            `);
                }
            }
        });


        {{--$(".edit-mockup-btn").on("click", function () {--}}
        {{--    const id = $(this).data("id");--}}
        {{--    const name = $(this).data("name");--}}
        {{--    const types = $(this).data("types"); // array of type IDs--}}
        {{--    console.log(types)--}}
        {{--    const productId = $(this).data("product-id");--}}
        {{--    const rawColors = $(this).attr("data-colors");--}}

        {{--    // ✅ Always read raw JSON for images to avoid jQuery's auto-parse issues--}}
        {{--    let rawImages = $(this).attr("data-images");--}}
        {{--    let imageList = [];--}}
        {{--    try {--}}
        {{--        let parsed = JSON.parse(rawImages);--}}
        {{--        if (Array.isArray(parsed)) {--}}
        {{--            imageList = parsed;--}}
        {{--        } else if (parsed && typeof parsed === "object") {--}}
        {{--            imageList = Object.values(parsed); // turn keyed object into array--}}
        {{--        }--}}
        {{--    } catch (e) {--}}
        {{--        imageList = [];--}}
        {{--    }--}}

        {{--    // ✅ Colors parsing--}}
        {{--    let colors = [];--}}
        {{--    try {--}}
        {{--        colors = JSON.parse(rawColors);--}}
        {{--        if (colors.length === 1 && colors[0].includes(",")) {--}}
        {{--            colors = colors[0].split(",").map(c => c.trim());--}}
        {{--        }--}}
        {{--    } catch (e) {--}}
        {{--        colors = [];--}}
        {{--    }--}}

        {{--    // ✅ Set form action--}}
        {{--    const actionUrl = "{{ route('mockups.update', ':id') }}".replace(":id", id);--}}
        {{--    $("#editMockupForm").attr("action", actionUrl);--}}

        {{--    // ✅ Set name & product--}}
        {{--    $("#edit-mockup-name").val(name);--}}
        {{--    $("#edit-products-select").val(productId).trigger("change");--}}

        {{--    // ✅ Reset and check appropriate types--}}
        {{--    const checkboxes = $("#editMockupModal .type-checkbox");--}}
        {{--    checkboxes.prop("checked", false).prop("disabled", false);--}}

        {{--    types.forEach(function (typeId) {--}}
        {{--        checkboxes.each(function () {--}}
        {{--            if (parseInt($(this).data("type-id")) === parseInt(typeId)) {--}}
        {{--                $(this).prop("checked", true);--}}
        {{--            }--}}
        {{--        });--}}
        {{--    });--}}

        {{--    // ✅ Set colors--}}
        {{--    editPreviousColors = colors;--}}
        {{--    renderAllColors();--}}

        {{--    // ✅ Trigger change so inputs are rendered for checked types--}}
        {{--    checkboxes.trigger("change");--}}

        {{--    // ✅ Inject image previews *after* inputs are rendered--}}
        {{--    setTimeout(() => {--}}
        {{--        imageList.forEach(img => {--}}
        {{--            console.log(img.custom_properties)--}}
        {{--            const typeName = img.custom_properties?.side || '';--}}
        {{--            const fileType = img.custom_properties?.role || ''; // e.g., 'base' or 'mask'--}}

        {{--            if (typeName && fileType) {--}}
        {{--                const inputId = `${typeName}-${fileType}-input`;--}}
        {{--                const preview = $(`#${inputId}`).siblings('.upload-card').find('.preview');--}}

        {{--                if (preview.length) {--}}
        {{--                    preview.html(`--}}
        {{--                <img src="${img.original_url}"--}}
        {{--                     alt="Preview"--}}
        {{--                     class="img-fluid rounded border"--}}
        {{--                     style="max-height:120px;">--}}
        {{--            `);--}}
        {{--                }--}}
        {{--            }--}}
        {{--        });--}}
        {{--    }, 50); // Small delay so DOM is ready--}}

        {{--    // ✅ Reset main product image area--}}
        {{--    $("#edit-uploaded-image").addClass("d-none").find("img").attr("src", "");--}}
        {{--    $("#file-details .file-name").text("");--}}
        {{--    $("#file-details .file-size").text("");--}}
        {{--    $("#edit-upload-progress").addClass("d-none");--}}
        {{--    $("#edit-upload-progress .progress-bar").css("width", "0%");--}}
        {{--});--}}

    });
</script>
