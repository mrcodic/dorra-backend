<div class="modal modal-slide-in new-user-modal fade" id="addOfferModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form
                id="addOfferForm"
                method="post"
                enctype="multipart/form-data"
                action="{{ route('offers.store') }}"
            >
                @csrf

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    ×
                </button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Add New Offer</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    {{-- Offer names --}}
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="offer_name_en" class="label-text mb-1">
                                Offer Name En
                            </label>

                            <input
                                type="text"
                                name="name[en]"
                                id="offer_name_en"
                                class="form-control"
                                placeholder="Enter offer’s name en"
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="offer_name_ar" class="label-text mb-1">
                                Offer Name Ar
                            </label>

                            <input
                                type="text"
                                name="name[ar]"
                                id="offer_name_ar"
                                class="form-control"
                                placeholder="Enter offer’s name ar"
                            >
                        </div>
                    </div>


                    {{-- Offer value --}}
                    <div class="form-group mb-2">
                        <label for="createDiscountValue" class="label-text mb-1">
                            Offer Value (%)
                        </label>

                        <input
                            type="number"
                            name="value"
                            id="createDiscountValue"
                            class="form-control"
                            placeholder="Enter offer’s value"
                            min="1"
                            max="100"
                            step="1"
                            required
                        >
                    </div>


                    {{--
                        Current Offer code treats type = 2 as Products.
                        Both radio choices below are product flows, so type is hidden.
                    --}}
                    <input type="hidden" name="type" value="2">


                    {{-- Product scope --}}
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">
                            Product Type
                        </label>

                        <div class="form-check form-check-inline">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="product_scope"
                                id="addProductsWithCategory"
                                value="with_category"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="addProductsWithCategory"
                            >
                                Products With Categories
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="product_scope"
                                id="addProductsWithoutCategory"
                                value="without_category"
                            >

                            <label
                                class="form-check-label"
                                for="addProductsWithoutCategory"
                            >
                                Products Without Categories
                            </label>
                        </div>
                    </div>


                    {{-- ====================================================== --}}
                    {{-- PRODUCTS WITH CATEGORIES                               --}}
                    {{-- Parent Category -> AJAX filtered child Products        --}}
                    {{-- ====================================================== --}}
                    <div id="addProductsWithCategoryWrapper">

                        <div class="form-group mb-2">
                            <label for="addCategorySelect" class="label-text mb-1">
                                Products
                            </label>

                            {{--
                                Filter-only field.
                                It intentionally has no name because only the selected
                                products should be submitted to the Offer request.
                            --}}
                            <select
                                id="addCategorySelect"
                                class="form-select select2"
                                multiple
                            >
                                @foreach($associatedData['product_with_categories'] as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group mb-2">
                            <label for="addCategoryProductsSelect" class="label-text mb-1">
                                Categories
                            </label>

                            <select
                                id="addCategoryProductsSelect"
                                name="product_ids[]"
                                class="form-select select2"
                                multiple
                            >
                            </select>
                        </div>

                    </div>


                    {{-- ====================================================== --}}
                    {{-- PRODUCTS WITHOUT CATEGORIES                            --}}
                    {{-- ====================================================== --}}
                    <div id="addProductsWithoutCategoryWrapper" class="d-none">

                        <div class="form-group mb-2">
                            <label
                                for="addProductsWithoutCategorySelect"
                                class="label-text mb-1"
                            >
                                Products
                            </label>

                            <select
                                id="addProductsWithoutCategorySelect"
                                name="product_ids[]"
                                class="form-select select2"
                                multiple
                                disabled
                            >
                                @foreach($associatedData['product_without_categories'] as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>


                    {{-- Dates --}}
                    <div class="row">
                        <div class="col mb-2">
                            <label for="addOfferStartDate" class="form-label">
                                Start Date
                            </label>

                            <input
                                id="addOfferStartDate"
                                name="start_at"
                                type="date"
                                class="form-control"
                            >
                        </div>

                        <div class="col mb-2">
                            <label for="addOfferEndDate" class="form-label">
                                End Date
                            </label>

                            <input
                                id="addOfferEndDate"
                                name="end_at"
                                type="date"
                                class="form-control"
                            >
                        </div>
                    </div>

                </div>


                <div class="modal-footer border-top-0 d-flex justify-content-end">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary fs-5 saveChangesButton"
                        id="addOfferSaveButton"
                    >
                        <span>Add</span>

                        <span
                            class="spinner-border spinner-border-sm d-none saveLoader"
                            role="status"
                            aria-hidden="true"
                        ></span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


<script>
    $(function () {
        const $modal = $('#addOfferModal');
        const $categorySelect = $('#addCategorySelect');
        const $categoryProductsSelect = $('#addCategoryProductsSelect');
        const $withoutCategorySelect = $('#addProductsWithoutCategorySelect');

        /*
         * Clamp discount between 1 and 100.
         */
        $('#createDiscountValue').on('input', function () {
            let value = parseInt($(this).val(), 10);

            if (Number.isNaN(value)) {
                return;
            }

            value = Math.max(1, Math.min(100, value));
            $(this).val(value);
        });


        /*
         * Initialize Select2 inside the modal.
         */
        $modal.on('shown.bs.modal', function () {
            $modal.find('.select2').each(function () {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }

                $select.select2({
                    dropdownParent: $modal,
                    width: '100%'
                });
            });
        });


        /*
         * Same dependent-select pattern used in the Templates page:
         *
         * Parent categories
         *      ↓
         * POST products.categories
         *      ↓
         * Filtered products
         */
        $categorySelect.on('change', function () {
            const selectedCategoryIds = $(this).val() || [];
            const previousSelectedProducts = (
                $categoryProductsSelect.val() || []
            ).map(String);

            if (!selectedCategoryIds.length) {
                $categoryProductsSelect
                    .empty()
                    .trigger('change');

                return;
            }

            $categoryProductsSelect
                .prop('disabled', true);

            $.ajax({
                url: "{{ route('products.categories') }}",
                type: 'POST',

                data: {
                    _token: "{{ csrf_token() }}",
                    category_ids: selectedCategoryIds
                },

                success: function (response) {
                    const products = response.data || [];

                    $categoryProductsSelect.empty();

                    products.forEach(function (product) {
                        const id = String(product.id);

                        /*
                         * Keep an already-selected product selected only if
                         * it still belongs to the currently selected parents.
                         *
                         * New returned products are NOT auto-selected.
                         */
                        const isSelected =
                            previousSelectedProducts.includes(id);

                        const option = new Option(
                            product.name,
                            product.id,
                            isSelected,
                            isSelected
                        );

                        $categoryProductsSelect.append(option);
                    });

                    $categoryProductsSelect
                        .prop('disabled', false)
                        .trigger('change');
                },

                error: function (xhr) {
                    console.error(
                        'Error loading products:',
                        xhr.responseText
                    );

                    $categoryProductsSelect
                        .empty()
                        .prop('disabled', false)
                        .trigger('change');
                }
            });
        });


        /*
         * Toggle:
         * - Products With Categories
         * - Products Without Categories
         *
         * Only the visible product select is enabled, so only one
         * product_ids[] collection is submitted.
         */
        $('input[name="product_scope"]').on('change', function () {
            const scope =
                $('input[name="product_scope"]:checked').val();

            if (scope === 'with_category') {
                $('#addProductsWithCategoryWrapper')
                    .removeClass('d-none');

                $('#addProductsWithoutCategoryWrapper')
                    .addClass('d-none');

                $categorySelect.prop('disabled', false);

                $categoryProductsSelect
                    .prop('disabled', false);

                $withoutCategorySelect
                    .prop('disabled', true)
                    .val(null)
                    .trigger('change');

                return;
            }

            $('#addProductsWithCategoryWrapper')
                .addClass('d-none');

            $('#addProductsWithoutCategoryWrapper')
                .removeClass('d-none');

            /*
             * Clear the filter branch so stale product IDs are never submitted.
             */
            $categorySelect
                .val(null)
                .trigger('change');

            $categorySelect.prop('disabled', true);

            $categoryProductsSelect
                .empty()
                .prop('disabled', true)
                .trigger('change');

            $withoutCategorySelect
                .prop('disabled', false);
        });


        /*
         * Initial state.
         */
        $('input[name="product_scope"]:checked')
            .trigger('change');
    });
</script>
