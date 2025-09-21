<div class="modal modal-slide-in new-user-modal fade" id="addLandingCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addCategoryForm" method="post" enctype="multipart/form-data" action="{{ route("categories.landing") }}" class="landing-category-form">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Add Product</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="row my-3">
                        <!-- Product -->
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="productsSelect" class="label-text mb-1">Products</label>
                                <select id="productsSelect" class="form-select category-select" name="category_id">
                                    <option value="" disabled selected>Choose product</option>
                                    @foreach($allCategories as $category)
                                        <option value="{{ $category->id }}" data-is-has-category="{{ $category->is_has_category }}">
                                            {{ $category->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- SubProducts -->
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="subCategorySelect" class="label-text mb-1">SubProducts</label>
                                <select id="subCategorySelect"
                                        class="form-select select2 category-sub-category-select"
                                        data-sub-category-url="{{ route('sub-categories')}}"
                                        name="sub_categories[]"
                                        multiple>
                                </select>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="tagsSelect" class="label-text mb-1">Category</label>
                                <select id="tagsSelect" class="form-select select2 sub-category-select" name="products[]" multiple>
                                    <!-- سيتم تحميلها ديناميك -->
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

<script>
    $(document).ready(function () {
        const $productsSelect   = $('#productsSelect');
        const $subCategorySelect = $('#subCategorySelect');
        const $categoriesSelect = $('#tagsSelect');
        const limit = 12;

        // init select2
        $subCategorySelect.select2();
        $categoriesSelect.select2();

        // enforce total limit (subProducts + categories)
        function enforceLimit(e) {
            const totalSelected = $subCategorySelect.select2('data').length + $categoriesSelect.select2('data').length;
            if (totalSelected > limit) {
                const $targetSelect = $(e.target);
                const selectedId = e.params.data.id;
                const newSelection = $targetSelect.val().filter(val => val !== selectedId);
                $targetSelect.val(newSelection).trigger('change');

                // show error
                $('.select-limit-error').remove();
                $targetSelect.next('.select2-container').after(`
                <div class="text-danger mt-1 select-limit-error" style="font-size: 0.875em;">
                    You can select a maximum of ${limit} items total (SubProducts + Category).
                </div>
            `);
                setTimeout(() => $('.select-limit-error').fadeOut(300, function () { $(this).remove(); }), 5000);
            }
        }
        $subCategorySelect.on('select2:select', enforceLimit);
        $categoriesSelect.on('select2:select', enforceLimit);

        // 🟢 load subProducts + categories
        function loadSubProductsAndCategories(productId) {
            // SubProducts
            $.ajax({
                url: `${$subCategorySelect.data('sub-category-url')}?filter[parent_id]=${productId}`,
                method: "GET",
                beforeSend: function() {
                    $subCategorySelect.empty().append('<option disabled>Loading SubProducts...</option>');
                },
                success: function(res) {
                    $subCategorySelect.empty();
                    $.each(res.data, (i, s) =>
                        $subCategorySelect.append(`<option value="${s.id}">${s.name}</option>`)
                    );
                },
                error: function() {
                    $subCategorySelect.empty().append('<option value="">Error loading Subcategories</option>');
                }
            });

            // Categories (⚠️ غيّر الـ API هنا على حسب عندك)
            $.ajax({
                url: `/products?filter[category.id]=${productId}`,
                method: "GET",
                beforeSend: function() {
                    $categoriesSelect.empty().append('<option disabled>Loading Categories...</option>');
                },
                success: function(res) {
                    $categoriesSelect.empty();
                    $.each(res.data, (i, c) =>
                        $categoriesSelect.append(`<option value="${c.id}">${c.name}</option>`)
                    );
                },
                error: function() {
                    $categoriesSelect.empty().append('<option value="">Error loading Categories</option>');
                }
            });
        }

        // 🟢 handle product change
        $productsSelect.on('change', function() {
            const productId = $(this).val();
            const isHasCategory = $(this).find(':selected').data('is-has-category');

            if (isHasCategory === 0) {
                $subCategorySelect.prop('disabled', true).val(null).trigger('change');
                $categoriesSelect.prop('disabled', true).val(null).trigger('change');

                $subCategorySelect.empty().append('<option disabled>SubProducts disabled</option>');
                $categoriesSelect.empty().append('<option disabled>Categories disabled</option>');
            } else {
                $subCategorySelect.prop('disabled', false);
                $categoriesSelect.prop('disabled', false);

                loadSubProductsAndCategories(productId);
            }
        });
    });
</script>
