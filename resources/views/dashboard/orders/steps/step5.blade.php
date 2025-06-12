<div id="step-5" class="step" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">Personal Information</h5>

    <div class="row g-2 mb-2">
        <div class="col">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="col">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>
    </div>
    <div class="mb-2">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-2">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone_number" class="form-control" required>
    </div>

    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-secondary me-1" data-prev-step>Back</button>
        <button class="btn btn-primary" id="step5-next-btn" data-next-step>Next</button>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Handle step 5 next button click
        $('#step5-next-btn').on('click', function(e) {
            e.preventDefault();
            $('#step-5').hide();
            $('#step-6').show();
            // Get form data
            const formData = {
                first_name: $('input[name="first_name"]').val(),
                last_name: $('input[name="last_name"]').val(),
                email: $('input[name="email"]').val(),
                phone_number: $('input[name="phone_number"]').val(),
                _token: "{{ csrf_token() }}"
            };

            // Simple validation
            if (!formData.first_name || !formData.last_name || !formData.email || !formData.phone_number) {
                alert('Please fill in all required fields');
                return;
            }

            // Email validation
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
                alert('Please enter a valid email address');
                return;
            }

            $.ajax({
                url: "{{ route('orders.step5') }}",
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Proceed to next step
                        $('.step').hide();
                        $('#step-6').show(); // Assuming there's a step 6
                    } else {
                        alert(response.message || 'Failed to save information');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        for (const field in errors) {
                            errorMessages.push(errors[field][0]);
                        }

                        alert(errorMessages.join('\n'));
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

    });
</script>
