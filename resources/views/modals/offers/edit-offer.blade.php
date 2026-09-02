@php
    /*
     * Compatibility fallback:
     * - New controller keys are preferred.
     * - Old keys keep the page from crashing if an older controller
     *   is still deployed/cached.
     */
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
            <form id="editOfferForm" method="post" enctype="multipart/form-data" action="">
                @csrf
                @method('PUT')

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Edit Offer</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="editOfferNameEn" class="label-text mb-1">Offer Name En</label>
                            <input
                                type="text"
                                name="name[en]"
                                id="editOfferNameEn"
                                class="form-control"
                                placeholder="Enter offer’s name en"
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="editOfferNameAr" class="label-text mb-1">Offer Name Ar</label>
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
                        <label for="editOfferValue" class="label-text mb-1">Offer Value (%)</label>
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

                    <input type="hidden" name="type" value="2">

                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Product Type</label>

                        <div class="form-check form-check-inline">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="edit_product_scope"
                                id="editProductsWithCategory"
                                value="with_category"
                                checked
                            >
                            <label class="form-check-label" for="editProductsWithCategory">
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
                            <label class="form-check-label" for="editProductsWithoutCategory">
                                Products Without Categories
                            </label>
                        </div>
                    </div>

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
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label for="editCategoryProductsSelect" class="label-text mb-1">
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

                    <div id="editProductsWithoutCategoryWrapper" class="d-none">

                        <div class="form-group mb-2">
                            <label for="editProductsWithoutCategorySelect" class="label-text mb-1">
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
                            <label for="editStartDate" class="form-label">Start Date</label>
                            <input
                                type="date"
                                name="start_at"
                                id="editStartDate"
                                class="form-control"
                            >
                        </div>

                        <div class="col mb-2">
                            <label for="editEndDate" class="form-label">End Date</label>
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
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
        const $parents = $('#editCategorySelect');
        const $withProducts = $('#editCategoryProductsSelect');
        const $withoutProducts = $('#editProductsWithoutCategorySelect');

        function toDateInput(value) {
            if (!value) return '';

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

            const match = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

            if (match) {
                return `${match[3]}-${match[2]}-${match[1]}`;
            }

            return '';
        }

        function parseItems(raw) {
            if (!raw) return [];

            if (typeof raw === 'string') {
                try {
                    raw = JSON.parse(raw);
                } catch (_) {
                    return [];
                }
            }

            return Array.isArray(raw) ? raw : [];
        }

        function itemId(item) {
            if (item && typeof item === 'object') {
                return String(item.id ?? item.value ?? '');
            }

            return String(item ?? '');
        }

        function itemName(item) {
            if (!item || typeof item !== 'object') {
                return '#' + itemId(item);
            }

            let name = item.name ?? item.title ?? item.label ?? null;

            if (name && typeof name === 'object') {
                name =
                    name.en ??
                    name.ar ??
                    Object.values(name)[0] ??
                    null;
            }

            return name ? String(name) : '#' + itemId(item);
        }

        function parentId(item) {
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

        function loadProductsByParents(parentIds, selectedIds = [], selectedItems = []) {
            parentIds = (parentIds || []).map(String);
            selectedIds = (selectedIds || []).map(String);

            if (!parentIds.length) {
                $withProducts.empty().trigger('change');
                return;
            }

            $withProducts.prop('disabled', true);

            $.ajax({
                url: "{{ route('products.categories') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    category_ids: parentIds
                },

                success: function (response) {
                    $withProducts.empty();

                    const returnedIds = new Set();

                    (response.data || []).forEach(function (product) {
                        const id = String(product.id);
                        returnedIds.add(id);

                        const selected = selectedIds.includes(id);

                        $withProducts.append(
                            new Option(
                                product.name,
                                product.id,
                                selected,
                                selected
                            )
                        );
                    });

                    // Keep existing selected items visible if endpoint omitted them.
                    selectedItems.forEach(function (item) {
                        const id = itemId(item);

                        if (selectedIds.includes(id) && !returnedIds.has(id)) {
                            $withProducts.append(
                                new Option(
                                    itemName(item),
                                    id,
                                    true,
                                    true
                                )
                            );
                        }
                    });

                    $withProducts
                        .prop('disabled', false)
                        .trigger('change');
                },

                error: function (xhr) {
                    console.error('Error loading edit products:', xhr.responseText);

                    $withProducts.empty();

                    selectedItems.forEach(function (item) {
                        $withProducts.append(
                            new Option(
                                itemName(item),
                                itemId(item),
                                true,
                                true
                            )
                        );
                    });

                    $withProducts
                        .prop('disabled', false)
                        .trigger('change');
                }
            });
        }

        $modal.on('shown.bs.modal', function () {
            $modal.find('.select2').each(function () {
                const $select = $(this);

                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        dropdownParent: $modal,
                        width: '100%'
                    });
                }
            });
        });

        $parents.on('change', function () {
            loadProductsByParents(
                $(this).val() || [],
                $withProducts.val() || []
            );
        });

        $('input[name="edit_product_scope"]').on('change', function () {
            const scope =
                $('input[name="edit_product_scope"]:checked').val();

            if (scope === 'with_category') {
                $('#editProductsWithCategoryWrapper').removeClass('d-none');
                $('#editProductsWithoutCategoryWrapper').addClass('d-none');

                $parents.prop('disabled', false);
                $withProducts.prop('disabled', false);

                $withoutProducts
                    .prop('disabled', true)
                    .val(null)
                    .trigger('change');

                return;
            }

            $('#editProductsWithCategoryWrapper').addClass('d-none');
            $('#editProductsWithoutCategoryWrapper').removeClass('d-none');

            $parents
                .prop('disabled', true)
                .val(null)
                .trigger('change.select2');

            $withProducts
                .empty()
                .prop('disabled', true)
                .trigger('change');

            $withoutProducts.prop('disabled', false);
        });

        $(document).on('click.fixedEditOffer', '.edit-details', function (e) {
            e.preventDefault();

            const $button = $(this);

            const offerId = $button.data('id');

            $('#editOfferForm').attr('action', '/offers/' + offerId);

            $('#editOfferNameEn').val($button.data('name_en') ?? '');
            $('#editOfferNameAr').val($button.data('name_ar') ?? '');

            $('#editOfferValue').val(
                String($button.data('value') ?? '')
                    .replace(/[%٪]/g, '')
                    .trim()
            );

            $('#editStartDate').val(
                toDateInput($button.data('start_at'))
            );

            $('#editEndDate').val(
                toDateInput($button.data('end_at'))
            );

            const selectedProducts =
                parseItems($button.attr('data-products'));

            const selectedIds =
                selectedProducts
                    .map(itemId)
                    .filter(Boolean);

            const withoutIds = new Set(
                $withoutProducts
                    .find('option')
                    .map(function () {
                        return String(this.value);
                    })
                    .get()
            );

            const selectedWithout =
                selectedIds.filter(id => withoutIds.has(String(id)));

            if (
                selectedIds.length &&
                selectedWithout.length === selectedIds.length
            ) {
                $('#editProductsWithoutCategory').prop('checked', true);

                $('input[name="edit_product_scope"]:checked')
                    .trigger('change');

                $withoutProducts
                    .val(selectedWithout)
                    .trigger('change');

                return;
            }

            $('#editProductsWithCategory').prop('checked', true);

            $('input[name="edit_product_scope"]:checked')
                .trigger('change');

            const parentIds = [
                ...new Set(
                    selectedProducts
                        .map(parentId)
                        .filter(Boolean)
                )
            ];

            if (parentIds.length) {
                $parents
                    .val(parentIds)
                    .trigger('change.select2');

                loadProductsByParents(
                    parentIds,
                    selectedIds,
                    selectedProducts
                );

                return;
            }

            // Parent IDs were not included in row.products.
            // Keep current products selected instead of losing them.
            $parents
                .val(null)
                .trigger('change.select2');

            $withProducts.empty();

            selectedProducts.forEach(function (item) {
                $withProducts.append(
                    new Option(
                        itemName(item),
                        itemId(item),
                        true,
                        true
                    )
                );
            });

            $withProducts.trigger('change');
        });

        $('#editOfferValue').on('input', function () {
            let value = parseInt($(this).val(), 10);

            if (Number.isNaN(value)) return;

            $(this).val(
                Math.max(1, Math.min(100, value))
            );
        });
    });
</script>
