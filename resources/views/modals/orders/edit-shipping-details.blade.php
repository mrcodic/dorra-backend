<div class="modal modal-slide-in new-user-modal fade" id="editOrderShippingModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editTagForm" enctype="multipart/form-data"
                  action="{{ route('orders.edit-shipping-addresses', ['order' => $model->id]) }}">>
                @csrf
                @method('PUT')
                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Shipping Details</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" id="edit-tag-id">

                    <input type="hidden" name="order_id" value="{{ $model->id }}">
                    <input type="hidden" name="location_id" id="selectedLocationId" value="{{ $locationId ?? '' }}">


                    <!-- Shipping Method Selection -->
                    @php
                        $address = $model->OrderAddress->first();
                        $selectedType = $address?->type;
                    @endphp

                    <div class="mb-3" id="shippingMethodSection">
                        <label class="form-label fw-bold fs-5 mb-2">Shipping Method</label>
                        <div class="d-flex gap-2">
                            <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                <input
                                        class="form-check-input"
                                        type="radio"
                                        name="type"
                                        id="shipToCustomer"
                                        value="{{ \App\Enums\Order\OrderTypeEnum::SHIPPING->value }}"
                                        @checked($selectedType === 'shipping')>
                                <label class="form-check-label fs-4 text-black" for="shipToCustomer">
                                    {{ \App\Enums\Order\OrderTypeEnum::SHIPPING->label() }}
                                </label>
                            </div>

                            <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                <input
                                        class="form-check-input"
                                        type="radio"
                                        name="type"
                                        id="pickUp"
                                        value="{{ \App\Enums\Order\OrderTypeEnum::PICKUP->value }}"
                                        @checked($selectedType === 'pickup')>
                                <label class="form-check-label fs-4 text-black" for="pickUp">
                                    {{ \App\Enums\Order\OrderTypeEnum::PICKUP->label() }}
                                </label>
                            </div>
                        </div>
                    </div>

                    @php
                        $shippingAddressId = $model->OrderAddress->firstWhere('shipping_address_id', '!=', null)?->shipping_address_id;
                    @endphp


                    <div id="shipSection">
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach (collect(optional($model->user)->addresses ?? optional($model->guest)->addresses) as $address)
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
                                <button type="button" class="lined-btn" data-bs-toggle="modal"
                                        data-bs-target="#selectLocationModal">
                                    Change Location
                                </button>
                            </div>
                            @php
                                $address = $model->OrderAddress->first();
                            @endphp

                            @if($address)
                                <div class="border rounded-3 p-1 mb-2">
                                    <p class="text-black">{{ $address->location_name }}</p>
                                    <p>{{ $address->state }} , {{ $address->country }}</p>
                                </div>
                            @endif
                            <div class="mb-2">
                                <label class="form-label fw-bold">Who's picking up the package?</label>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="pickup_first_name"
                                           value="{{ optional($model->pickupContact)->first_name }}"
                                           class="form-control">
                                </div>
                                <div class="col">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="pickup_last_name"
                                           value="{{ optional($model->pickupContact)->last_name }}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" name="pickup_email"
                                       value="{{ optional($model->pickupContact)->email }}" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="pickup_phone"
                                       value="{{ optional($model->pickupContact)->phone }}" class="form-control">
                            </div>
                        </div>


                        <div class="modal fade" id="selectLocationModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content rounded-4 shadow">

                                    <!-- Header -->
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fs-4 fw-bold text-dark">Change Pick up Location</h5>
                                    </div>

                                    <!-- Body -->
                                    <div class="modal-body">
                                        @php
                                            $locationId = $model->OrderAddress->firstWhere('location_id', '!=', null)?->location_id;
                                        @endphp

                                        <div class="mb-3">
                                            <input type="text" class="form-control form-control-lg"
                                                   placeholder="Search for a location..." id="locationSearch">
                                        </div>

                                        <div id="locationList" class="mb-3"></div>

                                        <div style="background-color: #f0f0f0; border-radius: 8px;">
                                            <div id="googleMap" style="width: 100%; height: 400px;"></div>
                                        </div>

                                    </div>

                                    <!-- Footer with Save button -->
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-primary" id="saveLocationBtn">
                                            Save Location
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button type="button" class="btn btn-outline-secondary fs-5"
                                    data-bs-dismiss="modal">Cancel
                            </button>
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


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDx7_example_REAL_KEY"></script>
<script>
    let map;
    let marker;

    function initMap(lat = 30.0444, lng = 31.2357) {
        const defaultLocation = {lat: parseFloat(lat), lng: parseFloat(lng)};
        map = new google.maps.Map(document.getElementById('googleMap'), {
            zoom: 10,
            center: defaultLocation,
        });
        marker = new google.maps.Marker({
            position: defaultLocation,
            map: map,
        });
    }

    $(document).ready(function () {
        $('#selectLocationModal').on('shown.bs.modal', function () {
            setTimeout(function () {
                if (!map) {
                    initMap();
                } else {
                    google.maps.event.trigger(map, 'resize');
                }
            }, 300);
        });

        $('#locationSearch').on('input', function () {
            const query = $(this).val();
            if (query.length >= 2) {
                $.ajax({
                    url: "{{ route('locations.search') }}",
                    method: 'GET',
                    data: {search: query},
                    success: function (response) {
                        $('#locationList').html(response);
                    }
                });
            } else {
                $('#locationList').html('');
            }
        });

        $(document).on('click', '.location-item', function () {
            const lat = parseFloat($(this).data('lat'));
            const lng = parseFloat($(this).data('lng'));
            const name = $(this).data('name');
            const id = $(this).data('id');

            if (marker) marker.setMap(null);

            const newLocation = {lat: lat, lng: lng};
            map.setCenter(newLocation);
            marker = new google.maps.Marker({
                position: newLocation,
                map: map,
            });

            $('#locationSearch').val(name);
            $('#selectedLocationId').val(id);
            $('#locationList').html('');
        });
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
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


    document.addEventListener("DOMContentLoaded", function () {
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

        changeLocationBtn.addEventListener("click", function () {
            pickupSection.style.display = "none";
            changeLocationSection.style.display = "block";
            shippingMethodSection.style.display = "none";
        });

        backToPickupBtn.addEventListener("click", function () {
            changeLocationSection.style.display = "none";
            pickupSection.style.display = "block";
            shippingMethodSection.style.display = "block";
        });

        feather.replace();
    });


    handleAjaxFormSubmit('#editTagForm', {
        successMessage: 'Shipping details updated successfully!',
        closeModal: '#yourModalId',
        onSuccess: function (response, $form) {
            console.log('Response:', response);

        },
        onError: function (xhr, $form) {
            console.error('Error:', xhr);
        }
    });


    $(document).ready(function () {
        $('#saveLocationBtn').on('click', function () {
            const selectedId = $('#selectedLocationId').val();
            const selectedName = $('#locationSearch').val();

            if (selectedId) {

                console.log('✅ New location selected:');
                console.log('ID:', selectedId);
                console.log('Name:', selectedName);
                $('#mainLocationIdInput').val(selectedId);

                $('#selectedLocationName').text(selectedName);

                $('#selectLocationModal').modal('hide');
            } else {
                alert("Please select a location first.");
            }
        });

        $(document).on('click', '.location-item', function () {
            const lat = parseFloat($(this).data('lat'));
            const lng = parseFloat($(this).data('lng'));
            const name = $(this).data('name');
            const id = $(this).data('id');

            if (marker) marker.setMap(null);

            const newLocation = {lat: lat, lng: lng};
            map.setCenter(newLocation);
            marker = new google.maps.Marker({
                position: newLocation,
                map: map,
            });

            $('#locationSearch').val(name);
            $('#selectedLocationId').val(id);
            $('#locationList').html('');
        });
    });


    $(document).on('click', '.location-item', function () {
        const lat = parseFloat($(this).data('lat'));
        const lng = parseFloat($(this).data('lng'));
        const name = $(this).data('name');
        const id = $(this).data('id');

        if (marker) marker.setMap(null);

        const newLocation = {lat: lat, lng: lng};
        map.setCenter(newLocation);
        marker = new google.maps.Marker({
            position: newLocation,
            map: map,
        });

        $('#locationSearch').val(name);       // تحديث اسم الموقع
        $('#selectedLocationId').val(id);     // تحديث الـ hidden input
        $('#locationList').html('');          // إخفاء النتائج
    });
    // handle add new address
</script>
