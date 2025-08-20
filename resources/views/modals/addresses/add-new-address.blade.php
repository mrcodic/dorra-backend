<div class="modal modal-slide-in new-user-modal fade" id="addNewAddressModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addAddressForm" class="row gy-1 gx-2" method="post"
                action="{{ route('shipping-addresses.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Add Address</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" name="user_id" value="{{ $modelId }}">
                    <div class="row my-1">
                        <div class="col-12">
                            <label class="form-label label-text">Address Label</label>
                            <input type="text" class="form-control" placeholder="Choose Address Label"
                                id="add-category-name-en" name="label" />
                            <div class="invalid-feedback" id="label-error"></div>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-6">
                            <label class="form-label label-text">Country</label>
                            <select id="modalAddressCountry" name="country_id"
                                class="form-select address-country-select">
                                <option value="" disabled selected>Select a Country</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="country_id-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label label-text">State</label>
                            <select id="modalAddressState" name="state_id" class="form-select address-state-select">
                                <option value="" disabled selected>Select a State</option>
                            </select>
                            <div class="invalid-feedback" id="state_id-error"></div>
                            <div id="state-url" data-url="{{ route('states') }}"></div>
                        </div>
                    </div>
                    <div class="row my-1">
                        <div class="col-12">
                            <label class="form-label label-text">Address Line</label>
                            <input type="text" class="form-control" placeholder="Choose Address Line"
                                id="add-category-name-en" name="line" />
                            <div class="invalid-feedback" id="line-error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
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
    $(document).ready(function () {
        // Country-State dropdown handling
        $(document).on("change", ".address-country-select", function () {
            const countryId = $(this).val();
            const stateSelect = $(".address-state-select");

            if (countryId) {
                $.ajax({
                    url: "{{ route('states') }}",
                    method: "GET",
                    data: { "filter[country_id]": countryId },
                    success: function (response) {
                        stateSelect.empty().append('<option value="">Select State</option>');
                        $.each(response.data, function (index, state) {
                            stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
                        });
                    },
                    error: function () {
                        stateSelect.empty().append('<option value="">Error loading states</option>');
                    }
                });
            } else {
                stateSelect.empty().append('<option value="">Select State</option>');
            }
        });

        // Form submission with validation
        $('#addAddressForm').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const formData = $form.serialize();
            const actionUrl = $form.attr('action');

            // Reset validation
            $('.invalid-feedback').text('').hide();
            $('.form-control, .form-select').removeClass('is-invalid');

            // Show loading state
            const saveButton = $('#saveChangesButton');
            const saveLoader = $('#saveLoader');
            const saveButtonText = $('.btn-text');

            saveButton.prop('disabled', true);
            saveLoader.removeClass('d-none');
            saveButtonText.addClass('d-none');

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                success: function (response) {
                    // Reset loading state
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');

                    // Show success toast
                    Toastify({
                        text: "Address added successfully!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                        callback: function() {
                            $('#addNewAddressModal').modal('hide');
                            $form[0].reset();
                            window.location.hash = '#tab3';
                            location.reload();
                        }
                    }).showToast();
                },
                error: function (xhr) {
                    // Reset loading state
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');

                    if (xhr.status === 422) {
                        // Handle validation errors
                        const errors = xhr.responseJSON.errors;

                        // Show individual field errors
                        $.each(errors, function (field, messages) {
                            const errorField = $(`#${field}-error`);
                            const inputField = $(`[name="${field}"]`);

                            if (errorField.length && inputField.length) {
                                inputField.addClass('is-invalid');
                                errorField.text(messages[0]).show();
                            }
                        });

                        // Show general error toast
                        Toastify({
                            text: "Please fix the validation errors",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                            close: true
                        }).showToast();
                    } else {
                        // Show general error toast
                        Toastify({
                            text: xhr.responseJSON?.message || "An error occurred while saving the address",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                            close: true
                        }).showToast();
                    }
                }
            });
        });

        // Clear validation when modal is closed
        $('#addNewAddressModal').on('hidden.bs.modal', function () {
            $('.invalid-feedback').text('').hide();
            $('.form-control, .form-select').removeClass('is-invalid');
            $('#addAddressForm')[0].reset();
        });
    });
</script>