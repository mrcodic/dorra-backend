@php use Illuminate\Support\Facades\Cache; @endphp
@php
    $orderData = Cache::get(getOrderStepCacheKey());
    $subtotal = isset($orderData['sub_total']) ? number_format($orderData['sub_total'], 2) : '0.00';
@endphp
<div id="step-4" class="step" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">Order Details</h5>
    <div class="mb-3 " id="discount-code-row">
        <form id="discount-code-form" action="{{ route("orders.apply-discount-code") }}" method="post">

            <div class="input-group">
                @csrf
                <input type="text" class="form-control" name="code" placeholder="Enter discount code">
                <button class="btn btn-secondary" type="submit">Apply</button>
            </div>
            <div id="discount-code-error" class="invalid-feedback d-block text-danger mt-1"></div>
            <div id="discount-code-success" class="valid-feedback d-block text-success mt-1"></div>
        </form>
    </div>


    <!-- Pricing Summary -->
    <h5 class="mt-3 mb-1 text-black fs-16">Pricing Details</h5>
    <div class="d-flex justify-content-between mb-1">
        <span class="text-dark fs-16 fw-bold">Subtotal</span>
        <span class="fs-4 text-black fw-bold">

            {{ $subtotal }}
        </span>
    </div>
    <div class="d-flex justify-content-between mb-1">
        <span class="text-dark fs-16 fw-bold" >Discount</span>
        <span class="fs-16 text-black" id="discount-amount">-0.00</span>
    </div>
    <div class="d-flex justify-content-between mb-1">
        <span class="text-dark fs-16 fw-bold">
            Delivery
            <i data-feather="info" data-bs-toggle="tooltip" title="Delivery charges may vary based on location."></i>
        </span>
        <span class="fs-16 text-black">30</span>
    </div>
    <div class="d-flex justify-content-between mb-1">
        <span class="text-dark fs-16 fw-bold">
            Tax
            <i data-feather="info" data-bs-toggle="tooltip" title="Tax is calculated as per applicable laws."></i>
        </span>
        <span class="fs-16 text-black">0.1</span>
    </div>

    <hr class="border-dashed my-1">

    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
        <span class="fs-4 text-black ">Total</span>
        <span class="fs-4 text-black fw-bold" id="total">{{ getTotalPrice(0,$orderData['pricing_details']["sub_total"]) }}</span>
    </div>


    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-primary" data-next-step>Next</button>
    </div>
</div>

<script>
    $(document).ready(function () {
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
                        console.log(response)
                        // Update UI with discount
                        $('#discount-amount').text('-' + response.data.discount_amount);
                        $('#discount-code-success').text(response.message).show();

                        // Update total if needed
                        $('#total').text(response.data.total);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        // Validation error
                        const errors = xhr.responseJSON.errors;

                        $('#discount-code-error').text(errors.code[0]).show();
                    } else {
                        $('#discount-code-error').text('An error occurred. Please try again.').show();
                    }
                }
            });
        });
    });
</script>
