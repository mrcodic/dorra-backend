<div class="modal modal-slide-in new-user-modal fade" id="editAddressModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editAddressForm" class="row gy-1 gx-2" method="post" action="{{ route('shipping-addresses.update',$address) }}">
                @csrf
                @method('PUT')
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Address</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" name="user_id" value="{{ $address->user->id }}">
                    <div class="row my-3">
                        <div class="col-12">
                            <label class="form-label label-text">Address Label</label>
                            <input type="text" class="form-control"  placeholder="choose Address Label"
                                   id="add-category-name-en" name="label" value="{{ $address->label  }}" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label label-text">Country</label>
                            <select id="modalAddressCountry" name="country_id" class=" form-select country-select">
                                <option value="">Select a Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" @selected($country->id == $address->state->country->id)> {{ $country->name }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">State</label>
                            <select id="modalAddressState" name="state_id" class="form-select state-select">

                            <option value="" >Select a State</option>
                            </select>
                            <div id="state-url" data-url="{{ route('states') }}"></div>

                        </div>
                    </div>
                    <div class="row my-3">
                        <div class="col-12">
                            <label class="form-label label-text">Address Line</label>
                            <input type="text" class="form-control"  placeholder="choose Address Line"
                                   id="add-category-name-en" name="line"  value="{{ $address->label  }}"/>
                        </div>
                    </div>
                </div>




                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Edit</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        // Function to populate states
        function loadStates(countryId, selectedStateId = null) {
            const baseUrl = $('#state-url').data('url');
            const stateSelect = $('.state-select');

            if (countryId) {
                $.ajax({
                    url: `${baseUrl}?filter[country_id]=${countryId}`,
                    method: 'GET',
                    success: function (response) {
                        stateSelect.empty().append('<option value="">Select a State</option>');
                        $.each(response.data, function (index, state) {
                            const selected = state.id == selectedStateId ? 'selected' : '';
                            stateSelect.append(`<option value="${state.id}" ${selected}>${state.name}</option>`);
                        });
                    },
                    error: function () {
                        stateSelect.empty().append('<option value="">Error loading states</option>');
                    }
                });
            } else {
                stateSelect.empty().append('<option value="">Select a State</option>');
            }
        }

        // When country is changed
        $(document).on('change', '.country-select', function () {
            const selectedCountry = $(this).val();
            loadStates(selectedCountry);
        });

        // When modal is shown — prefill the state dropdown
        $('#editAddressModal').on('shown.bs.modal', function () {
            const selectedCountry = $('.country-select').val();
            const selectedStateId = `{{ $address->state_id }}`; // Pass in from backend
            loadStates(selectedCountry, selectedStateId);
        });

        $('#editAddressForm').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const formData = $form.serialize();
            const actionUrl = $form.attr('action');

            $('#saveLoader').removeClass('d-none');

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                success: function (response) {
                    $('#saveLoader').addClass('d-none');
                    $('#saveChangesButton .btn-text').text('Edit');
                    $('#editAddressModal').modal('hide');

                    Toastify({
                        text: "Address updated successfully!",
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true ,
                        callback: function () {
                            location.reload();
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

