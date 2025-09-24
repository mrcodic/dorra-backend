<style>
    /* Keep autocomplete above Bootstrap modals */
    .pac-container { z-index: 2000; }
    /* In-map search input */
    .map-search {
        box-sizing: border-box; border:1px solid #ccc; border-radius:8px;
        height:40px; padding:0 12px; width:320px; font-size:14px; background:#fff;
    }
    /* Nearby list look */
    #nearbyLocations .list-group-item.active {
        border-color: #16a34a; /* green border */
        box-shadow: 0 0 0 2px rgba(22,163,74,.15);
    }
</style>

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
                <input type="hidden" name="pickup_place_id" id="pickup_place_id">
                <input type="hidden" name="pickup_country" id="pickup_country">
                <input type="hidden" name="pickup_state" id="pickup_state">
                <input type="hidden" name="location_id" id="selectedLocationId" value="{{ $locationId ?? '' }}">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3">Edit Shipping Details</h5>
                </div>

                <div class="modal-body flex-grow-1">
                    <!-- your shipping method UI ... -->

                    <!-- PICKUP SECTION -->
                    <div class="modal fade" id="selectLocationModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content rounded-4 shadow">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fs-4 fw-bold text-dark">Select Pick up Location</h5>
                                </div>

                                <div class="modal-body">
                                    <div style="background-color:#f0f0f0;border-radius:8px;">
                                        <div id="googleMap" style="width:100%;height:300px;"></div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="fw-bold mb-1">Near Pick up Locations</div>
                                        <div id="nearbyLocations" class="list-group"></div>
                                    </div>
                                </div>

                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-primary" id="saveLocationBtn">Use This Location</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /PICKUP SECTION -->
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <!-- Make this submit the form -->
                    <button type="submit" class="btn btn-primary fs-5" id="saveChangesButton" form="editTagForm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Load Google Maps on demand (no callback in URL) -->
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
    function fetchNearby(lat, lng) {
        const url = `{{ route('locations.nearby') }}?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}&radius=15&take=10`;
        $('#nearbyLocations').html('<div class="text-muted p-2">Loading…</div>');

        $.getJSON(url)
            .done(({items}) => renderNearby(items))
            .fail(() => $('#nearbyLocations').html('<div class="text-danger p-2">Failed to load nearby locations</div>'));
    }

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
              <div class="text-muted small">${esc(it.subtitle || '')}</div>
            </div>
          </div>
          <div class="text-success small">${it.distance_km} km from you</div>
        </button>`);
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
