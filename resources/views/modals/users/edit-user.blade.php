<!-- Edit User Modal -->
<div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-edit-user">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pb-5 px-sm-5 pt-50">
        <div class="text-center mb-2">
          <h1 class="mb-1">Edit User Information</h1>
          <p>Updating user details will receive a privacy audit.</p>
        </div>
        <form id="editUserForm" class="row gy-1 pt-75" method="post" action="{{ route("users.update",['user' => $user]) }}">
            @csrf
            @method("PUT")
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserFirstName">First Name</label>
            <input
              type="text"
              id="modalEditUserFirstName"
              name="first_name"
              class="form-control"
              placeholder="John"
              value="{{ $user->first_name }}"
              data-msg="Please enter your first name"
            />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserLastName">Last Name</label>
            <input
              type="text"
              id="modalEditUserLastName"
              name="last_name"
              class="form-control"
              placeholder="Doe"
              value="{{ $user->last_name }}"
              data-msg="Please enter your last name"
            />
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserEmail">Email</label>
            <input
              type="text"
              id="modalEditUserEmail"
              name=email
              class="form-control"
              value="{{ $user->email }}"
              placeholder="example@domain.com"
            />
          </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="phone_number">Phone Number</label>
                <div class="input-group">
                    <!-- Phone Code Select -->
                        <select class="form-select" id="phone-code" name="country_code_id">
                            @foreach($countryCodes as $countryCode)
                                <option value="{{ $countryCode->id }}" @selected($countryCode->id == $user->countryCode?->id)>
                                    {{ $countryCode->phone_code }} ({{ $countryCode->iso_code }})
                                </option>
                            @endforeach

                            <!-- Add more countries as needed -->
                        </select>

                    <!-- Phone Number Input -->
                    <input
                        type="text"
                        id="phone_number"
                        class="form-control dt-contact"
                        placeholder="(609) 933-44-22"
                        name="phone_number"
                        value="{{$user->phone_number}}"
                    />
                    <input type="hidden" name="full_phone_number"  id="full_phone_number" />
                </div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserStatus">Status</label>
            <select
              id="modalEditUserStatus"
              name="status"
              class="form-select"
              aria-label="Default select example"
            >
              <option selected disabled>Status</option>
              <option value="1" @selected($user->status == "Active")>Active</option>
              <option value="0" @selected($user->status == "Blocked")>Blocked</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditTaxID">Image</label>
            <input
              type="file"
              id="modalEditTaxID"
              name="image"
              class="form-control modal-edit-tax-id"

            />
          </div>

          <div class="col-12 text-center mt-2 pt-50">
            <button type="submit" class="btn btn-primary me-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">
              Discard
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit User Modal -->
