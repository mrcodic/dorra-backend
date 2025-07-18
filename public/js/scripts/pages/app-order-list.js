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
           render: function(data, type, row) {
    return `
        <div class="d-flex gap-1">
            <a href="/orders/${data}" class="">
                <i data-feather="file-text"></i>
            </a>
            <a href="/orders/${data}/edit" class="">
                <i data-feather="edit"></i>
            </a>
            <a href="#" class="text-danger open-delete-order-modal"
               data-id="${data}"
               data-name="${row.order_number}"
               data-action="/orders/${data}"
               data-bs-toggle="modal"
               data-bs-target="#deleteOrderModal">
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
        console.log(orderId);
        $("#deleteOrderForm").data("id", orderId);
        $("#deleteOrderModal").modal("show");
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

// Single order delete function
   $(document).on("submit", "#deleteOrderForm", function (e) {
        e.preventDefault();
        const OrderId = $(this).data("id");
        console.log(OrderId);

        $.ajax({
            url: `/orders/${OrderId}`,
            method: "DELETE",
            success: function (res) {

                $("#deleteOrderModal").modal("hide");

                Toastify({
                    text: "Order deleted successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".order-list-table").DataTable().ajax.reload(null, false);

            },
            error: function () {

                $("#deleteOrderModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                              $(".order-list-table").DataTable().ajax.reload(null, false);

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
            url: "orders/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteOrdersModal").modal("hide");
                Toastify({
                    text: "Selected orders deleted successfully!",
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
            $(".order-list-table").DataTable().ajax.reload(null, false);

            },
            error: function () {
                $("#deleteOrdersModal").modal("hide");
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
$(document).on('change', '.dt-button input[type="date"]', function() {
    const selectedDate = $(this).val();
    // Add your date filtering logic here
    dt_user_table.draw();
});
