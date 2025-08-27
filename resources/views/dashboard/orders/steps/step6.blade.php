<div id="step-6" class="step" style="display: none;">

    <h5 class="mb-2 fs-3 text-black">Personal Information</h5>

    <div>
        <input type="hidden" id="edit-tag-id">

        <!-- Shipping Method Selection -->
        <div class="mb-3" id="shippingMethodSection">
            <label class="form-label fw-bold fs-5 mb-2">Shipping Method</label>
            <div class="d-flex gap-2">
                <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                    <input class="form-check-input" type="radio" name="type" id="shipToCustomer"
                        value="{{ \App\Enums\Order\OrderTypeEnum::SHIPPING->value }}" checked>
                    <label class="form-check-label fs-4 text-black" for="shipToCustomer">
                        {{ \App\Enums\Order\OrderTypeEnum::SHIPPING->label() }}
                    </label>
                </div>

                <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                    <input class="form-check-input" type="radio" name="type" id="pickUp"
                        value="{{ \App\Enums\Order\OrderTypeEnum::PICKUP->value }}">
                    <label class="form-check-label fs-4 text-black" for="pickUp">
                        {{ \App\Enums\Order\OrderTypeEnum::PICKUP->label() }}
                    </label>
                </div>
            </div>
        </div>

        <!-- Ship to Customer Section -->
        <div id="shipSection">
            <!-- Existing Addresses -->
            <div class="d-flex gap-2">
                @if(!empty($orderData["user_info"]["id"]))
                @foreach(\App\Models\ShippingAddress::whereUserId($orderData["user_info"]["id"])->get() ??[]as
                $shippingAddress)
                <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill text-break">
                    <input class="form-check-input" type="radio" name="shipping_id"
                        id="address{{ $shippingAddress->id }}" value="{{ $shippingAddress->id }}">
                    <label class="form-check-label fs-4 text-black" for="address{{ $shippingAddress->id }}">
                        <p>{{ $shippingAddress->label }}</p>
                        <p class="text-dark fs-16">{{ $shippingAddress->line }}</p>
                    </label>
                </div>
                @endforeach
                @else
                <div class="col-12 text-center py-3">
                    <p class="text-muted">No saved addresses found</p>
                </div>
                @endif
            </div>


            <!-- Divider -->
            @if(!empty($orderData["user_info"]["id"]) &&
            \App\Models\ShippingAddress::whereUserId($orderData["user_info"]["id"])->count() > 0)
            <div class="text-center my-3 fw-bold">OR</div>
            @endif

            <!-- Add New Address -->
            <button class="upload-card p-1 w-100 bg-white mt-2" data-bs-toggle="modal"
                data-bs-target="#addNewAddressModal">
                <i data-feather="plus"></i>Add New Address
            </button>
        </div>

        <!-- Pick Up Section -->
        <div id="pickupSection" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="w-50 fs-4 text-black">
                    To proceed to checkout, choose a pick up location.
                    This will help us deliver your order to the preferred location.
                </div>
                <button type="button" class="btn btn-outline-secondary fs-16" data-bs-toggle="modal"
                    data-bs-target="#selectLocationModal"><i data-feather="map-pin"></i> Select pick up location
                </button>
            </div>


            <div class="row g-2 mb-2">
                <div class="col">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" id="pickup_first_name" name="pickup_first_name">
                </div>
                <div class="col">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="pickup_last_name" name="pickup_last_name">
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="pickup_email" name="pickup_email">
            </div>
            <div class="mb-2">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="pickup_phone" name="pickup_phone">
            </div>
            <input type="hidden" id="pickup_location_id" name="pickup_location_id" value="">

        </div>

        <!-- Change Location Section -->
        {{-- <div id="changeLocationSection" style="display: none;">
            <div class="d-flex align-items-center gap-1 mb-3">
                <i data-feather="chevron-left" class="cursor-pointer" id="backToPickup" style="cursor: pointer;"></i>
                <h5 class="fs-4 text-black mb-0">Change Pick up Location</h5>
            </div>

            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Search for a location..." id="">
            </div>

            <div id="mapPlaceholder" style="height: 300px; background-color: #f0f0f0; border-radius: 8px;">
                <p class="text-center text-muted pt-5">Map will display here based on search</p>
            </div>
        </div> --}}

    </div>


    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-secondary me-1" data-prev-step>Back</button>
        <button class="btn btn-primary" id="nextStep6" data-next-step>Next</button>
    </div>
    @include('modals.select-location')
    @include('modals.addresses.add-new-address',['countries' =>
    $associatedData['countries'],'modelId'=>$orderData["user_info"]["id"] ?? 0])

