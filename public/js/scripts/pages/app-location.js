$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".location-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: categoriesDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $("#search-location-form").val(); // get from input
            d.created_at = $(".filter-date").val();
            return d;
        },
    },
    columns: [
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return row?.action?.can_delete
                    ? `<input type="checkbox" name="ids[]" class="category-checkbox" value="${row.id}">`
                    : '';
            },
        },
        {data: "name"},
        {data: "country"},
        {data: "state"},
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row, meta) {
                const canEdit = row?.action?.can_edit ?? false;
                const canDelete = row?.action?.can_delete ?? false;
                const btns = [];
                if (canEdit) {
                    btns.push(`
    <a href="#" class="edit-details"
       data-bs-toggle="modal"
       data-bs-target="#editLocationModal"
       data-id="${row.id}"
       data-name="${_.escape(row.name)}"
       data-country_id="${row.country_id || ''}"
       data-state_id="${row.state_id || ''}"
       data-address_line="${_.escape(row.address_line || '')}"
       data-link="${_.escape(row.link || '')}"
       data-days='${JSON.stringify(row.days || [])}'
       data-available_time="${row.available_time || ''}">
       <i data-feather="edit-3"></i>
    </a>`);
                }

                if (canDelete) {
                    btns.push(`<a href="#" class="text-danger open-delete-location-modal"
   data-id="${data}"
   data-action="/logistics/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteLocationModal">
   <i data-feather="trash-2"></i>
</a>
`);
                }

                if (!btns.length) return '';
                return `<div class="d-flex gap-1 align-items-center">${btns.join('')}</div>`;
            },
        },
    ],
    order: [[1, "asc"]],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
        '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
        ">" +
        ">t" +
        '<"d-flex mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        ">",
    drawCallback: function () {
        feather.replace();
    },

    language: {
        sLengthMenu: "Show _MENU_",
        search: "",
        searchPlaceholder: "Search..",
        paginate: {
            previous: "&nbsp;",
            next: "&nbsp;",
        },
    },
});

