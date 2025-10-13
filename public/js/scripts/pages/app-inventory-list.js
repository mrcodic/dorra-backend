$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var dt_user_table = $(".inventory-list-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: inventoriesDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $("#search-inventory-form").val(); // get from input
            d.created_at = $(".filter-date").val();

            return d;
        },
    },
    columns: [
        {
            data: null,
            defaultContent: "",
            orderable: false,
            render: function (data, type, row, meta) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            }
        },
        {data: "name"},
        {data: "number"},
        {
            data: "id",
            orderable: false,
            render: function (data, type, row) {
                return `
        <div class="d-flex gap-1">

                            <a href="#" class="view-details"
                                   data-bs-toggle="modal"
                                     data-bs-target="#showInventoryModal"
                                     data-id="${data}"
                                     data-name="${row.name}"
                                     data-number="${row.number}"
                                     data-children_count="${row.available_places_count}"
                              >

                                     <i data-feather="eye"></i>
                                </a>

                          <a href="#" class="edit-details"
                           data-bs-toggle="modal"
                           data-bs-target="#editInventoryModal"
                                     data-id="${data}"
                                       data-name="${row.name}"
                                     data-number="${row.number}"
                                          >
                            <i data-feather="edit-3"></i>
                       </a>
            <a href="#" class="text-danger open-delete-order-modal"
               data-id="${data}"
               data-action="/inventories/${data}"
               data-bs-toggle="modal"
               data-bs-target="#deleteInventoryModal">
               <i data-feather="trash-2"></i>
            </a>
        </div>
    `;
            },
        },
    ],
    order: [[1, "asc"]],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
        '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
        ">" +
        ">t" +
        '<"d-flex  mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        ">",
    buttons: [
        {
            text: '<input type="date" class="form-control" style="width: 120px;" />',
            className: "btn border-0",
            action: function (e, dt, node, config) {
                e.preventDefault();
            },
        },
        {
            text: "Add New Inventory",
            className: "add-new btn btn-outline-primary",
            action: function (e, dt, node, config) {
                window.location.href = ordersCreateUrl;
            },
            init: function (api, node, config) {
                $(node).removeClass("btn-secondary");
            },
        },
    ],
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

function setActiveStatusCard(val) {

    $(".status-card").removeClass("selected");
    if (val) $(`.status-card[data-status="${val}"]`).addClass("selected");
}

