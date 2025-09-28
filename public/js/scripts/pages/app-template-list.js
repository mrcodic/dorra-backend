
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
            d.tags = $('.filter-tags').val();
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
$(document).ready(function () {
    fetchTemplates();

    $('#clear-search').on('click', function () {
        $('#search-category-form').val('');  // clear input
        fetchTemplates();
    });
    $('.filter-status, .filter-product,.filter-tags , #search-category-form, .filter-paginate-number').on('change keyup', function () {
        fetchTemplates();
    });
});


function fetchTemplates(page = 1) {
    $.ajax({
        url: '/product-templates',
        type: 'GET',
        data: {
            page,
            search_value: $('#search-category-form').val(),
            product_id  : $('.filter-product').val(),
            tags  : $('.filter-tags').val(),
            status      : $('.filter-status').val(),
            per_page    : $('.filter-paginate-number').val()
        },
        success: res => {
            $('#templates-container').html(res.data.cards);
            $('#pagination-container').html(res.data.pagination);
        },
        error: xhr => console.error('Failed to fetch templates:', xhr)
    });
}

/* 3-A  — page-size selector */
$('.filter-paginate-number').on('change', () => fetchTemplates(1));

/* 3-B  — delegate clicks on the (new) paginator links */
$(document).on('click', '#pagination-container a.page-link', function (e) {
    e.preventDefault();
    const url  = new URL($(this).attr('href'), location.origin);
    const page = url.searchParams.get('page') || 1;
    fetchTemplates(page);
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
    $('#deleteTemplateForm input[name="id"]').val(templateId);
    $("#deleteTemplateForm").attr('action',`product-templates/${templateId}`);
});

handleAjaxFormSubmit('#deleteTemplateForm', {
    successMessage: "✅ Template deleted successfully!",
    closeModal: '#deleteTemplateModal',
    onSuccess: function (response, $form) {
        const deletedId = response.data.id;
        const card = $(`button[data-id="${deletedId}"]`).closest('.col-md-6.col-lg-4.col-xxl-4');
        card.remove();
    }
});

function removeTemplateCards(ids) {
    if (Array.isArray(ids)) {
        ids.forEach(function (id) {
            $('[data-template-id="' + id + '"]').fadeOut(300, function () {
                $(this).remove();
            });
        });
    }
}


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

            removeTemplateCards(response.data);

            $('#bulk-delete-container').hide();
            $('.category-checkbox').prop('checked', false);
            $('#select-all-checkbox').prop('checked', false);
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



