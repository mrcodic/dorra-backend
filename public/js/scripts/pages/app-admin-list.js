$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".admin-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: adminsDataUrl,
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
        {data: "id"},
        {
            data: "image",
            render: function (data, type, row) {
                return `
            <img src="${data}" alt="Admin Image"
                style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid #ccc;" />
        `;
            }
        },
        {data: "name"},
        {data: "email"},
        {data: "role"},
        {
            data: "status",
            render: function (data, type, row, meta) {
                let textColor = "";
                let bgColor = "";

                switch (data) {
                    case "active":
                        textColor = "text-success";
                        bgColor = "#D7EEDD"; // light green
                        break;
                    case "blocked":
                        textColor = "text-secondary";
                        bgColor = "#F0F0F0"; // light gray
                        break;
                    default:
                        textColor = "text-muted";
                        bgColor = "#E9ECEF"; // default gray
                }

                return `<span class="badge rounded-pill ${textColor} px-1" style="background-color: ${bgColor};">${data}</span>`;
            },
        },

        {data: "created_at"},
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                console.log(row)
                return `

         <a href="#" class="edit-details"
           data-bs-toggle="modal"
           data-bs-target="#editAdminModal"
           data-image="${row.image ? row.image : ''}"
           data-image-id="${row.image_id ? row.image_id : ''}"
           data-id="${data}"
           data-first-name="${row.first_name}"
           data-last-name="${row.last_name}"
           data-email="${row.email}"
           data-phone-number="${row.phone_number}"
           data-role-id="${row.roles && row.roles.length > 0 ? row.roles[0].id : ''}"
           data-status="${row.status_value}">

                <i data-feather="edit-3"></i>
              </a>

        <a href="#" class=" text-danger open-delete-admin-modal" data-id="${data}"
                data-bs-toggle="modal"
                data-bs-target="#deleteAdminModal" >
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
        const image = $button.data('image') || defaultImage;

        // ✅ Populate modal fields
        $("#editAdminModal #first_name").val(firstName);
        $("#editAdminModal #last_name").val(lastName);
        $("#editAdminModal #phone").val(phoneNumber);
        $("#editAdminModal #email").val(email);
        $("#editAdminModal #role").val(roleId);
        $("#editAdminModal #status").val(status);
        $("#editAdminModal .avatarPreview").attr("src", image);
        $('#editAdminForm').attr('action', `admins/${adminId}`);

        // ✅ Preload existing image into Dropzone
        if (image && image !== defaultImage) {
            const dz = Dropzone.forElement("#editAvatarDropzone");

            dz.removeAllFiles(true); // clear old previews

            let mockFile = { name: "Current Avatar", size: 12345, type: 'image/jpeg' };
            dz.emit("addedfile", mockFile);
            dz.emit("thumbnail", mockFile, image);
            dz.emit("complete", mockFile);

            // ✅ prevent Dropzone from re-uploading this
            dz.files.push(mockFile);

            // ✅ also add hidden input for existing image_id (if you pass it in data-image-id)
            const imageId = $button.data('image-id') || '';
            if (imageId) {
                $(".edit-avatar-media-ids").html(
                    `<input type="hidden" name="image_id" value="${imageId}">`
                );
            }
        } else {
            // reset dropzone if no image
            Dropzone.forElement("#editAvatarDropzone").removeAllFiles(true);
            $(".edit-avatar-media-ids").html("");
        }
    });

    $(document).on("click", ".open-delete-admin-modal", function () {
        const adminId = $(this).data("id");

        $("#deleteAdminForm").attr('action',`admins/${adminId}`);
    });


    handleAjaxFormSubmit('#deleteAdminForm', {
        successMessage: "✅ Admin deleted successfully!",
        closeModal: '#deleteAdminModal',
        onSuccess: function (response, $form) {
            $(".admin-list-table").DataTable().ajax.reload(null, false); // false = stay on current page
        }
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
                $(".admin-list-table").DataTable().ajax.reload(null, false);

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
                $(".admin-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });




});
