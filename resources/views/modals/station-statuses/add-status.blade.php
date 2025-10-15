<div class="modal modal-slide-in new-user-modal fade" id="addStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            {{-- ✅ Update action to your real route --}}
            <form id="addStationStatusForm" method="POST" action="{{ route('station-statuses.store') }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Add New Station Status</h5>
                </div>

                <div class="modal-body flex-grow-1">

                        <div class="col-md-12">
                            <label class="form-label label-text">Name </label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Printing Started" required>
                        </div>


                    {{-- Station (required) --}}
                    <div class="mb-2">
                        <label class="form-label label-text">Station</label>
                        {{-- Static options version (uncomment if you pass $stations) --}}

                        <select class="form-select" name="station_id" required>
                          <option value="">— Select Station —</option>
                          @foreach ($associatedData['stations'] ?? [] as $station)
                            <option value="{{ $station->id }}">{{ $station->name }}</option>
                          @endforeach
                        </select>

                    </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-2">
                        <label for="categoriesSelect" class="label-text mb-1">Products With Categories</label>
                        <select id="categoriesSelect" class="form-select" name="product_with_category"
                                >
                            @foreach($associatedData['product_with_categories'] as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->getTranslation('name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label for="productsSelect" class="label-text mb-1">Categories</label>
                        <select id="productsSelect" class="form-select" name="product_ids[]"
                                >

                        </select>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label for="productsWithoutCategoriesSelect" class="label-text mb-1">Products Without Categories</label>
                    <select id="productsWithoutCategoriesSelect" class="form-select " name="category_ids[]"
                            >
                        @foreach($associatedData['product_without_categories'] as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->getTranslation('name', app()->getLocale()) }}
                            </option>
                        @endforeach
                    </select>
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
    // Listen for change on "Products With Categories"
    $('#categoriesSelect').on('change', function () {
        let selectedIds = $(this).val();

        if (selectedIds && selectedIds.length > 0) {
            $.ajax({
                url: "{{ route('products.categories') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    category_ids: [selectedIds]
                },
                success: function (response) {
                    const $productsSelect = $('#productsSelect');

                    // Save current selections
                    let currentValues = $productsSelect.val() || [];

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (category) {
                            // Only add if it doesn’t already exist
                            if ($productsSelect.find('option[value="' + category.id + '"]').length === 0) {
                                let option = new Option(category.name, category.id, false, false);
                                $productsSelect.append(option);
                            }
                        });
                    }

                    // Restore selections
                    $productsSelect.val(currentValues).trigger('change');
                },
                error: function (xhr) {
                    console.error("Error fetching categories:", xhr.responseText);
                }
            });
        }
    });

</script>
