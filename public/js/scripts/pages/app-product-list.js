console.log(productsDataUrl);
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var dt_user_table = $(".product-list-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: productsDataUrl,
        type: "GET",
    },
    columns: [
        { data: null, defaultContent: "", orderable: false },
        { data: "name" },
        { data: "category" },
        { data: "tags" },
        { data: "no_of_purchas" },
        { data: "added_date" },
        { data: "rating" },
        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                console.log(data);
                return `
          <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i data-feather="more-vertical"></i>
            </button>
            <div class="dropdown-menu">
             <a href="/products/${data}" class="dropdown-item">
                <i data-feather="file-text"></i> Details
              </a>
               <a href="/products/${data}/edit" class="dropdown-item">
                <i data-feather="edit"></i> Edit
              </a>


              <a href="#" class="dropdown-item text-danger delete-product" data-id="${data}">
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
            text: "Add New Product",
            className: "add-new btn btn-outline-primary",
            action: function (e, dt, node, config) {
                window.location.href = productsCreateUrl;
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
