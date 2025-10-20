<div class="modal modal-slide-in new-user-modal fade" id="editStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editStationStatusForm" method="POST" action="">
                @csrf
                @method('PUT')

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Edit Status</h5>
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
                                <select id="editProductsSelect"  class="form-select" name="resourceable_id"
                                        data-target-category-id="">
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
        const productId = $(this).val();
        if (!productId) return;

        $.ajax({
            url: "{{ route('products.categories') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}", category_ids: [productId] },
            success(res) {
                const $right = $('#editProductsSelect');
                const target = String($right.data('targetCategoryId') || '');

                $right.empty().append(new Option('— Select Category —', '', false, false));

                (res.data || []).forEach(c => {
                    // normalize ids to string
                    $right.append(new Option(c.name, String(c.id), false, false));
                });

                const evt = $right.hasClass('select2') ? 'change.select2' : 'change';
                if (target && $right.find(`option[value="${target}"]`).length) {
                    $right.val(target).trigger(evt);      // 3) preselect category now that options exist
                } else {
                    $right.val('').trigger(evt);
                }
                $right.removeData('targetCategoryId');   // cleanup
            },
            error(xhr) {
                console.error('Category load failed:', xhr.responseText);
            }
        });
    });
    // radio sync
    $('input[name="edit_product_mode"]').on('change', function () {
        editSetMode($(this).val());
    });

</script>

