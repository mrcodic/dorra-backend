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
                        <input type="text" class="form-control" name="name" placeholder="e.g. Printing Started"
                               required>
                    </div>


                    {{-- Station (required) --}}
                    <div class="mb-2">
                        <label class="form-label label-text">Station</label>
                        {{-- Static options version (uncomment if you pass $stations) --}}

                        <select class="form-select" name="station_id" required>
                            <option value="" selected disabled>— Select Station —</option>
                            @foreach ($associatedData['stations'] ?? [] as $station)
                                <option value="{{ $station->id }}">{{ $station->name }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="mb-2">
                        <label class="form-label label-text d-block mb-1">Products Source</label>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="product_mode" id="mode_with" value="with"
                                   checked>
                            <label class="form-check-label" for="mode_with">With Categories</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="product_mode" id="mode_without"
                                   value="without">
                            <label class="form-check-label" for="mode_without">Without Categories</label>
                        </div>
                    </div>
                    <div id="withCategoriesWrap">
                        <input type="hidden" name="resourceable_type" value="{{ \App\Models\Product::class }}">

                        <div class="row">
                            <div class="col-md-6 form-group mb-2">
                                <label for="categoriesSelect" class="label-text mb-1">Products With Categories</label>
                                <select id="categoriesSelect" class="form-select" 
                                >
                                    <option value="" selected disabled>— Select Product —</option>
                                    @foreach($associatedData['product_with_categories'] as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                <label for="productsSelect" class="label-text mb-1">Categories</label>
                                <select id="productsSelect" class="form-select" name="resourceable_id"
                                >
                                    <option value="" selected disabled>— Select Category —</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="withoutCategoriesWrap" class="d-none">
                        <input type="hidden" name="resourceable_type" value="{{ \App\Models\Category::class }}">
                        <div class="form-group mb-2">
                            <label for="productsWithoutCategoriesSelect" class="label-text mb-1">Products Without
                                Categories</label>
                            <select id="productsWithoutCategoriesSelect" class="form-select " name="resourceable_id"
                            >
                                <option value="" selected disabled>— Select Product —</option>
                                @foreach($associatedData['product_without_categories'] as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->getTranslation('name', app()->getLocale()) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span class="btn-text">Save</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none" role="status"
                                  aria-hidden="true"></span>
                        </button>
                    </div>
            </form>

        </div>
    </div>
</div>
<script>
    // keep your existing AJAX handler for #categoriesSelect
    $('#categoriesSelect').on('change', function () {
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
                    const $productsSelect = $('#productsSelect');
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

    // NEW: radio-driven toggling
    function setMode(mode) {
        const $withWrap = $('#withCategoriesWrap');
        const $withoutWrap = $('#withoutCategoriesWrap');

        const $categoriesSelect = $('#categoriesSelect');
        const $productsSelect = $('#productsSelect');
        const $productsWithout = $('#productsWithoutCategoriesSelect');

        if (mode === 'with') {
            // show with-categories, hide without
            $withWrap.removeClass('d-none');
            $withoutWrap.addClass('d-none');

            // required/disabled
            $categoriesSelect.prop('disabled', false).prop('required', true);
            $productsSelect.prop('disabled', false);

            // clear and disable the other group
            $productsWithout.val(null).trigger('change');
            $productsWithout.prop('disabled', true).prop('required', false);
        } else {
            // show without-categories, hide with
            $withWrap.addClass('d-none');
            $withoutWrap.removeClass('d-none');

            // disable with-categories fields
            $categoriesSelect.val(null).trigger('change');
            $productsSelect.val(null).trigger('change');

            $categoriesSelect.prop('disabled', true).prop('required', false);
            $productsSelect.prop('disabled', true);

            // enable without-categories
            $productsWithout.prop('disabled', false).prop('required', true);
        }
    }

    // init (default to "with")
    setMode($('input[name="product_mode"]:checked').val());

    // listen for radio changes
    $('input[name="product_mode"]').on('change', function () {
        setMode($(this).val());
    });

    handleAjaxFormSubmit("#addStationStatusForm", {
        successMessage: "Status added Successfully.",
        onSuccess:function () {
            location.reload()
        }
    })
</script>

