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

                    {{-- Names --}}
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


                    {{-- Value --}}
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


                    {{--
                        Current project logic uses type = 2 for Product offers.
                        Both choices below still submit products.
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


                    {{-- ====================================================== --}}
                    {{-- PRODUCTS WITH CATEGORIES                               --}}
                    {{-- ====================================================== --}}
                    <div id="editProductsWithCategoryWrapper">

                        <div class="form-group mb-2">
                            <label for="editCategorySelect" class="label-text mb-1">
                                Categories
                            </label>

                            {{-- Filter only; no name --}}
                            <select
                                id="editCategorySelect"
                                class="form-select select2"
                                multiple
                            >
                                @foreach($associatedData['edit_product_with_categories'] as $category)
                                    <option value="{{ $category->id }}">
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


                    {{-- ====================================================== --}}
                    {{-- PRODUCTS WITHOUT CATEGORIES                            --}}
                    {{-- ====================================================== --}}
                    <div id="editProductsWithoutCategoryWrapper" class="d-none">

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
                                @foreach($associatedData['edit_product_without_categories'] as $product)
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

        const $parentCategories =
            $('#editCategorySelect');

        const $productsWithCategory =
            $('#editCategoryProductsSelect');

        const $productsWithoutCategory =
            $('#editProductsWithoutCategorySelect');


        /*
         * Helpers
         */
        function editToDateInput(value) {
            if (!value) {
                return '';
            }

            value = String(value).trim();

            if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return value;
            }

            if (value.includes('T')) {
                return value.split('T')[0];
            }

            if (value.includes(' ')) {
                return value.split(' ')[0];
            }

            const match =
                value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

            if (match) {
                return `${match[3]}-${match[2]}-${match[1]}`;
            }

            return '';
        }


        function editToItemsArray(raw) {
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
                return String(item.id ?? item.value ?? '');
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


        /*
         * Try several common response shapes to get the parent category id
         * from an already-selected product.
         */
        function editParentId(item) {
            if (!item || typeof item !== 'object') {
                return null;
            }

            const id =
                item.category_id ??
                item.parent_id ??
                item.product_category_id ??
                item.category?.id ??
                item.parent?.id ??
                null;

            return id == null ? null : String(id);
        }


        /*
         * AJAX: parent categories -> child products.
         *
         * selectedProductIds:
         * existing Offer products that should be checked after loading.
         */
        function loadEditProducts(
            categoryIds,
            selectedProductIds = [],
            selectedItems = []
        ) {
            categoryIds = (categoryIds || []).map(String);
            selectedProductIds = (selectedProductIds || []).map(String);

            if (!categoryIds.length) {
                $productsWithCategory
                    .empty()
                    .trigger('change');

                return;
            }

            $productsWithCategory
                .prop('disabled', true);

            $.ajax({
                url: "{{ route('products.categories') }}",
                type: 'POST',

                data: {
                    _token: "{{ csrf_token() }}",
                    category_ids: categoryIds
                },

                success: function (response) {
                    $productsWithCategory.empty();

                    const returnedIds = new Set();

                    (response.data || []).forEach(function (product) {
                        const id = String(product.id);
                        returnedIds.add(id);

                        const isSelected =
                            selectedProductIds.includes(id);

                        const option = new Option(
                            product.name,
                            product.id,
                            isSelected,
                            isSelected
                        );

                        $productsWithCategory.append(option);
                    });


                    /*
                     * Fallback:
                     * if the resource response does not expose category_id,
                     * keep old selected products visible rather than losing them.
                     */
                    selectedItems.forEach(function (item) {
                        const id = editItemId(item);

                        if (
                            selectedProductIds.includes(id) &&
                            !returnedIds.has(id)
                        ) {
                            const option = new Option(
                                editItemName(item),
                                id,
                                true,
                                true
                            );

                            $productsWithCategory.append(option);
                        }
                    });


                    $productsWithCategory
                        .prop('disabled', false)
                        .trigger('change');
                },

                error: function (xhr) {
                    console.error(
                        'Error loading edit products:',
                        xhr.responseText
                    );

                    /*
                     * Do not silently erase the current values.
                     */
                    $productsWithCategory.empty();

                    selectedItems.forEach(function (item) {
                        const id = editItemId(item);

                        const option = new Option(
                            editItemName(item),
                            id,
                            true,
                            true
                        );

                        $productsWithCategory.append(option);
                    });

                    $productsWithCategory
                        .prop('disabled', false)
                        .trigger('change');
                }
            });
        }


        /*
         * Initialize Select2 inside modal.
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
         * Manual parent category change.
         */
        $parentCategories.on('change', function () {
            const ids = $(this).val() || [];

            loadEditProducts(
                ids,
                $productsWithCategory.val() || []
            );
        });


        /*
         * Scope switch.
         */
        $('input[name="edit_product_scope"]').on('change', function () {
            const scope =
                $('input[name="edit_product_scope"]:checked').val();

            if (scope === 'with_category') {
                $('#editProductsWithCategoryWrapper')
                    .removeClass('d-none');

                $('#editProductsWithoutCategoryWrapper')
                    .addClass('d-none');

                $parentCategories
                    .prop('disabled', false);

                $productsWithCategory
                    .prop('disabled', false);

                $productsWithoutCategory
                    .prop('disabled', true)
                    .val(null)
                    .trigger('change');

                return;
            }

            $('#editProductsWithCategoryWrapper')
                .addClass('d-none');

            $('#editProductsWithoutCategoryWrapper')
                .removeClass('d-none');

            $parentCategories
                .prop('disabled', true)
                .val(null)
                .trigger('change.select2');

            $productsWithCategory
                .empty()
                .prop('disabled', true)
                .trigger('change');

            $productsWithoutCategory
                .prop('disabled', false);
        });


        /*
         * IMPORTANT:
         * This handler replaces the old edit handler in app-offer-list.js.
         * stopImmediatePropagation prevents the old handler from changing
         * these new fields again.
         */
        $(document).on('click.fixedEditOffer', '.edit-details', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const $button = $(this);

            const offerId =
                $button.data('id');

            const nameEn =
                $button.data('name_en') ?? '';

            const nameAr =
                $button.data('name_ar') ?? '';

            let value =
                String($button.data('value') ?? '')
                    .replace(/[%٪]/g, '')
                    .trim();

            const startAt =
                editToDateInput(
                    $button.data('start_at')
                );

            const endAt =
                editToDateInput(
                    $button.data('end_at')
                );

            const selectedProducts =
                editToItemsArray(
                    $button.attr('data-products')
                );

            const selectedProductIds =
                selectedProducts
                    .map(editItemId)
                    .filter(Boolean);


            /*
             * Fill basic fields.
             */
            $('#editOfferForm')
                .attr('action', '/offers/' + offerId);

            $('#editOfferNameEn').val(nameEn);
            $('#editOfferNameAr').val(nameAr);
            $('#editOfferValue').val(value);
            $('#editStartDate').val(startAt);
            $('#editEndDate').val(endAt);


            /*
             * IDs that belong to the "without categories" collection.
             * These are rendered in the page already, so we can classify
             * existing selected values without another request.
             */
            const withoutCategoryIds = new Set(
                $productsWithoutCategory
                    .find('option')
                    .map(function () {
                        return String(this.value);
                    })
                    .get()
            );


            const selectedWithoutCategory =
                selectedProductIds.filter(function (id) {
                    return withoutCategoryIds.has(String(id));
                });


            /*
             * If every selected item belongs to the "without categories"
             * collection, open that branch.
             */
            if (
                selectedProductIds.length &&
                selectedWithoutCategory.length === selectedProductIds.length
            ) {
                $('#editProductsWithoutCategory')
                    .prop('checked', true);

                $('input[name="edit_product_scope"]:checked')
                    .trigger('change');

                $productsWithoutCategory
                    .val(selectedWithoutCategory)
                    .trigger('change');

                $modal.modal('show');

                return;
            }


            /*
             * Otherwise use Products With Categories.
             */
            $('#editProductsWithCategory')
                .prop('checked', true);

            $('input[name="edit_product_scope"]:checked')
                .trigger('change');


            /*
             * Try to detect the current parent categories from row.products.
             */
            const parentIds = [
                ...new Set(
                    selectedProducts
                        .map(editParentId)
                        .filter(Boolean)
                )
            ];


            if (parentIds.length) {
                $parentCategories
                    .val(parentIds)
                    .trigger('change.select2');

                loadEditProducts(
                    parentIds,
                    selectedProductIds,
                    selectedProducts
                );
            } else {
                /*
                 * The response did not expose parent/category IDs.
                 * Keep selected products visible so edit remains safe.
                 * Once the admin picks parent category/categories,
                 * the dropdown will be filtered through AJAX normally.
                 */
                $parentCategories
                    .val(null)
                    .trigger('change.select2');

                $productsWithCategory.empty();

                selectedProducts.forEach(function (item) {
                    const id = editItemId(item);

                    const option = new Option(
                        editItemName(item),
                        id,
                        true,
                        true
                    );

                    $productsWithCategory.append(option);
                });

                $productsWithCategory.trigger('change');
            }


            $modal.modal('show');
        });


        /*
         * Clamp percentage.
         */
        $('#editOfferValue').on('input', function () {
            let value = parseInt($(this).val(), 10);

            if (Number.isNaN(value)) {
                return;
            }

            value = Math.max(1, Math.min(100, value));
            $(this).val(value);
        });
    });
</script>
