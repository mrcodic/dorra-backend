$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var dt_user_table = $('.industry-list-table').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    orderable: false,
    ajax: {
        url: industriesDataUrl,
        type: 'GET',
        data: function (d) {
            d.search_value = $('#search-industry-form').val();
            d.created_at = $('.filter-date').val();

            return d;
        }
    },
    columns: [
        {
            data: null, defaultContent: "", orderable: false,
            render: function (data, type, row) {
                return row?.action?.can_delete
                    ? `<input type="checkbox" name="ids[]" class="category-checkbox" value="${row.id}">`
                    : '';
            },
        },
        {data: 'name', orderable: false},
        {data: 'added_date', orderable: false},

        {
            data: 'id',
            orderable: false,
            render: function (data, type, row, meta) {
                const canShow = row?.action?.can_show ?? false;
                const canEdit = row?.action?.can_edit ?? false;
                const canDelete = row?.action?.can_delete ?? false;
                const btns = [];
                if (canShow) {
                    btns.push(`<a href="#" class="view-details"
                                   data-bs-toggle="modal"
                                     data-bs-target="#showIndustryModal"
                                     data-id="${data}"
                                     data-name_ar="${row.name_ar}"
                                     data-name_en="${row.name_en}"
                                     data-products="${row.no_of_products}"
                                     data-categories="${row.categories_count}"
                                     data-templates="${row.no_of_templates}"
                                     data-showdate="${row.show_date}">
                                     <i data-feather="eye"></i>
                                </a>`);
                }
                if (canEdit) {
                    btns.push(`<a href="#" class="edit-details"
                           data-bs-toggle="modal"
                           data-bs-target="#editIndustryModal"
                             data-id="${data}"
                             data-name_ar="${row.name_ar}"
                             data-name_en="${row.name_en}"
                             data-products="${row.no_of_products}"
                             data-showdate="${row.show_date}">
                            <i data-feather="edit-3"></i>
                       </a>`);
                }
                if (canDelete) {
                    btns.push(`<a href="#" class="text-danger open-delete-tag-modal"
   data-id="${data}"
   data-name="${row.name}"
   data-action="/industries/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteIndustryModal">
   <i data-feather="trash-2"></i>
</a>
`);
                }

                if (!btns.length) return '';
                return `<div class="d-flex gap-1 align-items-center">${btns.join('')}</div>`;
            }
        }
    ],
    order: [[1, 'asc']],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mb-2"' +
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
        sLengthMenu: 'Show _MENU_',
        search: '',
        searchPlaceholder: 'Search..',
        paginate: {
            previous: '&nbsp;',
            next: '&nbsp;'
        }
    }
});
$('#clear-search').on('click', function () {
    $('#search-industry-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
let searchTimeout;
$('#search-industry-form').on('keyup', function () {
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

    $(document).on('click', '.view-details', function (e) {
        const tagNameAR = $(this).data('name_ar');
        const tagNameEn = $(this).data('name_en');
        const products = $(this).data('products');
        const templates = $(this).data('templates');
        const categories = $(this).data('categories');
        const addedDate = $(this).data('showdate');
        const id = $(this).data('id');
        // Populate modal
        $('#showIndustryModal #tag-name-ar').val(tagNameAR);
        $('#showIndustryModal #tag-name-en').val(tagNameEn);
        $('#showIndustryModal #tag-products').val(products);
        $('#showIndustryModal #tag-categories').val(categories);
        $('#showIndustryModal #tag-templates').val(templates);
        $('#showIndustryModal #tag-date').val(addedDate);
        $('#showIndustryModal #tag-id').val(id);


        // Show modal
        $('#showIndustryModal').modal('show');

    });

    $(document).on('click', '.edit-details', function (e) {
        const tagNameAR = $(this).data('name_ar');
        const tagNameEn = $(this).data('name_en');
        const tagId = $(this).data('id');

        // Populate modal
        $('#editIndustryModal #edit-tag-name-ar').val(tagNameAR);
        $('#editIndustryModal #edit-tag-name-en').val(tagNameEn);
        $('#editIndustryModal #edit-tag-id').val(tagId);

        // Show modal
        $('#editIndustryModal').modal('show');

    });

    $('#editButton').on('click', function () {
        var nameEN = $('#tag-name-en').val();
        var nameAR = $('#tag-name-ar').val();
        var id = $('#tag-id').val();


        $('#edit-tag-name-en').val(nameEN);
        $('#edit-tag-name-ar').val(nameAR);
        $('#edit-tag-id').val(id);
        $('#editIndustryModal').modal('show');

    });

    $('#editIndustryForm').on('submit', function (e) {
        e.preventDefault();
        var tagId = $(this).find('#edit-tag-id').val();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        $.ajax({
            url: `industries/${tagId}`,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {

                Toastify({
                    text: "Industry updated successfully!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true
                }).showToast();
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                // Close modal
                $('#editIndustryModal').modal('hide');
                $('#showIndustryModal').modal('hide');
                $('.industry-list-table').DataTable().ajax.reload(null, false);


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
                            close: true
                        }).showToast();
                    }
                }
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
            }
        });
    });


    $(document).on("click", ".open-delete-tag-modal", function () {
        const tagId = $(this).data("id");


        $("#deleteIndustryForm").data("id", tagId);
    });

    $(document).on('submit', '#deleteIndustryForm', function (e) {
        e.preventDefault();
        const tagId = $(this).data("id");

        $.ajax({
            url: `/industries/${tagId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteIndustryModal").modal("hide");

                Toastify({
                    text: "Industry deleted successfully!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".industry-list-table").DataTable().ajax.reload(null, false);


            },
            error: function () {
                $("#deleteIndustryModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                $(".industry-list-table").DataTable().ajax.reload(null, false);

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
            url: "industries/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteIndustriesModal").modal("hide");
                Toastify({
                    text: "Selected industries deleted successfully!",
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
                $(".industry-list-table").DataTable().ajax.reload(null, false);

            },
            error: function () {
                $("#deleteIndustriesModal").modal("hide");
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
                $('.tag-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".category-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });


});
