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
            d.search_value = $('#search-user-form').val(); // get from input
            d.created_at = $('.filter-date').val();
            return d;
        }
    },
    columns: [
        {
            data: null,
            defaultContent: "",
            orderable: false,
            render: function (data, type, row, meta) {
                return `<input type="checkbox" class="category-checkbox form-check-input" value="${data}">`;
            },
        },
        { data: "name" },
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
                console.log(data);
                return `
        <div class="d-flex gap-1">
             <a href="/users/${data}" class="">
                <i data-feather="eye"></i>
              </a>
              <a href="/users/${data}/edit" class="">
                <i data-feather="edit-3"></i>
              </a>

              <a href="#" class=" text-danger delete-user" data-id="${data}">
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

// Custom search with debounce
let searchTimeout;
$('#search-user-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$('.filter-date').on('change', function () {
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

    $(document).on("click", ".delete-user", function (e) {
        e.preventDefault();

        var $table = $(".user-list-table").DataTable();
        var $row = $(this).closest("tr");
        var rowData = $table.row($row).data();

        var userId = $(this).data("id");
        var userName = rowData.name;

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to delete user "${userName}". This action cannot be undone.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it!",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/users/${userId}`,
                    method: "DELETE",
                    success: function (res) {
                        Swal.fire(
                            "Deleted!",
                            "User has been deleted.",
                            "success"
                        );
                        $table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire("Failed", "Could not delete user.", "error");
                    },
                });
            }
        });
    });
});
