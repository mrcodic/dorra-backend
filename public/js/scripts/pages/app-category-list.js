$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".category-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: categoriesDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-category-form').val(); // get from input
            d.created_at = $('.filter-date').val();
            return d;
        }
    },
    columns: [
        {
            data: null,
            searchable: false,
            render: function (data) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            }
        },
        {data: "name"},
        {data: "sub_categories"},
        {
            data: "products",
            render: function (data, type, row) {
                if (!Array.isArray(JSON.parse(data))) return '';
                return `
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        ${JSON.parse(data).map(product => `
                            <span style="background-color: #FCF8FC; color: #000; padding: 6px 12px; border-radius: 12px; font-size: 14px;">
                                ${product}
                            </span>`).join("")}
                    </div>
                `;
            }
        },
        {data: "no_of_products"},
        {data: "added_date"},
        {
            data: "id",
  
            searchable: false,
            render: function (data, type, row) {
                return `
        <div class="d-flex gap-1">
                                <a href="#" class="view-details"
                                   data-bs-toggle="modal"
                                   data-bs-target="#modals-slide-in"
                                   data-id="${data}"
                                   data-name_ar="${row.name_ar}"
                                   data-name_en="${row.name_en}"
                                   data-image="${row.image}"
                                   data-image_id="${row.imageId}"
                                   data-description_en="${row.description_en}"
                                   data-description_ar="${row.description_ar}"
                                   data-subcategories="${row.children.map((child) => child.name)}"
                                   data-products="${row.no_of_products}"
                                   data-showdate="${row.show_date}">
                                                <i data-feather="eye"></i>
                                </a>


              <a href="#" class="edit-details"
               data-bs-toggle="modal"
                                   data-bs-target="#modals-slide-in"
                                   data-id="${data}"
                                   data-name_ar="${row.name_ar}"
                                   data-name_en="${row.name_en}"
                                   data-image="${row.image}"
                                   data-image_id="${row.imageId}"
                                   data-description_en="${row.description_en}"
                                   data-description_ar="${row.description_ar}"
                                   data-subcategories="${row.children.map((child) => child.name)}"
                                   data-products="${row.no_of_products}"
                                   data-showdate="${row.show_date}">

                <i data-feather="edit-3"></i>
              </a>

      <a href="#" class="text-danger  open-delete-category-modal"
   data-id="${data}"
   data-name="${row.name}"
   data-action="/categories/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteCategoryModal">
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

$('.filter-date').on('change', function () {
    dt_user_table.draw();
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
    const saveButton = $('.saveChangesButton');
    const saveLoader = $('.saveLoader');
    const saveButtonText = $('.saveChangesButton .btn-text');
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

    $("#addCategoryForm").on("submit", function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
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
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                $("#addCategoryForm")[0].reset();
                $("#addCategoryModal").modal("hide");
                $("#add-uploaded-image").addClass("d-none");
                $("#add-uploaded-image img").attr("src", "");
                $("#add-file-details .file-name").text("");
                $("#add-file-details .file-size").text("");
                $("#add-category-image").val(""); // clear file input
                $("#add-upload-progress").addClass("d-none");
                $("#add-upload-progress .progress-bar").css("width", "0%");

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
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
            },
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
        const imageId = $(this).data("image_id");
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
        $("#showCategoryModal #image-id").val(imageId);

        // Create badges for subcategories
        let badgesHtml = "";
        subCategories.forEach(function (subcategory) {
            badgesHtml += `<span class="badge bg-light text-dark border">${subcategory}</span>`;
        });

        // Set the badges HTML in the modal
        $("#subcategories-container").html(badgesHtml ? badgesHtml :"-");

        // Show modal
        const modal = new bootstrap.Modal(
            document.getElementById("showCategoryModal")
        );
        modal.show();
    });

    $(document).on("click", ".edit-details", function (e) {
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
        const imageId = $(this).data("image_id");
        $('.remove-old-image').on('click', function (e) {

            e.preventDefault();

            var imageElement = $(this).closest('.uploaded-image');
            $.ajax({
                url: 'api/media/' + imageId,
                method: "DELETE",
                success: function (response) {
                    imageElement.remove();
                    Toastify({
                        text: "Image Removed Successfully",
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true
                    }).showToast();
                },
                error: function (xhr) {
                    console.log(xhr.responseJson.errors)
                }
            })

        });

        const id = $(this).data("id");
        // Populate modal
        $("#editCategoryModal #edit-category-name-ar").val(categoryNameAR);
        $("#editCategoryModal #edit-category-name-en").val(categoryNameEn);
        $("#editCategoryModal #edit-category-products").val(products);
        $("#editCategoryModal #edit-category-date").val(addedDate);
        $("#editCategoryModal #edit-category-description-ar").val(descriptionAr);
        $("#editCategoryModal #edit-category-description-en").val(descriptionEn);
        $("#editCategoryModal #edit-uploaded-image").removeClass('d-none');
        $("#editCategoryModal #edit-preview-image").attr("src", image);
        $("#editCategoryModal #edit-category-id").val(id);

        // Create badges for subcategories
        let badgesHtml = "";
        subCategories.forEach(function (subcategory) {
            badgesHtml += `<span class="badge bg-light text-dark border">${subcategory}</span>`;
        });

        // Set the badges HTML in the modal
        $("#subcategories-container").html(badgesHtml);

        // Show modal
        const modal = new bootstrap.Modal(
            document.getElementById("editCategoryModal")
        );
        modal.show();
    });

    $("#editButton").on("click", function () {
        var nameEN = $("#category-name-en").val();
        var nameAR = $("#category-name-ar").val();
        var descEN = $("#category-description-en").val();
        var descAR = $("#category-description-ar").val();
        var imageId = $("#image-id").val();
        var image = $("#imagePreview").attr("src");
        var id = $("#category-id").val();
        $("#edit-category-name-en").val(nameEN);
        $("#edit-category-name-ar").val(nameAR);
        $("#edit-category-description-en").val(descEN);
        $("#edit-category-description-ar").val(descAR);
        $("#edit-category-id").val(id);
        $("#edit-uploaded-image").removeClass('d-none');
        $("#edit-preview-image").attr("src", image);
        $('.remove-old-image').on('click', function (e) {
            e.preventDefault();

            var imageElement = $(this).closest('.uploaded-image');

            $.ajax({
                url: 'api/media/' + imageId,
                method: "DELETE",
                success: function (response) {
                    imageElement.remove();
                    Toastify({
                        text: "Image Removed Successfully",
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true
                    }).showToast();
                },
                error: function (xhr) {
                    console.log(xhr.responseJSON.errors)
                }
            });
        });

        $("#editCategoryModal").modal("show");
    });

    $("#editCategoryForm").on("submit", function (e) {
        e.preventDefault(); // prevent default form submission
        var categoryId = $(this).find("#edit-category-id").val();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        $.ajax({
            url: `categories/${categoryId}`,
            type: "POST", // IMPORTANT: Laravel expects POST + method spoofing (@method('PUT'))
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                saveButton.prop('disabled', false);
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
                $("#showCategoryModal").modal("hide");

                // Optional: Clear form fields
                $("#editCategoryForm")[0].reset();

                // Optional: Remove uploaded image preview
                $("#edit-image-preview-container").hide();
                $("#edit-image-preview").attr("src", "");
                $("#edit-image-details").hide();

                $('.category-list-table').DataTable().ajax.reload();
            },
            error: function (xhr) {
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                saveButton.prop('disabled', false);
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


    $(document).on("click", ".open-delete-category-modal", function () {
        const categoryId = $(this).data("id");
        $("#deleteCategoryForm").data("id", categoryId);
    });

    $(document).on("submit", "#deleteCategoryForm", function (e) {
        e.preventDefault();
        const categoryId = $(this).data("id");
        $.ajax({
            url: `/categories/${categoryId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteCategoryModal").modal("hide");
                Toastify({
                    text: "Category deleted successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".category-list-table").DataTable().ajax.reload(null, false);
            },
            error: function () {
                $("#deleteCategoryModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                    $(".category-list-table").DataTable().ajax.reload(null, false);
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
            url: "categories/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteCategoriesModal").modal("hide");
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
                $('.category-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".category-list-table").DataTable().ajax.reload(null, false);

            },
        });

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




});
