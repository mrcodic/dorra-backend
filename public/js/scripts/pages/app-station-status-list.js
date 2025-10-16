$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// prevent the surrounding form (if any) from submitting
$(document).on('submit', '#filters-form', function (e) { e.preventDefault(); });

const dt = $('.status-list-table').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    ajax: {
        url: stationStatusesDataUrl,
        type: 'GET',
        data: function (d) {
            d.search_value = $('#search-status-form').val() || '';
            d.created_at   = $('.filter-date').val() || '';   // name it as your backend expects
        }
    },
    columns: [
        {
            data: null,
            orderable: false,
            render: (data) => `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`
        },
        { data: 'name', orderable: false },
        {
            data: 'station',
            orderable: false,
            render: (data) => data?.name ?? '-'
        },
        {
            data: 'resourceable',
            orderable: false,
            render: (data) => (data?.name?.[locale] ?? data?.name ?? '-')
        },
        {
            data: 'id',
            orderable: false,
            render: (id, type, row) => {
                const isProduct = row.resourceable_type === 'App\\Models\\Product';

                const resource = row.resourceable || {};
                const resourceName = resource?.name?.[locale] ?? resource?.name ?? '';

                // parent info (category for product; parent for category)
                const parentObj = isProduct ? (resource.category || {}) : (resource.parent || {});
                const parentId   = parentObj?.id ?? '';
                const parentName = parentObj?.name?.[locale] ?? parentObj?.name ?? '';

                const mode = isProduct ? 'with' : 'without';

                return `
      <div class="d-flex gap-1">
        <a href="#" class="view-details" data-bs-toggle="modal" data-bs-target="#showStatusModal"
           data-id="${id}"
           data-name="${row.name ?? ''}"
           data-station="${row.station?.name ?? ''}"
           data-resource="${resourceName}">
           <i data-feather="eye"></i>
        </a>

        <a href="#" class="edit-details" data-bs-toggle="modal" data-bs-target="#editStatusModal"
           data-id="${id}"
           data-name="${row.name ?? ''}"
           data-station-id="${row.station?.id ?? ''}"
           data-mode="${mode}"
           data-resourceable-type="${row.resourceable_type ?? ''}"
           data-resourceable-id="${resource?.id ?? ''}"
           data-resource-label="${resourceName}"
           data-parent-id="${parentId}"
           data-parent-label="${parentName}">
           <i data-feather="edit-3"></i>
        </a>

        <a href="#" class="text-danger open-delete-offer-modal"
           data-id="${id}" data-action="/station-statuses/${id}"
           data-bs-toggle="modal" data-bs-target="#deleteStatusModal">
           <i data-feather="trash-2"></i>
        </a>
      </div>
    `;
            }
        }



    ],
    order: [[1, 'asc']],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mb-2"<"col-12 d-flex flex-wrap align-items-center justify-content-between">>t' +
        '<"d-flex mx-2 row mb-1"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    drawCallback: function () { feather.replace(); }
});

// Clear button
$('#clear-search').on('click', function () {
    $('#search-status-form').val('');
    $('.filter-date').val('');
    dt.draw();                   // use the SAME instance variable
});

// Debounced text search (server param `search_value`)
let searchTimeout;
$('#search-status-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => dt.draw(), 300);
});

// Date filter
$('.filter-date').on('change', function () {
    dt.draw();
});

    // Switch the edit modal UI


    // Utility: ensure an option exists & select it
// Utility unchanged
function ensureAndSelect($select, value, label) {
    if (!value) return;
    const v = String(value);
    if ($select.find(`option[value="${v}"]`).length === 0) {
        $select.append(new Option(label || v, v, false, false));
    }
    $select.val(v).trigger('change');
}

$(document).on('click', '.edit-details', function (e) {
    e.preventDefault();

    const $b  = $(this);
    const id  = $b.data('id');

    $('#editStationStatusForm').attr('action', `/station-statuses/${id}`);
    $('#edit_status_id').val(id);
    $('#edit_name').val($b.data('name') || '');
    $('#edit_station_id').val(String($b.data('stationId') || ''));

    const mode = $b.data('mode') === 'with' ? 'with' : 'without';
    $('#edit_mode_with').prop('checked', mode === 'with');
    $('#edit_mode_without').prop('checked', mode === 'without');
    editSetMode(mode);

    const resourceableId = $b.data('resourceableId') || ''; // category id (in "with"
    console.log(resourceableId)
    const parentId       = $b.data('parentId') || '';       // product id (in "with")
    const resourceLabel  = $b.data('resourceLabel') || `#${resourceableId}`;

    if (mode === 'with') {
        const $leftProducts = $('#editCategoriesSelect'); // products
        const $rightCats    = $('#editProductsSelect');   // categories

        // keep full product list; don't empty() it
        // tell the right select which category to preselect once it loads
        $rightCats.empty().append(new Option('— Select Category —', '', false, false));
        $rightCats.data('targetCategoryId', String(resourceableId));

        // select the saved product; its change handler will load categories,
        // then your AJAX success will pick up targetCategoryId and select it
        $leftProducts.val(String(parentId)).trigger('change');

    } else {
        // Without categories → resourceable is a Product
        ensureAndSelect($('#editProductsWithoutCategoriesSelect'), resourceableId, resourceLabel);
    }

    $('#editStatusModal').modal('show');
});



    // Keep radio in sync
    $('input[name="edit_product_mode"]').on('change', function() {
    editSetMode($(this).val());
});

    // If the user picks a product on the left, load that product’s categories normally (optional)
    $('#editCategoriesSelect').on('change', function () {
    const productId = $(this).val();
    if (!productId) return;

    // Your existing endpoint — expects product id and returns its categories
    $.ajax({
    url: "/products/categories",
    type: "POST",
    data: { _token: $('meta[name="csrf-token"]').attr('content'), category_ids: [productId] },
    success: function (res) {
    const $right = $('#editProductsSelect');
    $right.empty().append(new Option('— Select Category —', '', false, false));
    (res.data || []).forEach(cat => {
    $right.append(new Option(cat.name, cat.id));
});
    $right.trigger('change');
}
});
});

    // Submit via your existing helper
    handleAjaxFormSubmit("#editStationStatusForm", {
    successMessage: "Status updated successfully.",
    onSuccess: function () {
    $('#editStatusModal').modal('hide');
    $(".status-list-table").DataTable().ajax.reload(null, false);
}
});


