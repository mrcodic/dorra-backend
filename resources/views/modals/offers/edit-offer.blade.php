@php
    $editProductWithCategories =
        $associatedData['edit_product_with_categories']
        ?? $associatedData['editCategories']
        ?? collect();

    $editProductWithoutCategories =
        $associatedData['edit_product_without_categories']
        ?? $associatedData['editProducts']
        ?? collect();
@endphp

<div class="modal modal-slide-in new-user-modal fade" id="editOfferModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">

            <form
                id="editOfferForm"
                method="post"
                enctype="multipart/form-data"
                action=""
            >
                @csrf
                @method('PUT')

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    ×
                </button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Edit Offer</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="editOfferNameEn" class="label-text mb-1">
                                Offer Name En
                            </label>

                            <input
                                type="text"
                                name="name[en]"
                                id="editOfferNameEn"
                                class="form-control"
                                placeholder="Enter offer’s name en"
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="editOfferNameAr" class="label-text mb-1">
                                Offer Name Ar
                            </label>

                            <input
                                type="text"
                                name="name[ar]"
                                id="editOfferNameAr"
                                class="form-control"
                                placeholder="Enter offer’s name ar"
                            >
                        </div>
                    </div>


                    <div class="form-group mb-2">
                        <label for="editOfferValue" class="label-text mb-1">
                            Offer Value (%)
                        </label>

                        <input
                            type="number"
                            name="value"
                            id="editOfferValue"
                            class="form-control"
                            min="1"
                            max="100"
                            step="1"
                            placeholder="Enter offer’s value"
                        >
                    </div>


                    {{-- Product offer --}}
                    <input type="hidden" name="type" value="2">


                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">
                            Product Type
                        </label>

                        <div class="form-check form-check-inline">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="edit_product_scope"
                                id="editProductsWithCategory"
                                value="with_category"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="editProductsWithCategory"
                            >
                                Products With Categories
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="edit_product_scope"
                                id="editProductsWithoutCategory"
                                value="without_category"
                            >

                            <label
                                class="form-check-label"
                                for="editProductsWithoutCategory"
                            >
                                Products Without Categories
                            </label>
                        </div>
                    </div>


                    {{-- ================================================ --}}
                    {{-- PRODUCTS WITH CATEGORIES                         --}}
                    {{-- ================================================ --}}
                    <div id="editProductsWithCategoryWrapper">

                        <div class="form-group mb-2">
                            <label for="editCategorySelect" class="label-text mb-1">
                                Categories
                            </label>

                            <select
                                id="editCategorySelect"
                                class="form-select select2"
                                multiple
                            >
                                @foreach($editProductWithCategories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        data-product-ids='@json(
                                            $category->relationLoaded("products")
                                                ? $category->products->pluck("id")->map(fn ($id) => (string) $id)->values()
                                                : []
                                        )'
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group mb-2">
                            <label
                                for="editCategoryProductsSelect"
                                class="label-text mb-1"
                            >
                                Products
                            </label>

                            <select
                                id="editCategoryProductsSelect"
                                name="product_ids[]"
                                class="form-select select2"
                                multiple
                            >
                            </select>
                        </div>

                    </div>


                    {{-- ================================================ --}}
                    {{-- PRODUCTS WITHOUT CATEGORIES                      --}}
                    {{-- ================================================ --}}
                    <div
                        id="editProductsWithoutCategoryWrapper"
                        class="d-none"
                    >
                        <div class="form-group mb-2">
                            <label
                                for="editProductsWithoutCategorySelect"
                                class="label-text mb-1"
                            >
                                Products
                            </label>

                            <select
                                id="editProductsWithoutCategorySelect"
                                name="product_ids[]"
                                class="form-select select2"
                                multiple
                                disabled
                            >
                                @foreach($editProductWithoutCategories as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col mb-2">
                            <label for="editStartDate" class="form-label">
                                Start Date
                            </label>

                            <input
                                type="date"
                                name="start_at"
                                id="editStartDate"
                                class="form-control"
                            >
                        </div>

                        <div class="col mb-2">
                            <label for="editEndDate" class="form-label">
                                End Date
                            </label>

                            <input
                                type="date"
                                name="end_at"
                                id="editEndDate"
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
                        id="editOfferSaveButton"
                    >
                        <span>Save Changes</span>

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
        const $modal = $('#editOfferModal');

        const $categorySelect =
            $('#editCategorySelect');

        const $categoryProductsSelect =
            $('#editCategoryProductsSelect');

        const $withoutCategorySelect =
            $('#editProductsWithoutCategorySelect');


        /*
         * -------------------------
         * Helpers
         * -------------------------
         */
        function toEditDate(value) {
            if (!value) {
                return '';
            }

            value = String(value).trim();

            if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return value;
            }

            const slashMatch =
                value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

            if (slashMatch) {
                return `${slashMatch[3]}-${slashMatch[2]}-${slashMatch[1]}`;
            }

            if (value.includes('T')) {
                return value.split('T')[0];
            }

            if (value.includes(' ')) {
                return value.split(' ')[0];
            }

            return '';
        }


        function parseEditItems(raw) {
            if (raw == null) {
                return [];
            }

            if (typeof raw === 'string') {
                try {
                    raw = JSON.parse(raw);
                } catch (_) {
                    return [];
                }
            }

            return Array.isArray(raw) ? raw : [];
        }


        function editItemId(item) {
            if (item && typeof item === 'object') {
                return String(
                    item.id ??
                    item.value ??
                    ''
                );
            }

            return String(item ?? '');
        }


        function editItemName(item) {
            if (!item || typeof item !== 'object') {
                return `#${editItemId(item)}`;
            }

            let name =
                item.name ??
                item.title ??
                item.label ??
                null;

            if (name && typeof name === 'object') {
                name =
                    name.en ??
                    name.ar ??
                    Object.values(name)[0] ??
                    null;
            }

            return name
                ? String(name)
                : `#${editItemId(item)}`;
        }


        function parseOptionProductIds(option) {
            const raw =
                $(option).attr('data-product-ids');

            if (!raw) {
                return [];
            }

            try {
                const ids = JSON.parse(raw);

                return Array.isArray(ids)
                    ? ids.map(String)
                    : [];
            } catch (_) {
                return [];
            }
        }


        /*
         * Find parent categories from the currently-selected child products.
         *
         * This is the important fix:
         * the Edit modal no longer requires row.products to contain category_id.
         */
        function findParentCategoriesForProducts(productIds) {
            const selectedIds =
                new Set((productIds || []).map(String));

            const parentIds = [];

            $categorySelect
                .find('option')
                .each(function () {
                    const childIds =
                        parseOptionProductIds(this);

                    const containsSelectedProduct =
                        childIds.some(id => selectedIds.has(String(id)));

                    if (containsSelectedProduct) {
                        parentIds.push(
                            String(this.value)
                        );
                    }
                });

            return [...new Set(parentIds)];
        }


        /*
         * Fallback if API/resource already provides category_id.
         */
        function getParentIdsFromProducts(items) {
            return [
                ...new Set(
                    (items || [])
                        .map(function (item) {
                            if (!item || typeof item !== 'object') {
                                return null;
                            }

                            return (
                                item.category_id ??
                                item.parent_id ??
                                item.product_category_id ??
                                item.category?.id ??
                                item.parent?.id ??
                                null
                            );
                        })
                        .filter(id => id !== null && id !== undefined)
                        .map(String)
                )
            ];
        }


        /*
         * Parent categories -> child products
         * Same AJAX endpoint used by Templates.
         */
        function loadEditProductsByCategories(
            categoryIds,
            selectedProductIds = [],
            selectedItems = []
        ) {
            categoryIds =
                (categoryIds || []).map(String);

            selectedProductIds =
                (selectedProductIds || []).map(String);


            if (!categoryIds.length) {
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
                    category_ids: categoryIds
                },

                success: function (response) {
                    const returnedItems =
                        response.data || [];

                    const returnedIds =
                        new Set();

                    $categoryProductsSelect.empty();


                    returnedItems.forEach(function (product) {
                        const id =
                            String(product.id);

                        returnedIds.add(id);

                        const selected =
                            selectedProductIds.includes(id);

                        const option =
                            new Option(
                                product.name,
                                product.id,
                                selected,
                                selected
                            );

                        $categoryProductsSelect
                            .append(option);
                    });


                    /*
                     * Safety fallback:
                     * never lose a currently-selected resource if the endpoint
                     * does not return it for any reason.
                     */
                    selectedItems.forEach(function (item) {
                        const id =
                            editItemId(item);

                        if (
                            selectedProductIds.includes(id) &&
                            !returnedIds.has(id)
                        ) {
                            $categoryProductsSelect.append(
                                new Option(
                                    editItemName(item),
                                    id,
                                    true,
                                    true
                                )
                            );
                        }
                    });


                    $categoryProductsSelect
                        .prop('disabled', false)
                        .trigger('change');
                },

                error: function (xhr) {
                    console.error(
                        'Error loading edit products:',
                        xhr.responseText
                    );

                    $categoryProductsSelect.empty();

                    /*
                     * Keep current selections visible on request failure.
                     */
                    selectedItems.forEach(function (item) {
                        $categoryProductsSelect.append(
                            new Option(
                                editItemName(item),
                                editItemId(item),
                                true,
                                true
                            )
                        );
                    });

                    $categoryProductsSelect
                        .prop('disabled', false)
                        .trigger('change');
                }
            });
        }


        /*
         * Select2
         */
        $modal.on('shown.bs.modal', function () {
            $modal
                .find('.select2')
                .each(function () {
                    const $select =
                        $(this);

                    if (
                        !$select.hasClass(
                            'select2-hidden-accessible'
                        )
                    ) {
                        $select.select2({
                            dropdownParent: $modal,
                            width: '100%'
                        });
                    }
                });
        });


        /*
         * User manually changes parent category.
         */
        $categorySelect.on('change', function () {
            const categoryIds =
                $(this).val() || [];

            const oldSelectedProducts =
                $categoryProductsSelect.val() || [];

            loadEditProductsByCategories(
                categoryIds,
                oldSelectedProducts
            );
        });


        /*
         * Switch between:
         * - products with categories
         * - products without categories
         */
        $('input[name="edit_product_scope"]').on(
            'change',
            function () {
                const scope =
                    $('input[name="edit_product_scope"]:checked')
                        .val();


                if (scope === 'with_category') {
                    $('#editProductsWithCategoryWrapper')
                        .removeClass('d-none');

                    $('#editProductsWithoutCategoryWrapper')
                        .addClass('d-none');

                    $categorySelect
                        .prop('disabled', false);

                    $categoryProductsSelect
                        .prop('disabled', false);

                    $withoutCategorySelect
                        .prop('disabled', true)
                        .val(null)
                        .trigger('change');

                    return;
                }


                $('#editProductsWithCategoryWrapper')
                    .addClass('d-none');

                $('#editProductsWithoutCategoryWrapper')
                    .removeClass('d-none');

                $categorySelect
                    .prop('disabled', true)
                    .val(null)
                    .trigger('change.select2');

                $categoryProductsSelect
                    .empty()
                    .prop('disabled', true)
                    .trigger('change');

                $withoutCategorySelect
                    .prop('disabled', false);
            }
        );


        /*
         * ==========================================
         * EDIT OFFER OPEN
         * ==========================================
         *
         * IMPORTANT:
         * This is the ONLY Edit handler that should exist.
         * The old .edit-details handler must be removed from
         * app-offer-list.js.
         */
        $(document).on(
            'click.offerEditV2',
            '.edit-details',
            function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const $button =
                    $(this);


                const offerId =
                    $button.data('id');


                $('#editOfferForm')
                    .attr(
                        'action',
                        '/offers/' + offerId
                    );


                $('#editOfferNameEn')
                    .val(
                        $button.data('name_en') ?? ''
                    );

                $('#editOfferNameAr')
                    .val(
                        $button.data('name_ar') ?? ''
                    );


                $('#editOfferValue')
                    .val(
                        String(
                            $button.data('value') ?? ''
                        )
                            .replace(/[%٪]/g, '')
                            .trim()
                    );


                $('#editStartDate')
                    .val(
                        toEditDate(
                            $button.data('start_at')
                        )
                    );


                $('#editEndDate')
                    .val(
                        toEditDate(
                            $button.data('end_at')
                        )
                    );


                /*
                 * Existing selected products
                 */
                const selectedProducts =
                    parseEditItems(
                        $button.attr('data-products')
                    );


                const selectedProductIds =
                    selectedProducts
                        .map(editItemId)
                        .filter(Boolean);


                /*
                 * Is this a "Products Without Categories" offer?
                 *
                 * Compare selected IDs against the options rendered in
                 * edit_product_without_categories.
                 */
                const withoutCategoryIds =
                    new Set(
                        $withoutCategorySelect
                            .find('option')
                            .map(function () {
                                return String(
                                    this.value
                                );
                            })
                            .get()
                    );


                const selectedWithoutCategory =
                    selectedProductIds.filter(
                        id =>
                            withoutCategoryIds.has(
                                String(id)
                            )
                    );


                if (
                    selectedProductIds.length &&
                    selectedWithoutCategory.length ===
                    selectedProductIds.length
                ) {
                    $('#editProductsWithoutCategory')
                        .prop('checked', true);


                    $('input[name="edit_product_scope"]:checked')
                        .trigger('change');


                    $withoutCategorySelect
                        .val(selectedWithoutCategory)
                        .trigger('change');

                    return;
                }


                /*
                 * Products With Categories
                 */
                $('#editProductsWithCategory')
                    .prop('checked', true);


                $('input[name="edit_product_scope"]:checked')
                    .trigger('change');


                /*
                 * FIRST:
                 * use the parent->children mapping rendered in the options.
                 */
                let selectedCategoryIds =
                    findParentCategoriesForProducts(
                        selectedProductIds
                    );


                /*
                 * FALLBACK:
                 * if the DataTable response already exposes category_id.
                 */
                if (!selectedCategoryIds.length) {
                    selectedCategoryIds =
                        getParentIdsFromProducts(
                            selectedProducts
                        );
                }


                /*
                 * Select the parent category/categories.
                 */
                $categorySelect
                    .val(selectedCategoryIds)
                    .trigger('change.select2');


                if (selectedCategoryIds.length) {
                    /*
                     * Load all products from these parents and restore current
                     * selected product(s).
                     */
                    loadEditProductsByCategories(
                        selectedCategoryIds,
                        selectedProductIds,
                        selectedProducts
                    );

                    return;
                }


                /*
                 * Last fallback:
                 * keep current products visible even if no parent could be resolved.
                 */
                $categoryProductsSelect.empty();


                selectedProducts.forEach(
                    function (product) {
                        $categoryProductsSelect.append(
                            new Option(
                                editItemName(product),
                                editItemId(product),
                                true,
                                true
                            )
                        );
                    }
                );


                $categoryProductsSelect
                    .trigger('change');
            }
        );


        /*
         * Percentage clamp
         */
        $('#editOfferValue').on(
            'input',
            function () {
                let value =
                    parseInt(
                        $(this).val(),
                        10
                    );

                if (Number.isNaN(value)) {
                    return;
                }

                value =
                    Math.max(
                        1,
                        Math.min(100, value)
                    );

                $(this).val(value);
            }
        );
    });
</script>