// Search functionality
let searchTimeout;
$('#search-inventory-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});
$('#clear-search').on('click', function () {
    $('#search-inventory-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
$(".filter-date").on("change", function () {
    dt_user_table.draw();
});
$(".filter-status").on("change", function () {
    setActiveStatusCard($(this).val() || "");
    dt_user_table.draw();
});

$(document).on("click", ".status-card", function () {
    const status = $(this).data("status");

    // set the dropdown filter for consistency
    $(".filter-status").val(status);

    // redraw DataTable
    dt_user_table.draw();
});

// Category select with timeout
let categoryFilterTimeout;
$('.category-select').on('change', function () {
    clearTimeout(categoryFilterTimeout);
    categoryFilterTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Tag select with timeout
let tagFilterTimeout;
$('.tag-select').on('change', function () {
    clearTimeout(tagFilterTimeout);
    tagFilterTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Checkbox selection functionality
$(document).on('change', '#select-all-checkbox', function () {
    const isChecked = $(this).prop('checked');
    $('.category-checkbox').prop('checked', isChecked);
    toggleBulkDeleteContainer();
});

$(document).on('change', '.category-checkbox', function () {
    const totalCheckboxes = $('.category-checkbox').length;
    const checkedCheckboxes = $('.category-checkbox:checked').length;

    $('#select-all-checkbox').prop('checked', totalCheckboxes === checkedCheckboxes);
    toggleBulkDeleteContainer();
});

function toggleBulkDeleteContainer() {
    const checkedCount = $('.category-checkbox:checked').length;
    if (checkedCount > 0) {
        $('#bulk-delete-container').show();
        $('#selected-count').text(checkedCount);
    } else {
        $('#bulk-delete-container').hide();
    }
}

$(document).ready(function () {
    $(document).on('click', '.view-details', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $m = $('#showInventoryModal');
        const name   = $btn.data('name') ?? '';
        const value  = $btn.data('number') ?? '';
        const count  = $btn.data('children_count') ?? '';
        console.log(count,value,name)

        $m.find('#show-name').val(name);
        $m.find('#show-number').val(value);
        $m.find('#show-children-count').val(count);

        $m.modal('show');
    });

    $(document).on('click', '.edit-details', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const id       = $btn.data('id');
        const name   = $btn.data('name') ?? '';
        const value  = $btn.data('number') ?? '';

        const $m = $('#editInventoryModal');
        $('#editInventoryForm').attr('action', '/inventories/' + id);
        $m.find('#editInventoryName').val(name);
        $m.find('#editInventoryNumber').val(value);

        $m.modal('show');
    });

    // Alternative modal-based delete handler
    $(document).on("click", ".open-delete-order-modal", function () {
        const orderId = $(this).data("id");
        console.log(orderId);
        $("#deleteInventoryForm").data("id", orderId);
        $("#deleteInventoryModal").modal("show");
    });

    // Single order delete form submission


    // Bulk delete form submission
    // $(document).on("submit", "#bulk-delete-form", function (e) {
    //     e.preventDefault();
    //     const selectedIds = $(".category-checkbox:checked").map(function () {
    //         return $(this).val();
    //     }).get();

    if (selectedIds.length === 0) {
        Toastify({
            text: "Please select at least one order to delete!",
            duration: 2000,
            gravity: "top",
            position: "right",
            backgroundColor: "#EA5455",
            close: true,
        }).showToast();
        return;
    }

    if (confirm(`Are you sure you want to delete ${selectedIds.length} selected order(s)?`)) {
        bulkDeleteOrders(selectedIds);
    }
});

// Bulk delete button handler
$(document).on("click", "#bulk-delete-btn", function (e) {
    e.preventDefault();
    const selectedIds = $(".category-checkbox:checked").map(function () {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
        Toastify({
            text: "Please select at least one order to delete!",
            duration: 2000,
            gravity: "top",
            position: "right",
            backgroundColor: "#EA5455",
            close: true,
        }).showToast();
        return;
    }

    $("#deleteInventoriesModal").modal("show");
});

// Confirm bulk delete modal
$(document).on("click", "#confirm-bulk-delete", function () {
    const selectedIds = $(".category-checkbox:checked").map(function () {
        return $(this).val();
    }).get();

    if (selectedIds.length > 0) {
        bulkDeleteOrders(selectedIds);
    }
});

// Single order delete function
$(document).on("submit", "#deleteInventoryForm", function (e) {
    e.preventDefault();
    const OrderId = $(this).data("id");
    console.log(OrderId);

    $.ajax({
        url: `/inventories/${OrderId}`,
        method: "DELETE",
        success: function (res) {

            $("#deleteInventoryModal").modal("hide");

            Toastify({
                text: "Inventory deleted successfully!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28C76F",
                close: true,
            }).showToast();
            $(".inventory-list-table").DataTable().ajax.reload(null, false);

        },
        error: function () {

            $("#deleteInventoryModal").modal("hide");
            Toastify({
                text: "Something Went Wrong!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EA5455", // red
                close: true,
            }).showToast();
            $(".inventory-list-table").DataTable().ajax.reload(null, false);

        },
    });
});


// Bulk delete orders function
$(document).on("submit", "#bulk-delete-form", function (e) {
    e.preventDefault();
    const selectedIds = $(".category-checkbox:checked").map(function () {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) return;

    $.ajax({
        url: "inventories/bulk-delete",
        method: "POST",
        data: {
            ids: selectedIds,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            $("#deleteInventoriesModal").modal("hide");
            Toastify({
                text: "Selected inventories deleted successfully!",
                duration: 1500,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745",
                close: true,
            }).showToast();


            $('#bulk-delete-container').hide();
            $('.category-checkbox').prop('checked', false);
            $('#select-all-checkbox').prop('checked', false);
            $(".category-list-table").DataTable().ajax.reload(null, false);


            resetBulkSelection();
            $(".inventory-list-table").DataTable().ajax.reload(null, false);

        },
        error: function () {
            $("#deleteInventoriesModal").modal("hide");
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
            $('.category-checkbox').prop('checked', false);
            $('#select-all-checkbox').prop('checked', false);
            $(".category-list-table").DataTable().ajax.reload(null, false);


        },
    });

});

// Reset bulk selection function
function resetBulkSelection() {
    $('#bulk-delete-container').hide();
    $('.category-checkbox').prop('checked', false);
    $('#select-all-checkbox').prop('checked', false);
}

// Date filter functionality (if needed)
$(document).on('change', '.dt-button input[type="date"]', function () {
    const selectedDate = $(this).val();
    // Add your date filtering logic here
    dt_user_table.draw();
});
