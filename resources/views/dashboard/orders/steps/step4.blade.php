<div id="step-4" class="step" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">Order Details</h5>
    <div class="mb-3 " id="discount-code-row">
        <form id="discount-code-form" action="{{ route('orders.apply-discount-code') }}" method="post">
            <div class="input-group">
                @csrf
                <input type="text" class="form-control" name="code" placeholder="Enter discount code">
                <button class="btn btn-secondary" type="submit">Apply</button>
            </div>
            <div id="discount-code-error" class="invalid-feedback d-block text-danger mt-1" style="display: none;"></div>
            <div id="discount-code-success" class="valid-feedback d-block text-success mt-1" style="display: none;"></div>
        </form>
    </div>

    <!-- Pricing Summary -->
    <h5 class="mt-3 mb-1 text-black fs-16">Pricing Details</h5>
    <div class="d-flex justify-content-between mb-1">
        <span class="text-dark fs-16 fw-bold">Subtotal</span>
        <span class="fs-4 text-black fw-bold">{{ $pricingSubtotal }}</span>
    </div>
    <div class="d-flex justify-content-between mb-1">
        <span class="text-dark fs-16 fw-bold">Discount</span>
        <span class="fs-16 text-black" id="discount-amount">-{{ $discountAmount }}</span>
    </div>
    <div class="d-flex justify-content-between mb-1">
        <span class="text-dark fs-16 fw-bold">
            Delivery
            <i data-feather="info" data-bs-toggle="tooltip" title="Delivery charges may vary based on location."></i>
        </span>
        <span class="fs-16 text-black">{{ $deliveryFee }}</span>
    </div>
    <div class="d-flex justify-content-between mb-1">
        <span class="text-dark fs-16 fw-bold">
            Tax
            <i data-feather="info" data-bs-toggle="tooltip" title="Tax is calculated as per applicable laws."></i>
        </span>
        <span class="fs-16 text-black">(10%) {{ $tax }}</span>
    </div>

    <hr class="border-dashed my-1">

    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
        <span class="fs-4 text-black">Total</span>
        <span class="fs-4 text-black fw-bold" id="total">{{ $total }}</span>
    </div>

    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-primary" id="nextStep4" data-next-step>Next</button>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Initialize Feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        $('#discount-code-form').on('submit', function (e) {
            e.preventDefault();

            // Reset messages
            $('#discount-code-error').text('').hide();
            $('#discount-code-success').text('').hide();

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.success) {
                        // Update UI with discount
                        $('#discount-amount').text('-' + response.data.discount_amount);
                        $('#discount-code-success').text(response.message).show();

                        // Update total
                        $('#total').text(response.data.total);
                    } else {
                        $('#discount-code-error').text(response.message).show();
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        // Validation error
                        const errors = xhr.responseJSON.errors;
                        $('#discount-code-error').text(errors.code ? errors.code[0] : 'Invalid discount code').show();
                    } else {
                        $('#discount-code-error').text('An error occurred. Please try again.').show();
                    }
                }
            });
        });

        $('#nextStep4').on('click', function () {
            const totalText = $('#total').text().replace(/[^0-9.-]+/g,"");
            const discountText = $('#discount-amount').text().replace(/[^0-9.-]+/g,"");

            const orderData = {
                sub_total: parseFloat("{{ $pricingSubtotal }}"),
                quantity: parseFloat("{{ $quantity }}"),
                tax: parseFloat("{{ $tax }}"),
                delivery: parseFloat("{{ $deliveryFee }}"),
                discount: Math.abs(parseFloat(discountText)) || 0,
                total: parseFloat(totalText)
            };

            $.ajax({
                url: "{{ route('orders.step4') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    orderData: orderData
                },
                success: function (response) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                },
                error: function (xhr) {
                    console.error(xhr);
                    alert('Failed to proceed. Please try again.');
                }
            });
        });
    });
</script>
