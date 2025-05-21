$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

const dt_user_table = $(".code-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    ajax: {
        url: discountCodeDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-code-form').val();
            d.created_at = $('.filter-date').val();
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
        { data: "code" },
        { data: "type" },
        { data: "max_usage" },
        { data: "used" },
        { data: "expired_at" },
        {
            data: "id",
            orderable: false,
            render: function (data, type, row) {
                return `
                    <div class="d-flex gap-1">
                        <a href="#" class="" data-bs-toggle="modal"
                        data-bs-target="#showCodeModal"
                        data-action="/discount-codes/${data}"
                        data-used="${row.used}"
                        data-type="${row.type}"
                        data-prefix="${row.prefix}"
                        data-value="${row.value}"
                        data-expired_at="${row.expired_at}"
                        data-usage="${row.max_usage}"
                        data-scope="${row.scope}"
                         data-categories='${JSON.stringify(row.categories)}'
                         data-products='${JSON.stringify(row.products)}'
                          ><i data-feather="eye"></i>
                        </a>
                        <a href="#" class="" data-bs-toggle="modal"
                        data-bs-target="#editCodeModal"
                         data-action="/discount-codes/${data}"
                        data-used="${row.used}"
                        data-type="${row.type}"
                        data-prefix="${row.prefix}"
                        data-value="${row.value}"
                        data-expired_at="${row.expired_at}"
                        data-usage="${row.max_usage}"
                        data-scope="${row.scope}"
                         data-categories='${JSON.stringify(row.categories)}'
                         data-products='${JSON.stringify(row.products)}'
                        ><i data-feather="edit-3"></i></a>
                        <a href="#" class="text-danger open-delete-code-modal"
                           data-id="${data}"
                           data-name="${row.name}"
                           data-action="/discount-codes/${data}"
                           data-bs-toggle="modal"
                           data-bs-target="#deleteCodeModal">
                           <i data-feather="trash-2"></i>
                        </a>
                    </div>
                `;
            },
        }
    ],
    order: [[1, "asc"]],
    dom: '<"row d-none"<"col"B>>t<"d-flex justify-content-between align-items-center px-2 pb-1"<"text-muted"i><"pagination"p>>',
    buttons: [
        {
            extend: 'excel',
            text: 'Export',
            exportOptions: { columns: [1, 2, 3, 4, 5] },
            className: 'd-none',
            init: function (api, node) {
                $('#export-excel').on('click', function () {
                    $(node).trigger('click');
                });
            }
        }
    ],
    drawCallback: function () {
        feather.replace();
        $('#select-all-checkbox').prop('checked', false);
        $('#bulk-delete-container').hide();
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

// Debounced search input
let searchTimeout;
$('#search-code-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => dt_user_table.draw(), 300);
});

// Date filter
$('.filter-date').on('change', function () {
    dt_user_table.draw();
});

// Bulk Delete Logic
function updateBulkDeleteVisibility() {
    const selectedCount = $('.category-checkbox:checked').length;
    $('#selected-count-text').text(`${selectedCount} Discount Code${selectedCount > 1 ? 's are' : ' is'} selected`);
    $('#bulk-delete-container').toggle(selectedCount > 0);
}

// Individual checkbox change
$(document).on('change', '.category-checkbox', function () {
    updateBulkDeleteVisibility();
    const total = $('.category-checkbox').length;
    const checked = $('.category-checkbox:checked').length;
    $('#select-all-checkbox').prop('checked', total === checked);
});

// Select All
$('#select-all-checkbox').on('change', function () {
    const isChecked = $(this).is(':checked');
    $('.category-checkbox').prop('checked', isChecked).trigger('change');
});

// Delete single code
$(document).on("click", ".open-delete-code-modal", function () {
    const codeId = $(this).data("id");
    $("#deleteCodeForm").data("id", codeId);
});

