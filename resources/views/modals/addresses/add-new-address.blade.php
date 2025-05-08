<!-- add new address modal -->
<div class="modal fade" id="addNewAddressModal" tabindex="-1" aria-labelledby="addNewAddressTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pb-5 px-sm-4 mx-50">
        <h1 class="address-title text-center mb-1" id="addNewAddressTitle">Add New Address</h1>
        <p class="address-subtitle text-center mb-2 pb-75">Add address for billing address</p>

        <form id="addNewAddressForm" class="row gy-1 gx-2" method="post" action="{{ route('shipping-addresses.update',$address) }}">
            @csrf
            @method('PUT')
            <div class="col-12">
                <label class="form-label" for="modalAddressAddress1">Address Label</label>
                <input
                    type="text"
                    id="modalAddressAddress1"
                    name="label"
                    class="form-control"
                    placeholder="Home or Office,etc"
                />
            </div>
            <div class="col-12">
                <label class="form-label" for="modalAddressAddress2">Address Line</label>
                <input
                    type="text"
                    id="modalAddressAddress2"
                    name="line"
                    class="form-control"
                    placeholder="12, Business Park"
                />
            </div>
          <div class="col-12">
            <label class="form-label" for="modalAddressCountry">Country</label>
            <select id="modalAddressCountry" name="country_id" class="select2 form-select country-select">
              <option value="">Select a Country</option>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}"> {{ $country->name }}</option>
                @endforeach

            </select>
          </div>

            <div class="col-12">
            <label class="form-label" for="modalAddressCountry">State</label>
            <select id="modalAddressCountry" name="state_id" class="select2 form-select state-select">
              <option value="">Select a State</option>
            </select>
          </div>
            <div id="state-url" data-url="{{ route('states') }}"></div>
            <input type="hidden" name="user_id" value="{{ $address->user->id }}">


          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-1 mt-2">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-2" data-bs-dismiss="modal" aria-label="Close">
              Discard
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- / add new address modal -->
