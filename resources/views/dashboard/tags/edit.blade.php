@extends('layouts/contentLayoutMaster')

@section('title', 'Products')

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
                        <h4 class="card-title">Edit Product</h4>
                    </div>
                    <div class="card-body">
                        <form id="product-form" class="form" action="{{ route('products.update',$model->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method("PUT")
                            <div class="row">
                                <!-- Product Name EN/AR -->
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="product-name-en">Product Name (EN)</label>
                                        <input type="text" id="product-name-en" class="form-control" name="name[en]" value="{{ $model->getTranslation('name','en') }}"  placeholder="Product Name (EN)"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="product-name-ar">Product Name (AR)</label>
                                        <input type="text" id="product-name-ar" class="form-control" name="name[ar]" value="{{ $model->getTranslation('name','ar') }}" placeholder="Product Name (AR)"/>
                                    </div>
                                </div>

                                <!-- Description EN/AR -->
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="description-en">Product Description (EN)</label>
                                        <textarea name="description[en]"  id="description-en" class="form-control" placeholder="Product Description (EN)">{{ $model->getTranslation('name','en') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="description-ar">Product Description (AR)</label>
                                        <textarea name="description[ar]"  id="description-ar" class="form-control" placeholder="Product Description (AR)">{{ $model->getTranslation('name','ar') }}</textarea>
                                    </div>
                                </div>

                                <!-- Main Image -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="product-image-main">Product Image (main)*</label>
                                        <input type="file" name="image" id="product-image-main" class="form-control">
                                    </div>
                                </div>

                                <!-- Multiple Images -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="product-images">Product Images</label>
                                        <input type="file" name="images[]" class="form-control" multiple>
                                    </div>
                                </div>

                                <!-- Category & Subcategory -->
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="category">Category</label>
                                        <select name="category_id" id="category" class="form-control category-select">
                                            <option value="">Select category</option>
                                            @foreach($associatedData['categories'] as $category)
                                                <option value="{{ $category->id }}" @selected($category->id == $model->category?->id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label" for="sub-category">Subcategory</label>
                                        <select name="sub_category_id" id="sub-category" class="form-control sub-category-select" data-sub-category-url="{{ route('sub-categories') }}">
                                            <option value="">Select subcategory</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Tags -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label" for="tags">Tags</label>
                                        <select name="tags[]" id="tags" class="select2 form-select" multiple>
                                            @foreach($associatedData['tags'] as $tag)
                                                <option value="{{ $tag->id }}" @if(in_array($tag->id, $model->tags->pluck('id')->toArray())) selected @endif >{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Price Option Toggle -->
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label">Quantity & Price Options</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="has_custom_prices" id="customPrice" value="1">
                                                    <label class="form-check-label" for="customPrice">Custom Prices</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="has_custom_prices" id="defaultPrice" value="0">
                                                    <label class="form-check-label" for="defaultPrice">Default Price</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Custom Prices -->
                                <div class="col-md-12" id="custom-price-section" style="display: none;">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="invoice-repeater">
                                                <div data-repeater-list="prices">
                                                    <div data-repeater-item>
                                                        <div class="row d-flex align-items-end">
                                                            @foreach ($model->prices as $index => $price)
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <label class="form-label">Quantity</label>
                                                                        <input type="number" name="prices[{{ $index }}][quantity]" value="{{ $price->quantity }}" class="form-control" placeholder="Add Quantity"/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="mb-1">
                                                                        <label class="form-label">Price</label>
                                                                        <input type="text" name="prices[{{ $index }}][price]" value="{{ $price->price }}" class="form-control" placeholder="Add Price"/>
                                                                    </div>
                                                                </div>
                                                            @endforeach

                                                            <div class="col-md-4">
                                                                <div class="mb-1">
                                                                    <button type="button" class="btn btn-outline-danger text-nowrap px-1" data-repeater-delete>
                                                                        <i data-feather="x" class="me-25"></i> <span>Delete</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr/>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-icon btn-primary" data-repeater-create>
                                                            <i data-feather="plus" class="me-25"></i> <span>Add New</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Default Price -->
                                <div class="col-md-12" id="default-price-section" style="display: none;">
                                    <div class="mb-1">
                                        <label class="form-label" for="base_price">Original Price</label>
                                        <input type="text" id="base_price" name="base_price" value="{{ $model->base_price }}" class="form-control" placeholder="Original Price"/>
                                    </div>
                                </div>

                                <!-- Specifications -->
                                <div class="col-12">
                                    <div class="mb-1">
                                        <label class="form-label">Product Specs</label>
                                        <div class="card">
                                            <div class="card-body">
                                                <!-- Outer Repeater for Specifications -->
                                                <div class="outer-repeater">
                                                    @foreach($model->specifications as $specification)

                                                    <div data-repeater-list="specifications">
                                                        <div data-repeater-item>
                                                            <div class="row">
                                                                <!-- Specification Name (English) -->
                                                                <div class="col-md-6">
                                                                    <div class="mb-1">
                                                                        <label class="form-label">Specification Name (EN)</label>
                                                                        <input type="text" name="specifications[{{$loop->index}}][name_en]" value="{{ $specification->getTranslation('name','ar') }}"  class="form-control" placeholder="Specification Name (EN)"/>
                                                                    </div>
                                                                </div>

                                                                <!-- Specification Name (Arabic) -->
                                                                <div class="col-md-6">
                                                                    <div class="mb-1">
                                                                        <label class="form-label">Specification Name (AR)</label>
                                                                        <input type="text" name="specifications[{{$loop->index}}][name_ar]" value="{{ $specification->getTranslation('name','ar') }}" class="form-control" placeholder="Specification Name (AR)"/>
                                                                    </div>
                                                                </div>

                                                                <!-- Inner Repeater for Specification Options -->
                                                                <div class="inner-repeater">
                                                                    @foreach($specification->options as $option)
                                                                    <div data-repeater-list="specification_options">
                                                                        <div data-repeater-item>
                                                                            <div class="row d-flex align-items-end mt-2">
                                                                                <!-- Option Name (English) -->
                                                                                <div class="col-md-3">
                                                                                    <label>Option (EN)</label>
                                                                                    <input type="text" name="specifications[{{$loop->parent->index}}]specification_options[{{$loop->index}}][value_en]"
                                                                                           value="{{ $option->getTranslation('value','ar') }}"
                                                                                           class="form-control" placeholder="Option (EN)"/>
                                                                                </div>

                                                                                <!-- Option Name (Arabic) -->
                                                                                <div class="col-md-3">
                                                                                    <label>Option (AR)</label>
                                                                                    <input type="text" name="specifications[{{$loop->parent->index}}]specification_options[{{$loop->index}}][value_ar]"
                                                                                           value="{{ $option->getTranslation('value','ar') }}"
                                                                                           class="form-control" placeholder="Option (AR)"/>
                                                                                </div>

                                                                                <!-- Option Price -->
                                                                                <div class="col-md-2">
                                                                                    <label>Price</label>
                                                                                    <input type="text" name="specifications[{{$loop->parent->index}}]specification_options[{{$loop->index}}][price]" value="{{ $option->price }}" class="form-control" placeholder="Price"/>
                                                                                </div>

                                                                                <!-- Option Image -->
                                                                                <div class="col-md-2">
                                                                                    <label>Image</label>
                                                                                    <input type="file" name="image" class="form-control"/>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach

                                                                    <!-- Add Option Button -->
                                                                    <div class="row">
                                                                        <div class="col-12">
                                                                            <button type="button" class="btn btn-icon btn-primary mt-2" data-repeater-create>
                                                                                <i data-feather="plus" class="me-25"></i> <span>Add Option</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- End of Inner Repeater -->

                                                                <!-- Delete Specification Button -->
                                                                <div class="col-12 text-end mt-1 mb-2">
                                                                    <button type="button" class="btn btn-outline-danger" data-repeater-delete>
                                                                        <i data-feather="x" class="me-25"></i> Delete Specification
                                                                    </button>
                                                                </div>
                                                                <hr/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    <!-- Add Specification Button -->
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <button type="button" class="btn btn-icon btn-primary w-100" data-repeater-create>
                                                                <i data-feather="plus" class="me-25"></i> <span>Add Specification</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Free Shipping -->
                                    <div class="col-md-12 col-12 mb-2">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="is_free_shipping" value="0">
                                            <input type="checkbox" class="form-check-input" id="free-shipping" name="is_free_shipping" value="1">
                                            <label class="form-check-label" for="free-shipping">Product available for free shipping</label>
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
