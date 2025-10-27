<div class="modal modal-slide-in new-user-modal fade" id="addLocationModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="locations" method="post" enctype="multipart/form-data" action="{{ route('locations.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Location</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label for="locationName" class="form-label">Location Name</label>
                        <input type="text" class="form-control" id="locationName" name="name" placeholder="Enter name">
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Country</label>
                            <select class="form-select address-country-select" name="country_id">
                                <option value="">Select Country</option>
                                @foreach ($associatedData['countries'] as $country)
                                <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : ''
                                    }}>
                                    {{ $country->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label label-text">State</label>
                            <select id="modalAddressState" name="state_id" class="form-select address-state-select">
                                <option value="">Select a State</option>
                            </select>
                            <div class="invalid-feedback" id="state_id-error"></div>
                            <div id="state-url" data-url="{{ route('states') }}"></div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="addressLine" class="form-label">Address Line</label>
                        <input type="text" class="form-control" id="addressLine" name="address_line"
                            placeholder="Enter address">
                    </div>

                    <div class="mb-1">
                        <label for="addressLink" class="form-label">Address Link</label>
                        <div class="input-group">
{{--                            <span class="input-group-text">https://</span>--}}
                            <input type="text" class="form-control" id="addressLink" name="link"
                                placeholder="example.com/location">
                        </div>
                    </div>

                    @php
                    use App\Enums\Location\DayEnum;
                    @endphp

                    <div class="mb-1">
                        <label for="Days" class="form-label">Select Available Days</label>
                        <select class="select2 form-select" id="Days" name="days[]" multiple>
                            @foreach(DayEnum::cases() as $day)
                            <option value="{{ $day->name }}">{{ $day->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label for="available_time" class="form-label">Select Available Time Range</label>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <input type="time" id="start_time_input" class="form-control" placeholder="Start Time">
                            <input type="time" id="end_time_input" class="form-control" placeholder="End Time">
                        </div>
                        <!-- Hidden input to hold the final combined value -->
                        <input type="hidden" id="available_time" name="available_time">
                    </div>

                    <div class="modal-footer border-top-0 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span>Add</span>
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
    handleAjaxFormSubmit("#locations",{
        successMessage: "Location added Successfully",
        onSuccess: function (){
            $('#addLocationModal').modal('hide');
            location.reload();
        }
    });
    $(document).ready(function() {
        $('#start_time_input, #end_time_input').on('change', function() {
            let start = $('#start_time_input').val();
            let end = $('#end_time_input').val();
            if (start && end) {
                $('#available_time').val(start + ' - ' + end);
            }
        });
    });


</script>
<script !src="">
    $(document).ready(function () {
        $('#Days').select2();

    });
</script>
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
                        // stateSelect.empty().append('<option value="">Select State</option>');
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

    });

</script>
