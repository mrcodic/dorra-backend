<div class="modal modal-slide-in new-user-modal fade" id="addPlanModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addPlanForm" enctype="multipart/form-data" action="{{ route('plans.store') }}" method="POST">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Plan</h5>
                </div>

                <div class="modal-body pt-0">
                    <div class="row mb-2">
                        <div class="col-12 col-md-12">
                            <label for="first_name" class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="first_name">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-2">
                        <label for="desc" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="" cols="10" rows="10"></textarea>
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

                    <!-- Status -->
                    <div class="mb-2">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="statusToggle" checked>
                            <label class="form-check-label" for="statusToggle">Active</label>
                        </div>

                        <!-- hidden input sent to backend -->
                        <input type="hidden" name="is_active" id="status" value="1">
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Add</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#statusToggle').on('change', function () {
        const isActive = $(this).is(':checked');
        $('#status').val(isActive ? 1 : 0);
        $(this).next('label').text(isActive ? 'Active' : 'Inactive');
    });

    $(document).ready(function () {

        // jQuery validation
        $('#addPlanForm').validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 255
                },
                description: {
                    maxlength: 1000
                },
                price: {
                    required: true,
                    number: true,
                    min: 0
                },
                credits: {
                    required: true,
                    digits: true,
                    min: 1
                },
                status: {
                    required: true
                }
            },
            messages: {
                name: "Please enter plan name.",
                price: "Please enter a valid price.",
                credits: "Credits must be at least 1."
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            }
        });




        // ✅ Call handleAjaxFormSubmit once
        handleAjaxFormSubmit('#addPlanForm', {
            successMessage: "✅ Plan created successfully!",
            closeModal: '#addPlanModal',
            onSuccess: function (response, $form) {
                $form[0].reset();
                $form.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                $form.find('.invalid-feedback').remove();
                $(".plan-list-table").DataTable().ajax.reload(null, false);
            }
        });
    });

</script>
