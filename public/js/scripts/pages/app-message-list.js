$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".message-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: messagesDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-message-form').val();
            d.created_at = $(".filter-date").val();

            console.log(d.status)
            return d;
        }
    },
    columns: [
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            }
        },
        {data: "email"},
        {data: "phone"},
        {data: "message"},
        {data: "created_at"},
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
        <a href="#" class="show-details"
           data-bs-toggle="modal"
           data-bs-target="#showMessageModal"
           data-id="${data}"
           data-name="${row.name}"
           data-phone="${row.phone}"
           data-email="${row.email}"
           data-message="${row.message}"
           data-created_at="${row.created_at}">
            <i data-feather="eye"></i>
        </a>

        <a href="#" class="text-danger open-delete-admin-modal"
           data-id="${data}"
           data-bs-toggle="modal"
           data-bs-target="#deleteMessageModal">
            <i data-feather="trash-2"></i>
        </a>
    `;
            }

        }
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
            next: "&nbsp;"
        }
    }
});
$('#clear-search').on('click', function () {
    $('#search-message-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
// Custom search with debounce
let searchTimeout;
$('#search-message-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$('.filter-date').on('change', function () {
    dt_user_table.ajax.reload();
});


// Checkbox select all
$('#select-all-checkbox').on('change', function () {
    $('.category-checkbox').prop('checked', this.checked);
    updateBulkDeleteVisibility();
});

// Single checkbox toggle
$(document).on('change', '.category-checkbox', function () {
    if (!this.checked) {
        $('#select-all-checkbox').prop('checked', false);
    } else if ($('.category-checkbox:checked').length === $('.category-checkbox').length) {
        $('#select-all-checkbox').prop('checked', true);
    }
    updateBulkDeleteVisibility();
});

// Redraw table resets checkboxes
dt_user_table.on('draw', function () {
    $('#select-all-checkbox').prop('checked', false);
    $('#bulk-delete-container').hide();
});

// Update bulk delete container
function updateBulkDeleteVisibility() {
    const selected = $('.category-checkbox:checked').length;
    if (selected > 0) {
        $('#selected-count-text').text(`${selected} Category${selected > 1 ? 'ies' : 'y'} are selected`);
        $('#bulk-delete-container').show();
    } else {
        $('#bulk-delete-container').hide();
    }
}


// Listen to checkbox change
$(document).on("change", ".category-checkbox", function () {
    let checkedCount = $(".category-checkbox:checked").length;
    $("#bulk-delete-container").toggle(checkedCount > 0);
});
// Select All functionality
$(document).on('change', '#select-all-checkbox', function () {
    const isChecked = $(this).is(':checked');
    $('.category-checkbox').prop('checked', isChecked).trigger('change');
});
// Update "Select All" checkbox based on individual selections
$(document).on('change', '.category-checkbox', function () {
    const all = $('.category-checkbox').length;
    const checked = $('.category-checkbox:checked').length;

    $('#select-all-checkbox').prop('checked', all === checked);
    $('#bulk-delete-container').toggle(checked > 0);
});


// Optional: Hide button when table is redrawn
dt_user_table.on("draw", function () {
    $("#bulk-delete-container").hide();
});

$(document).ready(function () {
    $(document).on("click", ".show-details", function () {
        const id = $(this).data("id");
        const name = $(this).data("name");
        const email = $(this).data("email");
        const phone = $(this).data("phone");
        const message = $(this).data("message");
        const createdAt = $(this).data("created_at");

        // Fill modal fields
        $("#modalName").val(name);
        $("#modalEmail").val(email);
        $("#modalPhone").val(phone);
        $("#modalMessage").val(message);
        $("#modalCreatedAt").val(createdAt);
        let actionUrl = `/messages/${id}/reply`;
        $("#editMessageForm").attr("action", actionUrl);
    });


    $(document).on("click", ".open-delete-admin-modal", function () {
        const messageId = $(this).data("id");

        $("#deleteMessageForm").attr('action',`messages/${messageId}`);
    });


    handleAjaxFormSubmit('#deleteMessageForm', {
        successMessage: "✅ Message deleted successfully!",
        closeModal: '#deleteMessageModal',
        onSuccess: function (response, $form) {
            $(".message-list-table").DataTable().ajax.reload(null, false); // false = stay on current page
        }
    });

    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "messages/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteMessagesModal").modal("hide");
                Toastify({
                    text: "Selected messages deleted successfully!",
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
                $(".message-list-table").DataTable().ajax.reload(null, false);

            },
            error: function () {
                $("#deleteCategoriesModal").modal("hide");
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
                $('.product-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".message-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });




});
