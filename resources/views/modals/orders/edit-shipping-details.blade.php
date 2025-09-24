

<div class="modal modal-slide-in new-user-modal fade" id="editOrderShippingModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editTagForm" enctype="multipart/form-data"
                action="{{ route('orders.edit-shipping-addresses', ['order' => $model->id]) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="pickup_lat" id="pickup_lat">
                <input type="hidden" name="pickup_lng" id="pickup_lng">
                <input type="hidden" name="pickup_location_name" id="pickup_location_name">
                <input type="hidden" name="pickup_place_id" id="pickup_place_id"> <!-- optional -->
                <input type="hidden" name="pickup_country" id="pickup_country">
                <input type="hidden" name="pickup_state" id="pickup_state">

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
                    $address = $model->OrderAddress;
                    $selectedType = $address?->type;
                    @endphp

                    <div class="mb-1" id="shippingMethodSection">
                        <label class="form-label fw-bold fs-5 mb-2">Shipping Method</label>
                        <div class="d-flex flex-column flex-md-row gap-1">
                            <div class="col-md-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                <input class="form-check-input" type="radio" name="type" id="shipToCustomer"
                                    value="{{ \App\Enums\Order\OrderTypeEnum::SHIPPING->value }}"
                                    @checked($selectedType ==\App\Enums\Order\OrderTypeEnum::SHIPPING)>
                                <label class="form-check-label fs-4 text-black" for="shipToCustomer">
                                    {{ \App\Enums\Order\OrderTypeEnum::SHIPPING->label() }}
                                </label>
                            </div>

                            <div class="col-md-6 form-check border rounded-3 p-1 px-3 flex-fill">
                                <input class="form-check-input" type="radio" name="type" id="pickUp"
                                    value="{{ \App\Enums\Order\OrderTypeEnum::PICKUP->value }}"
                                    @checked($selectedType===\App\Enums\Order\OrderTypeEnum::PICKUP)>
                                <label class="form-check-label fs-4 text-black" for="pickUp">
                                    {{ \App\Enums\Order\OrderTypeEnum::PICKUP->label() }}
                                </label>
                            </div>
                        </div>
                    </div>

                    @php
                    $shippingAddressId = $model->OrderAddress?->shipping_address_id;
                    @endphp


                    <div id="shipSection">
                        <div class="d-flex gap-1 flex-wrap">
                            @foreach ($model->user?->addresses ?? $model->guest?->addresses ?? [] as $address)
                                <div class="col-12 form-check border rounded-3 p-1 px-3 text-break">
                                <input class="form-check-input" type="radio" name="shipping_address_id"
                                    id="address{{ $address->id }}" value="{{ $address->id }}" {{
                                    $shippingAddressId==$address->id ? 'checked' : '' }}>
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


                </div>
            </form> <!-- Divider -->
            <div class="text-center my-1 fw-bold">OR</div>

            <div class="p-2">
                <div id="pickupSection" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="form-label ">Pick up Location</h5>
                        <button type="button" class="lined-btn" data-bs-toggle="modal"
                            data-bs-target="#selectLocationModal">
                            Change Location
                        </button>
                    </div>
                    @php
                    $address = $model->OrderAddress;
                    @endphp

                    @if($address)
                    <div class="border rounded-3 p-1 mb-2">
                        <p class="text-black">{{ $address?->location_name }}</p>
                        <p>{{ $address?->state }} , {{ $address?->country }}</p>
                    </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label fw-bold">Who's picking up the package?</label>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="pickup_first_name"
                                value="{{ optional($model->pickupContact)->first_name }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="pickup_last_name"
                                value="{{ optional($model->pickupContact)->last_name }}" class="form-control">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="pickup_email" value="{{ optional($model->pickupContact)->email }}"
                            class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="pickup_phone" value="{{ optional($model->pickupContact)->phone }}"
                            class="form-control">
                    </div>
                </div>
                <div id="addAddressSection">
                    <form id="addAddressForm" class="row gy-1 gx-2" method="post"
                        action="{{ route('shipping-addresses.store') }}">
                        @csrf
                        <h5 class="mb-2 text-black fs-4">Add new address</h5>
                        <input type="hidden" name="user_id" value="{{ $model->user?->id }}">
                        <input type="hidden" name="guest_id" value="{{ $model->guest?->id }}">

                        <div class="mb-2">
                            <label class="form-label label-text">Address Label</label>
                            <input type="text" class="form-control" placeholder="Choose Address Label"
                                id="add-category-name-en" name="label" />
                            <div class="invalid-feedback" id="label-error"></div>
                            <br>


                            <div class="row g-2 mb-1">
                                <div class="col-md-6">
                                    <label class="form-label">Country</label>
                                    <select class="form-select address-country-select" name="country_id">
                                        <option value="">Select Country</option>
                                        @foreach ($associatedData['countries'] as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id')==$country->id ?
                                            'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label label-text">State</label>
                                    <select id="modalAddressState" name="state_id"
                                        class="form-select address-state-select">
                                        <option value="">Select a State</option>
                                    </select>
                                    <div class="invalid-feedback" id="state_id-error"></div>
                                    <div id="state-url" data-url="{{ route('states') }}"></div>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Address Line</label>
                                    <input type="text" name="line" class="form-control">
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Delivery Instructions</label>
                                    <textarea class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-top-0">
                            <button type="reset" class="btn btn-outline-secondary fs-5"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                                <span class="btn-text">Add</span>
                                <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                    role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>

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
                                $locationId = $model->OrderAddress?->firstWhere('location_id', '!=',
                                null)?->location_id;
                                @endphp



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
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel
                    </button>
                    <button type="submit" class="btn btn-primary fs-5" id="saveChangesButton">Save</button>
                </div>




            </div>
        </div>

    </div>
    <div class="mt-2">
        <div class="fw-bold mb-1">Near Pick up Locations</div>
        <div id="nearbyLocations" class="list-group"></div>
    </div>

</div>

        <!-- Load Google Maps JS with callback -->
        <script async defer
                src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap">
        </script>
<script>
    let map, marker, geocoder, searchBox;
    let gmapsLoaded = false, gmapsLoading = false;

    function loadGoogleMaps(cb) {
        if (gmapsLoaded) return cb();
        if (gmapsLoading) { document.addEventListener('gmaps:ready', cb, { once: true }); return; }
        gmapsLoading = true;

        const s = document.createElement('script');
        s.async = true; s.defer = true;
        s.src = "https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places";
        s.onload = () => { gmapsLoaded = true; gmapsLoading = false; document.dispatchEvent(new Event('gmaps:ready')); cb(); };
        s.onerror = () => { gmapsLoading = false; console.error('Google Maps failed to load. Check key & referrer.'); };
        document.head.appendChild(s);
    }

    // simple HTML escape (no lodash needed)
    function esc(s){return String(s??'').replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));}

    function initMap(lat = 30.0444, lng = 31.2357) {
        const mapEl = document.getElementById('googleMap');
        if (!mapEl) return;

        const center = { lat: parseFloat(lat), lng: parseFloat(lng) };
        map = new google.maps.Map(mapEl, { zoom: 12, center });
        marker = new google.maps.Marker({ position: center, map, draggable: true });
        geocoder = new google.maps.Geocoder();

        // in-map search box
        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Search Google Maps...';
        input.className = 'map-search';
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        searchBox = new google.maps.places.SearchBox(input);
        map.addListener('bounds_changed', () => searchBox.setBounds(map.getBounds()));

        searchBox.addListener('places_changed', () => {
            const places = searchBox.getPlaces();
            if (!places?.length) return;
            const place = places[0];
            if (!place.geometry?.location) return;

            const loc = place.geometry.location;
            map.setCenter(loc);
            map.setZoom(15);
            marker.setPosition(loc);

            const addr = place.formatted_address || place.name || '';
            const { country, state } = extractCountryState(place.address_components || []);
            updateFields(loc.lat(), loc.lng(), addr, place.place_id || '', country, state);
            fetchNearby(loc.lat(), loc.lng());
        });

        // init fields & nearby list
        updateFields(center.lat, center.lng, '', '', '', '');
        fetchNearby(center.lat, center.lng());

        // interactions
        map.addListener('click', (e) => setMarkerPosition(e.latLng));
        marker.addEventListener?.('dragend', () => setMarkerPosition(marker.getPosition())); // Safari guard
        marker.addListener?.('dragend', () => setMarkerPosition(marker.getPosition()));
    }

    function extractCountryState(components) {
        let country = '', state = '';
        for (const c of (components || [])) {
            if (!c.types) continue;
            if (c.types.includes('country')) country = c.long_name;
            if (c.types.includes('administrative_area_level_1')) state = c.long_name;
        }
        return { country, state };
    }

    function setMarkerPosition(latLng) {
        marker.setPosition(latLng);
        map.panTo(latLng);
        geocoder.geocode({ location: latLng }, (results, status) => {
            const r = (status === 'OK' && results?.[0]) ? results[0] : null;
            const addr = r?.formatted_address || '';
            const { country, state } = extractCountryState(r?.address_components || []);
            updateFields(latLng.lat(), latLng.lng(), addr, r?.place_id || '', country, state);
            debounceFetchNearby();
        });
    }

    function updateFields(lat, lng, name, placeId, country, state) {
        $('#pickup_lat').val(lat);
        $('#pickup_lng').val(lng);
        $('#pickup_location_name').val(name || '');
        $('#pickup_place_id').val(placeId || '');
        $('#pickup_country').val(country || '');
        $('#pickup_state').val(state || '');
    }

    // ------- Nearby locations ----------
    function fetchNearby(originLat, originLng) {
        const url = `{{ route('locations.nearby') }}?lat=${encodeURIComponent(originLat)}&lng=${encodeURIComponent(originLng)}`;
        $('#nearbyLocations').html('<div class="text-muted p-2">Loading…</div>');

        $.getJSON(url)
            .done((resp) => {
                const list = Array.isArray(resp?.data) ? resp.data : [];
                // map into a normalized shape + compute client-side distance
                const items = list.map(loc => {
                    const lat = parseFloat(loc.latitude);
                    const lng = parseFloat(loc.longitude);
                    const distance_km = (isFinite(lat) && isFinite(lng))
                        ? Math.round(haversineKm(originLat, originLng, lat, lng) * 10) / 10
                        : null;

                    return {
                        id: loc.id,
                        name: loc.name || 'Location',
                        subtitle: esc([loc?.state?.name, loc?.state?.country?.name].filter(Boolean).join(', ')),
                        lat,
                        lng,
                        distance_km
                    };
                })
                    // keep only those with coords
                    .filter(it => isFinite(it.lat) && isFinite(it.lng))
                    // nearest first
                    .sort((a,b) => (a.distance_km ?? 1e9) - (b.distance_km ?? 1e9));

                renderNearby(items);
            })
            .fail(() => {
                $('#nearbyLocations').html('<div class="text-danger p-2">Failed to load nearby locations.</div>');
            });
    }

    // Render list
    function renderNearby(items) {
        const $wrap = $('#nearbyLocations').empty();
        if (!items?.length) {
            $wrap.html('<div class="text-muted p-2">No nearby locations.</div>');
            return;
        }

        items.forEach(it => {
            $wrap.append(`
      <button type="button"
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center nearby-item"
        data-id="${it.id}" data-lat="${it.lat}" data-lng="${it.lng}" data-name="${esc(it.name)}">
        <div class="d-flex align-items-start gap-2">
          <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
            <i class="bi bi-geo-alt"></i>
          </div>
          <div class="text-start">
            <div class="fw-bold">${esc(it.name)}</div>
            <div class="text-muted small">${it.subtitle || ''}</div>
          </div>
        </div>
        <div class="text-success small">${isFinite(it.distance_km) ? it.distance_km + ' km from you' : ''}</div>
      </button>
    `);
        });
    }

    function debounce(fn, ms=400){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }
    const debounceFetchNearby = debounce(() => {
        const lat = parseFloat($('#pickup_lat').val());
        const lng = parseFloat($('#pickup_lng').val());
        if (lat && lng) fetchNearby(lat, lng);
    }, 500);

    // pick from nearby list
    $(document).on('click', '.nearby-item', function () {
        const id  = $(this).data('id');
        const lat = parseFloat($(this).data('lat'));
        const lng = parseFloat($(this).data('lng'));
        const name= $(this).data('name');

        $('.nearby-item').removeClass('active');
        $(this).addClass('active');

        const pos = new google.maps.LatLng(lat, lng);
        marker.setPosition(pos);
        map.panTo(pos);
        map.setZoom(15);

        $('#pickup_lat').val(lat);
        $('#pickup_lng').val(lng);
        $('#pickup_location_name').val(name);
        $('#selectedLocationId').val(id); // persist chosen pickup location id into your order form
    });

    // open picker => load maps & init
    $('#selectLocationModal').on('shown.bs.modal', function () {
        loadGoogleMaps(() => {
            if (!map) initMap();
            else {
                google.maps.event.trigger(map, 'resize');
                if (marker?.getPosition()) map.setCenter(marker.getPosition());
            }
        });
    });

    // close picker, do NOT submit yet (just keep fields ready)
    $(document).on('click', '#saveLocationBtn', function () {
        const lat = $('#pickup_lat').val(), lng = $('#pickup_lng').val();
        if (!lat || !lng) return alert('Please select a location on the map first.');
        $('#selectLocationModal').modal('hide');
    });
</script>





<script>
            document.addEventListener("DOMContentLoaded", function () {
        const shipRadio = document.getElementById("shipToCustomer");
        const pickupRadio = document.getElementById("pickUp");
        const shipSection = document.getElementById("shipSection");
        const pickupSection = document.getElementById("pickupSection");
        const addAddressSection = document.getElementById("addAddressSection"); // NEW

        function toggleSections() {
            if (shipRadio.checked) {
                shipSection.style.display = "block";
                pickupSection.style.display = "none";
                addAddressSection.style.display = "block"; // Show Add Address
            } else {
                shipSection.style.display = "none";
                pickupSection.style.display = "block";
                addAddressSection.style.display = "none"; // Hide Add Address
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
