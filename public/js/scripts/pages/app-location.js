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
                                   data-countory-id="${row.state.countory.id}"
                                   data-days="${row.days}"
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

    // Delete the selected image
    $("#delete-image-button").on("click", function () {
        $("#edit-image-upload").val(""); // clear the file input
        $("#edit-image-preview-container").hide(); // hide preview container
        $("#edit-image-preview").attr("src", ""); // clear the img src
        $("#edit-image-details").hide(); // hide file details
    });

    $("#add-image-upload").on("change", function (event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                $("#add-image-preview").attr("src", e.target.result);
                $("#add-image-preview-container").show();

                // Show file name and size
                const fileSize = (file.size / 1024).toFixed(2); // size in KB
                $("#add-image-details")
                    .text(`${file.name} • ${fileSize} KB`)
                    .show();
            };

            reader.readAsDataURL(file); // Read the file as DataURL for preview
        }
    });

    // Delete the selected image
    $("#delete-image").on("click", function () {
        $("#add-image-upload").val(""); // clear the file input
        $("#add-image-preview-container").hide(); // hide preview container
        $("#add-image-preview").attr("src", ""); // clear the img src
        $("#add-image-details").hide(); // hide file details
    });
});
handleAjaxFormSubmit("#deleteLocationForm", {
    successMessage: "Location deleted successfully",
    onSuccess: function () {
        $('#deleteLocationModal').modal('hide');
        location.reload()
    }
})
