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
                                        <select id="productsSelect" class="form-select select2" name="product_ids[]" @if(session('product_type') != "other") readonly @endif multiple>
                                            <option value="" disabled>Choose Product</option>
                                            @foreach($associatedData['products'] as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->getTranslation('name', app()->getLocale()) }}
                                                    </option>
                                            @endforeach
                                            <input type="hidden" name="product_id" value="1">


                                        </select>
                                    </div>


                                </div>
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

    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
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
