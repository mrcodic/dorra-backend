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
                        <form id="editTemplateForm" enctype="multipart/form-data" method="post" action="{{ route('product-templates.update',$model->id) }}">
                            @csrf
                            @method("PUT")
                            <div class="flex-grow-1">
                                <div class="">
                                    <div class="form-group mb-2">
                                        <label class="label-text mb-1">Template Type</label>
                                        <div class="row">
                                            @foreach(\App\Models\Type::all(['id','value']) as $type)
                                                <div class="col">
                                                    <label class="radio-box">
                                                        <input
                                                            class="form-check-input type-checkbox"
                                                            type="checkbox"
                                                            name="types[]"
                                                            value="{{ $type->value }}"
                                                            data-type-name="{{ strtolower($type->value->name) }}"
                                                            @checked($model->types->contains($type->id))

                                                        >
                                                        <span>{{ $type->value->label() }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>


                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="templateNameAr" class="label-text mb-1">Name (AR)</label>
                                            <input type="text" id="templateNameAr" class="form-control"
                                                   name="name[ar]"
                                                   value="{{ $model->getTranslation('name','ar') }}"
                                                   placeholder="Template Name in Arabic">
                                        </div>

                                        <div class="col-md-6 mb-2">
                                            <label for="templateNameEn" class="label-text mb-1">Name (EN)</label>
                                            <input type="text" id="templateNameEn" class="form-control"
                                                   name="name[en]"
                                                   value="{{ $model->getTranslation('name','en') }}"
                                                   placeholder="Template Name in English">
                                        </div>
                                    </div>


                                    <div class="form-group mb-2">
                                        <label for="statusSelect" class="label-text mb-1">Status</label>
                                        <select id="statusSelect" name="status" class="form-select select2">
                                            <option value="">Choose status</option>
                                            @foreach(\App\Enums\Template\StatusEnum::cases() as $status)
                                                <option value="{{ $status->value }}" @selected($status == $model->status)>{{ $status->label() }}</option>
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
                                        <select id="tagsSelect" class="form-select select2" name="tags[]"multiple>
                                            <option value="" readonly>Choose tag</option>
                                            @foreach($associatedData['tags'] as $tag)
                                                <option value="{{ $tag->id }}" @selected($model->tags->contains($tag->id))>
                                                    {{ $tag->getTranslation('name', app()->getLocale()) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
{{--                                    <!-- Colors -->--}}
{{--                                    <div class="form-group mb-2">--}}
{{--                                        <label for="colorsSelect" class="label-text mb-1">Colors</label>--}}
{{--                                        <select id="colorsSelect" name="colors[]" class="form-select select2" multiple>--}}
{{--                                            <option value="">Choose colors</option>--}}
{{--                                            <!-- Add dynamic options here if needed -->--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
                                    <div class="form-group mb-2">
                                        <label for="productsSelect" class="label-text mb-1">Products</label>
                                        <select id="productsSelect" class="form-select select2" name="product_ids[]" multiple>
                                            <option value="" disabled>Choose Product</option>
                                            @foreach($associatedData['products'] as $product)
                                                <option
                                                    value="{{ $product->id }}"
                                                    @selected($product->id == $model->products->contains('id', $product->id))>
                                                    {{ $product->getTranslation('name', app()->getLocale()) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="d-flex justify-content-between pt-2">
                                <button type="button" class="btn btn-outline-secondary" id="cancelButton">Cancel</button>
                                <div class="d-flex gap-1">
                                    <a href="{{ config("services.editor_url")."templates/".$model->id. "?has_mockup=".
                                      ($template->products->pluck('has_mockup')->contains(true) ? 'true' : 'false')}}"
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
        $('#cancelButton').on('click', function(e) {
            e.preventDefault();

            // Reset the form inputs to initial values
            $('#editTemplateForm')[0].reset();

            // Reset all select2 fields inside the form to their original values
            $('#editTemplateForm').find('.select2').each(function() {
                var $select = $(this);
                // Get the option with selected attribute from original HTML
                var originalVal = $select.find('option[selected]').val() || '';
                $select.val(originalVal).trigger('change');
            });
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
    <script !src="">

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
