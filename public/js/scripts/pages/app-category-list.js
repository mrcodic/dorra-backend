$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
var dt_user_table = $(".category-list-table").DataTable({
    processing: true,
    serverSide: true,

    ajax: {
        url: categoriesDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = d.search.value || '';
            console.log(d.search_value);
            return d;
        }

    },
    columns: [
        { data: null, defaultContent: "", orderable: false, render: function (data, type, row, meta) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            }},
        { data: "name" },
        { data: "sub_categories" },
        { data: "no_of_products" },
        { data: "added_date" },
        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                return `
                    <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href=""
                               class="dropdown-item view-details"
                               data-bs-toggle="modal"
                               data-bs-target="#modals-slide-in"
                               data-id="${data}"
                               data-name_ar="${row.name_ar}"
                               data-name_en="${row.name_en}"
                               data-image="${row.image}"
                               data-description_en="${row.description_en}"
                               data-description_ar="${row.description_ar}"
                               data-subcategories="${row.children.map((child) => child.name)}"
                               data-products="${row.no_of_products}"
                               data-showdate="${row.show_date}">
                            <i data-feather="file-text"></i> Details
                            </a>
                            <a href="#" class="dropdown-item text-danger delete-category" data-id="${data}">
                                <i data-feather="trash-2"></i> Delete
                            </a>
                        </div>
                    </div>
                `;
            },
        },
    ],
    order: [[1, "asc"]],
    
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
    $(document).ready(function () {
        // Check if the product was added successfully
        if (sessionStorage.getItem("Category_added") == "true") {
            // Show the success Toastify message
            Toastify({
                text: "Category added successfully!",
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

    $(document).on("click", ".delete-category", function (e) {
        e.preventDefault();

        var $table = $(".category-list-table").DataTable();
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
        // Get the data from attributes
        var $table = $(".category-list-table").DataTable();
        var $row = $(this).closest("tr");
        var rowData = $table.row($row).data();
        const subCategories = rowData.children.map(function (child) {
            return child["name"][locale];
        });
        const categoryNameAR = $(this).data("name_ar");
        const categoryNameEn = $(this).data("name_en");

        const products = $(this).data("products");
        const addedDate = $(this).data("showdate");
        const descriptionAr = $(this).data("description_ar");
        const descriptionEn = $(this).data("description_en");
        const image = $(this).data("image");
        const id = $(this).data("id");
        // Populate modal
        $("#showCategoryModal #category-name-ar").val(categoryNameAR);
        $("#showCategoryModal #category-name-en").val(categoryNameEn);
        $("#showCategoryModal #category-products").val(products);
        $("#showCategoryModal #category-date").val(addedDate);
        $("#showCategoryModal #category-description-ar").val(descriptionAr);
        $("#showCategoryModal #category-description-en").val(descriptionEn);
        $("#showCategoryModal #imagePreview").attr("src", image);
        $("#showCategoryModal #category-id").val(id);

        // Create badges for subcategories
        let badgesHtml = "";
        subCategories.forEach(function (subcategory) {
            badgesHtml += `<span class="badge bg-light text-dark border">${subcategory}</span>`;
        });

        // Set the badges HTML in the modal
        $("#subcategories-container").html(badgesHtml);

        // Show modal
        const modal = new bootstrap.Modal(
            document.getElementById("showCategoryModal")
        );
        modal.show();
    });

    $("#editButton").on("click", function () {
        var nameEN = $("#category-name-en").val();
        var nameAR = $("#category-name-ar").val();
        var descEN = $("#category-description-en").val();
        var descAR = $("#category-description-ar").val();
        var id = $("#category-id").val();

        $("#edit-category-name-en").val(nameEN);
        $("#edit-category-name-ar").val(nameAR);
        $("#edit-category-description-en").val(descEN);
        $("#edit-category-description-ar").val(descAR);
        $("#edit-category-id").val(id);

        $("#editCategoryModal").modal("show");
    });

    $("#editCategoryForm").on("submit", function (e) {
        e.preventDefault(); // prevent default form submission
        var categoryId = $(this).find("#edit-category-id").val();
        $.ajax({
            url: `categories/${categoryId}`,
            type: "POST", // IMPORTANT: Laravel expects POST + method spoofing (@method('PUT'))
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                console.log(response);

                Toastify({
                    text: "Category updated successfully!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F", // green for success
                    close: true,
                }).showToast();

                // Close modal
                $("#editCategoryModal").modal("hide");

                // Optional: Clear form fields
                $("#editCategoryForm")[0].reset();

                // Optional: Remove uploaded image preview
                $("#edit-image-preview-container").hide();
                $("#edit-image-preview").attr("src", "");
                $("#edit-image-details").hide();

                // Optional: Reload your table or page
                // location.reload();
                // OR if you use DataTables, you can do:
                // $('#yourTableId').DataTable().ajax.reload();
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

    $("#edit-image-upload").on("change", function (event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                $("#edit-image-preview").attr("src", e.target.result);
                $("#edit-image-preview-container").show();

                // Show file name and size
                const fileSize = (file.size / 1024).toFixed(2); // size in KB
                $("#edit-image-details")
                    .text(`${file.name} • ${fileSize} KB`)
                    .show();
            };

            reader.readAsDataURL(file); // Read the file as DataURL for preview
        }
    });

    // Delete the selected image
    $("#delete-image-button").on("click", function () {
        $("#edit-image-upload").val(""); // clear the file input
        $("#edit-image-preview-container").hide(); // hide preview container
        $("#edit-image-preview").attr("src", ""); // clear the img src
        $("#edit-image-details").hide(); // hide file details
    });

    $("#add-image-upload").on("change", function (event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                $("#add-image-preview").attr("src", e.target.result);
                $("#add-image-preview-container").show();

                // Show file name and size
                const fileSize = (file.size / 1024).toFixed(2); // size in KB
                $("#add-image-details")
                    .text(`${file.name} • ${fileSize} KB`)
                    .show();
            };

            reader.readAsDataURL(file); // Read the file as DataURL for preview
        }
    });

    // Delete the selected image
    $("#delete-image").on("click", function () {
        $("#add-image-upload").val(""); // clear the file input
        $("#add-image-preview-container").hide(); // hide preview container
        $("#add-image-preview").attr("src", ""); // clear the img src
        $("#add-image-details").hide(); // hide file details
    });

    $("#addCategoryForm").on("submit", function (e) {
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

                $("#addCategoryForm")[0].reset();
                $("#addCategoryModal").modal("hide");

                $(".category-list-table").DataTable().ajax.reload(); // reload your table
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
            text: `You're about to delete ${selectedIds.length} categories.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete them!",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "categories/bulk-delete",
                    method: "POST",
                    data: {
                        ids: selectedIds,
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        Toastify({
                            text: "Selected categories deleted successfully!",
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
                        $(".category-list-table").DataTable().ajax.reload(null, false);

                    },
                    error: function () {
                        Swal.fire("Error", "Could not delete selected categories.", "error");
                    },
                });
            }
        });
    });


});
