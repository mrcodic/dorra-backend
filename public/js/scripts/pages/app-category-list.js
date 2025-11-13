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
            d.search_value = $("#search-category-form").val(); // get from input
            d.created_at = $(".filter-date").val();
            return d;
        },
    },
    columns: [
        {
            data: "id",
            name: "categories.id",
            orderable:false,
            searchable: false,
            render: function (data, type, row) {
                return row?.action?.can_delete
                    ? `<input type="checkbox" name="ids[]" class="category-checkbox" value="${row.id}">`
                    : '';
            },
        },
        {
            data: "image",
            render: function (data, type, row) {
                return `
            <img src="${data}" alt="User Image"
                style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid #ccc;" />
        `;
            }
        },
        {data: "name"},

        {data: "sub_categories"},
        {
            data: "products",
            render: function (data, type) {
                // normalize to array
                let items = [];
                if (Array.isArray(data)) {
                    items = data;
                } else if (typeof data === "string" && data.trim() !== "") {
                    try { items = JSON.parse(data); } catch { items = []; }
                }

                // clean & ensure strings
                items = (items || []).filter(v => v != null && String(v).trim() !== "").map(String);

                // for sort/search, return simple text
                if (type !== "display") return items.length ? items.join(", ") : "-";

                if (!items.length) return "-";

                const esc = s => String(s).replace(/[&<>"']/g, m => ({
                    "&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"
                }[m]));

                return `
      <div style="display:flex;flex-wrap:wrap;gap:6px;">
        ${items.map(p => `
          <span style="background:#FCF8FC;color:#000;padding:6px 12px;border-radius:12px;font-size:14px;">
            ${esc(p)}
          </span>
        `).join("")}
      </div>`;
            }
        },
        {data: "no_of_products"},
        {data: "added_date"},
        {
            data: "id",

            searchable: false,
            render: function (data, type, row) {
                const canShow   = row?.action?.can_show   ?? false;
                const canEdit   = row?.action?.can_edit   ?? false;
                const canDelete = row?.action?.can_delete ?? false;
                const btns = [];

                if (canShow) {
                    btns.push(`<a href="#" class="view-details"
                                              data-action="${row.is_has_category ? 'modal' : 'redirect'}"
                                              data-url="/categories/${data}"
                                              data-id="${data}"
                                              data-name_ar="${row.name_ar}"
                                              data-name_en="${row.name_en}"
                                              data-image="${row.image}"
                                              data-image_id="${row.imageId}"
                                              data-description_en="${row.description_en}"
                                              data-description_ar="${row.description_ar}"
                                              data-subcategories="${row.children.map(
                        (child) => child.name
                    )}"
                                              data-products="${row.no_of_products}"
                                              data-showdate="${row.show_date}">
                    <i data-feather="eye"></i>
                </a>`);
                }
                if (canEdit) {
                btns.push(` <a href="#" class="edit-details"
   data-id="${data}"
   data-name_ar="${row.name_ar}"
   data-name_en="${row.name_en}"
   data-has_mockup="${row.has_mockup}"
   data-image="${row.image}"
   data-image_id="${row.imageId}"
   data-description_en="${row.description_en}"
   data-description_ar="${row.description_ar}"
   data-subcategories="${row.children.map(child => child.name)}"
   data-products="${row.no_of_products}"
   data-showdate="${row.show_date}"
    data-action="${row.is_has_category ? 'modal' : 'redirect'}"
       data-url="/categories/${data}/edit"
       >

   <i data-feather="edit-3"></i>
</a>`);
                }
                if (canDelete) {

                    btns.push(`  <a href="#" class="text-danger  open-delete-category-modal"
   data-id="${data}"
   data-name="${row.name}"
   data-action="/categories/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteCategoryModal">
   <i data-feather="trash-2"></i>
</a>`)
                }


                if (!btns.length) return '';
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
            next: "&nbsp;",
        },
    },
});
$('#clear-search').on('click', function () {
    $('#search-category-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
// Custom search with debounce
let searchTimeout;
$("#search-category-form").on("keyup", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$(".filter-date").on("change", function () {
    dt_user_table.draw();
});

// Checkbox select all
$("#select-all-checkbox").on("change", function () {
    $(".category-checkbox").prop("checked", this.checked);
    updateBulkDeleteVisibility();
});

// Single checkbox toggle
$(document).on("change", ".category-checkbox", function () {
    if (!this.checked) {
        $("#select-all-checkbox").prop("checked", false);
    } else if (
        $(".category-checkbox:checked").length ===
        $(".category-checkbox").length
    ) {
        $("#select-all-checkbox").prop("checked", true);
    }
    updateBulkDeleteVisibility();
});

// Redraw table resets checkboxes
dt_user_table.on("draw", function () {
    $("#select-all-checkbox").prop("checked", false);
    $("#bulk-delete-container").hide();
});

// Update bulk delete container
function updateBulkDeleteVisibility() {
    const selected = $(".category-checkbox:checked").length;
    if (selected > 0) {
        $("#selected-count-text").text(
            `${selected} Category${selected > 1 ? "ies" : "y"} are selected`
        );
        $("#bulk-delete-container").show();
    } else {
        $("#bulk-delete-container").hide();
    }
}

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

// Optional: Hide button when table is redrawn
dt_user_table.on("draw", function () {
    $("#bulk-delete-container").hide();
});

$(document).ready(function () {
    const saveButton = $(".saveChangesButton");
    const saveLoader = $(".saveLoader");
    const saveButtonText = $(".saveChangesButton .btn-text");
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
        saveButton.prop("disabled", true);
        saveLoader.removeClass("d-none");
        saveButtonText.addClass("d-none");
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
                saveButton.prop("disabled", false);
                saveLoader.addClass("d-none");
                saveButtonText.removeClass("d-none");
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
                location.reload();
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
                saveButton.prop("disabled", false);
                saveLoader.addClass("d-none");
                saveButtonText.removeClass("d-none");
            },
        });
    });

// ================== VIEW DETAILS ==================
    // One handler for BOTH view + edit
    $(document).on("click", ".view-details, .edit-details", function (e) {
        const $btn = $(this);
        const action = $btn.data("action") || "modal";
        const url    = $btn.data("url");
        const modalId = $btn.data("modal") || ($btn.hasClass("edit-details") ? "#editCategoryModal" : "#showCategoryModal");

        if (action === "redirect" && url) {
            e.preventDefault();
            window.location.href = url;
            return;
        }

        // Open modal path
        e.preventDefault();

        // Pull row data (for subcategories)
        const table  = $(".category-list-table").DataTable();
        const row    = $btn.closest("tr");
        const rowData = table.row(row).data() || {};
        const loc = window.locale || "en";

        const subCategories = Array.isArray(rowData.children)
            ? rowData.children
                .map(c => (c.name?.[loc] ?? c.name ?? ""))
                .filter(Boolean)
            : [];

        hydrateCategoryModal(modalId, $btn.data(), subCategories);

        new bootstrap.Modal(document.querySelector(modalId)).show();
    });

// Fill modal fields based on which modal it is
    function hydrateCategoryModal(modalId, data, subCategories) {
        // Map selectors per modal
        const maps = {
            "#showCategoryModal": {
                nameAr: "#category-name-ar",
                nameEn: "#category-name-en",
                products: "#category-products",
                date: "#category-date",
                descAr: "#category-description-ar",
                descEn: "#category-description-en",
                img: "#imagePreview",
                id: "#category-id",
                has_mockup: "#has_mockup",
                imgId: "#image-id",
                subs: "#subcategories-container",
                extra: () => {} // nothing special
            },
            "#editCategoryModal": {
                nameAr: "#edit-category-name-ar",
                nameEn: "#edit-category-name-en",
                products: "#edit-category-products",
                date: "#edit-category-date",
                descAr: "#edit-category-description-ar",
                descEn: "#edit-category-description-en",
                has_mockup: "#has_mockup",
                img: "#edit-preview-image",
                id: "#edit-category-id",
                imgId: null, // not present in your edit modal
                subs: "#subcategories-container",
                extra: () => { $("#editCategoryModal #edit-uploaded-image").removeClass("d-none"); }
            }
        };

        const m = maps[modalId] || maps["#showCategoryModal"];
        const $scope = $(modalId);
        // Fill fields safely (data-* with underscores come through as same keys in jQuery)
        $scope.find(m.nameAr).val(data.name_ar || "");
        $scope.find(m.nameEn).val(data.name_en || "");
        $scope.find(m.products).val(data.products ?? "");
        $scope.find(m.date).val(data.showdate || "");
        $scope.find(m.descAr).val(data.description_ar || "");
        $scope.find(m.descEn).val(data.description_en || "");
        $scope.find(m.has_mockup).prop('checked', !!data.has_mockup);
        $scope.find(m.img).attr("src", data.image || "");
        if (m.imgId) $scope.find(m.imgId).val(data.image_id || "");
        $scope.find(m.id).val(data.id || "");

        // Subcategory badges (scope to the current modal!)
        const badgesHtml = subCategories.length
            ? subCategories.map(s => `<span class="badge bg-light text-dark border me-1 mb-1">${s}</span>`).join("")
            : "-";
        $scope.find(m.subs).html(badgesHtml);

        // Any modal-specific tweaks
        m.extra();
    }

    $("#editButton").on("click", function () {
        var nameEN = $("#category-name-en").val();
        var nameAR = $("#category-name-ar").val();
        var descEN = $("#category-description-en").val();
        var descAR = $("#category-description-ar").val();
        var imageId = $("#image-id").val();
        var hasMockup = $("#has_mockup").val();
        var image = $("#imagePreview").attr("src");
        var id = $("#category-id").val();
        $("#edit-category-name-en").val(nameEN);
        $("#edit-category-name-ar").val(nameAR);
        $("#edit-category-description-en").val(descEN);
        $("#edit-category-description-ar").val(descAR);
        $("#has_mockup").prop('checked', !!hasMockup);
        $("#edit-category-id").val(id);
        $("#edit-uploaded-image").removeClass("d-none");
        $("#edit-preview-image").attr("src", image);
        $(".remove-old-image").on("click", function (e) {
            e.preventDefault();

            var imageElement = $(this).closest(".uploaded-image");

            $.ajax({
                url: "api/media/" + imageId,
                method: "DELETE",
                success: function (response) {
                    imageElement.remove();
                    Toastify({
                        text: "Image Removed Successfully",
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                    }).showToast();
                },
                error: function (xhr) {
                    console.log(xhr.responseJSON.errors);
                },
            });
        });

        $("#editCategoryModal").modal("show");
    });

    $("#editCategoryForm").on("submit", function (e) {
        e.preventDefault(); // prevent default form submission
        var categoryId = $(this).find("#edit-category-id").val();
        saveButton.prop("disabled", true);
        saveLoader.removeClass("d-none");
        saveButtonText.addClass("d-none");
        $.ajax({
            url: `categories/${categoryId}`,
            type: "POST", // IMPORTANT: Laravel expects POST + method spoofing (@method('PUT'))
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                saveLoader.addClass("d-none");
                saveButtonText.removeClass("d-none");
                saveButton.prop("disabled", false);
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
                location.reload();
                // Optional: Remove uploaded image preview
                $("#edit-image-preview-container").hide();
                $("#edit-image-preview").attr("src", "");
                $("#edit-image-details").hide();

                $(".category-list-table").DataTable().ajax.reload();
            },
            error: function (xhr) {
                saveLoader.addClass("d-none");
                saveButtonText.removeClass("d-none");
                saveButton.prop("disabled", false);
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
        const selectedIds = $(".category-checkbox:checked")
            .map(function () {
                return $(this).val();
            })
            .get();

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

                $("#bulk-delete-container").hide();
                $(".category-checkbox").prop("checked", false);
                $("#select-all-checkbox").prop("checked", false);
                $(".category-list-table").DataTable().ajax.reload(null, false);
            },
            error: function () {
                $("#deleteCategoriesModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455",
                    close: true,
                }).showToast();

                // Reload DataTable

                $("#bulk-delete-container").hide();
                $(".category-checkbox").prop("checked", false);
                $("#select-all-checkbox").prop("checked", false);
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
