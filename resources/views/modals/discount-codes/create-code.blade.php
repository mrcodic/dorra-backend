<div class="modal modal-slide-in new-user-modal fade" id="createCodeTemplateModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addTagForm" enctype="multipart/form-data" action="{{ route('tags.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Create Discount Code</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="form-group mb-2">
                        <label for="discountType" class="label-text mb-1">Type</label>
                        <select id="discountType" class="form-select select2">
                            <option value="">Select discount code type</option>
                            <option value="fixed">Fixed</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label for="prefix" class="label-text mb-1">Prefix (Write 4 char)</label>
                        <input type="text" id="prefix" class="form-control" placeholder="Add prefix here">
                    </div>

                    <div class="form-group mb-2">
                        <label for="discountValue" class="label-text mb-1">Discount Value</label>
                        <input type="text" id="discountValue" class="form-control" placeholder="Enter discount value here">
                    </div>

                    <div class="d-flex gap-1">
                        <div class="form-group mb-2 col-6">
                            <label for="restrictions" class="label-text mb-1">Restrictions</label>
                            <input type="number" id="restrictions" class="form-control" placeholder="Enter number of usage times">
                        </div>
                        <div class="form-group mb-2 col-6">
                            <label for="expiryDate" class="label-text mb-1">Expiry Date</label>
                            <input type="date" id="expiryDate" class="form-control">
                        </div>
                    </div>

                    <!-- Radio switch for Products or Categories -->
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="applyTo" id="applyToProducts" value="products" checked>
                            <label class="form-check-label text-black fs-16" for="applyToProducts">Products</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="applyTo" id="applyToCategories" value="categories">
                            <label class="form-check-label text-black fs-16" for="applyToCategories">Categories</label>
                        </div>
                    </div>

                    <!-- Products dropdown -->
                    <div class="form-group mb-2" id="productsField">
                        <label for="productsSelect" class="label-text mb-1">Products</label>
                        <select id="productsSelect" class="form-select select2">
                            <option value="">Select Products</option>
                            <!-- dynamic options -->
                        </select>
                    </div>

                    <!-- Categories dropdown -->
                    <div class="form-group mb-2 d-none" id="categoriesField">
                        <label for="categoriesSelect" class="label-text mb-1">Category</label>
                        <select id="categoriesSelect" class="form-select select2">
                            <option value="">Select Categories</option>
                            <!-- dynamic options -->
                        </select>
                    </div>

                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary">Generate</button>
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span >Generate & Export</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Discount type changes
        $('#discountType').on('change', function() {
            const type = $(this).val();
            const input = $('#discountValue');
            if (type === 'fixed') {
                input.attr('placeholder', 'Enter fixed discount amount');
            } else if (type === 'percentage') {
                input.attr('placeholder', 'Enter discount percentage');
            } else {
                input.attr('placeholder', 'Enter discount value here');
            }
        });

        // Toggle products/categories visibility
        $('input[name="applyTo"]').on('change', function() {
            const value = $(this).val();
            if (value === 'products') {
                $('#productsField').removeClass('d-none');
                $('#categoriesField').addClass('d-none');
            } else {
                $('#categoriesField').removeClass('d-none');
                $('#productsField').addClass('d-none');
            }
        });
    });
</script>