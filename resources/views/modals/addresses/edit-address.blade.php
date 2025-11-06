<div class="modal modal-slide-in new-user-modal fade" id="editAddressModal-{{ $address->id }}">

    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editAddressForm-{{ $address->id }}" class="row gy-1 gx-2" method="post"
                action="{{ route('shipping-addresses.update',$address) }}">
                @csrf
                @method('PUT')
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Address</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" name="user_id" value="{{ $address->user->id }}">
                    <div class="row my-1">
                        <div class="col-12">
                            <label class="form-label label-text">Address Label</label>
                            <input type="text" class="form-control" placeholder="choose Address Label"
                                id="add-category-name-en" name="label" value="{{ $address->label  }}" />
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-4">
                            <label class="form-label label-text">Governorate</label>
                            <select id="modalAddressCountry" name="country_id" class=" form-select country-select">
                                <option value="" disabled selected>Select a Governorate</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}" @selected($country->id ==
                                    $address->state->country->id)> {{ $country->name }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-text">City</label>
                            <select id="modalAddressState" name="state_id" class="form-select state-select"
                                data-selected-id="{{ $address->state_id }}">


                                <option value="" disabled selected>Select a City</option>
                            </select>
                            <div id="state-url" data-url="{{ route('states') }}"></div>

                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-text">Zones</label>
                            <select id="modalAddressZone" name="zone_id" class="form-select address-zone-select">
                                <option value="" disabled selected>Select a Zone</option>
                            </select>
                            <div class="invalid-feedback" id="zone_id-error"></div>
                            <div id="zone-url" data-url="{{ route('zones') }}"></div>
                        </div>
                    </div>
                    <div class="row my-1">
                        <div class="col-12">
                            <label class="form-label label-text">Address Line</label>
                            <input type="text" class="form-control" placeholder="choose Address Line"
                                id="add-category-name-en" name="line" value="{{ $address->line  }}" />
                        </div>
                    </div>
                </div>




                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Edit</span>
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
        $(document).on('shown.bs.modal', '#editAddressModal-{{$address->id}}', function () {
            const $modal = $(this);
            const selectedCountry = $modal.find('.country-select').val();
            const selectedStateId = $modal.find('.state-select').data('selected-id'); // new
            loadStates($modal, selectedCountry, selectedStateId);
        });

        // On country change inside modal
        $(document).on('change', '.country-select', function () {
            const $modal = $(this).closest('.modal');
            const selectedCountry = $(this).val();
            loadStates($modal, selectedCountry);
        });
        $(document).on("change", ".address-state-select", function () {
            const stateId    = $(this).val();
            const zoneSelect = $(".address-zone-select");

            if (stateId) {
                $.ajax({
                    url: "{{ route('zones') }}",
                    method: "GET",
                    data: { "filter[state_id]": stateId },
                    success: function (response) {
                        zoneSelect.empty().append('<option value="" disabled selected>Select a Zone</option>');
                        $.each(response.data, function (_, zone) {
                            zoneSelect.append(`<option value="${zone.id}">${zone.name}</option>`);
                        });
                    },
                    error: function () {
                        zoneSelect.empty().append('<option value="">Error loading zones</option>');
                    }
                });
            } else {
                zoneSelect.empty().append('<option value="" disabled selected>Select a Zone</option>');
            }
        });

        function loadStates($modal, countryId, selectedStateId = null) {
            const stateSelect = $modal.find('.state-select');
            const baseUrl = $('#state-url').data('url');

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

        $('#editAddressForm-{{$address->id}}').on('submit', function (e) {
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
                    $('#editAddressModal-{{$address->id}}').modal('hide');
                    $form[0].reset();

                    Toastify({
                        text: "Address updated successfully!",
                        duration: 1500,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true ,
                        callback:function () {
                            window.location.hash = '#tab3';
                            location.reload()
                        }
                    }).showToast();

                },
                error: function (xhr) {
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');
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
