<div class="modal modal-slide-in new-user-modal fade" id="addStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addStationStatusForm" method="POST" action="{{ route('station-statuses.store') }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Add New Station Status</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    <div class="mb-2">
                        <label class="form-label label-text">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Printing Started" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label label-text">Station</label>
                        <select class="form-select" name="station_id" required>
                            <option value="" selected disabled>— Select Station —</option>
                            @foreach ($associatedData['stations'] ?? [] as $station)
                                <option value="{{ $station->id }}">{{ $station->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mutually-exclusive groups --}}
                    <div class="border rounded p-2 mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="target_mode" id="modeWithCat" value="with_category" checked>
                            <label class="form-check-label" for="modeWithCat">Products WITH Categories</label>
                        </div>
                        <div class="form-check form-check-inline ms-3">
                            <input class="form-check-input" type="radio" name="target_mode" id="modeWithoutCat" value="without_category">
                            <label class="form-check-label" for="modeWithoutCat">Products WITHOUT Categories</label>
                        </div>
                    </div>

                    {{-- WITH categories flow --}}
                    <div id="withCategoryBlock">
                        <div class="row">
                            <div class="col-md-6 form-group mb-2">
                                <label for="categoriesSelect" class="label-text mb-1">Category</label>
                                <select id="categoriesSelect" class="form-select">
                                    <option value="" selected>— Select Category —</option>
                                    @foreach($associatedData['product_with_categories'] as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Choose a category. If you don’t pick a product below, the status targets the category.</small>
                            </div>

                            <div class="col-md-6 form-group mb-2">
                                <label for="productsSelect" class="label-text mb-1">Product in Selected Category</label>
                                <select id="productsSelect" class="form-select">
                                    <option value="" selected>— (Optional) Select Product —</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- WITHOUT categories flow --}}
                    <div id="withoutCategoryBlock" class="d-none">
                        <div class="form-group mb-2">
                            <label for="productsWithoutCategoriesSelect" class="label-text mb-1">Product (No Category)</label>
                            <select id="productsWithoutCategoriesSelect" class="form-select">
                                <option value="" selected>— Select Product —</option>
                                @foreach($associatedData['product_without_categories'] as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->getTranslation('name', app()->getLocale()) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Hidden fields the backend expects --}}
                    <input type="hidden" name="resourceable_type" id="resourceable_type">
                    <input type="hidden" name="resourceable_id" id="resourceable_id">
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<script>
    (function () {
        const TYPE_PRODUCT  = 'App\\Models\\Product';
        const TYPE_CATEGORY = 'App\\Models\\Category';

        const $modeWithCat   = $('#modeWithCat');
        const $modeWithout   = $('#modeWithoutCat');
        const $withBlock     = $('#withCategoryBlock');
        const $withoutBlock  = $('#withoutCategoryBlock');

        const $categories    = $('#categoriesSelect');
        const $productsInCat = $('#productsSelect');
        const $prodNoCat     = $('#productsWithoutCategoriesSelect');

        const $typeInput     = $('#resourceable_type');
        const $idInput       = $('#resourceable_id');
        const $form          = $('#addStationStatusForm');

        // Toggle UI blocks
        function toggleBlocks() {
            if ($modeWithCat.is(':checked')) {
                $withBlock.removeClass('d-none');
                $withoutBlock.addClass('d-none');

                // Clear the "without" selection
                $prodNoCat.val('');
                computeResourceableFromWithCategory();
            } else {
                $withBlock.addClass('d-none');
                $withoutBlock.removeClass('d-none');

                // Clear the "with" selections
                $categories.val('');
                $productsInCat.html('<option value="" selected>— (Optional) Select Product —</option>');
                computeResourceableFromWithoutCategory();
            }
        }

        // AJAX: load products for selected category
        $categories.on('change', function () {
            const categoryId = $(this).val();
            // Clear downstream selection first
            $productsInCat.html('<option value="" selected>Loading...</option>');

            if (!categoryId) {
                $productsInCat.html('<option value="" selected>— (Optional) Select Product —</option>');
                computeResourceableFromWithCategory();
                return;
            }

            $.ajax({
                url: "{{ route('products.categories') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    category_ids: [categoryId]
                },
                success: function (response) {
                    $productsInCat.html('<option value="" selected>— (Optional) Select Product —</option>');
                    if (response.data && response.data.length) {
                        response.data.forEach(function (p) {
                            $productsInCat.append(new Option(p.name, p.id, false, false));
                        });
                    }
                    // After repopulating, recompute target (still category unless user picks a product)
                    computeResourceableFromWithCategory();
                },
                error: function (xhr) {
                    console.error("Error fetching products:", xhr.responseText);
                    $productsInCat.html('<option value="" selected>— (Optional) Select Product —</option>');
                    computeResourceableFromWithCategory();
                }
            });
        });

        // When user picks a product inside the category
        $productsInCat.on('change', computeResourceableFromWithCategory);

        // When user picks a product without category
        $prodNoCat.on('change', computeResourceableFromWithoutCategory);

        // Radio toggle
        $('input[name="target_mode"]').on('change', toggleBlocks);

        // Compute from WITH category block:
        // - If a product picked -> type=Product, id=product_id
        // - Else if a category picked -> type=Category, id=category_id
        // - Else -> clear hidden fields
        function computeResourceableFromWithCategory() {
            const catId = $categories.val();
            const prodId = $productsInCat.val();

            if (prodId) {
                $typeInput.val(TYPE_PRODUCT);
                $idInput.val(prodId);
            } else if (catId) {
                $typeInput.val(TYPE_CATEGORY);
                $idInput.val(catId);
            } else {
                $typeInput.val('');
                $idInput.val('');
            }
        }

        // Compute from WITHOUT category block:
        // - Must be a product -> type=Product, id=product_id
        function computeResourceableFromWithoutCategory() {
            const prodId = $prodNoCat.val();
            if (prodId) {
                $typeInput.val(TYPE_PRODUCT);
                $idInput.val(prodId);
            } else {
                $typeInput.val('');
                $idInput.val('');
            }
        }

        // On submit: enforce “exactly one target”
        $form.on('submit', function (e) {
            // Refresh hidden fields before validation
            if ($modeWithCat.is(':checked')) {
                computeResourceableFromWithCategory();
            } else {
                computeResourceableFromWithoutCategory();
            }

            const type = $typeInput.val();
            const id   = $idInput.val();

            if (!type || !id) {
                e.preventDefault();
                alert('Please select a Category (or a Product in it), or a Product without category.');
                return false;
            }
        });

        // Init
        toggleBlocks();
    })();
</script>
