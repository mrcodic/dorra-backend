
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".template-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: templatesDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-category-form').val(); // get from input
            d.product_id = $('.filter-product').val();
            d.status = $('.filter-status').val();
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
        {data: "name"},
        {data: "product",
        render: function (data, type, row, meta) {
            return row.product.name[locale];
        }
        },
        {
            data: "status",
            render: function (data, type, row, meta) {
                let textColor = "";
                let bgColor = "";

                switch (data) {
                    case "active":
                        textColor = "text-success";
                        bgColor = "#D7EEDD"; // light green
                        break;
                    case "blocked":
                        textColor = "text-secondary";
                        bgColor = "#F0F0F0"; // light gray
                        break;
                    default:
                        textColor = "text-muted";
                        bgColor = "#E9ECEF"; // default gray
                }

                return `<span class="badge rounded-pill ${textColor} px-1" style="background-color: ${bgColor};">${data}</span>`;
            },
        },

        {data: "created_at"},
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `<a href="${showTemplateUrl}${data}" class="view-details"
                               >
                                                <i data-feather="eye"></i>
                                </a>
        <a href="#" class=" text-danger open-delete-template-modal" data-id="${data}"
                data-bs-toggle="modal"
                data-bs-target="#deleteTemplateModal" >
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
// let searchTimeout;
// $('#search-category-form').on('keyup', function () {
//     clearTimeout(searchTimeout);
//     searchTimeout = setTimeout(() => {
//         dt_user_table.draw();
//     }, 300);
// });

// Custom search with debounce

// $('.filter-product, .filter-status').on('change', function () {
//     dt_user_table.ajax.reload();
// });
$('.filter-status, .filter-product, #search-category-form').on('change keyup', function() {
    // debounce this if needed!
    fetchTemplates();
});

function fetchTemplates() {
    $.ajax({
        url: '/product-templates',
        type: 'GET',
        data: {
            search_value: $('#search-category-form').val(),
            product_id: $('.filter-product').val(),
            status: $('.filter-status').val()
        },
        success: function(response) {
            $('#templates-container').html(response.data.html);
        },
        error: function(xhr) {
            console.error('Failed to fetch templates:', xhr);
        }

    });
}


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
        $('#selected-count-text').text(`${selected} Template${selected > 1 ? 's' : ''} are selected`);
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

$(document).on("click", ".open-delete-template-modal", function () {
    const templateId = $(this).data("id");

    $("#deleteTemplateForm").attr('action',`product-templates/${templateId}`);
});

handleAjaxFormSubmit('#deleteTemplateForm', {
    successMessage: "✅ Template deleted successfully!",
    closeModal: '#deleteTemplateModal',
    onSuccess: function (response, $form) {
        $(".template-list-table").DataTable().ajax.reload(null, false); // false = stay on current page
    }
});
$(document).on("submit", "#bulk-delete-form", function (e) {
    e.preventDefault();
    const selectedIds = $(".category-checkbox:checked").map(function () {
        return $(this).val();
    }).get();
    console.log(selectedIds)

    if (selectedIds.length === 0) return;

    $.ajax({
        url: "templates/bulk-delete",
        method: "POST",
        data: {
            ids: selectedIds,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            $("#deleteTemplatesModal").modal("hide");
            Toastify({
                text: "Selected templates deleted successfully!",
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
            $(".template-list-table").DataTable().ajax.reload(null, false);

        },
        error: function () {
            $("#deleteTemplatesModal").modal("hide");
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
            $('.product-checkbox').prop('checked', false);
            $('#select-all-checkbox').prop('checked', false);
            $(".template-list-table").DataTable().ajax.reload(null, false);

        },
    });

});
