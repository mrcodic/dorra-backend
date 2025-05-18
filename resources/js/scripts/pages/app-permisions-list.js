$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".permisions-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: categoriesDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-category-form').val(); // get from input
            return d;
        }
    },
    columns: [
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            }
        },
        { data: "name" },
        { data: "sub_categories" },
        { data: "no_of_products" },
        { data: "added_date" },
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                        <div class="dropdown">
                            <button class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="#" class="dropdown-item view-details"
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
            }
        }
    ],
    order: [[1, "asc"]],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
        '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
        '<"d-flex align-items-center flex-grow-1 me-2"f>' +
        '<"d-flex align-items-center gap-1"B>' +
        ">" +
        ">t" +
        '<"d-flex mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        ">",
    buttons: [

    ],
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

// Optional: Handle bulk delete form submission with confirmation
$('#bulk-delete-form').on('submit', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete selected categories.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
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
