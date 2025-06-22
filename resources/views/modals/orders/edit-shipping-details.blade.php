<div class="modal modal-slide-in new-user-modal fade" id="editOrderShippingModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editTagForm" enctype="multipart/form-data" action="{{ route('orders.edit-shipping-addresses', ['order' => $model->id]) }}">>
                @csrf
                @method('PUT')
                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Shipping Details</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" id="edit-tag-id">

                    <input type="hidden" name="order_id" value="{{ $model->id }}">

                    <!-- Shipping Method Selection -->
                    <div class="mb-3" id="shippingMethodSection">
                        <label class="form-label fw-bold fs-5 mb-2">Shipping Method</label>
                        <div class="d-flex gap-2 ">
                            <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                <input class="form-check-input" type="radio" name="shipping_method"
                                    id="shipToCustomer" value="ship" checked>
                                <label class="form-check-label fs-4 text-black" for="shipToCustomer">
                                    Ship to customer
                                </label>
                            </div>
                            <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                <input class="form-check-input" type="radio" name="shipping_method" id="pickUp"
                                    value="pickup">
                                <label class="form-check-label fs-4 text-black" for="pickUp">
                                    Pick up
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Ship to Customer Section -->
                    {{-- <div id="shipSection">
                        <!-- Existing Addresses -->
                        <div class="d-flex gap-2 ">
                            <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                <input class="form-check-input" type="radio" name="address_id" id="address1" value="1">
                                <label class="form-check-label fs-4 text-black" for="shipToCustomer">

                                    <p>Home</p>
                                    <p class="text-dark fs-16">15 street name, neighborhood</p>
                                </label>
                            </div>
                            <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                <input class="form-check-input" type="radio" name="address_id" id="address2" value="2">
                                <label class="form-check-label fs-4 text-black" for="pickUp">
                                    <p>Office</p>
                                    <p class="text-dark fs-16">15 street name, neighborhood</p>
                                </label>
                            </div>
                        </div> --}}

                   
                    @php
                        $shippingAddressId = $model->OrderAddress->firstWhere('shipping_address_id', '!=', null)?->shipping_address_id;
                    @endphp
                    

                    <div id="shipSection">
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach ($model->user->addresses as $address)
                                <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                    <input class="form-check-input" type="radio" name="shipping_address_id"
                                        id="address{{ $address->id }}" value="{{ $address->id }}"
                                        {{ $shippingAddressId == $address->id ? 'checked' : '' }}>
                                    <label class="form-check-label fs-4 text-black" for="address{{ $address->id }}">
                                        <p>{{ $address->label ?? 'No Label' }}</p>
                                        <p class="text-dark fs-16">
                                            {{ $address->line ?? 'No Address' }}
                                            @if($address->relationLoaded('state') && $address->state)
                                                , {{ $address->state->name }}
                                                @if($address->state->relationLoaded('country') && $address->state->country)
                                                    , {{ $address->state->country->name }}
                                                @endif
                                            @endif
                                        </p>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="text-center my-3 fw-bold">OR</div>

                    <div class="">


                        <div id="pickupSection" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="form-label ">Pick up Location</h5>
                                <button type="button" class="lined-btn">Change Location</button>
                            </div>
                            <div class="border rounded-3 p-1 mb-2">
                                <p class="text-black">Office</p>
                                <p>789 Store Blvd, Los Angeles, CA</p>
                                <p>Cairo, Egypt</p>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold">Who's picking up the package?</label>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control">
                                </div>
                                <div class="col">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control">
                            </div>
                        </div>

                        <!-- Change Location Section -->
                        <div id="changeLocationSection" style="display: none;">
                            <div class="d-flex align-items-center gap-1 mb-3">
                                <i data-feather="chevron-left" class="cursor-pointer" id="backToPickup"
                                    style="cursor: pointer;"></i>
                                <h5 class="fs-4 text-black mb-0">Change Pick up Location</h5>
                            </div>

                            <div class="mb-3">
                                <input type="text" class="form-control" placeholder="Search for a location..."
                                    id="locationSearch">
                            </div>

                            <div id="mapPlaceholder"
                                style="height: 300px; background-color: #f0f0f0; border-radius: 8px;">
                                <p class="text-center text-muted pt-5">Map will display here based on search</p>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-outline-secondary fs-5"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fs-5" id="saveChangesButton">Save</button>
                    </div>
            </form>

            
            <form id="addAddressForm" class="row gy-1 gx-2" method="post"
                action="{{ route('shipping-addresses.store') }}">
                @csrf
                <h5 class="mb-2 text-black fs-4">Add new address</h5>
                <input type="hidden" name="user_id" value="{{ $model->user->id }}">

                <div class="mb-2">
                    <label class="form-label label-text">Address Label</label>
                            <input type="text" class="form-control" placeholder="Choose Address Label"
                                id="add-category-name-en" name="label"/>
                    <div class="invalid-feedback" id="label-error"></div>
                    <br>
                <div class="row g-2 mb-2">
                    <div class="col">
                        <label class="form-label">Country</label>
                        <select class="form-select address-country-select" name="country_id">
                            <option value="">Select Country</option>
                            @foreach ($associatedData['countries'] as $country)
                                <option value="{{ $country->id }}"
                                    {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label label-text">State</label>
                        <select id="modalAddressState" name="state_id" class="form-select address-state-select">
                            <option value="">Select a State</option>
                        </select>
                        <div class="invalid-feedback" id="state_id-error"></div>
                        <div id="state-url" data-url="{{ route('states') }}"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Address Line</label>
                        <input type="text" name="line" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Delivery Instructions</label>
                        <textarea class="form-control" rows="2"></textarea>
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
    document.addEventListener("DOMContentLoaded", function() {
        feather.replace();
        const shipRadio = document.getElementById("shipToCustomer");
        const pickupRadio = document.getElementById("pickUp");
        const shipSection = document.getElementById("shipSection");
        const pickupSection = document.getElementById("pickupSection");

        function toggleSections() {
            if (shipRadio.checked) {
                shipSection.style.display = "block";
                pickupSection.style.display = "none";
            } else {
                shipSection.style.display = "none";
                pickupSection.style.display = "block";
            }
        }

        shipRadio.addEventListener("change", toggleSections);
        pickupRadio.addEventListener("change", toggleSections);
        toggleSections();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const shipRadio = document.getElementById("shipToCustomer");
        const pickupRadio = document.getElementById("pickUp");
        const shipSection = document.getElementById("shipSection");
        const pickupSection = document.getElementById("pickupSection");
        const changeLocationSection = document.getElementById("changeLocationSection");
        const shippingMethodSection = document.getElementById("shippingMethodSection");

        const changeLocationBtn = pickupSection.querySelector(".lined-btn");
        const backToPickupBtn = document.getElementById("backToPickup");

        function toggleSections() {
            if (shipRadio.checked) {
                shipSection.style.display = "block";
                pickupSection.style.display = "none";
                changeLocationSection.style.display = "none";
                shippingMethodSection.style.display = "block";
            } else {
                shipSection.style.display = "none";
                pickupSection.style.display = "block";
                changeLocationSection.style.display = "none";
                shippingMethodSection.style.display = "block";
            }
        }

        shipRadio.addEventListener("change", toggleSections);
        pickupRadio.addEventListener("change", toggleSections);
        toggleSections();

        changeLocationBtn.addEventListener("click", function() {
            pickupSection.style.display = "none";
            changeLocationSection.style.display = "block";
            shippingMethodSection.style.display = "none";
        });

        backToPickupBtn.addEventListener("click", function() {
            changeLocationSection.style.display = "none";
            pickupSection.style.display = "block";
            shippingMethodSection.style.display = "block";
        });

        feather.replace();
    });



 handleAjaxFormSubmit('#editTagForm', {
        successMessage: 'Shipping details updated successfully!',
        closeModal: '#yourModalId', 
        onSuccess: function(response, $form) {
            console.log('Response:', response);

        },
        onError: function(xhr, $form) {
            console.error('Error:', xhr);
        }
    });

    // handle add new address
</script>
