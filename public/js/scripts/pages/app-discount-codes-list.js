$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var dt_user_table = $(".order-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    ajax: {
        url: ordersDataUrl,
        type: "GET",
    },
    columns: [
        { data: null, defaultContent: "", orderable: false },
        { data: "name" },
        { data: "category" },
        {
            data: "tags",
            render: function (data, type, row) {
                if (!Array.isArray(JSON.parse(data))) return '';
                return `
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                ${JSON.parse(data)
                    .map(
                        tag => `
                        <span style="
                            background-color: #FCF8FC;
                            color: #000;
                            padding: 6px 12px;
                            border-radius: 12px;
                            font-size: 14px;
                            display: inline-block;
                        ">
                            ${tag}
                        </span>`
                    )
                    .join("")}
            </div>
        `;
            },
        },
        { data: "no_of_purchas" },
        { data: "added_date" },
        { data: "rating" },
        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                return `
         <div class="d-flex gap-1">
             <a href="/discount-codes/${data}" class="">
                <i data-feather="eye"></i>
              </a>
              <a href="/discount-codes/${data}/edit" class="">
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
        '<"d-flex align-items-center flex-grow-1 me-2"f>' + // Search input
        '<"d-flex align-items-center gap-1"B>' + // Buttons + Date Filter
        ">" +
        ">t" +
        '<"d-flex  mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        ">",
    buttons: [

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

$(document).ready(function () {
    $(document).ready(function () {
        // Check if the product was added successfully
        if (sessionStorage.getItem("product_added") == "true") {
            // Show the success Toastify message
            Toastify({
                text: "Product added successfully!",
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745", // Green for success
                close: true,
            }).showToast();

            // Remove the flag after showing the Toastify message
            sessionStorage.removeItem("product_added");
        }
        if (sessionStorage.getItem("product_updated") == "true") {
            // Show the success Toastify message
            Toastify({
                text: "Product updated successfully!",
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745", // Green for success
                close: true,
            }).showToast();

            // Remove the flag after showing the Toastify message
            sessionStorage.removeItem("product_updated");
        }
    });

    $(document).on("click", ".delete-product", function (e) {
        e.preventDefault();

        var $table = $(".product-list-table").DataTable();
        var $row = $(this).closest("tr");
        var rowData = $table.row($row).data();

        var productId = $(this).data("id");
        var productName = rowData.name;

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to delete user "${productName}". This action cannot be undone.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it!",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/products/${productId}`,
                    method: "DELETE",
                    success: function (res) {
                        Swal.fire(
                            "Deleted!",
                            "Product has been deleted.",
                            "success"
                        );
                        $table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire(
                            "Failed",
                            "Could not delete product.",
                            "error"
                        );
                    },
                });
            }
        });
    });
});
