<div class="modal modal-slide-in new-user-modal fade" id="editLocationModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addDiscountForm" method="post" enctype="multipart/form-data" action="{{ route('discount-codes.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Location</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label for="locationName" class="form-label">Location Name</label>
                        <input type="text" class="form-control" id="locationName" name="location_name" placeholder="Enter name">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" name="country">
                                <option selected disabled>Select country</option>
                                <option value="egypt">Egypt</option>
                                <option value="ksa">KSA</option>
                                <!-- Add more countries as needed -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label for="state" class="form-label">State</label>
                            <select class="form-select" id="state" name="state">
                                <option selected disabled>Select state</option>
                                <option value="cairo">Cairo</option>
                                <option value="riyadh">Riyadh</option>
                                <!-- Add more states as needed -->
                            </select>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="addressLine" class="form-label">Address Line</label>
                        <input type="text" class="form-control" id="addressLine" name="address_line" placeholder="Enter address">
                    </div>

                    <div class="mb-1">
                        <label for="addressLink" class="form-label">Address Link</label>
                        <div class="input-group">
                            <span class="input-group-text">https://</span>
                            <input type="text" class="form-control" id="addressLink" name="address_link" placeholder="example.com/location">
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="availableDays" class="form-label">Select Available Days</label>
                        <select class="edit-select2 form-select" id="availableDaysEdit" name="available_days[]" multiple>
                            <option value="sunday">Sunday</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                            <option value="saturday">Saturday</option>
                        </select>
                    </div>

                    <div class="mb-1">
                        <label for="availableTime" class="form-label">Select Available Time</label>
                        <select class="edit-select2 form-select" id="availableTimeEdit" name="available_times[]" multiple>
                            <option value="8am-10am">8:00 AM – 10:00 AM</option>
                            <option value="10am-12pm">10:00 AM – 12:00 PM</option>
                            <option value="12pm-2pm">12:00 PM – 2:00 PM</option>
                            <option value="2pm-4pm">2:00 PM – 4:00 PM</option>
                            <option value="4pm-6pm">4:00 PM – 6:00 PM</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-top-0 d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span>Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.edit-select2').select2();
    })
</script>