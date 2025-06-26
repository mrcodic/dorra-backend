<div class="modal new-user-modal fade" id="showCodeModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="add-new-user modal-content pt-0">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Show Discount Code</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="form-group mb-2">
                        <label class="label-text mb-1">Code Name:</label>
                        <input type="text" id="codeValue" class="form-control" readonly>
                    </div>
                    <div class="form-group mb-2">
                        <label class="label-text mb-1">Usage Times:</label>
                        <input type="text" id="usedCount" class="form-control" readonly>
                    </div>


                </div>
            <div class="modal-footer border-top-0 d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button"
                        class="btn btn-primary"
                        id="editDiscountBtn"
                        data-action=""
                        data-type=""
                        data-prefix=""
                        data-value=""
                        data-usage=""
                        data-scope=""
                        data-expired_at=""
                        data-categories=""
                        data-products=""
                        data-used="">
                    Edit
                </button>


            </div>

        </div>
    </div>
</div>


<script !src="">
    $('#editDiscountBtn').on('click', function () {
        const button = $(this);

        const type = button.data('type');
        const prefix = button.data('prefix');
        const value = button.data('value');
        const usage = button.data('usage');
        let expireDate = button.data('expired_at');
        const scope = button.data('scope');
        const categories = button.data('categories');
        const products = button.data('products');
        const used = button.data('used');
        const action = button.data('action');

        $('#showCodeModal').modal('hide');

        $('#showCodeModal').on('hidden.bs.modal', function () {
            const modal = $('#editCodeModal');
            modal.modal('show');

            modal.find('#discountType').val(type).trigger('change');
            modal.find('#prefix').val(prefix);
            modal.find('#discountValue').val(value);
            modal.find('#scopeType').val(scope);
            modal.find('#expiryDate').val(expireDate);
            modal.find('#restrictions').val(usage);
            modal.find('#usedCount').val(used);
            modal.find('form#editDiscountForm').attr('action',action);

            const selectedProductsContainer = modal.find('#selectedProducts');
            const selectedCategoriesContainer = modal.find('#selectedCategories');

            selectedProductsContainer.empty();
            selectedCategoriesContainer.empty();

            let categoryList = [];
            let productList = [];

            try {
                categoryList = Array.isArray(categories) ? categories : JSON.parse(categories);
                productList = Array.isArray(products) ? products : JSON.parse(products);
            } catch (e) {
                console.error("Error parsing products/categories", e);
            }

            if (scope === 'Category') {
                selectedCategoriesContainer.html(categoryList.map(cat => `<span class="badge bg-primary">${cat.name.{{app()->getLocale()}}}</span>`).join(''));
                selectedCategoriesContainer.closest('.form-group').show();
                selectedProductsContainer.closest('.form-group').hide();
            } else if (scope === 'Product') {
                selectedProductsContainer.html(productList.map(prod => `<span class="badge bg-primary">${prod.name.{{app()->getLocale()}}}</span>`).join(''));
                selectedProductsContainer.closest('.form-group').show();
                selectedCategoriesContainer.closest('.form-group').hide();
            } else {
                selectedCategoriesContainer.closest('.form-group').hide();
                selectedProductsContainer.closest('.form-group').hide();
            }


            $(this).off('hidden.bs.modal');
        });
    });




</script>



