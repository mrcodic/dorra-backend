@php
    $prefix = $prefix ?? 'bundle';

    $productWithCategories =
        $associatedData['product_with_categories']
        ?? collect();

    $productWithoutCategories =
        $associatedData['product_without_categories']
        ?? collect();
@endphp

<div class="bundle-form-fields" data-prefix="{{ $prefix }}">

    {{-- ============================================================= --}}
    {{-- GENERAL                                                       --}}
    {{-- ============================================================= --}}
    <h6 class="mb-1">General Information</h6>

    <div class="row mb-2">
        <div class="col-md-6">
            <label class="label-text mb-1" for="{{ $prefix }}BundleNameEn">
                Bundle Name En
            </label>
            <input
                type="text"
                name="name[en]"
                id="{{ $prefix }}BundleNameEn"
                class="form-control"
                placeholder="Enter bundle name en"
                required
            >
        </div>

        <div class="col-md-6">
            <label class="label-text mb-1" for="{{ $prefix }}BundleNameAr">
                Bundle Name Ar
            </label>
            <input
                type="text"
                name="name[ar]"
                id="{{ $prefix }}BundleNameAr"
                class="form-control"
                placeholder="Enter bundle name ar"
            >
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-6">
            <label class="label-text mb-1" for="{{ $prefix }}BundleDescriptionEn">
                Description En
            </label>
            <textarea
                name="description[en]"
                id="{{ $prefix }}BundleDescriptionEn"
                class="form-control"
                rows="2"
            ></textarea>
        </div>

        <div class="col-md-6">
            <label class="label-text mb-1" for="{{ $prefix }}BundleDescriptionAr">
                Description Ar
            </label>
            <textarea
                name="description[ar]"
                id="{{ $prefix }}BundleDescriptionAr"
                class="form-control"
                rows="2"
            ></textarea>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-4">
            <label class="label-text mb-1" for="{{ $prefix }}BundleStatus">
                Status
            </label>
            <select
                name="status"
                id="{{ $prefix }}BundleStatus"
                class="form-select bundle-select2"
            >
                <option value="active">Active</option>
                <option value="draft">Draft</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="label-text mb-1" for="{{ $prefix }}BundleStartAt">
                Start Date
            </label>
            <input
                type="date"
                name="start_at"
                id="{{ $prefix }}BundleStartAt"
                class="form-control"
            >
        </div>

        <div class="col-md-4">
            <label class="label-text mb-1" for="{{ $prefix }}BundleEndAt">
                End Date
            </label>
            <input
                type="date"
                name="end_at"
                id="{{ $prefix }}BundleEndAt"
                class="form-control"
            >
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="form-check">
                <input
                    type="hidden"
                    name="display_bundle_on_visit"
                    value="0"
                >

                <input
                    class="form-check-input bundle-display-on-visit"
                    type="checkbox"
                    name="display_bundle_on_visit"
                    value="1"
                    id="{{ $prefix }}DisplayBundleOnVisit"
                >

                <label
                    class="form-check-label"
                    for="{{ $prefix }}DisplayBundleOnVisit"
                >
                    Display this bundle popup when customer visits website
                </label>
            </div>

            <small class="text-muted d-block mt-50">
                Only one bundle can be displayed on website visit. The customer still chooses and configures it manually.
            </small>

            <small class="text-danger d-block mt-50 d-none bundle-display-on-visit-warning">
                Another bundle is already selected for website visit popup.
            </small>
        </div>
    </div>

    <hr>

    {{-- ============================================================= --}}
    {{-- TRIGGER                                                       --}}
    {{-- ============================================================= --}}
    <h6 class="mb-1">1. Customer Must Buy</h6>

    <div class="form-group mb-2">
        <label class="label-text mb-1 d-block">Product Type</label>

        <div class="form-check form-check-inline">
            <input
                class="form-check-input bundle-trigger-scope"
                type="radio"
                name="trigger[scope]"
                id="{{ $prefix }}TriggerWithCategory"
                value="with_category"
                checked
            >
            <label
                class="form-check-label"
                for="{{ $prefix }}TriggerWithCategory"
            >
                Products With Categories
            </label>
        </div>

        <div class="form-check form-check-inline">
            <input
                class="form-check-input bundle-trigger-scope"
                type="radio"
                name="trigger[scope]"
                id="{{ $prefix }}TriggerWithoutCategory"
                value="without_category"
            >
            <label
                class="form-check-label"
                for="{{ $prefix }}TriggerWithoutCategory"
            >
                Products Without Categories
            </label>
        </div>
    </div>

    <div class="bundle-trigger-with-category">
        <div class="form-group mb-2">
            {{--
                Intentionally follows your existing Dorra labels:
                actual value = Category(is_has_category=1), admin label = Products.
            --}}
            <label class="label-text mb-1">Products</label>
            <select
                name="trigger[parent_category_id]"
                class="form-select bundle-select2 bundle-trigger-parent"
            >
                <option value="">Select Product</option>

                @foreach($productWithCategories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-2">
            {{-- actual value = Product child, admin label = Categories. --}}
            <label class="label-text mb-1">Categories</label>
            <select
                name="trigger[item_id]"
                class="form-select bundle-select2 bundle-trigger-child"
            >
                <option value="">Select Category</option>
            </select>
        </div>
    </div>

    <div class="bundle-trigger-without-category d-none">
        <div class="form-group mb-2">
            <label class="label-text mb-1">Products</label>
            <select
                name="trigger[item_id]"
                class="form-select bundle-select2 bundle-trigger-direct"
                disabled
            >
                <option value="">Select Product</option>
                @foreach($productWithoutCategories as $product)
                    <option value="{{ $product->id }}">
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mb-2 bundle-trigger-price-wrapper d-none">
        <div class="col-md-6">
            <label class="label-text mb-1">
                Trigger Quantity  Option
            </label>

            <select
                name="trigger[price_id]"
                class="form-select bundle-select2 bundle-trigger-price-option"
                disabled
            >
                <option value="">Select quantity</option>
            </select>

            <input
                type="hidden"
                name="trigger[quantity_rule]"
                class="bundle-trigger-price-quantity-rule"
                value="minimum"
                disabled
            >

            <input
                type="hidden"
                name="trigger[quantity]"
                class="bundle-trigger-price-quantity"
                value=""
                disabled
            >
        </div>
    </div>

    <div class="row mb-2 bundle-trigger-manual-quantity-wrapper">
        <div class="col-md-6">
            <label class="label-text mb-1">Quantity Rule</label>
            <select
                name="trigger[quantity_rule]"
                class="form-select bundle-select2 bundle-trigger-quantity-rule"
            >
                <option value="any">Any Quantity</option>
                <option value="minimum">Minimum Quantity</option>
            </select>
        </div>

        <div class="col-md-6 bundle-trigger-quantity-wrapper d-none">
            <label class="label-text mb-1">Required Quantity</label>
            <input
                type="number"
                min="1"
                step="1"
                name="trigger[quantity]"
                class="form-control bundle-trigger-quantity"
                value="1"
            >
        </div>
    </div>

    <div class="bundle-trigger-flow alert alert-light border mb-3 d-none"></div>

    <hr>

    {{-- ============================================================= --}}
    {{-- REWARDS                                                       --}}
    {{-- ============================================================= --}}
    <div class="d-flex align-items-center justify-content-between mb-1">
        <h6 class="mb-0">2. Customer Gets</h6>

        <button
            type="button"
            class="btn btn-sm btn-outline-primary bundle-add-reward"
        >
            + Add Reward
        </button>
    </div>

    <div class="bundle-rewards-container"></div>

    <template class="bundle-reward-template">
        <div class="card border bundle-reward-card mb-2" data-reward-index="__INDEX__">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <strong>Reward #<span class="bundle-reward-number">__NUMBER__</span></strong>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger bundle-remove-reward"
                    >
                        Remove
                    </button>
                </div>

                <div class="form-group mb-2">
                    <label class="label-text mb-1 d-block">Product Type</label>

                    <div class="form-check form-check-inline">
                        <input
                            class="form-check-input bundle-reward-scope"
                            type="radio"
                            name="rewards[__INDEX__][scope]"
                            id="{{ $prefix }}Reward__INDEX__WithCategory"
                            value="with_category"
                            checked
                        >
                        <label
                            class="form-check-label"
                            for="{{ $prefix }}Reward__INDEX__WithCategory"
                        >
                            Products With Categories
                        </label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input
                            class="form-check-input bundle-reward-scope"
                            type="radio"
                            name="rewards[__INDEX__][scope]"
                            id="{{ $prefix }}Reward__INDEX__WithoutCategory"
                            value="without_category"
                        >
                        <label
                            class="form-check-label"
                            for="{{ $prefix }}Reward__INDEX__WithoutCategory"
                        >
                            Products Without Categories
                        </label>
                    </div>
                </div>

                <div class="bundle-reward-with-category">
                    <div class="form-group mb-2">
                        {{-- Same intentional opposite label as Offers --}}
                        <label class="label-text mb-1">Products</label>
                        <select
                            name="rewards[__INDEX__][parent_category_id]"
                            class="form-select bundle-select2 bundle-reward-parent"
                        >
                            <option value="">Select Product</option>
                            @foreach($productWithCategories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        {{-- actual child Product --}}
                        <label class="label-text mb-1">Categories</label>
                        <select
                            name="rewards[__INDEX__][item_id]"
                            class="form-select bundle-select2 bundle-reward-child"
                        >
                            <option value="">Select Category</option>
                        </select>
                    </div>
                </div>

                <div class="bundle-reward-without-category d-none">
                    <div class="form-group mb-2">
                        <label class="label-text mb-1">Products</label>
                        <select
                            name="rewards[__INDEX__][item_id]"
                            class="form-select bundle-select2 bundle-reward-direct"
                            disabled
                        >
                            <option value="">Select Product</option>
                            @foreach($productWithoutCategories as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-2 bundle-reward-manual-quantity-wrapper">
                        <label class="label-text mb-1">Reward Quantity</label>
                        <input
                            type="number"
                            min="1"
                            step="1"
                            name="rewards[__INDEX__][quantity]"
                            class="form-control bundle-reward-quantity"
                            value="1"
                        >
                    </div>

                    <div class="col-md-4 mb-2 bundle-reward-price-wrapper d-none">
                        <label class="label-text mb-1">Reward Quantity Option</label>
                        <select
                            name="rewards[__INDEX__][price_id]"
                            class="form-select bundle-select2 bundle-reward-price-option"
                            disabled
                        >
                            <option value="">Select quantity</option>
                        </select>

                        <input
                            type="hidden"
                            name="rewards[__INDEX__][quantity]"
                            class="bundle-reward-price-quantity"
                            value=""
                            disabled
                        >
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="label-text mb-1">Reward Type</label>
                        <select
                            name="rewards[__INDEX__][discount_type]"
                            class="form-select bundle-select2 bundle-reward-discount-type"
                        >
                            <option value="free">Free</option>
                            <option value="percentage">Percentage Discount</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-2 bundle-reward-discount-wrapper d-none">
                        <label class="label-text mb-1">Discount (%)</label>
                        <input
                            type="number"
                            min="0.01"
                            max="100"
                            step="0.01"
                            name="rewards[__INDEX__][discount_value]"
                            class="form-control bundle-reward-discount-value"
                        >
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label class="label-text mb-1">
                        Maximum Discount Amount
                        <span class="text-muted">(optional)</span>
                    </label>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        name="rewards[__INDEX__][max_discount_amount]"
                        class="form-control"
                        placeholder="Leave empty for no maximum"
                    >
                </div>

                <div class="bundle-reward-flow alert alert-light border mb-0 d-none"></div>
            </div>
        </div>
    </template>

    <hr>

    {{-- ============================================================= --}}
    {{-- BEHAVIOR                                                      --}}
    {{-- ============================================================= --}}
{{--    <h6 class="mb-1">3. Bundle Behavior</h6>--}}

{{--    <div class="row mb-2">--}}
{{--        <div class="col-md-6">--}}
{{--            <label class="label-text mb-1">Bundle Usage</label>--}}
{{--            <select--}}
{{--                name="repeat_type"--}}
{{--                class="form-select bundle-select2"--}}
{{--            >--}}
{{--                <option value="once">Apply Once</option>--}}
{{--                <option value="repeat">Repeat Based On Quantity</option>--}}
{{--            </select>--}}
{{--        </div>--}}
{{--    </div>--}}

</div>
