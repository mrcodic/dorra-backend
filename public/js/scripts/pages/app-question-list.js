console.log("Sdads")
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".faq-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: faqsDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-category-form').val(); // get from input
            d.role_id = $('.filter-role').val();
            d.status = $('.filter-status').val();
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

        {data: "question"},
        {data: "created_at"},
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {

                return `

         <a href="#" class="edit-details"
           data-bs-toggle="modal"
           data-bs-target="#editQuestionModal"
           data-image="${row.media && row.media.length > 0 ? row.media[0].original_url : ''}"
           data-id="${data}"
           data-first-name="${row.first_name}"
           data-last-name="${row.last_name}"
           data-email="${row.email}"
           data-phone-number="${row.phone_number}"
           data-role-id="${row.roles && row.roles.length > 0 ? row.roles[0].id : ''}"
           data-status="${row.status_value}">

                <i data-feather="edit-3"></i>
              </a>

        <a href="#" class=" text-danger open-delete-faq-modal" data-id="${data}"
                data-bs-toggle="modal"
                data-bs-target="#deleteFaqModal" >
                <i data-feather="trash-2"></i>
              </a>

          </div>
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

// Custom search with debounce
let searchTimeout;
$('#search-category-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$('.filter-role, .filter-status').on('change', function () {
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
    $(document).on("click", ".edit-details", function (e) {
        const $button = $(this);

        const adminId = $button.data('id') || '';
        const firstName = $button.data('first-name') || '';
        const lastName = $button.data('last-name') || '';
        const phoneNumber = $button.data('phone-number') || '';
        const email = $button.data('email') || '';
        const roleId = $button.data('role-id') || '';
        const status = $button.data('status');
        const image = $button.data('image') || '{{ asset("images/avatar.png") }}';
        console.log(status)
        // Populate modal
        $("#editAdminModal #first_name").val(firstName);
        $("#editAdminModal #last_name").val(lastName);
        $("#editAdminModal #phone").val(phoneNumber);
        $("#editAdminModal #email").val(email);
        $("#editAdminModal #role").val(roleId);
        $("#editAdminModal #status").val(status);
        $("#editAdminModal .avatarPreview").attr("src", image);
        $('#editAdminForm').attr('action', `admins/${adminId}`);
    });


    $(document).on("click", ".open-delete-faq-modal", function () {
        const adminId = $(this).data("id");

        $("#deleteFaqForm").attr('action',`faqs/${adminId}`);
    });


    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "admins/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteAdminsModal").modal("hide");
                Toastify({
                    text: "Selected admins deleted successfully!",
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
                $(".faq-list-table").DataTable().ajax.reload(null, false);

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
                $(".faq-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });




});
