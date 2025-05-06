$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
var dt_user_table = $(".sub-category-list-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: subCategoriesDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-sub-category-form').val(); // get from input
            return d;
        }
    },
    columns: [
        { data: null, defaultContent: "", orderable: false, render: function (data, type, row, meta) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            } },
        { data: "name" },
        { data: "no_of_products" },
        { data: "added_date" },

        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                console.log(row.parent.id)
                return `
          <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i data-feather="more-vertical"></i>
            </button>
            <div class="dropdown-menu">
              <a href=""
                 class="dropdown-item view-details"
                 data-bs-toggle="modal"
                 data-bs-target="#showSubCategoryModal"
                 data-id="${data}"
                 data-name_ar="${row.name_ar}"
                 data-name_en="${row.name_en}"
                 data-products="${row.no_of_products}"
                 data-showdate="${row.show_date}"
                 data-parent="${row.parent_name}"
                 data-parent_id="${row.parent.id}">
                <i data-feather="file-text"></i> Details
              </a>

              <a href="#" class="dropdown-item text-danger delete-sub-category" data-id="${data}">
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
        ">" +
        ">t" +
        '<"d-flex  mx-2 row mb-1"' +
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
            next: "&nbsp;",
        },
    },
});
let searchTimeout;
$('#search-sub-category-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});
$(document).ready(function () {
    $(document).ready(function () {
        // Check if the product was added successfully
        if (sessionStorage.getItem("Category_added") == "true") {
            // Show the success Toastify message
            Toastify({
                text: "Subcategory added successfully!",
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745", // Green for success
                close: true,
            }).showToast();

            // Remove the flag after showing the Toastify message
            sessionStorage.removeItem("Category_added");
        }
    });

    $(document).on("click", ".delete-sub-category", function (e) {
        e.preventDefault();

        var $table = $(".sub-category-list-table").DataTable();
        var $row = $(this).closest("tr");
        var rowData = $table.row($row).data();
        var categoryId = $(this).data("id");
        var categoryName = rowData.name;

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to delete category "${categoryName}". This action cannot be undone.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it!",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/categories/${categoryId}`,
                    method: "DELETE",
                    success: function (res) {
                        Swal.fire(
                            "Deleted!",
                            "Category has been deleted.",
                            "success"
                        );
                        $table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire(
                            "Failed",
                            "Could not delete category.",
                            "error"
                        );
                    },
                });
            }
        });
    });

    $(document).on("click", ".view-details", function (e) {
        const categoryNameAR = $(this).data("name_ar");
        const categoryNameEn = $(this).data("name_en");
        const products = $(this).data("products");
        const addedDate = $(this).data("showdate");
        const id = $(this).data("id");
        const parentName = $(this).data("parent");
        const parentId = $(this).data("parent_id");
        // Populate modal
        $("#showSubCategoryModal #sub-category-name-ar").val(categoryNameAR);
        $("#showSubCategoryModal #sub-category-name-en").val(categoryNameEn);
        $("#showSubCategoryModal #sub-category-products").val(products);
        $("#showSubCategoryModal #sub-category-date").val(addedDate);
        $("#showSubCategoryModal #sub-category-id").val(id);
        $("#showSubCategoryModal #parent-name").val(parentName);
        $("#showSubCategoryModal #parent-id").val(parentId);
        // Show modal
        $("#showSubCategoryModal").modal("show");
    });

    $("#editButton").on("click", function () {
        var nameEN = $("#sub-category-name-en").val();
        var nameAR = $("#sub-category-name-ar").val();
        var id = $("#sub-category-id").val();
        var parentId = $("#parent-id").val();

        $("#edit-sub-category-name-en").val(nameEN);
        $("#edit-sub-category-name-ar").val(nameAR);
        $("#edit-sub-category-id").val(id);
        $("select[name='parent_id']").val(parentId);
        $("#editSubCategoryModal").modal("show");
    });

    $("#editSubCategoryForm").on("submit", function (e) {
        e.preventDefault(); // prevent default form submission
        var categoryId = $(this).find("#edit-sub-category-id").val();
        console.log(categoryId);
        $.ajax({
            url: `sub-categories/${categoryId}`,
            type: "POST", // IMPORTANT: Laravel expects POST + method spoofing (@method('PUT'))
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                console.log(response);

                Toastify({
                    text: "Subcategory updated successfully!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F", // green for success
                    close: true,
                }).showToast();

                // Close modal
                $("#editSubCategoryModal").modal("hide");
                $("#showSubCategoryModal").modal("hide");
                $("#sub-category-list-table").DataTable().ajax.reload();
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
                            backgroundColor: "#EA5455", // red
                            close: true,
                        }).showToast();
                    }
                }
            },
        });
    });

    $("#addSubCategoryForm").on("submit", function (e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr("action"), // dynamic action URL
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Toastify({
                    text: "Category added successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();

                $("#addSubCategoryForm")[0].reset();
                $("#addSubCategoryModal").modal("hide");

                $(".sub-category-list-table").DataTable().ajax.reload(); // reload your table
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
                            backgroundColor: "#EA5455",
                            close: true,
                        }).showToast();
                    }
                }
            },
        });
    });

    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: `Are you sure?`,
            text: `You're about to delete ${selectedIds.length} sub-categories.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete them!",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "sub-categories/bulk-delete",
                    method: "POST",
                    data: {
                        ids: selectedIds,
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        Toastify({
                            text: "Selected sub-categories deleted successfully!",
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
                        $(".sub-category-list-table").DataTable().ajax.reload(null, false);

                    },
                    error: function () {
                        Swal.fire("Error", "Could not delete selected sub-categories.", "error");
                    },
                });
            }
        });
    });

});
