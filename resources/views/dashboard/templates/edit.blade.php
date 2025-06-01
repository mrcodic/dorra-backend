@extends('layouts/contentLayoutMaster')
@section('title', 'Edit Templates')
@section('main-page', 'Templates')
@section('sub-page', 'Edit Templates')

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
                                    <div class="row">
                                        <!-- Width -->
                                        <div class="col-md-4 mb-2">
                                            <label for="width" class="label-text mb-1 width">Width</label>
                                            <input type="number" id="width" name="width" value="{{ $model->width }}"
                                                   class="form-control"
                                                   placeholder="Enter width">
                                        </div>

                                        <!-- Height -->
                                        <div class="col-md-4 mb-2">
                                            <label for="height" class="label-text mb-1">Height</label>
                                            <input type="number" id="height" name="height" value="{{ $model->height }}"
                                                   class="form-control"
                                                   placeholder="Enter height">
                                        </div>

                                        <!-- Unit -->
                                        <div class="col-md-4 mb-2">
                                            <label for="unit" class="label-text mb-1">Unit</label>
                                            <select id="unit" name="unit" class="form-select">
                                                <option value="">Select Unit</option>
                                                @foreach(\App\Enums\Template\UnitEnum::cases() as $unit)
                                                    <option
                                                        value="{{ $unit->value }}" @selected($unit->value == $model->unit)> {{ $unit->label() }}</option>
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
                                                            @checked($type->value == $model->type)
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
                                                   value="{{ $model->getTranslation('name','ar') }}"
                                                   placeholder="Template Name in Arabic">
                                        </div>

                                        <div class="col-md-6 mb-2">
                                            <label for="templateName" class="label-text mb-1">Name (EN)</label>
                                            <input type="text" id="templateName" class="form-control"
                                                   name="name[en]"
                                                   value="{{ $model->getTranslation('name','en') }}"
                                                   placeholder="Template Name in English">
                                        </div>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label for="statusSelect" class="label-text mb-1">Status</label>
                                        <select id="statusSelect" name="status" class="form-select select2" >
                                            <option value="">Choose status</option>
                                            @foreach(\App\Enums\Template\StatusEnum::cases() as $status)
                                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="templateDescription" class="label-text mb-1">Description
                                                (AR)</label>
                                            <textarea id="templateDescription" class="form-control" rows="3"
                                                      name="description[ar]"
                                                      placeholder="Template Description in Arabic">{{ $model->getTranslation('description','ar') }}</textarea>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label for="templateDescription" class="label-text mb-1">Description
                                                (EN)</label>
                                            <textarea id="templateDescription" class="form-control" rows="3"
                                                      name="description[en]"
                                                      placeholder="Template Description in English">{{ $model->getTranslation('description','en') }}</textarea>
                                        </div>
                                    </div>


                                    <div class="form-group mb-2">
                                        <label for="tagsSelect" class="label-text mb-1">Tags</label>
                                        <select id="tagsSelect" class="form-select select2" multiple>
                                            <option value="">Choose tag</option>
                                        </select>
                                    </div>
                                    <!-- Colors -->
                                    <div class="form-group mb-2">
                                        <label for="colorsSelect" class="label-text mb-1">Colors</label>
                                        <select id="colorsSelect" name="colors[]" class="form-select select2" multiple>
                                            <option value="">Choose colors</option>
                                            <!-- Add dynamic options here if needed -->
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="productsSelect" class="label-text mb-1">Product</label>
                                        <select id="productsSelect" class="form-select select2" name="product_id">
                                            <option value="" disabled>Choose Product</option>
                                            @foreach($associatedData['products'] as $product)
                                                <option
                                                    value="{{ $product->id }}"
                                                    @selected($product->id == $model->product->id)>
                                                    {{ $product->getTranslation('name', app()->getLocale()) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if($model->specifications->isNotEmpty())
                                        <div class="form-group mb-2">
                                            <label class="label-text mb-1">Spec</label>
                                            <div class="row" id="specsContainer">
                                                @foreach($model->product->specifications as $spec)
                                                    <div class="col-12 mb-1">
                                                        <div class="border rounded p-1 d-flex align-items-center">
                                                            <input type="checkbox" name="specifications[]"
                                                                   value="{{$spec->id}}" class="form-check-input me-1"
                                                                @checked($model->specifications->contains($spec->id))
                                                            >
                                                            <label class="form-check-label">{{ $spec->name}}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                    @endif
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" id="addNewSpec"
                                >
                                    Add New Specs
                                </button>

                            </div>
                            <div class="d-flex justify-content-between pt-2">
                                <button type="button" class="btn btn-outline-secondary">Cancel</button>
                                     <div class="d-flex gap-1">
                                <a href="{{ config("services.editor_url")."templates/".$model->id }}"
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
    @include('modals.templates.add-spec')
@endsection


@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection


@section('page-script')
    <script !src="">
        $("#addNewSpec").on('click', function (e) {
            const productId = $('#productsSelect').val();
            $('#product_id_input').val(productId);
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
    </script>
    <script !src="">
        handleAjaxFormSubmit("#editTemplateForm", {
            successMessage: "Template updated successfully",
            onSuccess: function (response, $form) {
                const data = response.data;
                console.log(data.width)
                // Update numeric fields
                $('#width').val(data.width);
                $('#height').val(data.height);

                // Update select field
                $('#unit').val(data.unit).trigger('change');

                // Update name fields
                $('input[name="name[ar]"]').val(data.name.ar);
                $('input[name="name[en]"]').val(data.name.en);

                // Update description fields
                $('textarea[name="description[ar]"]').val(data.description.ar);
                $('textarea[name="description[en]"]').val(data.description.en);

                console.log('Form inputs updated with new data.');
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
                    url: "{{ url('api/product-specifications') }}/" + productId,
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
    <script !src="">
        handleAjaxFormSubmit("#addSpecForm", {
            successMessage: "Specification created successfully!",
            closeModal: '#addSpecModal',
            onSuccess: function (response, $form) {
                $form[0].reset();
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


                $form[0].reset();
            }
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
            $(containerSelector).find('[data-repeater-list]').each(function() {
                var items = $(this).find('[data-repeater-item]');
                items.each(function() {
                    $(this).find('[data-repeater-delete]').show();
                    feather.replace();
                });
            });
        }

        function initializeImageUploaders(context) {
            $(context).find('.option-upload-area').each(function() {
                const uploadArea = $(this);
                const input = uploadArea.closest('.col-md-12').find('.option-image-input');
                const previewContainer = uploadArea.closest('.col-md-12').find('.option-uploaded-image');
                const imagePreview = previewContainer.find('.option-image-preview');
                const fileNameLabel = previewContainer.find('.option-file-name');
                const fileSizeLabel = previewContainer.find('.option-file-size');
                const removeButton = previewContainer.find('.option-remove-image');

                uploadArea.off('click').on('click', function() {
                    input.trigger('click');
                });

                input.off('change').on('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.attr('src', e.target.result);
                            fileNameLabel.text(file.name);
                            fileSizeLabel.text((file.size / 1024).toFixed(1) + ' KB');
                            previewContainer.removeClass('d-none');
                        };
                        reader.readAsDataURL(file);
                    }
                });

                removeButton.off('click').on('click', function() {
                    input.val('');
                    previewContainer.addClass('d-none');
                });
            });
        }

        $('.outer-repeater').repeater({
            repeaters: [{
                selector: '.inner-repeater',
                show: function() {
                    $(this).slideDown();
                    updateDeleteButtons($(this).closest('.outer-repeater'));
                    initializeImageUploaders(this);
                    feather.replace();
                },
                hide: function(deleteElement) {
                    $(this).slideUp(deleteElement);
                    updateDeleteButtons($(this).closest('.outer-repeater'));
                },
                nestedInputName: 'specification_options'
            }],
            show: function() {
                $(this).slideDown();
                updateDeleteButtons($('.outer-repeater'));
                initializeImageUploaders(this);
                feather.replace();
            },
            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);
                updateDeleteButtons($('.outer-repeater'));
            },
            afterAdd: function() {
                updateDeleteButtons($('.outer-repeater'));
                initializeImageUploaders($('.outer-repeater'));
                feather.replace();
            },
            afterDelete: function() {
                updateDeleteButtons($('.outer-repeater'));
            }
        });

        // Initialize on page load for already existing items
        $(document).ready(function() {
            updateDeleteButtons($('.outer-repeater'));
            initializeImageUploaders($('.outer-repeater'));
        });
    </script>

@endsection
