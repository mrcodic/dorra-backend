<!-- ADD OFFER MODAL (fixed names + IDs stay the same) -->
<div class="modal modal-slide-in new-user-modal fade" id="addOfferModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addOfferForm" method="post" enctype="multipart/form-data" action="{{ route('offers.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Add New Offer</h5>
                </div>

                <div class="modal-body flex-grow-1">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="offer_name_en" class="label-text mb-1">Offer Name En</label>
                            <input type="text" name="name[en]" id="offer_name_en" class="form-control" placeholder="Enter offer’s name en">
                        </div>
                        <div class="col-md-6">
                            <label for="offer_name_ar" class="label-text mb-1">Offer Name Ar</label>
                            <input type="text" name="name[ar]" id="offer_name_ar" class="form-control" placeholder="Enter offer’s name ar">
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label for="createDiscountValue" class="label-text mb-1">Offer Value (%)</label>
                        <input type="number" name="value" id="createDiscountValue" class="form-control"
                               placeholder="Enter offer’s value" min="1" max="100" step="1" required>
                    </div>

                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="applyToProducts" value="2" checked>
                            <label class="form-check-label" for="applyToProducts">Products</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="applyToCategories" value="1">
                            <label class="form-check-label" for="applyToCategories">Categories</label>
                        </div>
                    </div>

                    <!-- Products dropdown (correct name) -->
                    <div class="form-group mb-2 productsField" id="productsField">
                        <label for="productsSelect" class="label-text mb-1">Products</label>
                        <select id="productsSelect" name="product_ids[]" class="form-select select2 productsSelect" multiple>
                            @foreach($associatedData['products'] as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categories dropdown (correct name) -->
                    <div class="form-group mb-2 d-none categoriesField" id="categoriesField">
                        <label for="categoriesSelect" class="label-text mb-1">Categories</label>
                        <select id="categoriesSelect" name="category_ids[]" class="form-select select2 categoriesSelect" multiple>
                            @foreach($associatedData['categories'] as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col mb-2">
                            <label class="form-label">Start Date</label>
                            <input name="start_at" type="date" class="form-control">
                        </div>
                        <div class="col mb-2">
                            <label class="form-label">End Date</label>
                            <input name="end_at" type="date" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span>Add</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // clamp value 1..100
    $('#createDiscountValue').on('input', function () {
        let v = parseInt(this.value || '0', 10);
        if (v > 100) this.value = 100;
        if (v < 1)   this.value = 1;
    });

    // Initialize Select2 correctly inside modal
    function initAddOfferSelect2() {
        $('#addOfferModal .select2').select2({
            dropdownParent: $('#addOfferModal'),
            width: '100%',
            placeholder: 'Select...',
            allowClear: true
        });
    }

    // Re-init on modal show (handles dynamic DOM)
    $('#addOfferModal').on('shown.bs.modal', function(){
        initAddOfferSelect2();
    });

    // Toggle between products and categories
    $(function () {
        initAddOfferSelect2();

        $('input[name="type"]').on('change', function () {
            const type = parseInt(this.value, 10);

            if (type === 2) {            // Products
                $('.productsField').removeClass('d-none');
                $('.categoriesField').addClass('d-none');
                $('#categoriesSelect').val(null).trigger('change');
            } else if (type === 1) {     // Categories
                $('.categoriesField').removeClass('d-none');
                $('.productsField').addClass('d-none');
                $('#productsSelect').val(null).trigger('change');
            } else {
                $('.productsField, .categoriesField').addClass('d-none');
                $('#productsSelect, #categoriesSelect').val(null).trigger('change');
            }
        });

        $('input[name="type"]:checked').trigger('change');
    });

    // Your existing Ajax helper
    handleAjaxFormSubmit("#addOfferForm",{
        successMessage: "Offer Created Successfully",
        onSuccess:function () { location.reload(); }
    });
</script>
