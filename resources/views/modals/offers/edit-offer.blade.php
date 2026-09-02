<div class="modal modal-slide-in new-user-modal fade" id="editOfferModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editOfferForm" method="post" enctype="multipart/form-data" action="">
                @csrf
                @method('PUT')
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Offer</h5>
                </div>
                <div class="modal-body flex-grow-1">


                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="editOfferNameEn" class="label-text mb-1">Offer Name En</label>
                            <input type="text" name="name[en]" id="editOfferNameEn" class="form-control"
                                   placeholder="Enter offer’s name en">
                        </div>

                        <div class="col-md-6">
                            <label for="editOfferNameAr" class="label-text mb-1">Offer Name Ar</label>
                            <input type="text" name="name[ar]" id="editOfferNameAr" class="form-control"
                                   placeholder="Enter offer’s name ar">
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="editOfferValue" class="label-text mb-1">Offer Value (%)</label>
                        <input type="text" name="value" id="editOfferValue" class="form-control"
                               placeholder="Enter offer’s value ">
                    </div>


                    <!-- Radios: unique IDs, and name="type" (matches your code) -->
                    {{-- Offer is always for products --}}
                    <input type="hidden" name="type" value="1">

                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Product Type</label>

                        <div class="form-check form-check-inline">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="product_scope"
                                id="addProductsWithCategory"
                                value="with_category"
                                checked
                            >
                            <label class="form-check-label" for="addProductsWithCategory">
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
                            <label class="form-check-label" for="addProductsWithoutCategory">
                                Products Without Categories
                            </label>
                        </div>
                    </div>


                    {{-- ============================= --}}
                    {{-- PRODUCTS WITH CATEGORY --}}
                    {{-- ============================= --}}

                    <div id="addProductsWithCategoryWrapper">

                        <div class="form-group mb-2">
                            <label for="addCategorySelect" class="label-text mb-1">
                                Categories
                            </label>

                            <select
                                id="addCategorySelect"
                                class="form-select select2"
                                multiple
                            >
                                @foreach($associatedData['categories'] as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group mb-2">
                            <label for="addCategoryProductsSelect" class="label-text mb-1">
                                Products
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


                    {{-- ============================= --}}
                    {{-- PRODUCTS WITHOUT CATEGORY --}}
                    {{-- ============================= --}}

                    <div id="addProductsWithoutCategoryWrapper" class="d-none">

                        <div class="form-group mb-2">
                            <label for="addProductsWithoutCategorySelect" class="label-text mb-1">
                                Products
                            </label>

                            <select
                                id="addProductsWithoutCategorySelect"
                                name="product_ids[]"
                                class="form-select select2"
                                multiple
                                disabled
                            >
                                @foreach($associatedData['productsWithoutCategories'] as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>

                    </div>

                    <div class="row">
                        <div class="col  mb-2">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_at" id="editStartDate" class="form-control">
                        </div>
                        <div class="col mb-2">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_at" id="editEndDate" class="form-control">
                        </div>

                    </div>

                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span>Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                              aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    $('#editOfferValue').on('input', function () {
        let val = parseInt($(this).val());
        if (val > 100) $(this).val(100);
        if (val < 1) $(this).val(1);
    });
    $(function () {
        // Listen on name="type" (not "type")
        $('input[name="type"]').on('change', function () {
            const v = parseInt(this.value, 10);
            if (v === 2) { // Products
                $('.productsField').removeClass('d-none');
                $('.categoriesField').addClass('d-none');
                $('#editCategoriesSelect').val(null).trigger('change');
            } else if (v === 1) { // Categories
                $('.categoriesField').removeClass('d-none');
                $('.productsField').addClass('d-none');
                $('#editProductsSelect').val(null).trigger('change');
            } else {
                $('.productsField, .categoriesField').addClass('d-none');
                $('#editProductsSelect, #editCategoriesSelect').val(null).trigger('change');
            }
        });

        $('input[name="type"]:checked').trigger('change');
    });

</script>