</div>


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDx7_example_REAL_KEY"></script>
<script>
    let map;
    let marker;

    function initMap(lat = 30.0444, lng = 31.2357) {
        const defaultLocation = {lat: parseFloat(lat), lng: parseFloat(lng)};
        map = new google.maps.Map(document.getElementById('mapPlaceholder'), {
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
            setTimeout(function () { // Important for modal rendering
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

            if (marker) marker.setMap(null);

            const newLocation = {lat: lat, lng: lng};
            map.setCenter(newLocation);
            marker = new google.maps.Marker({
                position: newLocation,
                map: map,
            });

            $('#locationSearch').val(name);
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

        // Initialize feather icons
        feather.replace();
    });
</script>

<script>
    $('#nextStep6').click(function (e) {
        e.preventDefault();

        const shippingMethod = parseInt($('input[name="type"]:checked').val()); // Now int not string

        const data = {
            type: shippingMethod, // send type (for Enum in PHP)
            _token: '{{ csrf_token() }}'
        };

        if (shippingMethod === {{ \App\Enums\Order\OrderTypeEnum::SHIPPING->value }}) {
            const shippingId = $('input[name="shipping_id"]:checked').val();
            if (!shippingId) {
                alert('Please select a shipping address');
                return;
            }
            data.shipping_id = shippingId;

        } else if (shippingMethod === {{ \App\Enums\Order\OrderTypeEnum::PICKUP->value }}) {
            data.pickup_first_name = $('#pickup_first_name').val();
            data.pickup_last_name = $('#pickup_last_name').val();
            data.pickup_email = $('#pickup_email').val();
            data.pickup_phone = $('#pickup_phone').val();
            data.location_id = $('#pickup_location_id').val();
        }

        // Button Loading
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: "{{ route('orders.step6') }}",
            type: "POST",
            data: data,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $('#step-6').hide();
                    $('#step-7').show();
                } else {
                    alert('Error: ' + (response.message || 'Failed to save shipping information'));
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred while saving shipping information');
            },
            complete: function () {
                $('#nextStep6').prop('disabled', false).html('Next');
            }
        });
    });


    $(document).on('click', '.location-item', function () {
        const lat = parseFloat($(this).data('lat'));
        const lng = parseFloat($(this).data('lng'));
        const name = $(this).data('name');
        const locationId = $(this).data('id'); // Your location ID

        console.log('Clicked Location:', {
            lat: lat,
            lng: lng,
            name: name,
            locationId: locationId
        });

        if (marker) marker.setMap(null);

        const newLocation = {lat: lat, lng: lng};
        map.setCenter(newLocation);
        marker = new google.maps.Marker({
            position: newLocation,
            map: map,
        });

        $('#locationSearch').val(name);
        $('#pickup_location_id').val(locationId); // Store location_id here
        console.log('pickup_location_id set to:', $('#pickup_location_id').val());

        $('#locationList').html('');
    });

    $('#confirmLocationBtn').click(function () {
        const selectedLocationId = $('#pickup_location_id').val();
        console.log('Confirm button clicked. Selected Location ID:', selectedLocationId);

        if (!selectedLocationId) {
            alert('Please select a location first.');
            return;
        }
        // Close the modal
        $('#selectLocationModal').modal('hide');
    });
</script>