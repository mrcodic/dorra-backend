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
        { data: "invoice_number" },
        { data: "user_name" },
        { data: "total_price" },
        // { data: "status" },
        { data: "issued_date" },
        {
            data: "id",
            orderable: false,
           render: function(data, type, row) {
    return `
        <div class="d-flex gap-1">
            <a href="/invoices/${data}" class="">
                <i data-feather="eye"></i>
            </a>
            <a href="#" class="text-danger open-delete-order-modal"
               data-id="${data}"
               data-action="/invoices/${data}"
               data-bs-toggle="modal"
               data-bs-target="#deleteInvoiceModal">
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
        // '<"d-flex align-items-center flex-grow-1 me-2"f>' + // Search input
        // '<"d-flex align-items-center gap-1"B>' + // Buttons + Date Filter
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

    $(document).on("click", ".delete-order", function (e) {
        e.preventDefault();
        const orderId = $(this).data("id");

        if (confirm("Are you sure you want to delete this order?")) {
            deleteOrder(orderId);
        }
    });

    $(document).on("click", ".open-delete-order-modal", function () {
        const orderId = $(this).data("id");
        console.log(orderId);
        $("#deleteInvoiceForm").data("id", orderId);
        $("#deleteInvoiceModal").modal("show");
    });



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

        $("#deleteInvoicesModal").modal("show");
    });

    $(document).on("click", "#confirm-bulk-delete", function () {
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length > 0) {
            bulkDeleteOrders(selectedIds);
        }
    });

$(document).on("submit", "#deleteInvoiceForm", function (e) {
    e.preventDefault();
    const productId = $(this).data("id");

    $.ajax({
        url: `/invoices/${productId}`,
        method: "DELETE",
        success: function (res) {
            $("#deleteInvoiceModal").modal("hide");

            Toastify({
                text: "Invoice deleted successfully!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28C76F",
                close: true,
            }).showToast();
            $(".order-list-table").DataTable().ajax.reload(null, false);


        },
        error: function () {
            $("#deleteInvoiceModal").modal("hide");
            Toastify({
                text: "Something Went Wrong!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EA5455",
                close: true,
            }).showToast();
            $(".order-list-table").DataTable().ajax.reload(null, false);

        },
    });
});

    $(document).on("submit", "#bulk-delete-form", function (e) {
            e.preventDefault();
            const selectedIds = $(".category-checkbox:checked").map(function () {
                return $(this).val();
            }).get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "invoices/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteInvoicesModal").modal("hide");
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
                        $("#deleteInvoicesModal").modal("hide");
                        Toastify({
                            text: "Something Went Wrong!",
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




                    },
                });

            });

        function resetBulkSelection() {
            $('#bulk-delete-container').hide();
            $('.category-checkbox').prop('checked', false);
            $('#select-all-checkbox').prop('checked', false);
        }

        $(document).on('change', '.dt-button input[type="date"]', function() {
            const selectedDate = $(this).val();
            dt_user_table.draw();
        });
