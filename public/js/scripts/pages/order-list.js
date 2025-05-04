$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
var dt_user_table = $(".order-list-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: categoriesDataUrl,
        type: "GET",
    },
    columns: [
        { data: null, defaultContent: "", orderable: false, render: function (data, type, row, meta) {
            return `<input type="checkbox" class="category-checkbox" value="${data}">`;
        } },
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
            text: "Add New Category",
            className: "add-new btn btn-outline-primary",
            attr: {
                "data-bs-toggle": "modal",
                "data-bs-target": "#addCategoryModal",
            },
            init: function (api, node, config) {
                $(node).removeClass("btn-secondary");
            },
        },
       
        
    ],
    drawCallback: function () {
        feather.replace();
        $('#select-all-checkbox').prop('checked', false);
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


