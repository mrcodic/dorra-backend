@extends('layouts/contentLayoutMaster')

@section('title', 'Products')

@section('vendor-style')
    <!-- vendor css files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
    <!-- Basic multiple Column Form section start -->
    <section id="multiple-column-form">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add New Product</h4>
                    </div>
                    <div class="card-body">
                        <form id="product-form" class="form" action="{{ route('products.store') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <!-- Product Name -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="product-name">Product Name</label>
                                        <input type="text" id="product-name" class="form-control" name="name"
                                               placeholder="Product Name"/>
                                    </div>
                                </div>

                                <!-- Product Description -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="product-description">Product Description</label>
                                        <textarea name="description" id="product-description" class="form-control"
                                                  placeholder="Product Description"></textarea>
                                    </div>
                                </div>

                                <!-- Main Image -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="product-image-main">Product Image (main)*</label>
                                        <input type="file" name="image" id="product-image-main" class="form-control"
                                               required>
                                    </div>
                                </div>

                                <!-- Multiple Images -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="product-image">Product Images</label>
                                        <input type="file" name="images[]" class="form-control" multiple>
                                    </div>
                                </div>

                                <!-- Category -->
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="category">Category</label>
                                        <select name="category_id" id="category" class="form-control category-select">
                                            <option value="">Select category</option>
                                            @foreach($associatedData['categories'] as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Subcategory -->
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="sub-category">Subcategory</label>
                                        <select name="sub_category_id" id="sub-category" class="form-control sub-category-select"
                                                data-sub-category-url="{{ route("sub-categories") }}"
                                        >
                                            <option value="">Select subcategory</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Tags -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="select2-multiple">Tags</label>
                                        <select name="tags[]" id="select2-multiple" class="select2 form-select"
                                                multiple>
                                            <option value="1">Tag 1</option>
                                            <option value="2">Tag 2</option>
                                            <option value="3">Tag 3</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Price Options -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label">Quantity & Price Options</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="has_custom_prices"
                                                           id="customPrice" value="1">
                                                    <label class="form-check-label" for="customPrice">Custom
                                                        Prices</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="has_custom_prices"
                                                           id="defaultPrice" value="0">
                                                    <label class="form-check-label" for="defaultPrice">Default
                                                        Price</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Custom Price Section -->
                                <div class="col-md-12" id="custom-price-section" style="display: none;">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="invoice-repeater">
                                                <div data-repeater-list="custom_prices">
                                                    <div data-repeater-item>
                                                        <div class="row d-flex align-items-end">
                                                            <div class="col-md-4">
                                                                <div class="mb-1">
                                                                    <label class="form-label">Quantity</label>
                                                                    <input type="number" name="quantity"
                                                                           class="form-control"
                                                                           placeholder="Add Quantity"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="mb-1">
                                                                    <label class="form-label">Price</label>
                                                                    <input type="text" name="price" class="form-control"
                                                                           placeholder="Add Price"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="mb-1">
                                                                    <button type="button"
                                                                            class="btn btn-outline-danger text-nowrap px-1"
                                                                            data-repeater-delete>
                                                                        <i data-feather="x" class="me-25"></i>
                                                                        <span>Delete</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr/>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-icon btn-primary"
                                                                data-repeater-create>
                                                            <i data-feather="plus" class="me-25"></i>
                                                            <span>Add New</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Default Price Section -->
                                <div class="col-md-12" id="default-price-section" style="display: none;">
                                    <div class="mb-1">
                                        <label class="form-label" for="base_price">Original Price</label>
                                        <input type="text" id="base_price" name="base_price" class="form-control"
                                               placeholder="Original Price"/>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-1">
                                        <label class="form-label">Product Specs</label>
                                        <div class="row">
                                            <div class="col-md-12 col-12">
                                                <label class="form-label" for="product-specs-name">Name</label>
                                                <input type="text" id="product-specs-name" name="base_price"
                                                       class="form-control" placeholder="Name"/>
                                            </div>
                                            <div class="col-md-12" id="custom-price-section">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="invoice-repeater">
                                                            <div data-repeater-list="custom_prices">
                                                                <div data-repeater-item>
                                                                    <div class="row d-flex align-items-end">
                                                                        <div class="col-md-6">
                                                                            <div class="mb-1">
                                                                                <label class="form-label">Value</label>
                                                                                <input type="number" name="value"
                                                                                       class="form-control"
                                                                                       placeholder="Add Value"/>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="mb-1">
                                                                                <label class="form-label">Price</label>
                                                                                <input type="text" name="price"
                                                                                       class="form-control"
                                                                                       placeholder="Add Price"/>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <div class="mb-1">
                                                                                <label class="form-label"
                                                                                       for="product-image">Image</label>
                                                                                <input type="file" name="image"
                                                                                       id="product-image"
                                                                                       class="form-control" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="mb-1">
                                                                                <button type="button"
                                                                                        class="btn btn-outline-danger text-nowrap px-1"
                                                                                        data-repeater-delete>
                                                                                    <i data-feather="x"
                                                                                       class="me-25"></i>
                                                                                    <span>Delete</span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <hr/>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <button type="button"
                                                                            class="btn btn-icon btn-primary"
                                                                            data-repeater-create>
                                                                        <i data-feather="plus" class="me-25"></i>
                                                                        <span>Add New</span>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                        </div>

                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                        <!-- Free Shipping Toggle -->
                                        <div class="col-md-12 col-12 mb-2">
                                            <div class="mb-1">
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="is_free_shipping" value="0">
                                                    <input type="checkbox" class="form-check-input" id="free-shipping"
                                                           name="is_free_shipping" value="1">
                                                    <label class="form-check-label" for="free-shipping">Product
                                                        available for free shipping</label>
                                                </div>
                                            </div>
                                        </div>
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
    <!-- vendor files -->
    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Toggle price section visibility
            $('input[name="has_custom_prices"]').on('change', function () {
                if ($(this).val() === '1') {
                    $('#custom-price-section').show();
                    $('#default-price-section').hide();
                } else {
                    $('#custom-price-section').hide();
                    $('#default-price-section').show();
                }
            });

            // Init repeater
            $('.invoice-repeater').repeater({
                show: function () {
                    $(this).slideDown();
                    if (feather) feather.replace({ width: 14, height: 14 });
                },
                hide: function (deleteElement) {
                    $(this).slideUp(deleteElement);
                }
            });

            // Init Select2
            $('.select2').select2();

            // Handle subcategory population
            $('.category-select').on('change', function () {
                const categoryId = $(this).val();
                const $subCategorySelect = $('.sub-category-select');
                const subCategoryUrl = $subCategorySelect.data('sub-category-url');

                $.ajax({
                    url: `${subCategoryUrl}?filter[parent_id]=${categoryId}`,
                    method: "GET",
                    success: function (response) {
                        $subCategorySelect.empty().append('<option value="">Select subcategory</option>');
                        $.each(response.data, function (index, subCategory) {
                            $subCategorySelect.append(`<option value="${subCategory.id}">${subCategory.name}</option>`);
                        });
                    },
                    error: function (err) {
                        console.error(err);
                        $subCategorySelect.empty().append('<option value="">Error loading Subcategories</option>');
                    }
                });
            });


            $(document).on('submit','#product-form',function (e){
                e.preventDefault();
                const actionUrl = $(this).attr('action');
                var form = $(this);
                let formData = new FormData(form[0]);
                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success)
                        {
                            sessionStorage.setItem('product_added', 'true');
                            window.location.href = '/products';
                        }
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors;

                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                Toastify({
                                    text: errors[key][0],
                                    duration: 4000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#EA5455", // red for errors
                                    close: true
                                }).showToast();
                            }
                        }
                    }

                });
            });
        });
    </script>


@endsection

