@extends('layouts/contentLayoutMaster')

@section('title', 'Templates')

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
                                    <div class="row @if(session('product_type') != "other")d-none @endif ">
                                        <!-- Width -->
                                        <div class="col-md-4 mb-2">
                                            <label for="edit-width" class="label-text mb-1">Width</label>
                                            <input type="number" id="edit-width" name="width" class="form-control"
                                                   placeholder="Enter width">
                                        </div>

                                        <!-- Height -->
                                        <div class="col-md-4 mb-2">
                                            <label for="edit-height" class="label-text mb-1">Height</label>
                                            <input type="number" id="edit-height" name="height" class="form-control"
                                                   placeholder="Enter height">
                                        </div>

                                        <!-- Unit -->
                                        <div class="col-md-4 mb-2">
                                            <label for="unit" class="label-text mb-1">Unit</label>
                                            <select id="unit" name="unit" class="form-select">
                                                <option value="">Select Unit</option>
                                                @foreach(\App\Enums\Template\UnitEnum::cases() as $unit)
                                                    <option value="{{ $unit->value }}"> {{ $unit->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="label-text mb-1">Template Type</label>
                                        <div class="row">
                                            @foreach(\App\Enums\Template\TypeEnum::cases() as $type)
                                                <div class="col">
                                                    <label class="radio-box">
                                                        <input class="form-check-input" type="radio" name="type"
                                                               value="{{ $type->value }}"
                                                        >
                                                        <span>{{ $type->label() }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="templateName" class="label-text mb-1">Name (AR)</label>
                                            <input type="text" id="templateName" class="form-control"
                                                   name="name[ar]"
                                                   placeholder="Template Name in Arabic">
                                        </div>

                                        <div class="col-md-6 mb-2">
                                            <label for="templateName" class="label-text mb-1">Name (EN)</label>
                                            <input type="text" id="templateName" class="form-control"
                                                   name="name[en]"
                                                   placeholder="Template Name in English">
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="templateDescription" class="label-text mb-1">Description
                                                (AR)</label>
                                            <textarea id="templateDescription" class="form-control" rows="3"
                                                      name="description[ar]"
                                                      placeholder="Template Description in Arabic"></textarea>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label for="templateDescription" class="label-text mb-1">Description
                                                (EN)</label>
                                            <textarea id="templateDescription" class="form-control" rows="3"
                                                      name="description[en]"
                                                      placeholder="Template Description in English"></textarea>
                                        </div>
                                    </div>

                                    {{--                                    <div class="form-group mb-2">--}}
                                    {{--                                        <label for="tagsSelect" class="label-text mb-1">Tags</label>--}}
                                    {{--                                        <select id="tagsSelect" class="form-select select2" multiple>--}}
                                    {{--                                            <option value="">Choose tag</option>--}}
                                    {{--                                        </select>--}}
                                    {{--                                    </div>--}}
                                    {{--                                    <!-- Colors -->--}}
                                    {{--                                    <div class="form-group mb-2">--}}
                                    {{--                                        <label for="colorsSelect" class="label-text mb-1">Colors</label>--}}
                                    {{--                                        <select id="colorsSelect" name="colors[]" class="form-select select2" multiple>--}}
                                    {{--                                            <option value="">Choose colors</option>--}}
                                    {{--                                            <!-- Add dynamic options here if needed -->--}}
                                    {{--                                        </select>--}}
                                    {{--                                    </div>--}}
                                    <div class="form-group mb-2">
                                        <label for="productsSelect" class="label-text mb-1">Product</label>
                                        <select id="productsSelect" class="form-select select2" name="product_id" @if(session('product_type') != "other") readonly @endif>
                                            <option value="" disabled>Choose Product</option>
                                            @foreach($associatedData['products'] as $product)
                                                @if(strtolower($product->getTranslation('name', 'en')) == 't-shirt')
                                                    <option
                                                        value="{{ $product->id }}"
                                                        {{ session('product_type') == 'T-shirt' ? 'selected' : '' }}
                                                    >
                                                        {{ $product->getTranslation('name', app()->getLocale()) }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mb-2 d-none">
                                        <label class="label-text mb-1">Spec</label>
                                        <div class="row" id="specsContainer">

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" id="addNewSpec">
                                    Add New Specs
                                </button>

                            </div>
                            <div class="d-flex justify-content-between pt-2">
                                <button type="button" class="btn btn-outline-secondary"  id="cancelButton">Cancel</button>
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
    @include('modals.templates.add-spec')

@endsection
@section('vendor-script')

    <script !src="">
        $(document).ready(function () {
            $('#cancelButton').on('click', function(e) {
                e.preventDefault();

                // Reset the form inputs to initial values
                $('#addTemplateForm')[0].reset();

                // Reset all select2 fields inside the form to their original values
                $('#addTemplateForm').find('.select2').each(function() {
                    var $select = $(this);
                    // Get the option with selected attribute from original HTML
                    var originalVal = $select.find('option[selected]').val() || '';
                    $select.val(originalVal).trigger('change');
                });
            });
            $(document).on('click', '#specsContainer .border', function(e) {
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

        $("#addNewSpec").on('click', function (e) {
            const productId = $('#productsSelect').val();

            if (!productId) {
                e.preventDefault(); // block behavior

                Toastify({
                    text: "Select product first to add spec on it!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // Red for warning
                    close: true,
                }).showToast();

            } else {
                $("#addSpecModal").modal("show");

            }
        });
        $('.saveChangesButton').on('click', function (e) {
            const $button = $(this);
            const action = $button.data('action');
            // Set form action
            if (action === 'draft') {
                $('#addTemplateForm').attr('action', "{{ route('templates.store') }}");
            } else if (action === 'editor') {
                $('#addTemplateForm').attr('action', "{{ route('templates.redirect.store') }}");
            }
        });


        handleAjaxFormSubmit("#addTemplateForm", {
            successMessage: "Template created successfully",
            onSuccess: function (response, $form) {
                // Re-enable buttons & hide all loaders
                $('.saveChangesButton').prop('disabled', false).find('.saveLoader').addClass('d-none');
                if (!response.data) {
                    setTimeout(function () {
                        window.location.href = '/product-templates';
                    }, 1000);
                }
                if (response.data.redirect_url) {
                    window.open(response.data.redirect_url, '_blank');
                }

            },
            onError: function () {
                // Re-enable buttons & hide all loaders on error too
                $('.saveChangesButton').prop('disabled', false).find('.saveLoader').addClass('d-none');
            }
        });

    </script>
    <script !src="">

        $('#productsSelect').on('change', function () {
            const productId = $(this).val();
            if (productId) {
                $('#addNewSpec').attr('data-bs-toggle', 'modal').attr('data-bs-target', '#addSpecModal');
                $('#product_id_input').val(productId);

            } else {
                $('#addNewSpec').removeAttr('data-bs-toggle').removeAttr('data-bs-target');
            }

            const $specsContainer = $('#specsContainer');
            const $specSection = $specsContainer.closest('.form-group');

            if (productId) {
                $.ajax({
                    url: "{{ url('api/v1/product-specifications') }}/" + productId,
                    method: 'GET',
                    success: function (res) {
                        if (res.data && res.data.length > 0) {
                            $specsContainer.empty();

                            res.data.forEach(spec => {
                                const specHtml = `
                                <div class="col-12 mb-1">
                                    <div class="border rounded p-1 d-flex align-items-center">
                                        <input type="checkbox" name="specifications[]" value="${spec.id}" class="form-check-input me-1">
                                        <label class="form-check-label">${spec.name}</label>
                                    </div>
                                </div>
                            `;
                                $specsContainer.append(specHtml);
                            });

                            $specSection.removeClass('d-none');
                        } else {
                            $specsContainer.empty();
                            $specSection.addClass('d-none');
                        }
                    },
                    error: function () {
                        Toastify({
                            text: 'Failed to load specifications.',
                            duration: 4000,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#EA5455',
                            close: true
                        }).showToast();
                        $specsContainer.empty();
                        $specSection.addClass('d-none');
                    }
                });
            } else {
                $specsContainer.empty();
                $specSection.addClass('d-none');
            }
        });
    </script>
    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
    <script !src="">
        handleAjaxFormSubmit("#addSpecForm", {
            successMessage: "Specification created successfully!",
            closeModal: '#addSpecModal',
            onSuccess: function (response, $form) {
                const spec = response.data;

                const specHtml = `
            <div class="col-12 mb-1">
                <div class="border rounded p-1 d-flex align-items-center">
                    <input type="checkbox" name="specifications[]" value="${spec.id}" class="form-check-input me-1">
                    <label class="form-check-label">${spec.name}</label>
                </div>
            </div>
        `;

                $('#specsContainer').append(specHtml);

                // Clear uploaded images and previews
                $form.find('.option-image-input').val(''); // Clear file input
                $form.find('.option-upload-progress').addClass('d-none').find('.progress-bar').css('width', '0%'); // Reset progress bar
                $form.find('.option-uploaded-image').addClass('d-none'); // Hide uploaded image preview container
                $form.find('.option-image-preview').attr('src', ''); // Clear preview src
                $form.find('.option-file-name').text(''); // Clear file name text
                $form.find('.option-file-size').text(''); // Clear file size text
            },
            resetForm: true,
        });

    </script>
    <script>
        $(document).ready(function () {
            $('#productsSelect').select2();
            $('#tagsSelect').select2();
            $('#colorsSelect').select2();

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
