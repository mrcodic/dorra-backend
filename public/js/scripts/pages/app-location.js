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
        {data: "country",render: function (data, type, row) {
                console.log()
                return row?.state?.country.name[locale]

            }},
        {data: "state",render: function (data, type, row) {
                return row?.state?.name[locale]

            },},
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row, meta) {
                const canEdit = row?.action?.can_edit ?? false;
                const canDelete = row?.action?.can_delete ?? false;
                const btns = [];
                if (canEdit) {
                    btns.push(`<a href="#" class="edit-details"
               data-bs-toggle="modal"
                                   data-bs-target="#editLocationModal"
                                   data-bs-toggle="modal"
                                   data-id="${data}"
                                   data-name="${row.name}"
                                   data-address="${row.address_line}"
                                   data-address-link="${row.link}"
                                   data-state-id="${row.state.id}"
                                   data-country-id="${row.state.country.id}"
                                    data-days='${row.days}'
                                   data-available-time="${row.available_time}"
                                  >
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
async function loadStates(countryId, selectedStateId = "") {

    const $state = $("#editState");
    const statesUrl =$state.data("url");

    $state.empty().append('<option value="">Select a State</option>');
    if (!countryId || !statesUrl) return;

    try {
        const resp = await $.getJSON(statesUrl, { "filter[country_id]": countryId });

        // ادعم أشكال متعددة للـpayload
        const items =
            Array.isArray(resp) ? resp :
                Array.isArray(resp?.data) ? resp.data :
                    Array.isArray(resp?.states) ? resp.states :
                        [];

        if (!Array.isArray(items)) {
            console.error("Unexpected states payload:", resp);
            return;
        }

        items.forEach((s) => {
            // استخدم s.id و s.name (مش s.data.id)
            $state.append(`<option value="${s.id}">${s.name}</option>`);
        });


        if (selectedStateId) {
            $state.val(selectedStateId).trigger("change");
        }
    } catch (err) {
        console.error("Failed to load states", err);
    }
}

$(document).ready(function () {
// Helper: parse days سواء JSON أو CSV أو Array
    function parseDaysAttr(raw) {
        if (Array.isArray(raw)) return raw.map(String);

        if (typeof raw === "string") {
            const s = raw.trim();
            // جرّب JSON أولًا
            if ((s.startsWith("[") && s.endsWith("]")) || (s.startsWith('["') && s.endsWith('"]'))) {
                try {
                    const j = JSON.parse(s);
                    if (Array.isArray(j)) return j.map(String);
                } catch (e) { /* ignore */ }
            }
            // fallback: CSV
            return s
                .split(/[,\s]+/)        // يفصل بفواصل أو مسافات
                .map(v => v.trim())
                .filter(Boolean);
        }

        // بعض الأحيان jQuery.data بيرجع object {0:"MONDAY",1:"TUESDAY",...}
        if (raw && typeof raw === "object") {
            try {
                return Object.values(raw).map(String);
            } catch { /* ignore */ }
        }

        return [];
    }



    $(document).on("click", ".edit-details", async function (e) {
        const $btn = $(this);

        const locationId  = $btn.data('id') || '';
        const name        = $btn.data('name') || '';
        const address     = $btn.data('address') || '';
        const addressLink = $btn.data('address-link') || '';
        const stateId     = String($btn.data('state-id') || '');
        console.log(stateId)
        const countryId   = String($btn.data('country-id') || '');
        const daysRaw     = $btn.data('days');
        const days        = parseDaysAttr(daysRaw);
        const avail       = $btn.data('available-time') || '';
        console.log(days, daysRaw)

        $("#editLocationForm")[0].reset();
        $("#editDays").val(null).trigger('change');
        $("#editCountry").val('').trigger('change');
        $("#editState").empty().append('<option value="">Select a State</option>').val('').trigger('change');


        $("#editLocationName").val(name);
        $("#editAddressLine").val(address);
        $("#editAddressLink").val(addressLink);


        $("#editDays").val(daysRaw).trigger('change');


        if (avail.includes('-')) {
            const [start, end] = avail.split('-').map(s => s.trim());
            $("#edit_start_time").val(start);
            $("#edit_end_time").val(end);
            $("#edit_available_time").val(avail);
        } else if (avail) {
            $("#edit_available_time").val(avail);
        }


        $("#editCountry").val(countryId).trigger('change');


        const statesUrl = $("#editState").data('url');

        if (countryId && statesUrl) {
            loadStates(countryId,stateId)
        }


        $('#editLocationForm').attr('action', `locations/${locationId}`);
    });

    $("#edit_start_time, #edit_end_time").on('change', function () {
        const s = $("#edit_start_time").val();
        const e = $("#edit_end_time").val();
        $("#edit_available_time").val(s && e ? `${s} - ${e}` : '');
    });

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

});
handleAjaxFormSubmit("#deleteLocationForm", {
    successMessage: "Location deleted successfully",
    onSuccess: function () {
        $('#deleteLocationModal').modal('hide');
        location.reload()
    }
})
