@forelse($locations as $location)
    <div class="d-flex align-items-center mb-1 location-item" 
         data-id="{{ $location->id }}"
         data-name="{{ $location->name }}"
         data-lat="{{ $location->latitude }}"
         data-lng="{{ $location->longitude }}">
        <img src="" class="mx-1 rounded" width="48" height="48" alt="location Image">
        <div>
            <div class="fw-bold">{{ $location->name }}</div>
            <div class="text-muted small">{{ $location->address_line }}</div>
        </div>
    </div>
@empty
    <div class="text-muted">No locations found.</div>
@endforelse