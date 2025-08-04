$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var dt_user_table = $(".product-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    ajax: {
        url: productsDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-product-form').val(); // get from input
            d.category_id = $('.category-select').val();
            d.tag_id = $('.tag-select').val();
            return d;
        }
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

        {
            data: "image",
            render: function (data, type, row) {
                return `
            <img src="${data}" alt="Product Image"
                style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid #ccc;" />
        `;
            }
        },
        { data: "name" },
        // { data: "category" },
        {
            data: "tags",
            render: function (data, type, row) {
                if (!Array.isArray(JSON.parse(data))) return '';
                return `
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        ${JSON.parse(data).map(tag => `
                            <span style="background-color: #FCF8FC; color: #000; padding: 6px 12px; border-radius: 12px; font-size: 14px;">
                                ${tag}
                            </span>`).join("")}
                    </div>
                `;
            }
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
             <a href="/products/${data}" class="">
                <i data-feather="eye"></i>
              </a>
              <a href="/products/${data}/edit" class="">
                <i data-feather="edit-3"></i>
              </a>

              <a href="#" class=" text-danger open-delete-product-modal" data-id="${data}"
                data-bs-toggle="modal"
                data-bs-target="#deleteProductModal" >
                <i data-feather="trash-2"></i>
              </a>

          </div>
        `;
            }
        }
    ],
    order: [[1, "asc"]],
    dom: '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
        '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
        ">" +
        ">t" +
        '<"d-flex  mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        ">",
    drawCallback: function () {
        feather.replace(); // Re-initialize feather icons
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

// Search input with timeout (for better performance)
let searchTimeout;
$('#search-product-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Category select with timeout (corrected to use 'change' event)
let categoryFilterTimeout;
$('.category-select').on('change', function () {
    clearTimeout(categoryFilterTimeout);
    categoryFilterTimeout = setTimeout(() => {
        dt_user_table.draw(); // Trigger table redraw
    }, 300);
});

// TAg select with timeout (corrected to use 'change' event)
let tagFilterTimeout;
$('.tag-select').on('change', function () {
    clearTimeout(tagFilterTimeout);
    tagFilterTimeout = setTimeout(() => {
        dt_user_table.draw(); // Trigger table redraw
    }, 300);
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

    $(document).on("click", ".open-delete-product-modal", function () {
        const productId = $(this).data("id");
        $("#deleteProductForm").data("id", productId);
    });

    $(document).on("submit", "#deleteProductForm", function (e) {
        e.preventDefault();
        const productId = $(this).data("id");

        $.ajax({
            url: `/products/${productId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteProductModal").modal("hide");

                Toastify({
                    text: "Product deleted successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".product-list-table").DataTable().ajax.reload(null, false);


            },
            error: function () {
                $("#deleteProductModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455",
                    close: true,
                }).showToast();
                $(".product-list-table").DataTable().ajax.reload(null, false);

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
            url: "products/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteProductsModal").modal("hide");
                Toastify({
                    text: "Selected products deleted successfully!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();


                $('#bulk-delete-container').hide();
                $('.category-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".product-list-table").DataTable().ajax.reload(null, false);

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


                $('#bulk-delete-container').hide();
                $('.product-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".category-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });


});