// Custom search with debounce
let searchTimeout;
$("#search-location-form").on("keyup", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$(".filter-date").on("change", function () {
    dt_user_table.draw();
});
$('#clear-search').on('click', function () {
    $('#search-location-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
// Checkbox select all
$("#select-all-checkbox").on("change", function () {
    $(".category-checkbox").prop("checked", this.checked);
    updateBulkDeleteVisibility();
});

// Single checkbox toggle
$(document).on("change", ".category-checkbox", function () {
    if (!this.checked) {
        $("#select-all-checkbox").prop("checked", false);
    } else if (
        $(".category-checkbox:checked").length ===
        $(".category-checkbox").length
    ) {
        $("#select-all-checkbox").prop("checked", true);
    }
    updateBulkDeleteVisibility();
});

// Redraw table resets checkboxes
dt_user_table.on("draw", function () {
    $("#select-all-checkbox").prop("checked", false);
    $("#bulk-delete-container").hide();
});

// Update bulk delete container
function updateBulkDeleteVisibility() {
    const selected = $(".category-checkbox:checked").length;
    if (selected > 0) {
        $("#selected-count-text").text(
            `${selected} Category${selected > 1 ? "ies" : "y"} are selected`
        );
        $("#bulk-delete-container").show();
    } else {
        $("#bulk-delete-container").hide();
    }
}

// Listen to checkbox change
$(document).on("change", ".category-checkbox", function () {
    let checkedCount = $(".category-checkbox:checked").length;
    $("#bulk-delete-container").toggle(checkedCount > 0);
});
// Select All functionality
$(document).on("change", "#select-all-checkbox", function () {
    const isChecked = $(this).is(":checked");
    $(".category-checkbox").prop("checked", isChecked).trigger("change");
});
// Update "Select All" checkbox based on individual selections
$(document).on("change", ".category-checkbox", function () {
    const all = $(".category-checkbox").length;
    const checked = $(".category-checkbox:checked").length;

    $("#select-all-checkbox").prop("checked", all === checked);
    $("#bulk-delete-container").toggle(checked > 0);
});

// Optional: Hide button when table is redrawn
dt_user_table.on("draw", function () {
    $("#bulk-delete-container").hide();
});

$(document).ready(function () {
    $(document).on("click", ".open-delete-location-modal", function () {
        const locationAction = $(this).data("action");
        $("#deleteLocationForm").attr("action", locationAction);
    });

    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked")
            .map(function () {
                return $(this).val();
            })
            .get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "locations/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteLocationsModal").modal("hide");
                Toastify({
                    text: "Selected Locations deleted successfully!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                // Reload DataTable

                $("#bulk-delete-container").hide();
                $(".category-checkbox").prop("checked", false);
                $("#select-all-checkbox").prop("checked", false);
                $(".location-list-table").DataTable().ajax.reload(null, false);
            },
            error: function () {
                $("#deleteLocationsModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                // Reload DataTable

                $("#bulk-delete-container").hide();
                $(".category-checkbox").prop("checked", false);
                $("#select-all-checkbox").prop("checked", false);
                $(".location-list-table").DataTable().ajax.reload(null, false);
            },
        });
    });

    const updateEditTimeHidden = () => {
        const s = $("#edit_start_time").val();
        const e = $("#edit_end_time").val();
        $("#edit_available_time").val(s && e ? `${s} - ${e}` : "");
    };

    $(document).on("change", "#edit_start_time, #edit_end_time", updateEditTimeHidden);

    function fillEditFormFromData(loc) {
        $("#editLocationName").val(loc.name || "");
        $("#editAddressLine").val(loc.address_line || "");
        $("#editAddressLink").val(loc.link || "");

        // Days (array of enum names)
        const days = Array.isArray(loc.days) ? loc.days : [];
        $("#editDays").val(days).trigger("change");

        // Time "HH:MM - HH:MM"
        const rng = (loc.available_time || "").split("-").map(s => s.trim());
        if (rng.length === 2) {
            $("#edit_start_time").val(rng[0] || "");
            $("#edit_end_time").val(rng[1] || "");
        } else {
            $("#edit_start_time").val("");
            $("#edit_end_time").val("");
        }
        updateEditTimeHidden();

        // Country -> States cascade
        const countryId = String(loc.country_id || "");
        const stateId = String(loc.state_id || "");

        $("#editCountry").val(countryId);

        if (countryId) {
            const url = $("#state-url").data("url");
            $.get(url, { "filter[country_id]": countryId })
                .done(resp => {
                    const $st = $("#editState").empty().append('<option value="">Select State</option>');
                    (resp.data || []).forEach(st => {
                        $st.append(`<option value="${st.id}">${st.name}</option>`);
                    });
                    $("#editState").val(stateId);
                })
                .fail(() => {
                    $("#editState").empty().append('<option value="">Error loading states</option>');
                });
        } else {
            $("#editState").empty().append('<option value="">Select State</option>');
        }
    }

    function setEditFormAction(id) {
        // if your route is locations.update:
        const url = `/locations/${id}`; // or use `{{ url('locations') }}/${id}`
        $("#editLocationForm").attr("action", url);
    }

    $(document).on("click", ".edit-details", function (e) {
        e.preventDefault();

        // Prefer pulling the whole row from DataTables:
        let rowData = null;
        try {
            rowData = dt_user_table.row($(this).closest("tr")).data();
        } catch (_) {}

        const id = rowData?.id || $(this).data("id");

        // Fallback: use data-* attributes if rowData lacks fields
        const candidate = rowData && (rowData.name || rowData.country_id || rowData.days)
            ? rowData
            : {
                id,
                name: $(this).data("name"),
                country_id: $(this).data("country_id"),
                state_id: $(this).data("state_id"),
                address_line: $(this).data("address_line"),
                link: $(this).data("link"),
                days: $(this).data("days"),
                available_time: $(this).data("available_time"),
            };

        // If still missing critical fields, fetch one record via show endpoint
        if (!candidate || candidate.name === undefined) {
            $.getJSON(`/locations/${id}`)
                .done(loc => {
                    setEditFormAction(id);
                    fillEditFormFromData(loc.data || loc);
                    $("#editLocationModal").modal("show");
                })
                .fail(() => {
                    Toastify({ text: "Failed to load location", duration: 2000 }).showToast();
                });
        } else {
            setEditFormAction(id);
            fillEditFormFromData(candidate);
            $("#editLocationModal").modal("show");
        }
    });




});
handleAjaxFormSubmit("#deleteLocationForm", {
    successMessage: "Location deleted successfully",
    onSuccess: function () {
        $('#deleteLocationModal').modal('hide');
        location.reload()
    }
})
