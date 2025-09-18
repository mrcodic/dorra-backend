    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var dt_user_table = $('.flag-list-table').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    orderable:false,
    ajax: {
        url: flagsDataUrl,
        type: 'GET',
        data: function (d) {
            d.search_value = $('#search-tag-form').val();
            d.created_at = $('.filter-date').val();

            return d;
        }
    },
    columns: [
        { data: null, defaultContent: "", orderable: false, render: function (data, type, row, meta) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            } },
        {data: 'name', orderable: false},
        {data: 'no_of_products', orderable: false},
        {data: 'no_of_templates', orderable: false},
        {data: 'added_date', orderable: false},

        {
            data: 'id',
            orderable: false,
            render: function (data, type, row, meta) {
                return `
        <div class="d-flex gap-1">
                                <a href="#" class="view-details"
                                   data-bs-toggle="modal"
                                     data-bs-target="#showFlagModal"
                                     data-id="${data}"
                                     data-name_ar="${row.name_ar}"
                                     data-name_en="${row.name_en}"
                                     data-products="${row.no_of_products}"
                                     data-templates="${row.no_of_templates}"
                                     data-showdate="${row.show_date}">
                                     <i data-feather="eye"></i>
                                </a>

                          <a href="#" class="edit-details"
                           data-bs-toggle="modal"
                           data-bs-target="#editFlagModal"
                             data-id="${data}"
                             data-name_ar="${row.name_ar}"
                             data-name_en="${row.name_en}"
                             data-products="${row.no_of_products}"
                             data-showdate="${row.show_date}">
                            <i data-feather="edit-3"></i>
                       </a>

      <a href="#" class="text-danger open-delete-tag-modal"
   data-id="${data}"
   data-name="${row.name}"
   data-action="/flags/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteFlagModal">
   <i data-feather="trash-2"></i>
</a>

          </div>
        `;
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
        $('#search-tag-form').val('');  // clear input
        dt_user_table.search('').draw();  // reset DataTable search
    });
    let searchTimeout;
    $('#search-tag-form').on('keyup', function () {
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
    $('#addFlagForm').on('submit', function (e) {
        e.preventDefault();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'), // dynamic action URL
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Toastify({
                    text: "Flag added successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true
                }).showToast();

                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                $('#addFlagForm')[0].reset();
                $('#addFlagModal').modal('hide');


                $('.flag-list-table').DataTable().ajax.reload(); // reload your table
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

    $(document).on('click', '.view-details', function (e) {
        const tagNameAR = $(this).data('name_ar');
        const tagNameEn = $(this).data('name_en');
        const products = $(this).data('products');
        const templates = $(this).data('templates');
        const addedDate = $(this).data('showdate');
        const id = $(this).data('id');
        // Populate modal
        $('#showFlagModal #tag-name-ar').val(tagNameAR);
        $('#showFlagModal #tag-name-en').val(tagNameEn);
        $('#showFlagModal #tag-products').val(products);
        $('#showFlagModal #tag-templates').val(templates);
        $('#showFlagModal #tag-date').val(addedDate);
        $('#showFlagModal #tag-id').val(id);


        // Show modal
        $('#showFlagModal').modal('show');

    });

        $(document).on('click', '.edit-details', function (e) {
        const tagNameAR = $(this).data('name_ar');
        const tagNameEn = $(this).data('name_en');
        const tagId = $(this).data('id');

        // Populate modal
        $('#editFlagModal #edit-tag-name-ar').val(tagNameAR);
        $('#editFlagModal #edit-tag-name-en').val(tagNameEn);
        $('#editFlagModal #edit-tag-id').val(tagId);

        // Show modal
        $('#editFlagModal').modal('show');

    });

    $('#editButton').on('click', function () {
        var nameEN = $('#tag-name-en').val();
        var nameAR = $('#tag-name-ar').val();
        var id = $('#tag-id').val();


        $('#edit-tag-name-en').val(nameEN);
        $('#edit-tag-name-ar').val(nameAR);
        $('#edit-tag-id').val(id);
        $('#editFlagModal').modal('show');

    });

    $('#editFlagForm').on('submit', function (e) {
        e.preventDefault();
        var tagId = $(this).find('#edit-tag-id').val();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        $.ajax({
            url: `flags/${tagId}`,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {

                Toastify({
                    text: "Flag updated successfully!",
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
                $('#editFlagModal').modal('hide');
                $('#showFlagModal').modal('hide');
                $('.flag-list-table').DataTable().ajax.reload(null,false);



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
        $("#deleteFlagForm").data("id", tagId);
    });

    $(document).on('submit', '#deleteFlagForm', function (e) {
        e.preventDefault();
        const tagId = $(this).data("id");

        $.ajax({
            url: `/flags/${tagId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteFlagModal").modal("hide");

                Toastify({
                    text: "Flag deleted successfully!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".flag-list-table").DataTable().ajax.reload(null, false);


            },
            error: function () {
                $("#deleteFlagModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                $(".flag-list-table").DataTable().ajax.reload(null, false);

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
            url: "flags/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteFlagsModal").modal("hide");
                Toastify({
                    text: "Selected tags deleted successfully!",
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
                $(".flag-list-table").DataTable().ajax.reload(null, false);

            },
            error: function () {
                $("#deleteFlagsModal").modal("hide");
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
