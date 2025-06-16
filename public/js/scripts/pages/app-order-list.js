$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var dt_user_table = $(".order-list-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: ordersDataUrl,
        type: "GET",
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
        { data: "order_number" },
        { data: "user_name" },
        { data: "items" },
        { data: "total_price" },
        { data: "status" },
        { data: "added_date" },
        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                return `
          <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i data-feather="more-vertical"></i>
            </button>
            <div class="dropdown-menu">
             <a href="/orders/${data}" class="dropdown-item">
                <i data-feather="file-text"></i> Details
              </a>
               <a href="/orders/${data}/edit" class="dropdown-item">
                <i data-feather="edit"></i> Edit
              </a>
              <a href="#" class="dropdown-item text-danger delete-order" data-id="${data}">
                <i data-feather="trash-2"></i> Delete
              </a>
            </div>
          </div>
        `;
            },
        },
    ],
    order: [[1, "asc"]],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
        '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
        '<"d-flex align-items-center flex-grow-1 me-2"f>' + // Search input
        '<"d-flex align-items-center gap-1"B>' + // Buttons + Date Filter
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
            text: "Add New Order",
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

// Search functionality
let searchTimeout;
$('#search-product-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
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
    // Success messages for orders
    if (sessionStorage.getItem("order_added") == "true") {
        Toastify({
            text: "Order added successfully!",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "#28a745",
            close: true,
        }).showToast();
        sessionStorage.removeItem("order_added");
    }
    
    if (sessionStorage.getItem("order_updated") == "true") {
        Toastify({
            text: "Order updated successfully!",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "#28a745",
            close: true,
        }).showToast();
        sessionStorage.removeItem("order_updated");
    }

    // Single order delete modal handler
    $(document).on("click", ".delete-order", function (e) {
        e.preventDefault();
        const orderId = $(this).data("id");
        
        // Show confirmation modal
        if (confirm("Are you sure you want to delete this order?")) {
            deleteOrder(orderId);
        }
    });

    // Alternative modal-based delete handler
    $(document).on("click", ".open-delete-order-modal", function () {
        const orderId = $(this).data("id");
        $("#deleteOrderForm").data("id", orderId);
        $("#deleteOrderModal").modal("show");
    });

    // Single order delete form submission
    $(document).on("submit", "#deleteOrderForm", function (e) {
        e.preventDefault();
        const orderId = $(this).data("id");
        deleteOrder(orderId);
    });

    // Bulk delete form submission
    $(document).on("submit", "#bulk-delete-form", function (e) {
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

        $("#deleteOrdersModal").modal("show");
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
});

// Single order delete function
function deleteOrder(orderId) {
    $.ajax({
        url: `/orders/${orderId}`,
        method: "DELETE",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function() {
            // Show loading state
            Toastify({
                text: "Deleting order...",
                duration: 1000,
                gravity: "top",
                position: "right",
                backgroundColor: "#17a2b8",
                close: true,
            }).showToast();
        },
        success: function (response) {
            // Hide any open modals
            $("#deleteOrderModal").modal("hide");
            
            Toastify({
                text: "Order deleted successfully!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28C76F",
                close: true,
            }).showToast();
            
            // Reload the DataTable
            dt_user_table.ajax.reload(null, false);
        },
        error: function (xhr, status, error) {
            // Hide any open modals
            $("#deleteOrderModal").modal("hide");
            
            let errorMessage = "Something went wrong!";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            Toastify({
                text: errorMessage,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EA5455",
                close: true,
            }).showToast();
        }
    });
}

// Bulk delete orders function
function bulkDeleteOrders(selectedIds) {
    $.ajax({
        url: "/orders/bulk-delete",
        method: "POST",
        data: {
            ids: selectedIds,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function() {
            // Show loading state
            Toastify({
                text: `Deleting ${selectedIds.length} order(s)...`,
                duration: 1000,
                gravity: "top",
                position: "right",
                backgroundColor: "#17a2b8",
                close: true,
            }).showToast();
        },
        success: function (response) {
            // Hide any open modals
            $("#deleteOrdersModal").modal("hide");
            
            Toastify({
                text: `${selectedIds.length} order(s) deleted successfully!`,
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745",
                close: true,
            }).showToast();

            // Reset checkboxes and hide bulk delete container
            resetBulkSelection();
            
            // Reload the DataTable
            dt_user_table.ajax.reload(null, false);
        },
        error: function (xhr, status, error) {
            // Hide any open modals
            $("#deleteOrdersModal").modal("hide");
            
            let errorMessage = "Something went wrong while deleting orders!";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            Toastify({
                text: errorMessage,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EA5455",
                close: true,
            }).showToast();

            // Reset checkboxes and hide bulk delete container
            resetBulkSelection();
        },
    });
}

// Reset bulk selection function
function resetBulkSelection() {
    $('#bulk-delete-container').hide();
    $('.category-checkbox').prop('checked', false);
    $('#select-all-checkbox').prop('checked', false);
}

// Date filter functionality (if needed)
$(document).on('change', '.dt-button input[type="date"]', function() {
    const selectedDate = $(this).val();
    // Add your date filtering logic here
    dt_user_table.draw();
});