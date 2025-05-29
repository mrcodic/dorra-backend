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
                    <form id="addTagForm" enctype="multipart/form-data" action="{{ route('tags.store') }}">
                        @csrf
                        <div class="flex-grow-1">
                            <div class="">
                                <div class="row">
                                    <!-- Width -->
                                    <div class="col-md-4 mb-2">
                                        <label for="width" class="label-text mb-1">Width</label>
                                        <input type="number" id="width" name="width" class="form-control" placeholder="Enter width">
                                    </div>

                                    <!-- Height -->
                                    <div class="col-md-4 mb-2">
                                        <label for="height" class="label-text mb-1">Height</label>
                                        <input type="number" id="height" name="height" class="form-control" placeholder="Enter height">
                                    </div>

                                    <!-- Unit -->
                                    <div class="col-md-4 mb-2">
                                        <label for="unit" class="label-text mb-1">Unit</label>
                                        <select id="unit" name="unit" class="form-select">
                                            <option value="">Select Unit</option>
                                            <option value="cm">cm</option>
                                            <option value="inch">inch</option>
                                            <option value="mm">mm</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="templateName" class="label-text mb-1">Name</label>
                                    <input type="text" id="templateName" class="form-control" placeholder="Template Name">
                                </div>

                                <div class="form-group mb-2">
                                    <label for="templateDescription" class="label-text mb-1">Description</label>
                                    <textarea id="templateDescription" class="form-control" rows="3" placeholder="Template Description"></textarea>
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
                                    <select id="productsSelect" class="form-select select2">
                                        <option value="">Choose Product</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="label-text mb-1">Spec</label>
                                    <div class="row">
                                        @foreach(['Spec A', 'Spec B'] as $spec)
                                        <div class="col-12 mb-1">
                                            <div class="border rounded p-1 d-flex align-items-center">
                                                <input type="checkbox" name="specs[]" value="{{ $spec }}" class="form-check-input me-1">
                                                <label class="form-check-label">{{ $spec }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addSpecModal">Add New Specs</button>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <button type="button" class="btn btn-outline-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                                <span >Add Template</span>
                                <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
  
</section>
@endsection
  @include('modals.templates.add-spec')
@section('vendor-script')
<script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
    $(document).ready(function() {
        $('#productsSelect').select2();
        $('#tagsSelect').select2();
         $('#colorsSelect').select2();

    });
</script>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle pricing
        $('input[name="has_custom_prices"]').on('change', function() {
            const isCustom = $(this).val() === '1';
            $('#custom-price-section').toggle(isCustom).find('input').prop('disabled', !isCustom);
            $('#default-price-section').toggle(!isCustom).find('input').prop('disabled', isCustom).val('');
        });

        // Repeater
        $('.invoice-repeater').repeater({
            show: function() {
                $(this).slideDown();
                feather && feather.replace();

                // Recalculate delete button visibility when an item is shown
                var items = $(this).closest('.invoice-repeater').find('[data-repeater-item]');
                items.each(function(index) {
                    // Hide delete button for the first item (index 0) and show for others
                    if (index === 0) {
                        $(this).find('[data-repeater-delete]').hide(); // Hide the delete button for the first item
                    } else {
                        $(this).find('[data-repeater-delete]').show(); // Show delete button for others
                    }
                });
            },
            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);

                // Recalculate delete button visibility after an item is removed
                var items = $(this).closest('.invoice-repeater').find('[data-repeater-item]');
                items.each(function(index) {
                    // Hide delete button for the first item (index 0) and show for others
                    if (index === 0) {
                        $(this).find('[data-repeater-delete]').hide(); // Hide the delete button for the first item
                    } else {
                        $(this).find('[data-repeater-delete]').show(); // Show delete button for others
                    }
                });
            }
        });



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


        $('.select2').select2();

        // Category -> Subcategory
        $('.category-select').on('change', function() {
            const categoryId = $(this).val();
            const $subCategorySelect = $('.sub-category-select');
            $.ajax({
                url: `${$subCategorySelect.data('sub-category-url')}?filter[parent_id]=${categoryId}`,
                method: "GET",
                success: function(res) {
                    $subCategorySelect.empty().append('<option value="">Select subcategory</option>');
                    $.each(res.data, (i, s) => $subCategorySelect.append(`<option value="${s.id}">${s.name}</option>`));
                },
                error: function() {
                    $subCategorySelect.empty().append('<option value="">Error loading Subcategories</option>');
                }
            });
        });

        // Form submit
        $('#product-form').on('submit', function(e) {
            e.preventDefault();
            const saveButton = $('.saveChangesButton');
            const saveLoader = $('.saveLoader');
            const saveButtonText = $('.saveChangesButton .btn-text');
            saveButton.prop('disabled', true);
            saveLoader.removeClass('d-none');
            saveButtonText.addClass('d-none');
            const formData = new FormData(this);
            $.ajax({
                url: this.action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        sessionStorage.setItem('product_added', 'true');
                        window.location.href = '/products';
                    }
                },
                error: function(xhr) {
                    $.each(xhr.responseJSON.errors, (k, msgArr) => {
                        Toastify({
                            text: msgArr[0],
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true
                        }).showToast();
                    });
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');
                }
            });
        });
    });
</script>
@endsection