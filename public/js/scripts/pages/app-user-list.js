$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

let dateSortOrder = "asc";

var dt_user_table = $(".user-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    ajax: {
        url: usersDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $("#search-user-form").val(); // get from input
            d.created_at = $(".filter-date").val();
            return d;
        },
    },
    columns: [
        {
            data: null,
            defaultContent: "",
            orderable: false,
            render: function (data, type, row) {
                return row?.action?.can_delete
                    ? `<input type="checkbox" name="ids[]" class="category-checkbox" value="${row.id}">`
                    : '';
            },
        },
        { data: "name" },
        {
            data: "image",
            render: function (data, type, row) {
                return `
            <img src="${data}" alt="User Image"
                style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid #ccc;" />
        `;
            }
        },
        { data: "email" },
        {
            data: "status",
            render: function (data, type, row, meta) {
                let textColor = "";
                let bgColor = "";

                switch (data.toLowerCase()) {
                    case "active":
                        textColor = "text-success";
                        bgColor = "#D7EEDD"; // light green
                        break;
                    case "inactive":
                        textColor = "text-secondary";
                        bgColor = "#F0F0F0"; // light gray
                        break;
                    case "pending":
                        textColor = "text-warning";
                        bgColor = "#FFF3CD"; // light yellow
                        break;
                    default:
                        textColor = "text-muted";
                        bgColor = "#E9ECEF"; // default gray
                }

                return `<span class="badge rounded-pill ${textColor} px-1" style="background-color: ${bgColor};">${data}</span>`;
            },
        },
        { data: "joined_date" },
        { data: "orders_count" },
        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                const canShow   = row?.action?.can_show   ?? false;
                const canEdit   = row?.action?.can_edit   ?? false;
                const canDelete = row?.action?.can_delete ?? false;

                const btns = [];

                if (canShow) {
                    btns.push(`
        <a href="/users/${data}" class="text-body" aria-label="View user">
          <i data-feather="eye"></i>
        </a>
      `);
                }

                if (canEdit) {
                    btns.push(`
        <a href="/users/${data}/edit" class="text-body" aria-label="Edit user">
          <i data-feather="edit-3"></i>
        </a>
      `);
                }

                if (canDelete) {
                    btns.push(`
        <a href="#" class="text-danger open-delete-user-modal"
           data-id="${data}"
           data-action="/users/${data}"
           data-bs-toggle="modal"
           data-bs-target="#deleteUserModal"
           aria-label="Delete user">
          <i data-feather="trash-2"></i>
        </a>
      `);
                }

                if (!btns.length) return ''; // no permissions -> empty cell
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
        '<"d-flex  mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        ">",
    drawCallback: function () {
        feather.replace();
        $("#select-all-checkbox").prop("checked", false);
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

$('#clear-search').on('click', function () {
    $('#search-user-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
// Custom search with debounce
let searchTimeout;
$("#search-user-form").on("keyup", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$(".filter-date").on("change", function () {
    dt_user_table.draw();
});

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

$(document).ready(function () {
    $(document).ready(function () {
        $(".add-new-user").submit(function (event) {
            event.preventDefault();

            var form = $(this);
            var isChecked = $("#status").is(":checked");
            var status = isChecked ? 1 : 0;
            form.find('input[name="status"]').remove();
            form.append(
                '<input type="hidden" name="status" value="' + status + '">'
            );

            var selectedOption = $("#phone-code option:selected");
            var phoneCode = selectedOption.data("phone-code");
            var phoneNumber = $("#phone_number").val();
            var fullPhoneNumber = phoneCode + phoneNumber.replace(/\D/g, "");
            $("#full_phone_number").val(fullPhoneNumber);

            var actionUrl = form.attr("action");

            $(".alert-danger").remove();
            let formData = new FormData(form[0]);
            $.ajax({
                url: actionUrl,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response);
                    if (response.success) {
                        form[0].reset();
                        $("#modals-slide-in").modal("hide");
                        Toastify({
                            text: "User added successfully!",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true,
                        }).showToast();
                    }
                },
                error: function (xhr) {
                    var errors = xhr.responseJSON.errors;

                    for (var key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            Toastify({
                                text: errors[key][0],
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#EA5455", // red for errors
                                close: true,
                            }).showToast();
                        }
                    }
                },
            });
        });
    });

    $(document).on("change", ".country-select", function () {
        const countryId = $(this).val();
        const stateSelect = $(this)
            .closest("[data-repeater-item]")
            .find(".state-select");
        const baseUrl = $("#state-url").data("url");
        if (countryId) {
            $.ajax({
                url: `${baseUrl}?filter[country_id]=${countryId}`,
                method: "GET",
                success: function (response) {
                    stateSelect
                        .empty()
                        .append('<option value="">Select State</option>');
                    $.each(response.data, function (index, state) {
                        stateSelect.append(
                            `<option value="${state.id}">${state.name}</option>`
                        );
                    });
                },
                error: function () {
                    stateSelect
                        .empty()
                        .append(
                            '<option value="">Error loading states</option>'
                        );
                },
            });
        } else {
            stateSelect
                .empty()
                .append('<option value="">Select State</option>');
        }
    });

    $(document).on("click", ".open-delete-user-modal", function () {
        const userId = $(this).data("id");
        $("#deleteUserForm").data("id", userId);
    });

    $(document).on("submit", "#deleteUserForm", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");

        $.ajax({
            url: `/users/${userId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteUserModal").modal("hide");

                Toastify({
                    text: "User deleted successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".user-list-table").DataTable().ajax.reload(null, false);
            },
            error: function () {
                $("#deleteUserModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                $(".user-list-table").DataTable().ajax.reload(null, false);
            },
        });
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
            url: "users/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteUsersModal").modal("hide");
                Toastify({
                    text: "Selected users deleted successfully!",
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
                $(".user-list-table").DataTable().ajax.reload(null, false);
            },
            error: function () {
                $("#deleteUsersModal").modal("hide");
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
                $(".user-checkbox").prop("checked", false);
                $("#select-all-checkbox").prop("checked", false);
                $(".category-list-table").DataTable().ajax.reload(null, false);
            },
        });
    });
});
