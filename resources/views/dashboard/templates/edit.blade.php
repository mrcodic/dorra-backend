@extends('layouts/contentLayoutMaster')

@section('title', 'Edit Templates')
@section('main-page', 'Templates')
@section('sub-page', 'Edit Templates')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<section id="multiple-column-form">
    <div class="row">
        <div class="col-12">
            <div class="card p-2">
                <div class="row">
                    {{-- Left Column --}}
                    <div class="col-md-4 border-end">
                        <div class="mb-2 text-center">
                            <img src="{{  asset('images/portrait/small/avatar-s-2.jpg') }}" alt="Template Preview" class="img-fluid rounded mb-1" style="max-width: 240px;" />
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary">Edit</button>
                                <button class="btn btn-secondary">Upload</button>
                            </div>
                        </div>

                        <div>
                            <h5 class="mt-3 fs-16 text-black">Version History</h5>
                            <ul class="list-group">
                                <li class=" d-flex justify-content-between align-items-start flex-column mt-1">
                                    <div class="w-100 d-flex justify-content-between align-items-center">
                                        <span>Version 1</span>
                                        <div>
                                            <span class="fs-10">13/10/2024</span>
                                            <i class="mx-1 text-success" data-feather="eye" role="button"></i>
                                            <i class="text-danger" data-feather="trash" role="button"></i>
                                        </div>
                                    </div>

                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="col-md-8">
                        <div class="form-group mb-2">
                            <label class="form-label">Template State</label>
                            <div class="form-check form-switch d-flex justify-content-between align-items-center border rounded-3 p-1">
                                <label class="form-check-label" for="templateSwitch">Drafted</label>
                                <input class="form-check-input" type="checkbox" id="templateSwitch">

                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label for="templateName" class="label-text mb-1">Template Name</label>
                            <input type="text" id="templateName" class="form-control" placeholder="Template Name">
                        </div>

                        <div class="form-group mb-2">
                            <label for="templateDescription" class="label-text mb-1">Template Description</label>
                            <textarea id="templateDescription" class="form-control" rows="3" placeholder="Template Description"></textarea>
                        </div>

                        <div class="row mb-2">
                            <div class="col">
                                <label for="categorySelect" class="label-text mb-1"> Category</label>
                                <select id="categorySelect" class="form-select select2">
                                    <option value="">Select Category</option>
                                    {{-- Add your options --}}
                                </select>
                            </div>
                            <div class="col">
                                <label for="subcategorySelect" class="label-text mb-1">Subcategory</label>
                                <select id="subcategorySelect" class="form-select select2">
                                    <option value="">Select Subcategory</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label for="tagsSelect" class="label-text mb-1">Tags</label>
                            <select id="tagsSelect" class="form-select select2" multiple>
                            <option value="">Choose Font</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="paletteSelect" class="label-text mb-1">Color Palette</label>
                                <select id="paletteSelect" class="form-select select2">
                                    <option value="">Choose Palette</option>
                                </select>
                            </div>
                            <div class="col">
                                <label for="fontSelect" class="label-text mb-1">Fonts</label>
                                <select id="fontSelect" class="form-select select2">
                                    <option value="">Choose Font</option>
                                </select>
                            </div>
                        </div>
                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-between mt-2">


                            <button class="btn btn-outline-secondary">Discard Changes</button>
                            <div class="d-flex gap-1">
                                <button class="btn btn-outline-secondary">Save as Draft</button>
                                <button class="btn btn-primary"> Save Changes & Publish</button>
                            </div>
                        </div>
                    </div>
                </div> <!-- row end -->
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Toggle pricing
            $('input[name="has_custom_prices"]').on('change', function () {
                const isCustom = $(this).val() === '1';
                $('#custom-price-section').toggle(isCustom).find('input').prop('disabled', !isCustom);
                $('#default-price-section').toggle(!isCustom).find('input').prop('disabled', isCustom).val('');
            });

            // Repeater
            $('.invoice-repeater').repeater({
                show: function () { $(this).slideDown(); feather && feather.replace(); },
                hide: function (deleteElement) { $(this).slideUp(deleteElement); }
            });

            $('.outer-repeater').repeater({
                repeaters: [{
                    selector: '.inner-repeater',
                    show: function () { $(this).slideDown(); },
                    hide: function (deleteElement) {
                        if(confirm('Are you sure you want to delete this value?')) {
                            $(this).slideUp(deleteElement);
                        }
                    },
                    nestedInputName: 'specification_options'
                }],
                show: function () { $(this).slideDown(); },
                hide: function (deleteElement) {
                    if(confirm('Are you sure you want to delete this specification?')) {
                        $(this).slideUp(deleteElement);
                    }
                }
            });

            $('.select2').select2();

            // Category -> Subcategory
            $('.category-select').on('change', function () {
                const categoryId = $(this).val();
                const $subCategorySelect = $('.sub-category-select');
                $.ajax({
                    url: `${$subCategorySelect.data('sub-category-url')}?filter[parent_id]=${categoryId}`,
                    method: "GET",
                    success: function (res) {
                        $subCategorySelect.empty().append('<option value="">Select subcategory</option>');
                        $.each(res.data, (i, s) => $subCategorySelect.append(`<option value="${s.id}">${s.name}</option>`));
                    },
                    error: function () {
                        $subCategorySelect.empty().append('<option value="">Error loading Subcategories</option>');
                    }
                });
            });

            // Form submit
            $('#product-form').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                $.ajax({
                    url: this.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            sessionStorage.setItem('product_added', 'true');
                            window.location.href = '/products';
                        }
                    },
                    error: function (xhr) {
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
                    }
                });
            });
        });
    </script>
@endsection