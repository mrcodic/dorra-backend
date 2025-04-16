@extends('layouts/contentLayoutMaster')

@section('title', 'Products')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<section id="multiple-column-form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add New Product</h4>
                </div>
                <div class="card-body">
                    <form class="form" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            {{-- Product Name --}}
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label" for="product-name">Product Name</label>
                                    <input type="text" id="product-name" class="form-control @error('name') is-invalid @enderror"
                                           name="name" placeholder="Product Name" value="{{ old('name') }}" />
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Product Description --}}
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label" for="product-description">Product Description</label>
                                    <textarea name="description" id="product-description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Product Description">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Product Main Image --}}
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label" for="product-image-main">Product Image (main)*</label>
                                    <input type="file" name="image" id="product-image-main" class="form-control @error('image') is-invalid @enderror">
                                    @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Product Images --}}
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label" for="product-image">Product Images</label>
                                    <input type="file" name="images[]" class="form-control" multiple>
                                </div>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label" for="category">Category</label>
                                    <select name="category_id" id="category"
                                            class="form-control category-select @error('category_id') is-invalid @enderror">
                                        <option value="">Select category</option>
                                        @foreach($associatedData['categories'] as $category)
                                        <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Subcategory --}}
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label" for="sub-category">Subcategory</label>
                                    <select name="sub_category_id" id="sub-category"
                                            class="form-control sub-category-select @error('sub_category_id') is-invalid @enderror"
                                            data-sub-category-url="{{ route('sub-categories') }}">
                                        <option value="">Select subcategory</option>
                                        {{-- Subcategory values will be populated via AJAX --}}
                                    </select>
                                    @error('sub_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Tags --}}
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label" for="select2-multiple">Tags</label>
                                    <select name="tags[]" id="select2-multiple" class="select2 form-select" multiple>
                                        <option value="1" {{ in_array(1, old('tags', [])) ? 'selected' : '' }}>Tag 1</option>
                                        <option value="2" {{ in_array(2, old('tags', [])) ? 'selected' : '' }}>Tag 2</option>
                                        <option value="3" {{ in_array(3, old('tags', [])) ? 'selected' : '' }}>Tag 3</option>
                                    </select>
                                    @error('tags')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Price Options --}}
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Quantity & Price Options</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="has_custom_prices"
                                                       id="customPrice" value="1" {{ old('has_custom_prices') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="customPrice">Custom Prices</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="has_custom_prices"
                                                       id="defaultPrice" value="0" {{ old('has_custom_prices') == '0' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="defaultPrice">Default Price</label>
                                            </div>
                                        </div>
                                    </div>
                                    @error('has_custom_prices')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Default Price Section --}}
                            <div class="col-md-12" id="default-price-section" style="display: none;">
                                <div class="mb-1">
                                    <label class="form-label" for="base_price">Original Price</label>
                                    <input type="text" id="base_price" name="base_price"
                                           class="form-control @error('base_price') is-invalid @enderror"
                                           placeholder="Original Price" value="{{ old('base_price') }}">
                                    @error('base_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Free Shipping --}}
                            <div class="col-md-12 col-12 mb-2">
                                <div class="mb-1">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="free-shipping"
                                               name="is_free_shipping" value="1" {{ old('is_free_shipping') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="free-shipping">Product available for free shipping</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit --}}
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
<script>
    $(document).ready(function () {
        // Toggle price sections
        $('input[name="has_custom_prices"]').on('change', function () {
            if ($(this).val() == 1) {
                $('#custom-price-section').show();
                $('#default-price-section').hide();
            } else {
                $('#custom-price-section').hide();
                $('#default-price-section').show();
            }
        }).trigger('change'); // trigger on load to restore state

        // Select2
        $('.select2').select2();

        // Init repeater
        $('.invoice-repeater').repeater({
            show: function () {
                $(this).slideDown();
                if (feather) feather.replace({ width: 14, height: 14 });
