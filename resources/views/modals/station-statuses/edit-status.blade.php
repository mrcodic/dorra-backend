<div class="modal modal-slide-in new-user-modal fade" id="editStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editStationStatusForm" method="POST" action="">
                @csrf
                @method('PUT')

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Edit Station Status</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    <input type="hidden" id="edit_status_id" value="">
                    <input type="hidden" name="resourceable_type" id="editResourceableType" value="">

                    <div class="col-md-12 mb-2">
                        <label class="form-label label-text">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label label-text">Station</label>
                        <select class="form-select" name="station_id" id="edit_station_id" required>
                            <option value="" disabled>— Select Station —</option>
                            @foreach ($associatedData['stations'] ?? [] as $station)
                                <option value="{{ $station->id }}">{{ $station->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label label-text d-block mb-1">Products Source</label>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="edit_product_mode" id="edit_mode_with" value="with">
                            <label class="form-check-label" for="edit_mode_with">With Categories</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="edit_product_mode" id="edit_mode_without" value="without">
                            <label class="form-check-label" for="edit_mode_without">Without Categories</label>
                        </div>
                    </div>

                    <!-- With Categories -->
                    <div id="editWithCategoriesWrap" class="d-none">
                        <div class="row">
                            <div class="col-md-6 form-group mb-2">
                                <label for="editCategoriesSelect" class="label-text mb-1">Products With Categories</label>
                                <select id="editCategoriesSelect" class="form-select">
                                    <option value="" disabled>— Select Product —</option>
                                    @foreach($associatedData['product_with_categories'] as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                <label for="editProductsSelect" class="label-text mb-1">Categories</label>
                                <select id="editProductsSelect" class="form-select" name="resourceable_id">
                                    <option value="" disabled>— Select Category —</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Without Categories -->
                    <div id="editWithoutCategoriesWrap" class="d-none">
                        <div class="form-group mb-2">
                            <label for="editProductsWithoutCategoriesSelect" class="label-text mb-1">Products Without Categories</label>
                            <select id="editProductsWithoutCategoriesSelect" class="form-select" name="resourceable_id">
                                <option value="" disabled>— Select Product —</option>
                                @foreach($associatedData['product_without_categories'] as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->getTranslation('name', app()->getLocale()) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5" id="editSaveChangesButton">
                        <span class="btn-text">Update</span>
                        <span id="editSaveLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    $('#editCategoriesSelect').on('change', function () {
        let selectedId = $(this).val();
        if (selectedId) {
            $.ajax({
                url: "{{ route('products.categories') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    category_ids: [selectedId]
                },
                success: function (response) {
                    const $productsSelect = $('#editProductsSelect');
                    let currentValues = $productsSelect.val() || [];

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (category) {
                            if ($productsSelect.find('option[value="' + category.id + '"]').length === 0) {
                                let option = new Option(category.name, category.id, false, false);
                                $productsSelect.append(option);
                            }
                        });
                    }

                    $productsSelect.val(currentValues).trigger('change');
                },
                error: function (xhr) {
                    console.error("Error fetching categories:", xhr.responseText);
                }
            });
        }
    });

</script>
<script>
    function editSetMode(mode) {
        const $withWrap     = $('#editWithCategoriesWrap');
        const $withoutWrap  = $('#editWithoutCategoriesWrap');
        const $leftProducts = $('#editCategoriesSelect');                // left (products-with-categories)
        const $rightCats    = $('#editProductsSelect');                  // right (categories)
        const $productsNo   = $('#editProductsWithoutCategoriesSelect'); // products-without-categories
        const $type         = $('#editResourceableType');

        if (mode === 'with') {
            $withWrap.removeClass('d-none');
            $withoutWrap.addClass('d-none');

            $leftProducts.prop('disabled', false).prop('required', true);
            $rightCats.prop('disabled', false).prop('required', true);

            $productsNo.val(null).trigger('change');
            $productsNo.prop('disabled', true).prop('required', false);

            // Category::class
            $type.val(@json(\App\Models\Product::class));
        } else {
            $withWrap.addClass('d-none');
            $withoutWrap.removeClass('d-none');

            $leftProducts.val(null).trigger('change').prop('disabled', true).prop('required', false);
            $rightCats.val(null).trigger('change').prop('disabled', true).prop('required', false);

            $productsNo.prop('disabled', false).prop('required', true);

            // Product::class
            $type.val(@json(\App\Models\Category::class));
        }
    }
</script>
