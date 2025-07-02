@forelse ($data as $mockup)
    <div class="col-md-6 col-lg-4 col-xxl-4 custom-4-per-row" data-template-id="{{ $mockup->id }}">
        <div class="position-relative" style="box-shadow: 0px 4px 6px 0px #4247460F;">
            <!-- Checkbox -->
            <input type="checkbox" class="form-check-input position-absolute top-0 start-0 m-1 category-checkbox"
                   value="{{ $mockup->id }}" name="selected_mockups[]">

            <div style="background-color: #F4F6F6;height:200px"> <!-- Top Image --> <img
                    src="{{  $mockup->getFirstMediaUrl('mockups') ?: asset("images/default-photo.png") }}"
                    class="mx-auto d-block " style="height:100%; width:auto;max-width: 100%; " alt="Template Image">
            </div> <!-- Template Info -->
            <div class="card-body text-start p-2">
                <div>
                    <h6 class="fw-bold mb-1 text-black fs-3"
                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 300px; height: 54px;">
                        {{ $mockup->name }}
                    </h6>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div
                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;height:29px">{{ $mockup->product->name }}
                        </div>

                        <span class="badge text-light p-75 px-2 template-status-label"
                              style="background-color: #222245">
                        {{ $mockup->type?->label() }}
                    </span>
                    </div>

                </div>
                <div class="d-flex justify-content-around p-2">
                    <button type="button" class="btn  btn-outline-secondary show-mockup-btn"
                            data-image="{{ $mockup->getFirstMediaUrl('mockups') }}"
                            data-colors="{{ json_encode($mockup->colors) }}"
                            data-bs-toggle="modal"
                            data-bs-target="#showMockupModal">Show
                    </button>
                    <button type="button" class="btn  btn-outline-secondary edit-mockup-btn" data-bs-toggle="modal"
                            data-bs-target="#editMockupModal"
                            data-id = "{{ $mockup->id }}"
                            data-name="{{ $mockup->name }}"
                            data-type="{{ $mockup->type }}"
                            data-product-id="{{ $mockup->product->id }}"
                            data-colors="{{ json_encode($mockup->colors) }}"
                            data-image="{{ $mockup->getFirstMediaUrl('mockups') }}"
                    >Edit
                    </button>
                    <button class="btn  btn-outline-danger open-delete-mockup-modal"
                            data-id="{{ $mockup->id }}"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteMockupModal"><i data-feather="trash-2"></i> Delete
                    </button>
                </div>

            </div>
        </div>
    </div>
    @include('modals.mockups.edit-mockup',['mockup' => $mockup])

@empty
    <div class="d-flex flex-column justify-content-center align-items-center text-center py-5 w-100"
         style="min-height:65vh;">
        <!-- Empty Image -->
        <img src="{{ asset('images/Empty.png') }}" alt="No Templates" style="max-width: 200px;" class="mb-2">

        <!-- Empty Message -->
        <p class="mb-2 text-secondary">Nothing to show yet.</p>

        <!-- Create Button -->
        <a class="btn btn-primary" data-bs-toggle="modal"
           data-bs-target="#addMockupModal" href="">
            <i data-feather="plus"></i>
            Create Mockup
        </a>
    </div>
@endforelse
<script !src="">
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
            let imageUrl = $(this).data("image");
            let colorsJson = $(this).attr("data-colors");

            let colors = [];
            try {
                colors = JSON.parse(colorsJson);


                if (colors.length === 1 && colors[0].includes(',')) {
                    colors = colors[0].split(',');
                }

            } catch (e) {
                colors = [];
            }

            $("#showMockupModal img").attr("src", imageUrl);

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
        });

        $(".edit-mockup-btn").on("click", function () {
            var id = $(this).data("id");
            var name = $(this).data("name");
            var type = $(this).data("type");
            var productId = $(this).data("product-id");
            var colors = $(this).attr("data-colors"); // get as raw string
            var imgUrl = $(this).data("image");

            try {
                colors = JSON.parse(colors);
                if (colors.length === 1 && colors[0].includes(',')) {
                    colors = colors[0].split(',');
                }
            } catch (e) {
                colors = [];
            }

            $("#editMockupModal #editMockupForm").attr('action',"{{ route("mockups.update",':id') }}".replace(':id',id));
            $("#editMockupModal #edit-mockup-name").val(name);
            $("#editMockupModal #edit-mockup-type").val(type);
            $("#editMockupModal #edit-products-select").val(productId).trigger("change");
            $("#editMockupModal #edit-colorsInput").val(colors.join(", "));

            const previousColorsContainer = $("#editMockupModal #previous-colors");
            previousColorsContainer.empty();

            colors.forEach(color => {
                previousColorsContainer.append(`
            <div style="
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: ${color};
                border: 1px solid #ccc;
            "></div>
        `);
            });

            $("#editMockupModal #edit-uploaded-image img").attr("src", imgUrl);
            $("#editMockupModal #edit-uploaded-image").removeClass("d-none");
            $("#editMockupModal #file-details .file-name").text(imgUrl.split('/').pop());
            $("#editMockupModal #file-details .file-size").text('');

            $("#editMockupModal #edit-upload-progress").addClass("d-none");
            $("#editMockupModal #edit-upload-progress .progress-bar").css("width", "0%");
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
    });
</script>
