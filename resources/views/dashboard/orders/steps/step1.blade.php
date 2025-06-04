<div id="step-1" class="step">
    <h5 class="mb-2 fs-3 text-black">1. Select Customer</h5>

    <!-- Search Input -->
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
            <i data-feather="search"></i>
        </span>
        <input type="text" id="customer-search" class="form-control border-start-0 border-end-0"
               placeholder="Search for a customer">
        <span class="input-group-text bg-white border-start-0"></span>
    </div>

    <!-- Results Wrapper -->
    <div id="customer-results-wrapper" class="border shadow rounded-2 p-1 mt-2" style="display: none;">
        <div id="customer-results"></div>

        <!-- Show All Button -->
        <div id="show-all-container" class="text-end mt-1" style="display: none;">
            <button class="btn btn-link p-0" id="show-all-btn">Show all results</button>
        </div>
    </div>

    <!-- Next Step -->
    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-primary fs-5" id="next-step-btn" data-next-step>Next</button>
    </div>
</div>

<!-- Script -->
<script>
    let searchTimeout;
    let selectedUserId = null;

    // Search input listener
    $('#customer-search').on('input', function () {
        const query = $(this).val().trim();

        clearTimeout(searchTimeout);
        if (query.length < 2) {
            $('#customer-results-wrapper').hide();
            return;
        }

        searchTimeout = setTimeout(() => fetchResults(query), 300);
    });

    // Fetch user search results
    function fetchResults(query, all = false) {
        $.get("{{ route('users.search') }}", { search: query, all }, function (data) {
            if (!data.data.length) {
                $('#customer-results').html('<div class="text-muted">No results found.</div>');
                $('#show-all-container').hide();
            } else {
                const html = data.data.map(user => `
                    <div class="customer-result d-flex align-items-center mb-1 p-1 rounded hover-bg-light"
                         style="cursor: pointer;"
                         data-id="${user.id}" data-name="${user.name}">
                        <img src="${user.image_url || '/images/default-avatar.png'}"
                             class="rounded-circle mx-1" width="40" height="40" alt="Avatar">
                        <span class="fw-bold">${user.name}</span>
                    </div>
                `).join('');

                $('#customer-results').html(html);
                $('#show-all-container').toggle(!all && data.data.length === 5);
            }

            $('#customer-results-wrapper').show();
        });
    }

    // Delegate click on dynamically added results
    $('#customer-results').on('click', '.customer-result', function () {
        const name = $(this).data('name');
        selectedUserId = $(this).data('id');

        $('#customer-search').val(name);
        $('#customer-results-wrapper').hide();
    });

    // Show all click
    $('#show-all-btn').on('click', function () {
        const query = $('#customer-search').val().trim();
        if (query) fetchResults(query, true);
    });

    // Step submit
    $('#next-step-btn').on('click', function () {
        if (!selectedUserId) {
            alert('Please select a customer.');
            return;
        }

        $.ajax({
            url: '{{ route("orders.step1") }}',
            method: 'POST',
            data: {
                user_id: selectedUserId,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {

            },
            error: function (xhr) {
                console.error(xhr.responseJSON);
            }
        });
    });
</script>
