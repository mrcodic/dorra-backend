$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var dt_user_table = $('.sub-industry-list-table').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    orderable: false,
    ajax: {
        url: subIndustriesDataUrl,
        type: 'GET',
        data: function (d) {
            d.search_value = $('#search-sub-industry-form').val();
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
        {data: 'industry',
            render:function (data, type, row) {
                return row.parent?.name[locale] ?? '';
            },
            orderable: false},
        {data: 'templates_count', orderable: false},
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
                                     data-bs-target="#showSubIndustryModal"
                                     data-id="${data}"
                                     data-parent_name="${row.parent.name[locale]}"
                                     data-name_ar="${row.name_ar}"
                                     data-name_en="${row.name_en}"
                                     data-templates="${row.templates_count}"
                                     data-showdate="${row.show_date}">
                                     <i data-feather="eye"></i>
                                </a>`);
                }
                if (canEdit) {
                    btns.push(`<a href="#" class="edit-details"
                           data-bs-toggle="modal"
                           data-bs-target="#editSubIndustryModal"
                             data-id="${data}"
                             data-name_ar="${row.name_ar}"
                             data-name_en="${row.name_en}"
                             data-parent_id="${row.parent.id}"
                            >
                            <i data-feather="edit-3"></i>
                       </a>`);
                }
                if (canDelete) {
                    btns.push(`<a href="#" class="text-danger open-delete-tag-modal"
   data-id="${data}"
   data-name="${row.name}"
   data-action="/industries/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteSubIndustryModal">
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
    $('#search-sub-industry-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
let searchTimeout;
$('#search-sub-industry-form').on('keyup', function () {
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
        const subIndustryNameAR = $(this).data('name_ar');
        const parentName =  $(this).data('parent_name');
        const subIndustryNameEn = $(this).data('name_en');
        const templates = $(this).data('templates');
        const addedDate = $(this).data('showdate');
        const id = $(this).data('id');
        // Populate modal
        $('#showSubIndustryModal #sub-industry-name-ar').val(subIndustryNameAR);
        $('#showSubIndustryModal #sub-industry-name-en').val(subIndustryNameEn);
        $('#showSubIndustryModal #sub-industry-templates').val(templates);
        $('#showSubIndustryModal #sub-industry-date').val(addedDate);
        $('#showSubIndustryModal #parent-name').val(parentName);
        $('#showSubIndustryModal #sub-industry-id').val(id);


        // Show modal
        $('#showSubIndustryModal').modal('show');

    });

    $(document).on('click', '.edit-details', function (e) {
        const tagNameAR = $(this).data('name_ar');
        const tagNameEn = $(this).data('name_en');
        const tagId = $(this).data('id');
        console.log(tagId)
        const parentId = $(this).data('parent_id');


        // Populate modal
        $('#editSubIndustryModal #edit-tag-name-ar').val(tagNameAR);
        $('#editSubIndustryModal #edit-tag-name-en').val(tagNameEn);
        $('#editSubIndustryModal #edit-tag-id').val(tagId);
        $('#editSubIndustryModal #parent-id').val(parentId);
        $('#editSubIndustryModal #editSubIndustryForm').action(`sub-industries/${tagId}`);

        // Show modal
        $('#editSubIndustryModal').modal('show');

    });



    $(document).on("click", ".open-delete-tag-modal", function () {
        const tagId = $(this).data("id");


        $("#deleteSubIndustryForm").data("id", tagId);
    });

    $(document).on('submit', '#deleteSubIndustryForm', function (e) {
        e.preventDefault();
        const tagId = $(this).data("id");

        $.ajax({
            url: `/industries/${tagId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteSubIndustryModal").modal("hide");

                Toastify({
                    text: "Sub Industry deleted successfully!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".sub-industry-list-table").DataTable().ajax.reload(null, false);


            },
            error: function () {
                $("#deleteSubIndustryModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                $(".sub-industry-list-table").DataTable().ajax.reload(null, false);

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
            url: "sub-industries/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteSubIndustriesModal").modal("hide");
                Toastify({
                    text: "Selected sub industries deleted successfully!",
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
                $(".sub-industry-list-table").DataTable().ajax.reload(null, false);

            },
            error: function () {
                $("#deleteSubIndustriesModal").modal("hide");
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
