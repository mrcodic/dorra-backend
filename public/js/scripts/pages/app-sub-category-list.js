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
            d.created_at = $('.filter-date').val();
            return d;
        }
    },
    columns: [
        { data: null, defaultContent: "", orderable: false, render: function (data, type, row, meta) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            } },
        { data: "name" },
        { data: "sub_category_products_count" },
        { data: "added_date" },

        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                return `
        <div class="d-flex gap-1">
                                <a href="#" class="view-details"
                                   data-bs-toggle="modal"
                                    data-bs-target="#showSubCategoryModal"
                                     data-id="${data}"
                                     data-name_ar="${row.name_ar}"
                                     data-name_en="${row.name_en}"
                                     data-products="${row.no_of_products}"
                                     data-showdate="${row.show_date}"
                                     data-parent="${row.parent_name}"
                                     data-parent_id="${row.parent.id}">
                                                <i data-feather="eye"></i>
                                </a>


              <a href="#" class="edit-details"
               data-bs-toggle="modal"
               data-bs-target="#editSubCategoryModal"
               data-id="${data}"
               data-name_ar="${row.name_ar}"
               data-name_en="${row.name_en}"
               data-products="${row.no_of_products}"
               data-showdate="${row.show_date}"
               data-parent="${row.parent_name}"
               data-parent_id="${row.parent.id}">

                <i data-feather="edit-3"></i>
              </a>

      <a href="#" class="text-danger open-delete-sub-category-modal"
       data-id="${data}"
       data-name="${row.name}"
       data-action="/sub-categories/${data}"
       data-bs-toggle="modal"
       data-bs-target="#deleteSubCategoryModal">
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

$('.filter-date').on('change', function () {
    dt_user_table.draw();
});

$(document).ready(function () {
    const saveButton = $('.saveChangesButton');
    const saveLoader = $('.saveLoader');
    const saveButtonText = $('.saveChangesButton .btn-text');
    $("#addSubCategoryForm").on("submit", function (e) {
        e.preventDefault();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr("action"), // dynamic action URL
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {

                Toastify({
                    text: "SubCategory added successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();

                $("#addSubCategoryForm")[0].reset();
                $("#addSubCategoryModal").modal("hide");
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
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
                        saveButton.prop('disabled', false);
                        saveLoader.addClass('d-none');
                        saveButtonText.removeClass('d-none');
                    }
                }
            },
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

    $(document).on("click", ".edit-details", function (e) {
        const categoryNameAR = $(this).data("name_ar");
        const categoryNameEn = $(this).data("name_en");
        const products = $(this).data("products");
        const addedDate = $(this).data("showdate");
        const id = $(this).data("id");
        const parentName = $(this).data("parent");
        const parentId = $(this).data("parent_id");
        // Populate modal
        $("#editSubCategoryModal #edit-sub-category-name-ar").val(categoryNameAR);
        $("#editSubCategoryModal #edit-sub-category-name-en").val(categoryNameEn);
        $("#editSubCategoryModal #edit-sub-category-products").val(products);
        $("#editSubCategoryModal #edit-sub-category-date").val(addedDate);
        $("#editSubCategoryModal #edit-sub-category-id").val(id);
        $("#editSubCategoryModal select[name='parent_id']").val(parentId);

        // Show modal
        $("#editSubCategoryModal").modal("show");
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
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        $.ajax({
            url: `sub-categories/${categoryId}`,
            type: "POST", // IMPORTANT: Laravel expects POST + method spoofing (@method('PUT'))
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {

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
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                $(".sub-category-list-table").DataTable().ajax.reload(null, false);

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
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
            },
        });
    });

    $(document).on("click", ".open-delete-sub-category-modal", function () {

        const categoryId = $(this).data("id");
        $("#deleteSubCategoryForm").data("id", categoryId);

    });


    $(document).on("submit", "#deleteSubCategoryForm", function (e) {
        e.preventDefault();

        var subCategoryId = $(this).data("id");

        $.ajax({
            url: `/sub-categories/${subCategoryId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteSubCategoryModal").modal("hide");

                Toastify({
                    text: "Subcategory deleted successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".sub-category-list-table").DataTable().ajax.reload(null, false);
            },
            error: function () {
                $("#deleteSubCategoryModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                $(".sub-category-list-table").DataTable().ajax.reload(null, false);
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
            url: "sub-categories/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteSubCategoriesModal").modal("hide");
                Toastify({
                    text: "Selected subcategories deleted successfully!",
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
                $("#deleteSubCategoriesModal").modal("hide");
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
                $('.category-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".sub-category-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });


});
