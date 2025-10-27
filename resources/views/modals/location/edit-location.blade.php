{{-- EDIT MODAL --}}
<div class="modal modal-slide-in new-user-modal fade" id="editLocationModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editLocationForm" method="post" enctype="multipart/form-data" action="">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Edit Location</h5>
                </div>

                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="editLocationName">Location Name</label>
                        <input type="text" class="form-control" id="editLocationName" name="name" placeholder="Enter name">
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Country</label>
                            <select class="form-select address-country-select" name="country_id" id="editCountry">
                                <option value="">Select Country</option>
                                @foreach ($associatedData['countries'] as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">State</label>
                            <select id="editState" name="state_id" class="form-select address-state-select" data-url ="{{ route("states") }}">
                                <option value="">Select a State</option>
                            </select>
                            <div class="invalid-feedback" id="state_id-error"></div>
                            <div id="state-url" data-url="{{ route('states') }}"></div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="editAddressLine">Address Line</label>
                        <input type="text" class="form-control" id="editAddressLine" name="address_line" placeholder="Enter address">
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="editAddressLink">Address Link</label>
                        <div class="input-group">
{{--                            <span class="input-group-text">https://</span>--}}
                            <input type="text" class="form-control" id="editAddressLink" name="link" placeholder="example.com/location">
                        </div>
                    </div>

                    @php use App\Enums\Location\DayEnum; @endphp
                    <div class="mb-1">
                        <label class="form-label" for="editDays">Select Available Days</label>
                        <select class="select2 form-select" id="editDays" name="days[]" multiple>
                            @foreach(DayEnum::cases() as $day)
                                <option value="{{ $day->name }}">{{ $day->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Select Available Time Range</label>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <input type="time" id="edit_start_time" class="form-control" placeholder="Start Time">
                            <input type="time" id="edit_end_time" class="form-control" placeholder="End Time">
                        </div>
                        <input type="hidden" id="edit_available_time" name="available_time">
                    </div>

                    <div class="modal-footer border-top-0 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fs-5">
                            <span>Save Changes</span>
                            <span class="spinner-border spinner-border-sm d-none" id="editSaveLoader" aria-hidden="true"></span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    $(function () {
        $('#editDays').select2({
            width: '100%',
            placeholder: 'Select Available Days',
            dropdownParent: $('#editLocationModal')
        });
        $('#editCountry, #editState').select2({
            width: '100%',
            dropdownParent: $('#editLocationModal')
        });
    });

</script>
