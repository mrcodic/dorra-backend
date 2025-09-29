$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var dt_user_table = $(".job-list-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: jobsDataUrl,
        type: "GET",

        data: function (d) {
            d.search_value = $("#search-invoice-form").val(); // get from input
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
        {data: "code"},
        {data: "priority_label"},
        {data: "current_station"},
        {data: "status_label"},
        {data: "due_at"},
        {data: "order_number"},
        {data: "order_item_name"},
        {
            data: "id",
            orderable: false,
            render: function (data, type, row) {
                return `

        <div class="d-flex gap-1">
             <a href="jobs/${data}" class="view-details"

             >
                <i data-feather="eye"></i>
            </a>
            <a href="#" class="edit-details"
                data-id="${data}"
  data-station="${row.station_id}"
   data-priority="${row.priority}"
   data-due_at="${row.due_at}"
   data-status="${row.status}"
   data-action = "jobs/${data}"

     >

   <i data-feather="edit-3"></i>
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

// Custom search with debounce
let searchTimeout;
$("#search-invoice-form").on("keyup", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});
$('#clear-search').on('click', function () {
    $('#search-invoice-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
$(".filter-date").on("change", function () {
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
    $(document).on('click', '.edit-details', function (e) {
        const jobStatus = $(this).data('status');
        const jobPriority = $(this).data('priority');
        const jobStationId = $(this).data('station');
        const jobDueAt = $(this).data('due_at');
        const action = $(this).data('action');


        // Populate modal
        $('#editJobModal #edit-station-id').val(jobStationId);
        $('#editJobModal #edit-status').val(jobStatus);
        $('#editJobModal #edit-due-at').val(jobDueAt);
        $('#editJobModal #edit-priority').val(jobPriority);
        $('#editJobModal #editJobForm').attr('action', action);

        // Show modal
        $('#editJobModal').modal('show');

    });
    $(document).on('click', 'view-details', function (e) {


        // Show modal
        $('#showJobModal').modal('show');

    });


    $(document).on("click", ".open-delete-order-modal", function () {
        const orderId = $(this).data("id");
        $("#deleteInvoiceForm").data("id", orderId);
        let modalEl = document.getElementById("deleteInvoiceModal");
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });

});
handleAjaxFormSubmit("#editJobForm", {
    successMessage: "Job Updated Successfully",
    onSuccess: function () {
        location.reload()
    }

})
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

    let modalEl = document.getElementById("deleteInvoicesModal");
    let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
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

function bulkDeleteOrders(ids) {
    $.ajax({
        url: "invoices/bulk-delete",
        method: "POST",
        data: {
            ids: ids,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function () {
            let modalEl = document.getElementById("deleteInvoicesModal");
            let modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            Toastify({
                text: "Selected invoices deleted successfully!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745",
                close: true,
            }).showToast();

            resetBulkSelection();
            $(".job-list-table").DataTable().ajax.reload(null, false);
        },
        error: function () {
            let modalEl = document.getElementById("deleteInvoicesModal");
            let modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            Toastify({
                text: "Something Went Wrong!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EA5455",
                close: true,
            }).showToast();

            resetBulkSelection();
            $(".job-list-table").DataTable().ajax.reload(null, false);
        },
    });
}

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
            $(".job-list-table").DataTable().ajax.reload(null, false);


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
            $(".job-list-table").DataTable().ajax.reload(null, false);

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
                text: "Selected invoices deleted successfully!",
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
            $(".job-list-table").DataTable().ajax.reload(null, false);

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

$(document).on('change', '.dt-button input[type="date"]', function () {
    const selectedDate = $(this).val();
    dt_user_table.draw();
});
