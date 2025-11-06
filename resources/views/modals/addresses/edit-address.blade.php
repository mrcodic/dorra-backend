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
                                    $address->zone->state->country->id)> {{ $country->name }}</option>
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
                            <select id="modalAddressZone" name="zone_id" class="form-select address-zone-select"
                                    data-selected-id="{{ $address->zone_id }}"
                            >
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
        // When the edit modal opens, load states (and then zones) with preselected values
        $(document).on('shown.bs.modal', '#editAddressModal-{{ $address->id }}', function () {
            const $modal          = $(this);
            const countryId       = $modal.find('.country-select').val();
            const selectedStateId = $modal.find('.state-select').data('selected-id') || null;
            const selectedZoneId  = $modal.find('.address-zone-select').data('selected-id') || null;

            loadStates($modal, countryId, selectedStateId, function () {
                if (selectedStateId) {
                    loadZones($modal, selectedStateId, selectedZoneId);
                }
            });
        });

        // Country → States (and reset zones)
        $(document).on('change', '.country-select', function () {
            const $modal = $(this).closest('.modal');
            const countryId = $(this).val();
            // reset zones immediately
            $modal.find('.address-zone-select')
                .empty()
                .append('<option value="" disabled selected>Select a Zone</option>');

            loadStates($modal, countryId);
        });

        // State → Zones  ✅ (fixed selector + scoping)
        $(document).on('change', '.state-select', function () {
            const $modal = $(this).closest('.modal');
            const stateId = $(this).val();
            loadZones($modal, stateId);
        });

        // ---- helpers ---------------------------------------------------------

        function loadStates($modal, countryId, selectedStateId = null, done = () => {}) {
            const stateSelect = $modal.find('.state-select');
            const baseUrl = $modal.find('#state-url').data('url'); // scoped to this modal

            if (!countryId) {
                stateSelect.empty().append('<option value="" disabled selected>Select a State</option>');
                return done();
            }

            $.ajax({
                url: `${baseUrl}?filter[country_id]=${countryId}`,
                method: 'GET',
                success: function (response) {
                    stateSelect.empty().append('<option value="" disabled selected>Select a State</option>');
                    $.each(response.data, function (_, state) {
                        const selected = selectedStateId && (String(state.id) === String(selectedStateId)) ? 'selected' : '';
                        stateSelect.append(`<option value="${state.id}" ${selected}>${state.name}</option>`);
                    });
                    done();
                },
                error: function () {
                    stateSelect.empty().append('<option value="">Error loading states</option>');
                    done();
                }
            });
        }

        function loadZones($modal, stateId, selectedZoneId = null) {
            const zoneSelect = $modal.find('.address-zone-select');
            const baseUrl = $modal.find('#zone-url').data('url'); // scoped to this modal

            if (!stateId) {
                zoneSelect.empty().append('<option value="" disabled selected>Select a Zone</option>');
                return;
            }

            $.ajax({
                url: `${baseUrl}?filter[state_id]=${stateId}`,
                method: 'GET',
                success: function (response) {
                    zoneSelect.empty().append('<option value="" disabled selected>Select a Zone</option>');
                    $.each(response.data, function (_, zone) {
                        const selected = selectedZoneId && (String(zone.id) === String(selectedZoneId)) ? 'selected' : '';
                        zoneSelect.append(`<option value="${zone.id}" ${selected}>${zone.name}</option>`);
                    });
                },
                error: function () {
                    zoneSelect.empty().append('<option value="">Error loading zones</option>');
                }
            });
        }

        // Submit (unchanged)
        $('#editAddressForm-{{ $address->id }}').on('submit', function (e) {
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
                success: function () {
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');
                    $('#editAddressModal-{{ $address->id }}').modal('hide');
                    $form[0].reset();
                    Toastify({
                        text: "Address updated successfully!",
                        duration: 1500,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                        callback: function () {
                            window.location.hash = '#tab3';
                            location.reload();
                        }
                    }).showToast();
                },
                error: function (xhr) {
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');
                    let errorMsg = 'An error occurred while updating the address.';
                    if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                    alert(errorMsg);
                }
            });
        });
    });

</script>
