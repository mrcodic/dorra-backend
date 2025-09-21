<div class="modal modal-slide-in new-user-modal fade" id="editLandingCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editCategoryForm" method="post" enctype="multipart/form-data" action="{{ route("categories.landing.edit") }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Product</h5>
                </div>
                <div class="modal-body flex-grow-1">

                    <!-- Name in Arabic and English -->
                    <div class="row my-3">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="editProductsSelect" class="label-text mb-1">Products</label>
                                <select id="editProductsSelect" class="form-select category-select" name="category_id">
                                    <option value="" disabled selected>Choose product</option>
                                    @foreach($allCategories as $category)
                                        <option value="{{ $category->id }}" data-is-has-category="{{ $category->is_has_category }}">
                                            {{ $category->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="editSubCategorySelect" class="label-text mb-1">SubProducts</label>
                                <select id="editSubCategorySelect" class="form-select select2 category-sub-category-select" data-sub-category-url="{{ route('sub-categories')}}" name="sub_categories[]" multiple>

                                </select>
                            </div>

                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="editTagsSelect" class="label-text mb-1">Category</label>
                                <select id="editTagsSelect" class="form-select select2 sub-category-select" name="products[]" multiple>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    handleAjaxFormSubmit("#editCategoryForm",{
        successMessage: "Product updated successfully",
        onSuccess:function () {
            location.reload()
        }
    })
</script>
<script !src="">
    $(document).ready(function () {
        $('#editSubCategorySelect').select2();
        $('#editTagsSelect').select2();

    });
</script>
<script !src="">
    $(document).ready(function () {
        const $subProducts = $('#editSubCategorySelect');
        const $categories = $('#editTagsSelect');
        const limit = 12;

        function showError($target) {
            const errorMsg = `
            <div class="text-danger mt-1 select-limit-error" style="font-size: 0.875em;">
                You can select a maximum of ${limit} items total (SubProducts + Category).
            </div>`;

            // Remove existing error messages
            $('.select-limit-error').remove();

            // Insert message after the select2 container
            $target.next('.select2-container').after(errorMsg);

            // Auto-hide after 5 seconds
            setTimeout(() => $('.select-limit-error').fadeOut(300, function () { $(this).remove(); }), 5000);
        }

        function enforceLimit(e) {
            const totalSelected = $subProducts.select2('data').length + $categories.select2('data').length;

            if (totalSelected > limit) {
                const $targetSelect = $(e.target);
                const selectedId = e.params.data.id;

                // Revert the last selected option
                const newSelection = $targetSelect.val().filter(val => val !== selectedId);
                $targetSelect.val(newSelection).trigger('change');

                showError($targetSelect);
            }
        }

        $subProducts.on('select2:select', enforceLimit);
        $categories.on('select2:select', enforceLimit);
    });
</script>
<script>
    $(document).ready(function () {
        const $editProductsSelect = $('#editProductsSelect');
        const $editSubCategorySelect = $('#editSubCategorySelect');
        const $categories = $('#editTagsSelect');

        // 🟢 دالة مساعدة لتحميل SubProducts و Categories
        function loadSubProductsAndCategories(productId, selectedSubs = [], selectedCats = []) {
            // SubProducts
            $.ajax({
                url: `${$editSubCategorySelect.data('sub-category-url')}?filter[parent_id]=${productId}`,
                method: "GET",
                beforeSend: function() {
                    $editSubCategorySelect.empty().append('<option disabled>Loading SubProducts...</option>');
                },
                success: function(res) {
                    $editSubCategorySelect.empty();
                    $.each(res.data, (i, s) =>
                        $editSubCategorySelect.append(`<option value="${s.id}">${s.name}</option>`)
                    );
                    if (selectedSubs.length > 0) {
                        $editSubCategorySelect.val(selectedSubs).trigger('change');
                    }
                },
                error: function() {
                    $editSubCategorySelect.empty().append('<option value="">Error loading SubProducts</option>');
                }
            });

            // Categories (غير الـ URL حسب API عندك)
            $.ajax({
                url: `/products?filter[category.id]=${productId}`,
                method: "GET",
                beforeSend: function() {
                    $categories.empty().append('<option disabled>Loading Categories...</option>');
                },
                success: function(res) {
                    $categories.empty();
                    $.each(res.data, (i, c) =>
                        $categories.append(`<option value="${c.id}">${c.name}</option>`)
                    );
                    if (selectedCats.length > 0) {
                        $categories.val(selectedCats).trigger('change');
                    }
                },
                error: function() {
                    $categories.empty().append('<option value="">Error loading Categories</option>');
                }
            });
        }

        // 🟢 عند الضغط على Edit (من غير trigger('change'))
        document.querySelectorAll(".edit-category").forEach(btn => {
            btn.addEventListener("click", function () {
                const id = this.dataset.id;
                const image = this.dataset.image;
                const subcategories = JSON.parse(this.dataset.subcategories || "[]");
                const products = JSON.parse(this.dataset.products || "[]");

                // set product value فقط
                $editProductsSelect.val(id);

                // نادينا مرة واحدة
                loadSubProductsAndCategories(id, subcategories, products);

                if (image) {
                    $('#editCategoryImagePreview').attr('src', image).removeClass('d-none');
                }
            });
        });

        // 🟢 عند تغيير المنتج يدويًا
        $editProductsSelect.on('change', function() {
            const productId = $(this).val();
            loadSubProductsAndCategories(productId);
        });
    });
</script>