$(document).on("submit", "#deleteCodeForm", function (e) {
    e.preventDefault();
    const codeId = $(this).data("id");

    $.ajax({
        url: `/discount-codes/${codeId}`,
        method: "DELETE",
        success: function () {
            $("#deleteCodeModal").modal("hide");
            Toastify({
                text: "Code deleted successfully!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28C76F",
                close: true
            }).showToast();
            dt_user_table.ajax.reload(null, false);
        },
        error: function () {
            $("#deleteCodeModal").modal("hide");
            Toastify({
                text: "Something went wrong!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EA5455",
                close: true
            }).showToast();
        }
    });
});

// Bulk delete
$(document).on("submit", "#bulk-delete-form", function (e) {
    e.preventDefault();
    const selectedIds = $(".category-checkbox:checked").map(function () {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) return;

    $.ajax({
        url: "discount-codes/bulk-delete",
        method: "POST",
        data: {
            ids: selectedIds,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function () {
            $("#deleteCodesModal").modal("hide");
            Toastify({
                text: "Selected codes deleted successfully!",
                duration: 1500,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745",
                close: true,
            }).showToast();
            $('#bulk-delete-container').hide();
            dt_user_table.ajax.reload(null, false);
        },
        error: function () {
            $("#deleteCodesModal").modal("hide");
            Toastify({
                text: "Something went wrong!",
                duration: 1500,
                gravity: "top",
                position: "right",
                backgroundColor: "#EA5455",
                close: true,
            }).showToast();
            $('#bulk-delete-container').hide();
        }
    });
});

$('#showCodeModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget); // the eye icon trigger

    const type = button.data('type');
    const prefix = button.data('prefix');
    const value = button.data('value');
    const usage = button.data('usage');
    const scope = button.data('scope');
    const expiredDate = button.data('expired_at');
    const categories = button.data('categories');
    const products = button.data('products');
    const used = button.data('used');
    const action = button.data('action');
    // Set read-only value
    $(this).find('#usedCount').val(used || 0);
    // Store values in Edit button's data attributes
    const editBtn = $('#editDiscountBtn');
    editBtn.data('type', type);
    editBtn.data('prefix', prefix);
    editBtn.data('value', value);
    editBtn.data('usage', usage);
    editBtn.data('scope', scope);
    editBtn.data('expired_at', expiredDate);
    editBtn.data('categories', categories);
    editBtn.data('products', products);
    editBtn.data('used', used);
    editBtn.data('action', action);
});

$('#editCodeModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);

    // Set form action URL
    const actionUrl = button.data('action');
    $('#editDiscountForm').attr('action', actionUrl);

    const scope = button.data('scope') || '';

    $('#discountType').val(button.data('type'));
    $('#prefix').val(button.data('prefix') || '');
    $('#discountValue').val(button.data('value') || '');
    $('#restrictions').val(button.data('usage') || '');
    $('#expiryDate').val(button.data('expired_at') || '');
    $('#scopeType').val(scope);

    const $productContainer = $('#selectedProducts').empty();
    const $categoryContainer = $('#selectedCategories').empty();

    // Show/hide based on scope
    if (scope === 'Product') {
        $('#selectedProducts').closest('.form-group').show();
        $('#selectedCategories').closest('.form-group').hide();
    } else if (scope === 'Category') {
        $('#selectedProducts').closest('.form-group').hide();
        $('#selectedCategories').closest('.form-group').show();
    } else {
        // Show both or hide both if scope is undefined or something else
        $('#selectedProducts').closest('.form-group').hide();
        $('#selectedCategories').closest('.form-group').hide();
    }

    try {
        const products = JSON.parse(button.attr('data-products') || '[]');
        products.forEach(product => {
            $productContainer.append(`<span class="badge bg-primary">${product.name[locale]}</span>`);
        });

        const categories = JSON.parse(button.attr('data-categories') || '[]');
        categories.forEach(category => {
            $categoryContainer.append(`<span class="badge bg-primary">${category.name[locale]}</span>`);
        });
    } catch (err) {
        console.error("Failed to parse products/categories JSON", err);
    }
});



