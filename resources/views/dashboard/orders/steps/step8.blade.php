<div id="step-8" class="step" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">Order Confirmation</h5>

    <!-- Checkboxes -->
    <div class="form-check mb-1">
        <input class="form-check-input" type="checkbox" id="track-order" name="track_order" checked>
        <label class="form-check-label fs-16 text-black" for="track-order">
            Allow order tracking for customer.
        </label>
    </div>

    <div class="form-check mb-1">
        <input class="form-check-input" type="checkbox" id="send-email" name="send_email" checked>
        <label class="form-check-label fs-16 text-black" for="send-email">
            Send email to customer when order status is updated.
        </label>
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" id="send-notification" name="send_notification" checked>
        <label class="form-check-label fs-16 text-black" for="send-notification">
            Send notification to customer when order status is updated.
        </label>
    </div>

    <!-- Navigation Buttons -->
    <div class="d-flex justify-content-end mt-3">
        <button class="btn btn-outline-secondary me-1" data-prev-step>Back</button>
        <form method="post" action="{{ route('orders.store') }}" class="order-form">
            @csrf
            <!-- Hidden inputs for each order data field with null checks -->
            <input type="hidden" name="product_id" value="{{ $orderData['product_id'] ?? '' }}">
            <input type="hidden" name="product_name" value="{{ $orderData['product_name'] ?? '' }}">

            <!-- User Info -->
            @isset($orderData['user_info'])
            @foreach($orderData['user_info'] as $key => $value)
            <input type="hidden" name="user_info[{{ $key }}]" value="{{ $value }}">
            @endforeach
            @endisset

            <!-- Pricing Details -->
            @isset($orderData['pricing_details'])
            @foreach($orderData['pricing_details'] as $key => $value)
            <input type="hidden" name="pricing_details[{{ $key }}]" value="{{ $value }}">
            @endforeach
            @endisset

            <!-- Template Info -->
            @isset($orderData['template_info'])
            @foreach($orderData['template_info'] as $key => $value)
            <input type="hidden" name="template_info[{{ $key }}]" value="{{ $value }}">
            @endforeach
            @endisset

            <!-- Personal Info -->
            @isset($orderData['personal_info'])
            @foreach($orderData['personal_info'] as $key => $value)
            <input type="hidden" name="personal_info[{{ $key }}]" value="{{ $value }}">
            @endforeach
            @endisset

            <!-- Shipping Info -->
            @isset($orderData['shipping_info'])
            @foreach($orderData['shipping_info'] as $key => $value)
            <input type="hidden" name="shipping_info[{{ $key }}]" value="{{ $value }}">
            @endforeach
            @endisset

            <!-- Notification Options -->
            <input type="hidden" name="track_order" value="0">
            <input type="hidden" name="send_email" value="0">
            <input type="hidden" name="send_notification" value="0">

            <button type="submit" class="btn btn-primary" id="place-order">
                Place Order
            </button>
        </form>
    </div>
</div>