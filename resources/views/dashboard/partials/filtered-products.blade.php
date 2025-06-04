@forelse($products as $product)
    <div class="d-flex align-items-center mb-1 product-item" data-id="{{ $product->id }}"
         data-name="{{ $product->name }}">
        <img src="{{ asset($product->getMainImageUrl()) }}" class="mx-1 rounded" width="48" height="48" alt="Product Image">
        <div>
            <div class="fw-bold product-item"  data-name="{{ $product->name }}">{{ $product->name }}</div>
            <div class="text-muted small">{{ $product->description }}</div>
        </div>
    </div>
@empty
    <div class="text-muted">No products found.</div>
@endforelse
