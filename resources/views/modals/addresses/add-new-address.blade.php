<div class="modal modal-slide-in new-user-modal fade" id="addNewAddressModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addAddressForm" class="row gy-1 gx-2" method="post" action="{{ route('shipping-addresses.store') }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Add Address</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" name="user_id" value="{{ $model->id }}">
                    <div class="row my-3">
                        <div class="col-12">
                            <label class="form-label label-text">Address Label</label>
                            <input type="text" class="form-control"  placeholder="choose Address Label"
                                   id="add-category-name-en" name="label"/>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label label-text">Country</label>
                            <select id="modalAddressCountry" name="country_id" class=" form-select address-country-select">
                                <option value="">Select a Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}"> {{ $country->name }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">State</label>
                            <select id="modalAddressState" name="state_id" class="form-select address-state-select">

                                <option value="" >Select a State</option>
                            </select>
                            <div id="state-url" data-url="{{ route('states') }}"></div>

                        </div>
                    </div>
                    <div class="row my-3">
                        <div class="col-12">
                            <label class="form-label label-text">Address Line</label>
                            <input type="text" class="form-control"  placeholder="choose Address Line"
                                   id="add-category-name-en" name="line"  />
                        </div>
                    </div>
                </div>




                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Add</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {

        $(document).on("change", ".address-country-select", function () {

            const countryId = $(this).val();
            const stateSelect = $(".address-state-select");
            if (countryId) {
                $.ajax({
                    url: "{{ route('states') }}",  // Make sure this is wrapped in quotes for the URL
                    method: "GET",
                    data: {
                        "filter[country_id]": countryId  // Corrected way to pass the data
                    },
                    success: function (response) {
                        stateSelect
                            .empty()
                            .append('<option value="">Select State</option>');
                        $.each(response.data, function (index, state) {
                            stateSelect.append(
                                `<option value="${state.id}">${state.name}</option>`
                            );
                        });
                    },
                    error: function () {
                        stateSelect
                            .empty()
                            .append('<option value="">Error loading states</option>');
                    },
                });

            } else {
                stateSelect
                    .empty()
                    .append('<option value="">Select State</option>');
            }
        });



        $('#addAddressForm').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const formData = $form.serialize();
            const actionUrl = $form.attr('action');

            const saveButton = $('.saveChangesButton');
            const saveLoader = $('.saveLoader');
            const saveButtonText = $('.saveChangesButton .btn-text');
            saveButton.prop('disabled', true);
            saveLoader.removeClass('d-none');
            saveButtonText.addClass('d-none');

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                success: function (response) {
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');
                    $('#addNewAddressModal').modal('hide');
                    $form[0].reset();
                    Toastify({
                        text: "Address added successfully!",
                        duration: 1500,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                        callback:function () {
                            window.location.hash = '#tab3';
                            location.reload()
                        }
                    }).showToast();

                },
                error: function (xhr) {
                    $('#saveLoader').addClass('d-none');


                    // Handle validation errors or server error
                    let errorMsg = 'An error occurred while updating the address.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        });
    });
</script>

