<div class="modal modal-slide-in new-user-modal fade" id="editCodeModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editDiscountForm" method="post" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Discount Code</h5>
                </div>
                <input type="hidden" id="codeId">
                <div class="modal-body flex-grow-1">
                    <div class="form-group mb-2">
                        <label for="discountType" class="label-text mb-1">Type</label>
                        <input type="text" id="discountType" class="form-control" disabled>

                    </div>

                    <div class="form-group mb-2">
                        <label for="prefix" class="label-text mb-1">Prefix</label>
                        <input type="text" name="code" id="prefix" class="form-control" placeholder="Add prefix here"
                            disabled>
                    </div>

                    <div class="form-group mb-2">
                        <label for="discountValue" class="label-text mb-1">Discount Value</label>
                        <input type="text" name="value" id="discountValue" class="form-control"
                            placeholder="Enter discount value here" disabled>
                    </div>


                    <div class="d-flex flex-column flex-md-row gap-1">
                        <div class="form-group col-12 col-md-6">
                            <label for="restrictions" class="label-text mb-1">Restrictions</label>
                            <input type="number" name="max_usage" id="restrictions" class="form-control"
                                placeholder="Enter number of usage times">
                        </div>
                        <div class="form-group mb-2 col-12 col-md-6">
                            <label for="expiryDate" class="label-text mb-1">Expiry Date</label>
                            <input type="date" name="expired_at" id="expiryDate" class="form-control">
                        </div>
                    </div>

                    <!-- Radio switch for Products or Categories -->
                    <div class="form-group mb-2">
                        <label for="scopeType" class="label-text mb-1">Type</label>
                        <input type="text" id="scopeType" class="form-control" disabled>

                    </div>

                    <div class="form-group mb-2">
                        <label class="label-text mb-1">Product</label>
                        <div id="selectedProducts" class="d-flex flex-wrap gap-1"></div>
                    </div>

                    <div class="form-group mb-2">
                        <label class="label-text mb-1">Category</label>
                        <div id="selectedCategories" class="d-flex flex-wrap gap-1"></div>
                    </div>


                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span>Save Changes</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                role="status" aria-hidden="true"></span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#editDiscountForm').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(this);
            let actionUrl = form.attr('action');

            const saveButton = $('.saveChangesButton');
            const loader = $('.saveLoader');

            // Show loader and disable button
            loader.removeClass('d-none');
            saveButton.prop('disabled', true);

            $.ajax({
                url: actionUrl,
                type: 'POST',
                headers: { 'X-HTTP-Method-Override': 'PUT' }, // Laravel method spoofing
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Toastify({
                        text: "Code updated successfully!",
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28C76F",
                        close: true,
                    }).showToast();

                    $('#editCodeModal').modal('hide');
                    form[0].reset();
                    $(".code-list-table").DataTable().ajax.reload();
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON?.errors;

                    if (errors) {
                        Object.values(errors).forEach(errorArray => {
                            errorArray.forEach(message => {
                                Toastify({
                                    text: message,
                                    duration: 4000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#EA5455",
                                    close: true,
                                }).showToast();
                            });
                        });
                    } else {
                        Toastify({
                            text: "Something went wrong. Please try again.",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true,
                        }).showToast();
                    }
                },
                complete: function () {
                    // Always hide loader and enable button
                    loader.addClass('d-none');
                    saveButton.prop('disabled', false);
                }
            });
        });
    });
</script>