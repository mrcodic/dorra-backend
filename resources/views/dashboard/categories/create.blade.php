@extends('layouts/contentLayoutMaster')

@section('title', 'Categories')
@section('main-page-url', route("products.index"))
@section('sub-page-url',  route("products.create"))
@section('vendor-style')
    <!-- Vendor CSS Files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
    <section id="multiple-column-form">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add New Category</h4>
                    </div>
                    <div class="card-body">
                        <form id="Category-form" class="form" action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <!-- Main Image -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="Category-image-main">Category Image</label>
                                        <input type="file" name="image" id="Category-image-main" class="form-control" required>
                                    </div>
                                </div>

                                <!-- Category Name EN/AR -->
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="Category-name-en">Category Name (EN)</label>
                                        <input type="text" id="Category-name-en" class="form-control" name="name[en]" placeholder="Category Name (EN)"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="Category-name-ar">Category Name (AR)</label>
                                        <input type="text" id="Category-name-ar" class="form-control" name="name[ar]" placeholder="Category Name (AR)"/>
                                    </div>
                                </div>

                                <!-- Description EN/AR -->
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="description-en">Category Description (EN)</label>
                                        <textarea name="description[en]" id="description-en" class="form-control" placeholder="Category Description (EN)"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="description-ar">Category Description (AR)</label>
                                        <textarea name="description[ar]" id="description-ar" class="form-control" placeholder="Category Description (AR)"></textarea>
                                    </div>
                                </div>




                                <!-- Submit -->
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-1">Submit</button>
                                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
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
            $('#Category-form').on('submit', function (e) {
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
                            sessionStorage.setItem('Category_added', 'true');
                            window.location.href = '/categories';
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