$(document).ready(function () {
    const saveButton = $('.saveChangesButton');
    const saveLoader = $('.saveLoader');
    const saveButtonText = $('.saveChangesButton .btn-text');
    $('#addFlagForm').on('submit', function (e) {
        e.preventDefault();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'), // dynamic action URL
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Toastify({
                    text: "Flag added successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true
                }).showToast();

                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                $('#addFlagForm')[0].reset();
                $('#addFlagModal').modal('hide');
                location.reload();

                $('.status-list-table').DataTable().ajax.reload(); // reload your table
            },
            error: function (xhr) {
                var errors = xhr.responseJSON.errors;
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        Toastify({
                            text: errors[key][0],
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true
                        }).showToast();
                    }
                }
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
            }
        });
    });

// helper: normalize "2025-10-06 12:34:00" or ISO to "2025-10-06"
    function toDateForInput(s) {
        if (!s) return '';
        s = String(s).trim();

        // already yyyy-mm-dd
        if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;

        // dd/mm/yyyy → yyyy-mm-dd
        const m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (m) return `${m[3]}-${m[2]}-${m[1]}`;

        // yyyy-mm-dd HH:MM:SS or ISO → take date part
        if (s.includes(' ') || s.includes('T')) return s.split('T')[0].split(' ')[0];

        return '';
    }

// helpers to normalize payloads & render chips
    function toItemsArray(raw) {
        // Accept JSON string, array of IDs, or array of objects with {id, name/title/label}
        if (raw == null) return [];
        if (typeof raw === 'string') {
            try { raw = JSON.parse(raw); } catch (_) { /* keep as-is */ }
        }
        if (!Array.isArray(raw)) return [];

        return raw.map((it) => {
            // normalize id
            const id = String(
                (it && (it.id ?? it.value ?? it)) // object or primitive
            );

            // pick a "name-like" field
            let name = (it && (it.name ?? it.title ?? it.label ?? null));

            // if name is another object (e.g. {en, ar}), pick a sensible locale/first value
            if (name && typeof name === 'object') {
                name = name.en || name.ar || Object.values(name)[0] || `#${id}`;
            }

            if (!name) name = `#${id}`; // fallback

            return { id, name: String(name) };
        });
    }
    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderChips($container, items) {
        if (!items || !items.length) {
            $container.html('<span class="text-muted">— none —</span>');
            return;
        }
        const html = items.map(it => `
    <span class="badge rounded-pill bg-light border text-dark me-1 mb-1">
      ${escapeHtml(it.name)}
    </span>
  `).join('');
        $container.html(html);
    }


    $(document).on('click', '.view-details', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $m = $('#showStatusModal');


        const name   = $btn.data('name') ?? '';
        const station  = $btn.data('station') ?? '';
        const resource   =$btn.data('resource') ?? '';

        $m.find('#show-status-name').val(name);
        $m.find('#show-status-station').val(station);
        $m.find('#show-status-resource').val(resource);


        $m.modal('show');
    });




    $(document).on("click", ".open-delete-offer-modal", function () {
        const OfferId = $(this).data("id");
        const OfferAction = $(this).data("action");
        $("#deleteStatusForm").data("id", OfferId).attr("action", OfferAction);

    });

    $(document).on('submit', '#deleteStatusForm', function (e) {
        e.preventDefault();
        const tagId = $(this).data("id");

        $.ajax({
            url: `/station-statuses/${tagId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteStatusModal").modal("hide");

                Toastify({
                    text: "Status deleted successfully!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".status-list-table").DataTable().ajax.reload(null, false);


            },
            error: function () {
                $("#deleteStatusModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                $(".status-list-table").DataTable().ajax.reload(null, false);

            },
        });
    });

// Open the bulk delete modal (only if at least one is selected)
    $(document).on('click', '#open-bulk-delete-modal', function () {
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Toastify({
                text: "Select at least one offer first.",
                duration: 2000, gravity: "top", position: "right",
                backgroundColor: "#EA5455", close: true
            }).showToast();
            return;
        }

        // show modal
        $('#deleteStatusesModal').modal('show');
    });

// When user confirms in the modal, submit the hidden form
    $(document).on('click', '#confirm-bulk-delete', function () {
        $('#bulk-delete-form').trigger('submit');
    });

    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "station-statuses/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteStatusesModal").modal("hide");
                Toastify({
                    text: "Selected custom statuses deleted successfully!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                // Reload DataTable

                $('#bulk-delete-container').hide();
                $('.category-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".status-list-table").DataTable().ajax.reload(null, false);

            },
            error: function () {
                $("#deleteStatusesModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                // Reload DataTable

                $('#bulk-delete-container').hide();
                $('.tag-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".category-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });


});
