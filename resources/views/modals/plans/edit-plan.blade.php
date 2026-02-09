<div class="modal modal-slide-in new-user-modal fade" id="editPlanModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editPlanForm" enctype="multipart/form-data" action="" method="POST">
                @csrf
                @method("PUT")
                <div class="edit-avatar-media-ids"></div> <!-- hidden input gets appended here -->

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Plan</h5>
                </div>

                <div class="modal-body pt-0">
                    <div class="row mb-2">
                        <div class="col-12 col-md-12">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="name">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-2">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" class="form-control" name="description" id="" cols="10" rows="10"></textarea>
                    </div>
                    <!-- Recommended For -->
                    <div class="mb-2">
                        <label class="form-label">Recommended For</label>
                        <textarea class="form-control" name="recommended_for" id="edit_recommended_for" cols="10" rows="6"></textarea>
                    </div>


                    <div class="row mb-2">
                        <div class="col-12 col-md-6">
                            <label for="credits" class="form-label">Credits</label>
                            <input type="number" class="form-control" name="credits" id="credits">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" id="price">
                        </div>

                    </div>
                    {{-- Features --}}
                    <div class="mt-2">
                        <h6 class="mb-1">Features</h6>
                        <small class="text-muted">Add feature descriptions for this plan.</small>

                        <div class="invoice-repeater mt-1" id="editFeaturesRepeater">
                            <div data-repeater-list="features" id="editFeaturesList">

                                {{-- template row (will be replaced) --}}
                                <div data-repeater-item class="row g-1 align-items-end mb-1">
                                    <div class="col-12 col-md-11">
                                        <label class="form-label">Description</label>
                                        <input type="text" name="description" class="form-control" required>
                                    </div>

                                    <div class="col-12 col-md-1 d-flex justify-content-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-repeater-delete>
                                            <i data-feather="x"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm mt-1" data-repeater-create>
                                <i data-feather="plus"></i> Add Feature
                            </button>
                        </div>
                    </div>
                    <!-- Status -->
                    <div class="mb-2">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editStatusToggle" checked>
                            <label class="form-check-label" for="statusToggle">Active</label>
                        </div>

                        <!-- hidden input sent to backend -->
                        <input type="hidden" name="is_active" id="status" value="1">
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveChangesButton">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script >
    function initEditRepeater() {
        const $rep = $('#editPlanModal .invoice-repeater');
        if (!$rep.length) return;

        if (!$.fn.repeater) {
            console.error('Repeater plugin not loaded');
            return;
        }

        if ($rep.data('repeater-initialized')) return;
        $rep.data('repeater-initialized', true);

        $rep.repeater({
            initEmpty: false,
            show: function () {
                $(this).slideDown();
                if (window.feather) feather.replace();
                // toggleFirstDeleteBtn($rep);
            },
            hide: function (deleteElement) {
                $(this).slideUp(deleteElement);
                // toggleFirstDeleteBtn($rep);
            }
        });

        toggleFirstDeleteBtn($rep);
        if (window.feather) feather.replace();
    }

    function toggleFirstDeleteBtn($rep) {
        const items = $rep.find('[data-repeater-item]');
        items.each(function (i) {
            $(this).find('[data-repeater-delete]').toggle(i !== 0);
        });
    }

    $(document).on('shown.bs.modal', '#editPlanModal', function () {
        initEditRepeater();
    });
</script>
<script>
    $('#editStatusToggle').on('change', function () {
        const isActive = $(this).is(':checked');
        console.log(isActive)
        $('#status').val(isActive ? 1 : 0);
        $(this).next('label').text(isActive ? 'Active' : 'Inactive');
    });


        handleAjaxFormSubmit('#editPlanForm', {
            successMessage: "✅ Plan updated successfully!",
            closeModal: '#editPlanModal',
            resetForm: false,
            onSuccess: function (response, $form) {
                $(".plan-list-table").DataTable().ajax.reload(null, false); // false = stay on current page
            }
        });

</script>
